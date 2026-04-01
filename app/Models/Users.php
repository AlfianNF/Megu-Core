<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject; // INI KUNCINYA
use Illuminate\Support\Facades\Hash;

class Users extends Authenticatable implements JWTSubject
{
    use HasFactory,Notifiable;

    protected $table = 'users';

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'username' => $this->username,
            'role_id'  => $this->role_id
        ];
    }

    protected $fillable = array (
  0 => 'fullname',
  1 => 'username',
  2 => 'password',
  3 => 'email',
  4 => 'telephone',
  5 => 'role_id',
  6 => 'email_verified_at',
  7 => 'status_code',
  8 => 'api_token',
);

    public const FIELD_LIST = array (
  0 => 'id',
  1 => 'fullname',
  2 => 'username',
  3 => 'password',
  4 => 'email',
  5 => 'telephone',
  6 => 'role_id',
  7 => 'email_verified_at',
  8 => 'status_code',
  9 => 'api_token',
  10 => 'created_at',
  11 => 'updated_at',
);
    public const FIELD_ADD = array (
  0 => 'fullname',
  1 => 'username',
  2 => 'password',
  3 => 'email',
  4 => 'telephone',
  5 => 'role_id',
  6 => 'email_verified_at',
  7 => 'status_code',
  8 => 'api_token',
);
    public const FIELD_EDIT = array (
  0 => 'fullname',
  1 => 'username',
  2 => 'password',
  3 => 'email',
  4 => 'telephone',
  5 => 'role_id',
  6 => 'email_verified_at',
  7 => 'status_code',
  8 => 'api_token',
);
    public const FIELD_VALIDATION = array (
  'fullname' => 'required|max:255',
  'username' => 'required|max:255',
  'password' => 'required',
  'email' => 'nullable|email|max:255',
  'telephone' => 'nullable|max:255',
  'role_id' => 'required|integer',
  'email_verified_at' => 'nullable',
  'status_code' => 'required|max:255',
  'api_token' => 'nullable|max:255',
);
    public const FIELD_UNIQUE = array (
  0 => 
  array (
    0 => 'username',
  ),
);
    public const FIELD_UPLOAD = array (
);
    public const FILEROOT = 'users';

    public const FIELD_RELATION = [
        'role_id' => [
            'linkTable' => 'roles',
            'linkField' => 'id',
            'displayName' => 'rel_role',
        ],
    ];

    public static function beforeInsert($input)
    {
        if (isset($input['password']) && !empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        }
        if (isset($input['pin']) && !empty($input['pin'])) {
            $input['pin'] = Hash::make($input['pin']);
        }
        return $input;
    }

    public static function beforeUpdate($input)
    {
        if (isset($input['password']) && !empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        } else {
            unset($input['password']);
        }

        if (isset($input['pin']) && !empty($input['pin'])) {
            $input['pin'] = Hash::make($input['pin']);
        } else {
            unset($input['pin']);
        }
        return $input;
    }


    public static function afterInsert($object, $input)
    {
        return $input;
    }
    
    
    public static function afterUpdate($object, $input)
    {
        return $input;
    }
    
    public static function beforeDelete($input)
    {
        return $input;
    }

    public static function afterDelete($object, $input)
    {
        return $input;
    }

    // -- Start Custom Code --
    
    // -- End Custom Code --
}