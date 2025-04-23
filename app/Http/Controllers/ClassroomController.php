<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\CursusHistory;
use App\Models\User;
use Illuminate\Http\Request;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ClassroomController extends Controller
{
    use AuthorizesRequests ;

    public function index(Request $request)
    {
        $request->validate([
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
        ]);

        $perPage = $request->get('per_page', 10);

        $classrooms = Classroom::orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'data' => $classrooms->items(),
            'current_page' => $classrooms->currentPage(),
            'last_page' => $classrooms->lastPage(),
            'per_page' => $classrooms->perPage(),
            'total' => $classrooms->total(),
        ]);
    }

    public function createClassroom(Request $request)
    {
        $this->authorize("admin", User::class);

        // Validate the request data
        $request->validate([
            'slug' => 'required|string|unique:classrooms,slug',
            'name' => 'required|string|max:255',
            'level' => 'required|string|max:255',
            'campus' => 'required|string|max:255',
            'promotion_id' => 'required|exists:promotions,id',
            'cover_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5000',
            'teacher_id' => 'required|exists:users,id',
            'delegate_id' => 'nullable|exists:users,id',
        ]);

        // Handle the file upload
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $filePath = 'classrooms_images';
            $path = $file->store($filePath, 'public');
        } else {
            return response()->json(['message' => 'Image upload failed'], 500);
        }

        // Create the classroom
        $classroom = Classroom::create([
            'slug' => $request->slug,
            'name' => $request->name,
            'level' => $request->level,
            'campus' => $request->campus,
            'promotion_id' => $request->promotion_id,
            'cover_image' => $path,
            'teacher_id' => $request->teacher_id,
            'delegate_id' => $request->delegate_id,
        ]);

        // Fetch the teacher and delegate details
        $teacher = User::find($classroom->teacher_id);
        $delegate = User::find($classroom->delegate_id);

        // Update delegate attributes
        if ($delegate) {
            $delegate->level = $classroom->level === '1ère Année' ? 'A1' : ($classroom->level === '2ème Année' ? 'A2' : $classroom->level);
            $delegate->classroom = $classroom->name;
            $delegate->referent_coach = $teacher->name;
            $delegate->save();
        }

        return response()->json([
            'message' => 'Classroom created successfully',
            'classroom' => $classroom, // Directly return the classroom model
        ], 201);
    }

    public function addStudents(Request $request, Classroom $classroom)
    {
        $this->authorize("admin", User::class);

        // Validate the request data
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id',
        ]);

        $studentsAdded = [];
        $studentsAlreadyInClassroom = [];

        // Fetch the teacher of the classroom
        $teacher = User::find($classroom->teacher_id);

        foreach ($request->student_ids as $studentId) {
            // Find the student
            $student = User::findOrFail($studentId);

            // Check if the student is already in the classroom
            if ($classroom->students()->where('student_id', $student->id)->exists()) {
                $studentsAlreadyInClassroom[] = $student->id;
            } else {
                // Add the student to the classroom
                $classroom->students()->attach($student->id);
                $studentsAdded[] = $student->id;

                // Update the old CursusHistory record to "PASS" status
                CursusHistory::where('student_id', $student->id)
                    ->where('status', 'IN PROGRESS')
                    ->update(['status' => 'PASS']);


                // Create new CursusHistory record
                $event = ($student->level == $classroom->level) ? 'Class Change' : $classroom->level;
                CursusHistory::create([
                    'student_id' => $student->id,
                    'coach_id' => $teacher->id,
                    'date' => now(),
                    'event' => $event,
                    'status' => 'IN PROGRESS',
                    'class_id' => $classroom->id,
                    'promotion_id' => $classroom->promotion_id,
                    'remarks' => 'Student added to classroom'
                ]);

                // Update student's level, classroom, and referent_coach fields
                $student->level = $classroom->level === '1ère Année' ? 'A1' :($classroom->level === '2ème Année' ? 'A2' : $classroom->level);
                $student->classroom = $classroom->name;
                $student->referent_coach = $teacher->name;
                $student->save();
            }
        }

        $classroom->load('students');

        return response()->json([
            'message' => 'Students added to classroom successfully',
            'students_added' => $studentsAdded,
            'students_already_in_classroom' => $studentsAlreadyInClassroom,
            'classroom' => $classroom
        ], 200);
    }

    public function updateClassroom(Request $request, Classroom $classroom)
    {
        $this->authorize("admin", User::class);

       // Validate the request data
       $request->validate([
        'slug' => 'nullable|string|unique:classrooms,slug,' . $classroom->id,
        'name' => 'nullable|string|max:255',
        'level' => 'nullable|string|max:255',
        'campus' => 'nullable|string|max:255',
        'promotion_id' => 'nullable|exists:promotions,id',
        'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5000',
        'teacher_id' => 'nullable|exists:users,id',
        'delegate_id' => 'nullable|exists:users,id',
    ]);

    // Handle the file upload
    if ($request->hasFile('cover_image')) {
        // Get the file from the request
        $file = $request->file('cover_image');

        // Define the file path and name
        $filePath = 'classrooms_images';

        // Store the file
        $path = $file->store($filePath, 'public');
        $classroom->cover_image = $path;
    }

    // Update the classroom
    $classroom->update(array_filter($request->only([
        'slug',
        'name',
        'level',
        'campus',
        'promotion_id',
        'teacher_id',
        'delegate_id',
    ])));

    // Fetch the teacher and delegate details
    $teacher = User::find($classroom->teacher_id);
    $delegate = User::find($classroom->delegate_id);
    $delegate->level = $classroom->level === '1ère Année' ? 'A1' :($classroom->level === '2ème Année' ? 'A2' : $classroom->level);
    $delegate->classroom = $classroom->name;
    $delegate->referent_coach = $teacher->name;
    $delegate->save();

        $classroom->delegate = $delegate;
        $classroom->teacher = $teacher;
    return response()->json([
        'message' => 'Classroom updated successfully',
        'classroom' => $classroom
    ], 200);
    }

    public function deleteClassroom(Classroom $classroom)
    {
        $this->authorize("admin", user::class);

        $classroom->delete();

        return response()->json([
            'message' => 'Classroom deleted successfully'
        ], 200);
    }
    public function removeStudent($classroomId, $studentId)
    {
        $this->authorize("admin",User::class);
        // Retrieve the classroom
        $classroom = Classroom::find($classroomId);
        if (!$classroom) {
            return response()->json(['message' => 'Classroom not found.'], 404);
        }

        // Retrieve the student
        $student = User::find($studentId);
        if (!$student || $student->role !== 'student') {
            return response()->json(['message' => 'Student not found or invalid.'], 404);
        }

        // Check if the student is in the classroom
        if (!$classroom->students()->where('student_id', $studentId)->exists()) {
            return response()->json(['message' => 'Student is not in this classroom.'], 400);
        }

        // Remove the student from the classroom
        $classroom->students()->detach($studentId);

        return response()->json(['message' => 'Student removed from the classroom successfully.'], 200);
    }

    public function removeStudents(Request $request, $classroomId)
    {
        $this->authorize("admin", User::class);

        // Validate the request data
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id',
        ]);

        // Retrieve the classroom
        $classroom = Classroom::find($classroomId);
        if (!$classroom) {
            return response()->json(['message' => 'Classroom not found.'], 404);
        }

        // Retrieve the students
        $students = User::whereIn('id', $request->student_ids)->where('role', 'student')->get();

        if ($students->isEmpty()) {
            return response()->json(['message' => 'No valid students found to remove.'], 404);
        }

        // Check if the students are in the classroom
        $studentsNotInClassroom = [];
        $studentsRemoved = [];

        foreach ($students as $student) {
            if (!$classroom->students()->where('student_id', $student->id)->exists()) {
                $studentsNotInClassroom[] = $student->id;
            } else {
                // Remove the student from the classroom
                $classroom->students()->detach($student->id);
                $studentsRemoved[] = $student->id;
            }
        }
        $classroom->load('students');
        return response()->json([
            'message' => 'Students removed from the classroom successfully.',
            'students_removed' => $studentsRemoved,
            'students_not_in_classroom' => $studentsNotInClassroom,
            'classroom' => $classroom,
        ], 200);
    }

    public function getClassroomDelegates()
    {

    // Fetch classrooms with their delegates
    $classrooms = Classroom::with('delegate', 'promotion') // Load delegate name and promotion details
    ->select('id', 'name', 'campus', 'delegate_id', 'promotion_id') // Select only the required fields
    ->get();

    // Filter out classrooms where delegate is null
    $filteredClassrooms = $classrooms->filter(function ($classroom) {
        return $classroom->delegate !== null;
    });

    return response()->json($filteredClassrooms->values());
    }

    public function getClassroomById($id)
    {
        $classroom = Classroom::with(['teacher', 'delegate', 'students', 'promotion'])->find($id);

        if (!$classroom) {
            return response()->json(['message' => 'Classroom not found.'], 404);
        }

        return response()->json($classroom, 200); // Directly return the classroom model
    }

    public function searchClassrooms(Request $request)
    {
        $request->validate([
            'query' => 'nullable|string|max:255',
            'campus' => 'nullable|string|max:100',
            'level' => 'nullable|string|max:50',
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
        ]);

        $query = $request->get('query');
        $campus = $request->get('campus');
        $level = $request->get('level');
        $perPage = $request->get('per_page', 10);

        $classroomsQuery = Classroom::with(['teacher', 'delegate', 'students', 'promotion'])->orderBy('created_at', 'desc');

        if ($query) {
            $classroomsQuery->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('slug', 'like', "%{$query}%");
            });
        }

        if ($campus) {
            $classroomsQuery->where('campus', 'like', "%{$campus}%");
        }

        if ($level) {
            $classroomsQuery->where('level', $level);
        }

        $classrooms = $classroomsQuery->paginate($perPage);

        return response()->json([
            'classrooms' => $classrooms->items(),
            'current_page' => $classrooms->currentPage(),
            'last_page' => $classrooms->lastPage(),
            'per_page' => $classrooms->perPage(),
            'total' => $classrooms->total(),
        ]);
    }
}