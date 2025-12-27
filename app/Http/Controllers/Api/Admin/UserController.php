<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('q');

        $query = User::where('role', 'owner')
            ->withCount('boardingHouses');

        if (!empty($search)) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'data' => $query->latest()->paginate(20)
        ]);
    }

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