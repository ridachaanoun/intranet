<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CursusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'date',
        'event',
        'status',
        'class_id',
        'promotion_id',
        'remarks',
        'coach_id',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function class()
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promotion_id');
    }
    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_id');
    }
}