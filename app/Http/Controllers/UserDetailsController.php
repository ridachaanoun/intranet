<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserDetailsController extends Controller
{
    use AuthorizesRequests ;

    public function updatePersonalInfo(Request $request, User $user)
    {
        $this->authorize('create', User::class);


        $validatedData = $request->validate([
            'first_name'    => 'nullable|string|max:255',
            'last_name'     => 'nullable|string|max:255',
            'id_card_cnie'  => 'nullable|string|max:255',
            'birthdate'     => 'nullable|date',
            'city'          => 'nullable|string|max:255',
            'phone'         => 'nullable|string|max:20',
            'email'         => 'nullable|string|email|max:255',
            'about_me'      => 'nullable|string',
        ]);

        // Update the personal info and retrieve the updated instance
        $user->personalInfo()->update($validatedData);
        $updatedPersonalInfo = $user->personalInfo()->first();

        return response()->json([
            'message'       => 'Personal info updated successfully',
            'personal_info' => $updatedPersonalInfo
        ]);
    }

    public function updateAccountInfo(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'campus'             => 'nullable|string|max:255',
            'registration_date'  => 'nullable|date',
            'promotion_id'     => 'nullable|string|max:255',
            'email_login'        => 'nullable|string|email|max:255',
            'username'           => 'nullable|string|max:255',
            'password'           => 'nullable|string|min:6',
            'discord_username'   => 'nullable|string|max:255',
        ]);

        $user->accountInfo()->update($validatedData);
        $updatedPersonalInfo = $user->accountInfo()->first();

        return response()->json([
            'message'      => 'Account info updated successfully',
            'account_info' => $updatedPersonalInfo
        ]);
    }

}