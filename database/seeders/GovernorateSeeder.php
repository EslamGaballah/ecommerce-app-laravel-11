<?php

namespace Database\Seeders;

use App\Models\Governorate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GovernorateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Governorate::create([
        //     ['name' => 'القاهرة', 'shipping_price' => 50],
        //     ['name' => 'الجيزة', 'shipping_price' => 55],
        //     ['name' => 'الإسكندرية', 'shipping_price' => 65],
        // ]);

        DB::table('governorates')->insert([
            ['name' => 'القاهرة', 'shipping_price' => 50, 'delivery_days' => '3' ],
            ['name' => 'الجيزة', 'shipping_price' => 55, 'delivery_days' => '4'],
            ['name' => 'الإسكندرية', 'shipping_price' => 65, 'delivery_days' => '6'],

        ]);

    }
}
