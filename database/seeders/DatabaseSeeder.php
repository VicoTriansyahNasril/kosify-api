<?php

namespace Database\Seeders;

use App\Enums\RoomStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\BoardingHouse;
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
        // ==========================================
        // 1. SUPER ADMIN
        // ==========================================
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@kosify.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '081234567890'
        ]);

        // ==========================================
        // 2. OWNERS (20 AKUN)
        // ==========================================
        $owners = [];
        $password = Hash::make('kos12345');

        // Nama-nama umum Indonesia agar terlihat real
        $firstNames = ['Budi', 'Siti', 'Andi', 'Dewi', 'Rahmat', 'Putri', 'Rizky', 'Nur', 'Agus', 'Dian', 'Eko', 'Sri', 'Fajar', 'Indah', 'Bayu', 'Lestari', 'Hendra', 'Wulan', 'Joko', 'Sari'];
        $lastNames = ['Santoso', 'Wijaya', 'Saputra', 'Hidayat', 'Pratama', 'Kusuma', 'Suryana', 'Wibowo', 'Siregar', 'Lubis', 'Utami', 'Pertiwi', 'Nugraha', 'Setiawan', 'Ramadhan'];

        for ($i = 1; $i <= 20; $i++) {
            $name = fake()->randomElement($firstNames) . ' ' . fake()->randomElement($lastNames);
            
            $owners[] = User::create([
                'name' => $name,
                'email' => "kos{$i}@gmail.com",
                'password' => $password,
                'role' => 'owner',
                'phone' => '08' . fake()->numerify('##########'), // 08xxxxxxxxxx
            ]);
        }

        // ==========================================
        // 3. BOARDING HOUSES (DATA RILL & BANYAK)
        // ==========================================
        
        $cities = [
            'Jakarta Selatan' => ['Setiabudi', 'Tebet', 'Kuningan', 'Mampang', 'Cilandak'],
            'Jakarta Barat' => ['Palmerah', 'Grogol', 'Kebon Jeruk', 'Tanjung Duren'],
            'Bandung' => ['Dago', 'Dipatiukur', 'Ciumbuleuit', 'Geger Kalong', 'Jatinangor'],
            'Yogyakarta' => ['Seturan', 'Gejayan', 'Kaliurang', 'Pogung', 'Babarsari'],
            'Surabaya' => ['Gubeng', 'Sukolilo', 'Keputih', 'Dukuh Kupang'],
            'Malang' => ['Suhat', 'Dinoyo', 'Sigura-gura', 'Lowokwaru'],
            'Semarang' => ['Tembalang', 'Gunung Pati', 'Sampangan'],
            'Bali' => ['Denpasar', 'Jimbaran', 'Dalung', 'Kuta Utara']
        ];

        $kosAdjectives = ['Indah', 'Nyaman', 'Asri', 'Executive', 'Residence', 'House', 'Living', 'Home', 'Griya', 'Wisma', 'Paviliun', 'Kost'];
        
        $allFacilities = ['WiFi', 'AC', 'Kamar Mandi Dalam', 'Kamar Mandi Luar', 'Water Heater', 'Kasur', 'Lemari', 'Meja Belajar', 'Dapur Bersama', 'Kulkas Umum', 'Dispenser', 'Parkir Motor', 'Parkir Mobil', 'CCTV', 'Security 24 Jam', 'Laundry', 'Rooftop', 'Gym', 'Kolam Renang'];

        $allRules = ['Dilarang Merokok di Kamar', 'Maksimal 2 Orang', 'Tamu Dilarang Menginap', 'Gerbang Tutup Jam 23.00', 'Bebas Jam Malam', 'Dilarang Membawa Hewan', 'Wajib Menjaga Kebersihan', 'Tamu Lawan Jenis Dilarang Masuk'];

        // Generate sekitar 3-4 kos per owner (Total ~60-80 Kos)
        foreach ($owners as $owner) {
            $totalKos = rand(2, 4); 

            for ($k = 0; $k < $totalKos; $k++) {
                $city = array_rand($cities);
                $district = fake()->randomElement($cities[$city]);
                
                $kosName = fake()->randomElement($kosAdjectives) . ' ' . fake()->firstName() . ' ' . $district;
                if (rand(0, 1)) $kosName = "Kost " . $district . " " . fake()->lastName();

                $category = fake()->randomElement(['putra', 'putri', 'campur']);
                
                $kos = BoardingHouse::create([
                    'user_id' => $owner->id,
                    'name' => $kosName,
                    'slug' => Str::slug($kosName) . '-' . Str::random(6),
                    'address' => "Jl. " . fake()->streetName() . " No. " . rand(1, 200) . ", " . $district . ", " . $city,
                    'description' => "Kos " . $category . " yang nyaman, bersih, dan strategis di kawasan " . $district . ". Cocok untuk mahasiswa dan karyawan. Dekat dengan pusat perbelanjaan dan kampus.",
                    'category' => $category,
                    'facilities' => fake()->randomElements($allFacilities, rand(4, 10)),
                    'rules' => fake()->randomElements($allRules, rand(3, 6)),
                    'cover_image' => null, // Image kosong dulu
                ]);

                // ==========================================
                // 4. ROOMS (10 - 20 KAMAR PER KOS)
                // ==========================================
                $totalRooms = rand(10, 20);
                
                for ($r = 1; $r <= $totalRooms; $r++) {
                    // Logic Status: 50% Occupied, 40% Available, 10% Maintenance
                    $randStatus = rand(1, 100);
                    if ($randStatus <= 50) $status = RoomStatus::OCCUPIED;
                    elseif ($randStatus <= 90) $status = RoomStatus::AVAILABLE;
                    else $status = RoomStatus::MAINTENANCE;

                    // Harga: 500rb - 3.5jt tergantung fasilitas (random logic)
                    $price = rand(5, 35) * 100000;
                    
                    // Nama Kamar: A1, A2 atau 101, 102
                    $roomName = (rand(0, 1) ? "Kamar " : "") . (rand(0, 1) ? chr(65 + rand(0, 2)) : "") . $r;

                    $room = Room::create([
                        'boarding_house_id' => $kos->id,
                        'name' => $roomName,
                        'price' => $price,
                        'capacity' => rand(1, 2),
                        'status' => $status,
                        'description' => 'Ukuran 3x4m, ventilasi baik, pencahayaan cukup.',
                    ]);

                    // ==========================================
                    // 5. TENANTS (JIKA OCCUPIED)
                    // ==========================================
                    if ($status === RoomStatus::OCCUPIED) {
                        $entryDate = fake()->dateTimeBetween('-1 year', '-1 month');
                        // Due date: Antara H-5 sampai H+20 dari hari ini (biar ada yang telat bayar)
                        $dueDate = now()->addDays(rand(-5, 25));

                        $tenant = Tenant::create([
                            'room_id' => $room->id,
                            'name' => fake('id_ID')->name(),
                            'identification_number' => fake()->nik(),
                            'phone' => '08' . fake()->numerify('##########'),
                            'emergency_contact' => '08' . fake()->numerify('##########'),
                            'entry_date' => $entryDate,
                            'due_date' => $dueDate, 
                            'status' => 'active',
                        ]);

                        // ==========================================
                        // 6. TRANSACTIONS (HISTORY & CURRENT)
                        // ==========================================
                        
                        // A. Transaksi Masa Lalu (History Lunas - 1 s/d 5 bulan ke belakang)
                        $monthsBack = rand(1, 5);
                        for ($m = 1; $m <= $monthsBack; $m++) {
                            $pastDate = (clone $entryDate)->modify("+$m month");
                            if ($pastDate > now()) break; // Jangan buat masa depan di loop ini

                            Transaction::create([
                                'tenant_id' => $tenant->id,
                                'room_id' => $room->id,
                                'invoice_number' => 'INV/' . $pastDate->format('Ymd') . '/' . Str::random(5),
                                'type' => TransactionType::RENT,
                                'amount' => $room->price,
                                'status' => TransactionStatus::PAID,
                                'due_date' => $pastDate,
                                'paid_at' => (clone $pastDate)->modify('+' . rand(0, 5) . ' days'),
                                'notes' => 'Pembayaran sewa bulan ke-' . $m,
                                'created_at' => $pastDate,
                            ]);
                        }

                        // B. Transaksi Bulan Ini (Tagihan Aktif)
                        // Status random: 70% Lunas, 30% Belum Lunas (Unpaid)
                        $isPaid = rand(1, 100) > 30; 
                        
                        Transaction::create([
                            'tenant_id' => $tenant->id,
                            'room_id' => $room->id,
                            'invoice_number' => 'INV/' . now()->format('Ymd') . '/' . Str::random(5),
                            'type' => TransactionType::RENT,
                            'amount' => $room->price,
                            'status' => $isPaid ? TransactionStatus::PAID : TransactionStatus::UNPAID,
                            'due_date' => $tenant->due_date,
                            'paid_at' => $isPaid ? now()->subDays(rand(0, 5)) : null,
                            'notes' => 'Tagihan sewa bulan berjalan',
                        ]);

                        // C. Tambahan Tagihan Lain (Listrik/Denda - Optional)
                        if (rand(0, 1)) {
                            Transaction::create([
                                'tenant_id' => $tenant->id,
                                'room_id' => $room->id,
                                'invoice_number' => 'ADD/' . now()->format('Ymd') . '/' . Str::random(5),
                                'type' => TransactionType::ELECTRICITY,
                                'amount' => rand(5, 20) * 10000, // 50rb - 200rb
                                'status' => TransactionStatus::UNPAID,
                                'due_date' => $tenant->due_date,
                                'notes' => 'Tagihan listrik tambahan',
                            ]);
                        }
                    }
                }
            }
        }
    }
}