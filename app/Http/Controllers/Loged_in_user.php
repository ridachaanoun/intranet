<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

class Loged_in_user extends Controller
{
    public function getUserDetails()
    {
        $user = Auth::user();

        $personalInfo = $user->personalInfo()->first();
        $accountInfo = $user->accountInfo()->first();
        $profiles = $user->profiles()->first();

        return response()->json([
            'personal_info' => $personalInfo,
            'account_info' => $accountInfo,
            'profiles' => $profiles,
        ], 200);
    }

}
