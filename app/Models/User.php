<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
class User extends Authenticatable
{
    use HasFactory, Notifiable,HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'image',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
    // Define the inverse relationship with the Classroom model as a teacher
    public function classroomsAsTeacher()
    {
        return $this->hasMany(Classroom::class, 'teacher_id');
    }

    // Define the inverse relationship with the Classroom model as a delegate
    public function classroomsAsDelegate()
    {
        return $this->hasMany(Classroom::class, 'delegate_id');
    }

    // Define the many-to-many relationship with the Classroom model for students
    public function classroomsAsStudent()
    {
        return $this->belongsToMany(Classroom::class, 'classroom_student', 'student_id', 'classroom_id');
    }
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
