<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

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

    /**
     * Super Admin menghapus User (Owner)
     */
    public function destroy(User $user): JsonResponse
    {
        if ($user->id === auth()->id() || $user->role === 'admin') {
            return response()->json(['message' => 'Cannot delete admin account.'], 403);
        }

        foreach ($user->boardingHouses as $kos) {
            if ($kos->cover_image) {
                Storage::disk('public')->delete($kos->cover_image);
            }
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully.']);
    }
}