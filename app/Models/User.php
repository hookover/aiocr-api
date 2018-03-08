<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    protected $guarded = [
        'preparation_a',
        'preparation_b',
        'preparation_c',
        'preparation_d',
        'preparation_e',
    ];

    protected $hidden = [
        'password', 'remember_token', 'salt',
        'preparation_a',
        'preparation_b',
        'preparation_c',
        'preparation_d',
        'preparation_e',
    ];

    const STATUS_ACCOUNT_VALID  = 1;
    const STATUS_ACCOUNT_LOCKED = 0;

}