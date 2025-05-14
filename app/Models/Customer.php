<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Http\Request;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'user_type',
        'is_registered'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_registered' => 'boolean',
    ];

    public function checkPhone(Request $request)
    {
        $request->validate(['phone' => 'required']);
        $user = self::where('phone_number', $request->phone)->first();

        if ($user) {
            // Show a view asking if the user wants to login
            return view('reservations.ask_login', ['phone' => $request->phone]);
        } else {
            // Show a view asking if the user wants to sign up
            return view('reservations.ask_signup', ['phone' => $request->phone]);
        }
    }

    /**
     * Check if a user exists by phone number.
     *
     * @param string $phone
     * @return User|null
     */
    public static function findByPhone($phone)
    {
        return self::where('phone_number', $phone)->first();
    }
}
