<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\TaskHistory;
use App\Models\TaskAttachment;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::where('assigned_to', auth()->id())->with('project');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tasks = $query->latest()->paginate(15)->withQueryString();
        return view('employee.tasks.index', compact('tasks'));
    }

    public function show(Task $task)
    {
        if ($task->assigned_to !== auth()->id()) {
            abort(403);
        }

        $task->load(['project', 'assignee', 'creator', 'comments.user', 'comments.replies.user', 'attachments.uploader', 'histories.user']);
        return view('employee.tasks.show', compact('task'));
    }

    public function updateStatus(Request $request, Task $task)
    {
        if ($task->assigned_to !== auth()->id()) {
            abort(403);
        }

        $request->validate(['status' => 'required|in:' . implode(',', config('constants.task_statuses'))]);

        $oldStatus = $task->status;
        $task->update(['status' => $request->status]);

        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'action' => 'status_changed',
            'details' => "Status changed from '{$oldStatus}' to '{$request->status}'",
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Task status updated.');
    }

    public function addComment(Request $request, Task $task)
    {
        if ($task->assigned_to !== auth()->id()) {
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
        if ($task->assigned_to !== auth()->id()) {
            abort(403);
        }

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

    public function kanban()
    {
        $tasks = Task::where('assigned_to', auth()->id())
            ->with('project')
            ->get()
            ->groupBy('status');

        $statuses = config('constants.task_statuses');

        return view('employee.tasks.kanban', compact('tasks', 'statuses'));
    }
}
