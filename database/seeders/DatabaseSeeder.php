<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Line;
use App\Models\LineProduction;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderDetailSize;
use App\Models\SignalBit\UserPassword;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // Lines Dummy
        // for ($i = 0;$i < 10;$i++) {
        //     Line::create([
        //         'name' => 'line '.sprintf("%02d", ($i+1)),
        //         'username' => 'line_'.sprintf("%02d", ($i+1)),
        //         'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        //         'remember_token' => Str::random(10)
        //     ]);
        // }

        // // Orders Dummy
        // $colours = [
        //     "red",
        //     "green",
        //     "blue"
        // ];

        // $sizes = [
        //     "s",
        //     "m",
        //     "l",
        //     "xl",
        //     "xxl",
        // ];

        // for ($i = 0;$i < 10;$i++) {
        //     Order::create([
        //         'ws_number' => "WS/".sprintf("%03d", ($i+1)),
        //         'buyer_name' => 'buyer '.sprintf("%02d", random_int(1,5)),
        //         'style_name' => 'style '.sprintf("%02d", random_int(1,5)),
        //         'product_type' => 'product type '.sprintf("%02d", random_int(1,5)),
        //         'qty' => random_int(100,1000),
        //         'qty_output' => 0
        //     ]);
        // }

        // for ($i = 0;$i < 10;$i++) {
        //     OrderDetail::create([
        //         'order_id' => ($i+1),
        //         'product_color' => $colours[(random_int(1,3) - 1)],
        //         'qty' => random_int(100,1000),
        //         'qty_output' => 0
        //     ]);
        // }

        // for ($i = 0;$i < 10;$i++) {
        //     OrderDetailSize::create([
        //         'order_detail_id' => ($i+1),
        //         'product_size' => $sizes[(random_int(1,5) - 1)],
        //         'qty' => random_int(100,1000),
        //         'qty_output' => 0
        //     ]);
        // }

        // // Line Productions Dummy
        // for ($i = 0;$i < 10;$i++) {
        //     LineProduction::create([
        //         'line_id' => ($i+1),
        //         'order_id' => ($i+1),
        //         'qty' => random_int(100, 1000),
        //         'qty_output' => 0
        //     ]);
        // }

        // Signal Bit UserPassword
        // for ($i = 0;$i < 10;$i++) {
        //     $userPassword = new UserPassword(array(
        //         'line_id' => ($i+1),
        //         'FullName' => 'SEWING LINE '.sprintf("%02d", ($i+1)),
        //         'Groupp' => 'SEWING',
        //         'username' => 'line_'.sprintf("%02d", ($i+1)),
        //         'Password' => 'password',
        //         'password_encrypt' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        //         'remember_token' => Str::random(10)
        //     ));

        //     $userPassword->timestamps = false;
        //     $userPassword->save();
        // }

        $userPassword = new UserPassword(array(
            'line_id' => '999',
            'FullName' => 'ADMIN SEWING',
            'Groupp' => 'ALLSEWING',
            'username' => 'admin_sewing',
            'Password' => 'password',
            'password_encrypt' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'remember_token' => Str::random(10)
        ));

        $userPassword->timestamps = false;
        $userPassword->save();
    }
}
