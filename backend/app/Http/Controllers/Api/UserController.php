<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\UserService;



class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $filters = $request->input('filter', []);

        $users = $this->userService->getUsers($filters, $perPage);

        return response()->json([
            'status' => true,
            'message' => 'Users fetched successfully',
            'data' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'total' => $users->total(),
                'per_page' => $users->perPage(),
            ]
        ]);     

    }

    public function store(StoreUserRequest $request)
    {
        $user = $this->userService->createUser($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'User created successfully',
            'data' => new UserResource($user)
        ], 201);
    }

    public  function show(User $user)
    {
        return response()->json([
            'status' => true,
            'message' => 'User fetched successfully',
            'data' => new UserResource($user)
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $updatedUser = $this->userService->updateUser($user, $request->validated());

        return response()->json([
            'status' => true,
            'message' => 'User updated successfully',
            'data' => new UserResource($updatedUser)
        ]);
    }
}
