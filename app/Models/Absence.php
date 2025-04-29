<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    protected $fillable = ['date', 'status', 'class', 'session', 'confirmed_by', 'user_id', 'reason', 'classroom_id'];

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
    public function user (){
        return $this->belongsTo(User::class);
    }
    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }
}
