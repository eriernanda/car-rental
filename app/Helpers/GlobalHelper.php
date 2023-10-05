<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class GlobalHelper
{
    public static function checkCarAvailability($id, $date)
    {
        $check = DB::table('t_rent')->select('id')->where("car_id", $id)->where("rent_date", $date)->whereIn('status', [1, 3]);

        return $check->exists();
    }

    public static function checkDriverAvailability($id, $date)
    {
        $check = DB::table('t_rent')->select('id')->where("driver_id", $id)->where("rent_date", $date)->whereIn('status', [1, 3]);

        return $check->exists();
    }
}
