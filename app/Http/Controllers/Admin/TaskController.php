<?php

namespace App\Http\Controllers\Admin;

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
    public function index(Request $request)
    {
        $query = Task::with(['assignee', 'project', 'originalAssignee']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }
        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        $tasks = $query->latest()->paginate(15)->withQueryString();
        $employees = User::where('is_admin', false)->orderBy('name')->get();

        return view('admin.tasks.index', compact('tasks', 'employees'));
    }

    public function create()
    {
        $employees = User::where('is_admin', false)->orderBy('name')->get();

        return view('admin.tasks.create', compact('employees'));
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
            'details' => 'Task created.',
        ]);

        if ($task->status === 'completed') {
            $this->notifyAdminsTaskCompleted($task);
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

        $task->assignee->notify(new TaskNotification(
            'New Task Assigned',
            "You have been assigned to task: {$task->title}",
            route('employee.tasks.show', $task)
        ));

        return redirect()->route('admin.tasks.index')->with('success', 'Task created successfully.');
    }

    public function show(Task $task)
    {
        $task->load(['project', 'assignee', 'originalAssignee', 'creator', 'comments.user', 'comments.replies.user', 'attachments.uploader', 'histories.user']);
        $employees = User::where('is_admin', false)->orderBy('name')->get();

        return view('admin.tasks.show', compact('task', 'employees'));
    }

    public function edit(Task $task)
    {
        $employees = User::where('is_admin', false)->orderBy('name')->get();

        return view('admin.tasks.edit', compact('task', 'employees'));
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'due_days' => 'required|integer|min:1|max:3650',
            'status' => 'required|in:'.implode(',', config('constants.task_statuses')),
            'priority' => 'required|in:'.implode(',', config('constants.task_priorities')),
        ]);

        $start = Carbon::parse($validated['start_date']);
        $dueDays = (int) $validated['due_days'];
        unset($validated['due_days']);
        $validated['due_date'] = $start->copy()->addDays($dueDays)->toDateString();

        if ($task->originally_assigned_to === null && $validated['assigned_to']) {
            $validated['originally_assigned_to'] = $validated['assigned_to'];
        }

        $oldStatus = $task->status;
        $changes = [];
        foreach ($validated as $key => $value) {
            if ($task->{$key} != $value) {
                $changes[] = ucfirst(str_replace('_', ' ', $key))." changed from '{$task->{$key}}' to '{$value}'";
            }
        }

        $oldAssignee = $task->assigned_to;
        $task->update($validated);

        if (! empty($changes)) {
            TaskHistory::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'action' => 'updated',
                'details' => implode('; ', $changes),
            ]);
        }

        if ($oldAssignee != $task->assigned_to && $task->assigned_to) {
            $task->assignee->notify(new TaskNotification(
                'Task Reassigned',
                "Task '{$task->title}' has been assigned to you.",
                route('employee.tasks.show', $task)
            ));

            TaskHistory::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'action' => 'reassigned',
                'details' => 'Task reassigned to '.$task->assignee->name,
            ]);
        }

        if ($oldStatus !== 'completed' && $task->status === 'completed') {
            $this->notifyAdminsTaskCompleted($task);
        }

        return redirect()->route('admin.tasks.show', $task)->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        $task->delete();

        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'details' => 'Task soft deleted.',
        ]);

        return redirect()->route('admin.tasks.index')->with('success', 'Task deleted successfully.');
    }

    public function kanban(Request $request)
    {
        $employeeId = $request->get('employee_id');
        $query = Task::with(['assignee', 'project', 'originalAssignee']);

        if ($employeeId) {
            $query->where(function ($q) use ($employeeId) {
                $q->where('assigned_to', $employeeId)
                    ->orWhere('originally_assigned_to', $employeeId);
            });
        }

        $tasks = $query->get()->groupBy('status');
        $employees = User::where('is_admin', false)->orderBy('name')->get();
        $statuses = config('constants.task_statuses');

        return view('admin.tasks.kanban', compact('tasks', 'employees', 'statuses', 'employeeId'));
    }

    public function updateStatus(Request $request, Task $task)
    {
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
            $this->notifyAdminsTaskCompleted($task);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Task status updated.');
    }

    public function addComment(Request $request, Task $task)
    {
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

    public function reassign(Request $request, Task $task)
    {
        $request->validate(['assigned_to' => 'required|exists:users,id']);

        $oldAssignee = $task->assignee ? $task->assignee->name : 'Unassigned';
        $updates = ['assigned_to' => $request->assigned_to];
        if ($task->originally_assigned_to === null) {
            $updates['originally_assigned_to'] = $task->assigned_to ?? $request->assigned_to;
        }
        $task->update($updates);
        $newAssignee = $task->fresh()->assignee;

        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'action' => 'reassigned',
            'details' => "Reassigned from {$oldAssignee} to {$newAssignee->name}",
        ]);

        $newAssignee->notify(new TaskNotification(
            'Task Assigned',
            "Task '{$task->title}' has been assigned to you.",
            route('employee.tasks.show', $task)
        ));

        return back()->with('success', 'Task reassigned successfully.');
    }

    protected function notifyAdminsTaskCompleted(Task $task): void
    {
        $actor = auth()->user();
        $by = $actor ? $actor->name : 'System';

        foreach (User::where('is_admin', true)->get() as $admin) {
            $admin->notify(new TaskNotification(
                'Task Completed',
                "Task '{$task->title}' was marked completed by {$by}.",
                route('admin.tasks.show', $task)
            ));
        }
    }
}
