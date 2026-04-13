<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskComment;
use App\Models\TaskHistory;
use App\Models\User;
use App\Notifications\TaskNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    protected function tasksVisibleToEmployee()
    {
        $uid = auth()->id();

        return Task::query()->where(function ($q) use ($uid) {
            $q->where('assigned_to', $uid)->orWhere('created_by', $uid);
        });
    }

    protected function employeeCanViewTask(Task $task): bool
    {
        $uid = (int) auth()->id();

        return (int) $task->assigned_to === $uid || (int) $task->created_by === $uid;
    }

    protected function employeeCanActOnTask(Task $task): bool
    {
        return (int) $task->assigned_to === (int) auth()->id();
    }

    public function index(Request $request)
    {
        $query = $this->tasksVisibleToEmployee()->with(['originalAssignee', 'assignee']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tasks = $query->latest()->paginate(15)->withQueryString();

        return view('employee.tasks.index', compact('tasks'));
    }

    public function create()
    {
        $assignableUsers = User::assignableForTasks();

        return view('employee.tasks.create', compact('assignableUsers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'due_days' => 'required|integer|min:1|max:3650',
            'status' => 'required|in:'.implode(',', config('constants.task_statuses')),
            'priority' => 'required|in:'.implode(',', config('constants.task_priorities')),
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $start = Carbon::parse($validated['start_date']);
        $dueDays = (int) $validated['due_days'];
        unset($validated['due_days']);

        $validated['due_date'] = $start->copy()->addDays($dueDays)->toDateString();
        $validated['project_id'] = null;
        $validated['type'] = 'feature';
        $validated['category'] = 'other';
        $validated['originally_assigned_to'] = $validated['assigned_to'];
        $validated['cancellation_reason'] = null;
        $validated['created_by'] = auth()->id();

        $task = Task::create($validated);

        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'action' => 'created',
            'details' => 'Task created by employee.',
        ]);

        if ($task->status === 'completed') {
            foreach (User::where('is_admin', true)->get() as $admin) {
                $admin->notify(new TaskNotification(
                    'Task Completed',
                    "Task '{$task->title}' was marked completed by ".auth()->user()->name.'.',
                    route('admin.tasks.show', $task)
                ));
            }
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $originalName = $file->getClientOriginalName();
                $mimeType = $file->getClientMimeType();
                $size = $file->getSize();
                $filename = time().'_'.$originalName;
                $file->move(public_path('uploads/attachments'), $filename);
                TaskAttachment::create([
                    'task_id' => $task->id,
                    'uploaded_by' => auth()->id(),
                    'filename' => $filename,
                    'original_name' => $originalName,
                    'mime_type' => $mimeType,
                    'size' => $size,
                ]);
            }
        }

        if ($task->assignee) {
            $task->assignee->notify(new TaskNotification(
                'New Task Assigned',
                "You have been assigned to task: {$task->title}",
                $task->urlForAssignee($task->assignee)
            ));
        }

        return redirect()->route('employee.tasks.index')->with('success', 'Task created successfully.');
    }

    public function show(Task $task)
    {
        if (! $this->employeeCanViewTask($task)) {
            abort(403);
        }

        $task->load(['project', 'assignee', 'originalAssignee', 'creator', 'comments.user', 'comments.replies.user', 'attachments.uploader', 'histories.user']);

        return view('employee.tasks.show', compact('task'));
    }

    public function updateStatus(Request $request, Task $task)
    {
        if (! $this->employeeCanActOnTask($task)) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:'.implode(',', config('constants.task_statuses')),
            'cancellation_reason' => 'required_if:status,cancelled|nullable|string|max:2000',
        ]);

        $oldStatus = $task->status;
        $newStatus = $request->status;

        $update = ['status' => $newStatus];
        if ($newStatus === 'cancelled') {
            $update['cancellation_reason'] = $request->cancellation_reason;
        } else {
            $update['cancellation_reason'] = null;
        }

        $task->update($update);

        $details = "Status changed from '{$oldStatus}' to '{$newStatus}'";
        if ($newStatus === 'cancelled' && $request->filled('cancellation_reason')) {
            $details .= '. Reason: '.$request->cancellation_reason;
        }

        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'action' => 'status_changed',
            'details' => $details,
        ]);

        if ($oldStatus !== 'completed' && $newStatus === 'completed') {
            foreach (User::where('is_admin', true)->get() as $admin) {
                $admin->notify(new TaskNotification(
                    'Task Completed',
                    "Task '{$task->title}' was marked completed by ".auth()->user()->name.'.',
                    route('admin.tasks.show', $task)
                ));
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Task status updated.');
    }

    public function addComment(Request $request, Task $task)
    {
        if (! $this->employeeCanActOnTask($task)) {
            abort(403);
        }

        $request->validate(['comment' => 'required|string']);

        TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'comment' => $request->comment,
            'parent_id' => $request->parent_id,
        ]);

        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'action' => 'commented',
            'details' => 'Added a comment.',
        ]);

        return back()->with('success', 'Comment added.');
    }

    public function addAttachment(Request $request, Task $task)
    {
        if (! $this->employeeCanActOnTask($task)) {
            abort(403);
        }

        $request->validate(['attachment' => 'required|file|max:10240']);

        $file = $request->file('attachment');
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getClientMimeType();
        $size = $file->getSize();
        $filename = time().'_'.$originalName;
        $file->move(public_path('uploads/attachments'), $filename);

        TaskAttachment::create([
            'task_id' => $task->id,
            'uploaded_by' => auth()->id(),
            'filename' => $filename,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'size' => $size,
        ]);

        return back()->with('success', 'Attachment uploaded.');
    }

    public function kanban()
    {
        $tasks = $this->tasksVisibleToEmployee()
            ->with(['project', 'originalAssignee'])
            ->get()
            ->groupBy('status');

        $statuses = config('constants.task_statuses');

        return view('employee.tasks.kanban', compact('tasks', 'statuses'));
    }
}
