<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    use AuthorizesRequests;

    public function assignTask(Request $request,User $user)
    {
        $this->authorize('teacher',$user);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        $teacher = Auth::user();

        $task = new Task();
        $task->title = $request->title;
        $task->description = $request->description;
        $task->assigned_by = $teacher->id;
        $task->assigned_to = $request->assigned_to;
        $task->due_date = $request->due_date;
        $task->status = 'Pending';
        $task->save();

        return response()->json(['message' => 'Task assigned successfully', 'task' => $task], 201);
    }

    public function getTasksForStudent(User $student)
    {
       $tasks = $student->tasksAssignedTo()->get();

        return response()->json([
            'success' => true,
            'data' => $tasks,
        ]);
    }
}