<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'campus', 'registration_date', 'promotion', 'email_login', 'username', 'password', 'discord_username'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
        public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }
}
