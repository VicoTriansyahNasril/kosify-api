<?php

namespace Database\Seeders;

use App\Enums\RoomStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\BoardingHouse;
use App\Models\Review;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. SUPER ADMIN
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@kosify.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '081234567890'
        ]);

        // 2. OWNERS (20 AKUN)
        $owners = [];
        $password = Hash::make('kos12345');

        for ($i = 1; $i <= 20; $i++) {
            $owners[] = User::create([
                'name' => fake('id_ID')->name(),
                'email' => "kos{$i}@gmail.com",
                'password' => $password,
                'role' => 'owner',
                'phone' => '08' . fake()->numerify('##########'),
            ]);
        }

        // 3. REGULAR USERS (PENCARI KOS - UNTUK REVIEW) - BARU
        $renters = [];
        for ($j = 1; $j <= 30; $j++) {
            $renters[] = User::create([
                'name' => fake('id_ID')->name(),
                'email' => "user{$j}@gmail.com",
                'password' => $password,
                'role' => 'user', // Asumsi ada role user biasa
                'phone' => '08' . fake()->numerify('##########'),
            ]);
        }

        // 4. BOARDING HOUSES
        $cities = ['Jakarta Selatan', 'Jakarta Barat', 'Bandung', 'Yogyakarta', 'Surabaya', 'Malang', 'Semarang', 'Bali'];
        $facilitiesList = ['WiFi', 'AC', 'Kamar Mandi Dalam', 'Kamar Mandi Luar', 'Water Heater', 'Kasur', 'Lemari', 'Meja Belajar', 'Dapur Bersama', 'Kulkas Umum', 'Dispenser', 'Parkir Motor', 'Parkir Mobil', 'CCTV', 'Security 24 Jam', 'Laundry', 'Rooftop'];

        foreach ($owners as $owner) {
            $totalKos = rand(1, 3);

            for ($k = 0; $k < $totalKos; $k++) {
                $city = fake()->randomElement($cities);
                $kosName = "Kost " . fake()->firstName() . " " . $city;

                $kos = BoardingHouse::create([
                    'user_id' => $owner->id,
                    'name' => $kosName,
                    'slug' => Str::slug($kosName) . '-' . Str::random(6),
                    'address' => "Jl. " . fake()->streetName() . " No. " . rand(1, 100) . ", " . $city,
                    'description' => "Kos nyaman dan strategis di pusat kota " . $city . ". Dekat dengan fasilitas umum.",
                    'category' => fake()->randomElement(['putra', 'putri', 'campur']),
                    'facilities' => fake()->randomElements($facilitiesList, rand(4, 8)),
                    'rules' => ['Dilarang merokok di kamar', 'Tamu menginap lapor', 'Jaga kebersihan'],
                    'cover_image' => null,
                ]);

                // --- SEED REVIEWS (BARU) ---
                // Ambil 3-8 user acak untuk mereview kos ini
                $randomRenters = fake()->randomElements($renters, rand(3, 8));
                foreach ($randomRenters as $renter) {
                    Review::create([
                        'boarding_house_id' => $kos->id,
                        'user_id' => $renter->id,
                        'rating' => rand(3, 5), // Rating bagus-bagus aja biar cantik
                        'comment' => fake('id_ID')->sentence(10),
                    ]);
                }

                // --- SEED ROOMS ---
                for ($r = 1; $r <= rand(5, 10); $r++) {
                    $status = fake()->randomElement([RoomStatus::AVAILABLE, RoomStatus::OCCUPIED]);
                    $price = rand(5, 25) * 100000;

                    $room = Room::create([
                        'boarding_house_id' => $kos->id,
                        'name' => 'Kamar ' . $r,
                        'price' => $price,
                        'capacity' => rand(1, 2),
                        'status' => $status,
                        'description' => 'Kamar standar dengan ventilasi baik.',
                    ]);

                    // --- SEED TENANT & TRANSACTION ---
                    if ($status === RoomStatus::OCCUPIED) {
                        $entryDate = now()->subMonths(rand(1, 6));

                        $tenant = Tenant::create([
                            'room_id' => $room->id,
                            'name' => fake('id_ID')->name(),
                            'identification_number' => fake()->nik(),
                            'phone' => '08' . fake()->numerify('##########'),
                            'emergency_contact' => '08' . fake()->numerify('##########'),
                            'entry_date' => $entryDate,
                            'due_date' => now()->addDays(rand(1, 30)),
                            'status' => 'active',
                        ]);

                        Transaction::create([
                            'tenant_id' => $tenant->id,
                            'room_id' => $room->id,
                            'invoice_number' => 'INV/' . now()->format('Ymd') . '/' . Str::random(5),
                            'type' => TransactionType::RENT,
                            'amount' => $price,
                            'status' => TransactionStatus::PAID,
                            'due_date' => $entryDate,
                            'paid_at' => $entryDate,
                            'notes' => 'Pembayaran awal masuk',
                        ]);
                    }
                }
            }
        }
    }
}