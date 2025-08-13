<?php

namespace App\Repositories\AuthRepository;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthRepository
{

    private $model;

    public function __construct(User $user){
        $this->model = $user;
    }

    public function get($email){
        return $this->model->where('email', $email)->first();
    }

    public function save(User $user){
        $user->password = Hash::make($user->password);
        $user->save();
        return $user;
    }

}
