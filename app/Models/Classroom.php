<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'promotion_id', 'teacher_id'];

    // Define the relationship with the Promotion model
    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    // Define the relationship with the User model for the teacher
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // Define the many-to-many relationship with the User model for students
    public function students()
    {
        return $this->belongsToMany(User::class, 'classroom_student', 'classroom_id', 'student_id')->where('role', 'student');
    }
}