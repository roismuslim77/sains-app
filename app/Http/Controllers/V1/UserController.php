<?php

namespace App\Http\Controllers\V1;

use App\Helpers\Format;
use App\Http\Controllers\Controller;
use App\Repositories\User\UserRepositoryFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserController extends Controller
{
    protected $userRepository;

    public function __construct()
    {
        $userFactory = new UserRepositoryFactory();
        $this->userRepository = $userFactory->make('firestore');        
    }

    public function index()
    {
        $users = $this->userRepository->all();
        
        return Format::response($users);
    }

    public function show($id)
    {
        $user = $this->userRepository->find($id);
        
        return Format::response($user);
    }
}