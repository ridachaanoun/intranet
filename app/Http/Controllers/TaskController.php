<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    use AuthorizesRequests;

    public function assignTask(Request $request)
    {
        $teacher = Auth::user();

        $this->authorize('teacher', $teacher);

        // Validate the request
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'classroom_id' => 'required|exists:classrooms,id',
            'due_date' => 'nullable|date',
            'points' => 'required|integer|min:0',
            'task_type' => 'required|string|in:Assignment,Quiz,Project,Research,Other',
            'assignment_type' => 'required|string|in:class,students',
            'student_ids' => 'required_if:assignment_type,students|array',
            'student_ids.*' => 'exists:users,id',
        ]);

        // Create the task
        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'assigned_by' => $teacher->id,
            'classroom_id' => $request->classroom_id,
            'due_date' => $request->due_date,
            'status' => 'Pending',
            'task_type' => $request->task_type,
            'points' => $request->points,
            'assignment_type' => $request->assignment_type,
        ]);

        // Attach the task to students based on the assignment type
        if ($request->assignment_type === 'class') {
            $this->assignTaskToClass($task, $request->classroom_id);
        } elseif ($request->assignment_type === 'students') {
            return $this->assignTaskToSpecificStudents($task, $request->student_ids);
        }
        $task->load('assignedStudents');
        return response()->json(['message' => 'Task assigned successfully', 'task' => $task], 201);
    }

    private function assignTaskToClass(Task $task, $classroomId)
    {
        $classroom = Classroom::findOrFail($classroomId);

        // Fetch all students in the classroom
        $students = $classroom->students;

        // Attach the task to all students in the classroom
        $task->assignedStudents()->attach($students->pluck('id'));
    }

    private function assignTaskToSpecificStudents(Task $task, array $studentIds)
    {
        $classroom = Classroom::findOrFail($task->classroom_id);

        $studentsInClassroom = $classroom->students()->pluck('id')->toArray();

        $validStudentIds = array_intersect($studentIds, $studentsInClassroom);

        $invalidStudentIds = array_diff($studentIds, $studentsInClassroom);

        if (empty($validStudentIds)) {
            // If no valid students are found, return an error with the invalid students
            return response()->json([
                'message' => 'No valid students found in the specified classroom.',
                'invalid_students' => $invalidStudentIds,
            ], 400);
        }

        // Attach the task to the valid students
        $task->assignedStudents()->attach($validStudentIds);

        // Return a success response with the invalid students
        return response()->json([
            'message' => 'Task assigned successfully to valid students.',
            'invalid_students' => $invalidStudentIds,
            'task' => $task->load('assignedStudents'),
        ], 200);

    }

    //   Get tasks assigned to a specific student.  
    public function getTasksForStudent(User $student)
    {
        $tasks = $student->tasksAssignedTo()
            ->with(['assignedBy']) // Eager load the teacher who assigned the task
            ->withCount('assignedStudents')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tasks,
        ]);
    }

    //  Get tasks assigned by a specific teacher.
    public function getTasksAssignedByTeacher(User $teacher)
    {
        $tasks = $teacher->tasksAssignedBy()->withCount('assignedStudents')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $tasks,
        ]);
    }

    public function getClassroomsWithStudentsAndTasks()
    {
        $teacher = Auth::user();

        $this->authorize('teacher', $teacher);

        // Fetch classrooms where the teacher is assigned
        $classrooms = $teacher->classroomsAsTeacher()
            ->with(['students.points', 'tasks.assignedStudents']) // Include students with their points and tasks, and assigned students for each task
            ->orderBy('created_at', 'desc')
            ->get();

        // Add total points for each student
        $classrooms->each(function ($classroom) {
            $classroom->students->each(function ($student) {
                $student->total_points = $student->points->sum('points'); // Calculate total points for the student
            });
        });

        return response()->json([
            'success' => true,
            'message' => 'Classrooms with students, their total points, and tasks retrieved successfully.',
            'data' => $classrooms,
        ]);
    }
}