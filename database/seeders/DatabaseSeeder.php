<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->_accountSeed();
        $this->_carSeed();
        $this->_driverSeed();
    }

    private function _accountSeed()
    {
        DB::table('t_account')->insert([
            'name'        => 'Eri Ernanda',
            'email'       => 'eri@email.com',
            'password'    => Hash::make("eri_backend"),
            'role'        => "A",
            'flag'        => 1,
        ]);

        $arrEmail = ["tes@email.com", "you@email.com", "me@email.com", "they@email.com"];

        for ($i = 0; $i < count($arrEmail); $i++) {
            $num = $i + 1;

            DB::table('t_account')->insert([
                'name'        => "Test Account {$num}",
                'email'       => $arrEmail[$i],
                'password'    => Hash::make("12345678"),
                'role'        => "M",
                'flag'        => 1,
            ]);
        }
    }

    private function _carSeed()
    {
        $arrPolNum = ["N 1341 JH", "N 2582 HG", "N 2952 UG", "N 7173 UJ"];
        $arrCar = ["Toyota Camry", "Ford Mustang", "Honda Civic", "BMW X5"];

        for ($i = 0; $i < count($arrCar); $i++) {
            DB::table('t_car')->insert([
                'name'            => $arrCar[$i],
                'police_number'   => $arrPolNum[$i],
                'flag'            => 1,
            ]);
        }
    }

    private function _driverSeed()
    {
        $arrName    = ["Muklis", "Abdul", "Nia", "Rani"];
        $arrGender  = ["Male", "Male", "Female", "Female"];

        for ($i = 0; $i < count($arrName); $i++) {
            DB::table('t_driver')->insert([
                'name'    => $arrName[$i],
                'gender'  => $arrGender[$i],
                'flag'    => 1,
            ]);
        }
    }
}
