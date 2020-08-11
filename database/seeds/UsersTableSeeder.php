<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'yoghiyb',
            'identity_id' => '12345678345',
            'gender' => 1,
            'address' => 'sidoarjo',
            'photo' => 'dummy.png', //note: tidak ada gambar
            'email' => 'admin@yoghiyb.id',
            'password' => app('hash')->make('secret'),
            'phone_number' => '085343966997',
            'api_token' => Str::random(40),
            'role' => 0,
            'status' => 1
        ]);
    }
}
