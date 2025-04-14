<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    // Specify the table if it's not the default plural form
    protected $table = 'announcements';

    // Mass assignable attributes
    protected $fillable = ['title', 'content'];
}