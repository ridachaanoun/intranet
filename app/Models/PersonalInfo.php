<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'id_card_cnie', 'birthdate', 'city', 'phone', 'email', 'about_me'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
