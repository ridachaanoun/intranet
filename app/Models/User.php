<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'image',
        'campus', 
        'level', 
        'classroom', 
        'referent_coach', 
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['image_url'];

    public function personalInfo()
    {
        return $this->hasOne(PersonalInfo::class);
    }

    public function accountInfo()
    {
        return $this->hasOne(AccountInfo::class);
    }

    public function profiles()
    {
        return $this->hasOne(Profile::class);
    }

    public function classroomsAsTeacher()
    {
        return $this->hasMany(Classroom::class,"teacher_id");
    }

    public function classroomsAsDelegate()
    {
        return $this->hasMany(Classroom::class);
    }

    public function classroomsAsStudent()
    {
        return $this->belongsToMany(Classroom::class, 'classroom_student', 'student_id', 'classroom_id');
    }
    public function cursusHistories()
    {
        return $this->hasMany(CursusHistory::class,"student_id");
    }
    public function absences()
    {
        return $this->hasMany(Absence::class);
    }
    public function points() 
    {
        return $this->hasMany(Point::class,'student_id');
    }
    public function tasksAssignedTo()
    {
        return $this->belongsToMany(Task::class, 'task_user', 'user_id', 'task_id')
                    ->withTimestamps();
    }

    public function tasksAssignedBy()
    {
        return $this->hasMany(Task::class, 'assigned_by');
    }
    public function classrooms()
    {
        return $this->belongsToMany(Classroom::class, 'classroom_student', 'student_id', 'classroom_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'student_product');
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}