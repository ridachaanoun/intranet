<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'name', 'level', 'campus', 'promotion_id', 'cover_image', 'teacher_id', 'delegate_id'
    ];

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

    // Define the relationship with the User model for the delegate
    public function delegate()
    {
        return $this->belongsTo(User::class, 'delegate_id');
    }

    // Define the relationship with the User model for students
    public function students()
    {
        return $this->belongsToMany(User::class, 'classroom_student', 'classroom_id', 'student_id')
                    ->where('role', 'student');
    }
}