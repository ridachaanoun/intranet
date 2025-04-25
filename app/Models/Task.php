<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{

    protected $fillable = [
        'title',
        'description',
        'assigned_by',
        'assigned_to',
        'classroom_id',
        'due_date',
        'status',
        'task_type',
        'points',
        'assignment_type',
    ];

    public function assignedBy()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with the Classroom
    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function assignedStudents()
    {
        return $this->belongsToMany(User::class, 'task_user', 'task_id', 'user_id');
    }
}