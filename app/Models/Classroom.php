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

    protected $appends = ['cover_image_url'];

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
                    ->where('users.role', 'student')
                    ->select('users.*');
    }

    public function cursusHistories()
    {
        return $this->hasMany(CursusHistory::class, 'class_id');
    }

    public function getCoverImageUrlAttribute()
    {
        return $this->cover_image ? asset('storage/' . $this->cover_image) : null;
    }
    public function tasks()
    {
        return $this->hasMany(Task::class, 'classroom_id');
    }
    public function absences()
    {
        return $this->hasMany(Absence::class,'classroom_id');
    }
}