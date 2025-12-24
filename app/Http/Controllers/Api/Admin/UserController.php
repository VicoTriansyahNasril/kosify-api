<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $owners = User::where('role', 'owner')
            ->withCount('boardingHouses')
            ->latest()
            ->get();

        return response()->json([
            'data' => $owners
        ]);
    }
}