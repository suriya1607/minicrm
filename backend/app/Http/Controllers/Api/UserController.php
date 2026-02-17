<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;



class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with('role:id,name')->where('id', '!=', auth()->id());
        $perPage = $request->get('per_page', 10); 
        $filters = $request->input('filter', []);

        foreach ($filters as $field => $value) {
        if ($field === 'created_at') {
            $query->whereDate($field, $value);
        } elseif ($field === 'role_id') {
            $query->where($field, $value);
        } else {
            $query->where($field, 'like', "%{$value}%");
        }
        }

        // $query->orderBy($sortBy, $sortDir);

        $users = $query->paginate($perPage);

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
}
