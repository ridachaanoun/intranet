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

        $formattedClassrooms = $classrooms->map(function ($classroom) {
            return [
                'id' => $classroom->id,
                'slug' => $classroom->slug,
                'name' => $classroom->name,
                'level' => $classroom->level,
                'campus' => $classroom->campus,
                'promotion_id' => $classroom->promotion_id,
                'cover_image' => asset('storage/' . $classroom->cover_image),
                'teacher_id' => $classroom->teacher_id,
                'delegate_id' => $classroom->delegate_id,
                'created_at' => $classroom->created_at,
                'updated_at' => $classroom->updated_at,
            ];
        });

        return response()->json([
            'data' => $formattedClassrooms,
            'current_page' => $classrooms->currentPage(),
            'last_page' => $classrooms->lastPage(),
            'per_page' => $classrooms->perPage(),
            'total' => $classrooms->total(),
        ]);
    }

    public function createClassroom(Request $request)
    {
        $this->authorize("admin",user::class);
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
            // Get the file from the request
            $file = $request->file('cover_image');

            // Define the file path and name
            $filePath = 'classrooms_images';

            // Store the file
            $path = $file->store($filePath, 'public');
        } else {
            return response()->json([
                'message' => 'Image upload failed'
            ], 500);
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

        $delegate->level = $classroom->level === '1ère Année' ? 'A1' :($classroom->level === '2ème Année' ? 'A2' : $classroom->level);
        $delegate->classroom = $classroom->name;
        $delegate->referent_coach = $teacher->name;
        $delegate->save();
        // Generate asset paths for teacher and delegate images
        $teacherImage = $teacher ? asset('storage/' . $teacher->image) : null;
        $delegateImage = $delegate ? asset('storage/' . $delegate->image) : null;

        return response()->json([
            'message' => 'Classroom created successfully',
            'classroom' => [
                'id' => $classroom->id,
                'slug' => $classroom->slug,
                'name' => $classroom->name,
                'level' => $classroom->level,
                'campus' => $classroom->campus,
                'promotion_id' => $classroom->promotion_id,
                'cover_image' => asset('storage/' . $path),
                'teacher_id' => $classroom->teacher_id,
                'delegate_id' => $classroom->delegate_id,
                'created_at' => $classroom->created_at,
                'updated_at' => $classroom->updated_at,
                'learners' => 0,
                'teacher' => [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'email' => $teacher->email,
                    'email_verified_at' => $teacher->email_verified_at,
                    'role' => $teacher->role,
                    'created_at' => $teacher->created_at,
                    'updated_at' => $teacher->updated_at,
                    'image' => $teacherImage,
                ],
                'delegate' => $delegate ? [
                    'id' => $delegate->id,
                    'name' => $delegate->name,
                    'email' => $delegate->email,
                    'email_verified_at' => $delegate->email_verified_at,
                    'role' => $delegate->role,
                    'created_at' => $delegate->created_at,
                    'updated_at' => $delegate->updated_at,
                    'image' => $delegateImage,
                ] : null,
            ]
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

        // Load the students with their asset URLs
        $classroom->load('students');
        $students = $classroom->students->unique('id')->map(function ($student) {
            return [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'role' => $student->role,
                'created_at' => $student->created_at,
                'updated_at' => $student->updated_at,
                'image_url' => asset('storage/' . $student->image),
            ];
        });

        return response()->json([
            'message' => 'Students added to classroom successfully',
            'students_added' => $studentsAdded,
            'students_already_in_classroom' => $studentsAlreadyInClassroom,
            'classroom' => [
                'id' => $classroom->id,
                'slug' => $classroom->slug,
                'name' => $classroom->name,
                'level' => $classroom->level,
                'campus' => $classroom->campus,
                'promotion_id' => $classroom->promotion_id,
                'cover_image' => asset('storage/' . $classroom->cover_image),
                'teacher_id' => $classroom->teacher_id,
                'delegate_id' => $classroom->delegate_id,
                'created_at' => $classroom->created_at,
                'updated_at' => $classroom->updated_at,
                'students' => $students,
            ]
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

    // Generate asset paths for teacher and delegate images
    $teacherImage = $teacher ? asset('storage/' . $teacher->image) : null;
    $delegateImage = $delegate ? asset('storage/' . $delegate->image) : null;

    return response()->json([
        'message' => 'Classroom updated successfully',
        'request'=>$request->all(),
        'classroom' => [
            'id' => $classroom->id,
            'slug' => $classroom->slug,
            'name' => $classroom->name,
            'level' => $classroom->level,
            'campus' => $classroom->campus,
            'promotion_id' => $classroom->promotion_id,
            'cover_image' => asset('storage/' . $classroom->cover_image),
            'teacher_id' => $classroom->teacher_id,
            'delegate_id' => $classroom->delegate_id,
            'created_at' => $classroom->created_at,
            'updated_at' => $classroom->updated_at,
            'learners' => $classroom->students()->count(),
            'teacher' => [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $teacher->email,
                'email_verified_at' => $teacher->email_verified_at,
                'role' => $teacher->role,
                'created_at' => $teacher->created_at,
                'updated_at' => $teacher->updated_at,
                'image' => $teacherImage,
            ],
            'delegate' => $delegate ? [
                'id' => $delegate->id,
                'name' => $delegate->name,
                'email' => $delegate->email,
                'email_verified_at' => $delegate->email_verified_at,
                'role' => $delegate->role,
                'created_at' => $delegate->created_at,
                'updated_at' => $delegate->updated_at,
                'image' => $delegateImage,
            ] : null,
        ]
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
    $delegates= $filteredClassrooms->map(function ($class) {
    $class->delegate->img_url= asset('storage/'. $class->delegate->image);
        return $class;
    });

    return response()->json($delegates->values());
    }

    public function getClassroomById($id)
    {
        $classroom = Classroom::with(['teacher', 'delegate', 'students', 'promotion'])->find($id);
    
        if (!$classroom) {
            return response()->json(['message' => 'Classroom not found.'], 404);
        }
    
        // Format the classroom data
        $formattedClassroom = [
            'id' => $classroom->id,
            'slug' => $classroom->slug,
            'name' => $classroom->name,
            'level' => $classroom->level,
            'campus' => $classroom->campus,
            'promotion_id' => $classroom->promotion_id,
            'cover_image' => asset('storage/' . $classroom->cover_image),
            'teacher' => $classroom->teacher ? [
                'id' => $classroom->teacher->id,
                'name' => $classroom->teacher->name,
                'email' => $classroom->teacher->email,
                'image_url' => asset('storage/' . $classroom->teacher->image),
            ] : null,
            'delegate' => $classroom->delegate ? [
                'id' => $classroom->delegate->id,
                'name' => $classroom->delegate->name,
                'email' => $classroom->delegate->email,
                'image_url' => asset('storage/' . $classroom->delegate->image),
            ] : null,
            'promotion' => $classroom->promotion,
            'students' => $classroom->students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'image_url' => asset('storage/' . $student->image),
                ];
            }),
            'created_at' => $classroom->created_at,
            'updated_at' => $classroom->updated_at,
        ];
    
        return response()->json($formattedClassroom, 200);
    }
}