<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BoardingHouse;
use App\Models\Booking;
use App\Models\Room;
use App\Services\ImageService;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
    protected ImageService $imageService;
    protected TenantService $tenantService;

    public function __construct(ImageService $imageService, TenantService $tenantService)
    {
        $this->imageService = $imageService;
        $this->tenantService = $tenantService;
    }

    public function index(BoardingHouse $boardingHouse)
    {
        $this->authorize('view', $boardingHouse);

        $bookings = Booking::where('boarding_house_id', $boardingHouse->id)
            ->with('room:id,name')
            ->latest()
            ->get();

        return response()->json(['data' => $bookings]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'start_date' => 'required|date|after_or_equal:today',
            'duration' => 'required|integer|min:1',
            'ktp_image' => 'required|image|max:2048',
        ]);

        $room = Room::findOrFail($validated['room_id']);

        if ($room->status !== \App\Enums\RoomStatus::AVAILABLE) {
            return response()->json(['message' => 'Kamar tidak tersedia.'], 422);
        }

        $ktpPath = null;
        if ($request->hasFile('ktp_image')) {
            $ktpPath = $this->imageService->upload($request->file('ktp_image'), 'ktp_images');
        }

        $booking = Booking::create([
            'boarding_house_id' => $room->boarding_house_id,
            'room_id' => $room->id,
            'user_id' => $request->user()?->id,
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'start_date' => $validated['start_date'],
            'duration' => $validated['duration'],
            'ktp_image' => $ktpPath,
            'status' => 'pending'
        ]);

        return response()->json([
            'message' => 'Pengajuan sewa berhasil dikirim. Tunggu konfirmasi pemilik.',
            'data' => $booking
        ], 201);
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking->boardingHouse);

        $request->validate(['status' => 'required|in:approved,rejected']);
        $status = $request->status;

        if ($status === 'approved') {
            DB::transaction(function () use ($booking) {
                $startDate = Carbon::parse($booking->start_date);

                $this->tenantService->checkIn([
                    'room_id' => $booking->room_id,
                    'name' => $booking->name,
                    'phone' => $booking->phone,
                    'entry_date' => $startDate->format('Y-m-d'),
                    'due_date' => $startDate->addMonths(1)->format('Y-m-d'),
                    'identification_number' => 'FROM_BOOKING',
                ]);

                $booking->update(['status' => 'approved']);
            });

            return response()->json(['message' => 'Booking disetujui. Data penyewa telah dibuat.']);
        }

        if ($status === 'rejected') {
            $booking->update(['status' => 'rejected']);
            return response()->json(['message' => 'Booking ditolak.']);
        }
    }
}