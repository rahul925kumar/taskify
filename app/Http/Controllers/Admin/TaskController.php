<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskComment;
use App\Models\TaskHistory;
use App\Models\User;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['assignee', 'project']);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
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
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $tasks = $query->latest()->paginate(15)->withQueryString();
        $projects = Project::orderBy('name')->get();
        $employees = User::where('is_admin', false)->orderBy('name')->get();

        return view('admin.tasks.index', compact('tasks', 'projects', 'employees'));
    }

    public function create()
    {
        $projects = Project::orderBy('name')->get();
        $employees = User::where('is_admin', false)->orderBy('name')->get();
        return view('admin.tasks.create', compact('projects', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'required|exists:projects,id',
            'assigned_to' => 'nullable|exists:users,id',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:' . implode(',', config('constants.task_statuses')),
            'priority' => 'required|in:' . implode(',', config('constants.task_priorities')),
            'type' => 'required|in:' . implode(',', config('constants.task_types')),
            'category' => 'required|in:' . implode(',', config('constants.task_categories')),
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $validated['created_by'] = auth()->id();
        $task = Task::create($validated);

        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'action' => 'created',
            'details' => 'Task created.',
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $originalName = $file->getClientOriginalName();
                $mimeType = $file->getClientMimeType();
                $size = $file->getSize();
                $filename = time() . '_' . $originalName;
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

        if ($task->assigned_to) {
            $task->assignee->notify(new \App\Notifications\TaskNotification(
                'New Task Assigned',
                "You have been assigned to task: {$task->title}",
                route('employee.tasks.show', $task)
            ));
        }

        return redirect()->route('admin.tasks.index')->with('success', 'Task created successfully.');
    }

    public function show(Task $task)
    {
        $task->load(['project', 'assignee', 'creator', 'comments.user', 'comments.replies.user', 'attachments.uploader', 'histories.user']);
        $employees = User::where('is_admin', false)->orderBy('name')->get();
        return view('admin.tasks.show', compact('task', 'employees'));
    }

    public function edit(Task $task)
    {
        $projects = Project::orderBy('name')->get();
        $employees = User::where('is_admin', false)->orderBy('name')->get();
        return view('admin.tasks.edit', compact('task', 'projects', 'employees'));
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'required|exists:projects,id',
            'assigned_to' => 'nullable|exists:users,id',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:' . implode(',', config('constants.task_statuses')),
            'priority' => 'required|in:' . implode(',', config('constants.task_priorities')),
            'type' => 'required|in:' . implode(',', config('constants.task_types')),
            'category' => 'required|in:' . implode(',', config('constants.task_categories')),
        ]);

        $changes = [];
        foreach ($validated as $key => $value) {
            if ($task->{$key} != $value) {
                $changes[] = ucfirst(str_replace('_', ' ', $key)) . " changed from '{$task->{$key}}' to '{$value}'";
            }
        }

        $oldAssignee = $task->assigned_to;
        $task->update($validated);

        if (!empty($changes)) {
            TaskHistory::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'action' => 'updated',
                'details' => implode('; ', $changes),
            ]);
        }

        if ($oldAssignee != $task->assigned_to && $task->assigned_to) {
            $task->assignee->notify(new \App\Notifications\TaskNotification(
                'Task Reassigned',
                "Task '{$task->title}' has been assigned to you.",
                route('employee.tasks.show', $task)
            ));

            TaskHistory::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'action' => 'reassigned',
                'details' => 'Task reassigned to ' . $task->assignee->name,
            ]);
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
        $projectId = $request->get('project_id');
        $query = Task::with(['assignee', 'project']);

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $tasks = $query->get()->groupBy('status');
        $projects = Project::orderBy('name')->get();
        $statuses = config('constants.task_statuses');

        return view('admin.tasks.kanban', compact('tasks', 'projects', 'statuses', 'projectId'));
    }

    public function updateStatus(Request $request, Task $task)
    {
        $request->validate(['status' => 'required|in:' . implode(',', config('constants.task_statuses'))]);

        $oldStatus = $task->status;
        $task->update(['status' => $request->status]);

        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'action' => 'status_changed',
            'details' => "Status changed from '{$oldStatus}' to '{$request->status}'",
        ]);

        return response()->json(['success' => true]);
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
        $filename = time() . '_' . $originalName;
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
        $task->update(['assigned_to' => $request->assigned_to]);
        $newAssignee = $task->fresh()->assignee;

        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'action' => 'reassigned',
            'details' => "Reassigned from {$oldAssignee} to {$newAssignee->name}",
        ]);

        $newAssignee->notify(new \App\Notifications\TaskNotification(
            'Task Assigned',
            "Task '{$task->title}' has been assigned to you.",
            route('employee.tasks.show', $task)
        ));

        return back()->with('success', 'Task reassigned successfully.');
    }
}
