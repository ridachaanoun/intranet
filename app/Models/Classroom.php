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

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class);
    }

    public function delegate()
    {
        return $this->belongsTo(User::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'classroom_student', 'classroom_id', 'student_id')
                    ->where('role', 'student');
    }
    public function cursusHistories()
    {
        return $this->hasMany(CursusHistory::class, 'class_id');
    }

}