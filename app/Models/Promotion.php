<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = ["year"];

    public function accountInfos()
    {
        return $this->hasMany(AccountInfo::class);
    }
}
