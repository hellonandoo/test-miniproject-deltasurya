<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Login untuk Role Kasir dan Marketing
        if(!\App\Models\User::where('email', 'kasir@rsdeltasurya.com')->exists()) {
            \App\Models\User::create([
                'name' => 'Admin Kasir',
                'email' => 'kasir@rsdeltasurya.com',
                'password' => bcrypt('password123'),
                'role' => 'kasir',
            ]);
        }

        if(!\App\Models\User::where('email', 'marketing@rsdeltasurya.com')->exists()) {
            \App\Models\User::create([
                'name' => 'Admin Marketing',
                'email' => 'marketing@rsdeltasurya.com',
                'password' => bcrypt('password123'),
                'role' => 'marketing',
            ]);
        }

        // 2. Dummy Data Diskon / Voucher 
        // Asumsi asuransi di API memilki ID (misalnya: asuransi1, asuransi2 / String guid)
        // Kita cukup masukkan dua voucher contoh 
        DB::table('discount_vouchers')->insert([
            [
                'insurance_id' => '1',
                'insurance_name' => 'BPJS Kesehatan',
                'discount_type' => 'percentage',
                'discount_value' => 50, // Diskon 50%
                'max_discount' => 100000, // Maksimal diskon 100rb
                'valid_from' => Carbon::now()->subDays(10),
                'valid_until' => Carbon::now()->addDays(30),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'insurance_id' => '2',
                'insurance_name' => 'Prudential',
                'discount_type' => 'fixed',
                'discount_value' => 50000, 
                'max_discount' => null,
                'valid_from' => Carbon::now()->subDays(10),
                'valid_until' => Carbon::now()->addDays(30),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
