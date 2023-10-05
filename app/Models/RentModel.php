<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Support\Facades\Crypt;

class RentModel extends Model
{
    use HasFactory;

    protected $table = 't_rent tr';

    public function rentDatatable($req)
    {
        $getData = DB::table("t_rent as tr")
            ->select("tr.id", "tc.name as car", "td.name as driver", "tr.status", "tr.created_at", "tr.rent_date")
            ->leftJoin("t_car as tc", "tr.car_id", "=", "tc.id")
            ->leftJoin("t_driver as td", "tr.driver_id", "=", "td.id");

        if (isset($req["length"]) && isset($req["start"])) {
            $getData->limit($req["length"])->offset($req["start"]);
        }

        if (isset($req['start_date'])) {
            $getData->where('tr.rent_date', '>=', $req['start_date']);
        }

        if (isset($req['end_date'])) {
            $getData->where('tr.rent_date', '<=', $req['end_date']);
        }

        $getData->orderBy('id', 'ASC');

        return $getData;
    }

    public function rentCar($req)
    {
        $date = date("Y-m-d H:i:s");

        $insertId = DB::table("t_rent")->insertGetId([
            "car_id"        => $req['rn_car'],
            "driver_id"     => $req['rn_driver'],
            "rent_date"     => $req['rn_date'],
            "status"        => 3,
            "created_at"    => $date,
            "updated_at"    => $date,
        ]);

        for ($i = 0; $i < count($req['rn_approval']); $i++) {
            $num = $i + 1;

            $status = 3;
            if ($num == 1) {
                $status = 0;
            }

            DB::table("t_rent_approval")->insert([
                "rent_id"   => $insertId,
                "user_id"   => $req['rn_approval'][$i],
                "status"    => $status,
                "seq"       => $num,
            ]);
        }

        return true;
    }

    public function approvalDatatable($req)
    {
        $getData = DB::table("t_rent_approval as tra")
            ->select("tra.id", "tc.name as car", "td.name as driver", "tr.rent_date")
            ->leftJoin("t_rent as tr", "tra.rent_id", "=", "tr.id")
            ->leftJoin("t_car as tc", "tr.car_id", "=", "tc.id")
            ->leftJoin("t_driver as td", "tr.driver_id", "=", "td.id")
            ->where("tra.status", 0)
            ->limit($req["length"])
            ->offset($req["start"]);

        if (session("account_role") == "M") {
            $id = Crypt::decryptString(session("account_id"));
            $getData->where("user_id", $id);
        }

        return $getData;
    }

    public function returnRent($id)
    {
        $update = DB::table('t_rent')->where('id', $id)->update([
            "status" => 2,
        ]);

        return $update;
    }

    public function approveRent($req)
    {
        DB::beginTransaction();
        $id = Crypt::decryptString($req->id);

        $date = date("Y-m-d H:i:s");
        $update = DB::table('t_rent_approval')->where('id', $id)->update([
            "status"          => $req->status,
            "approval_date"   => $date,
        ]);

        $getRent = DB::table('t_rent_approval')->select("rent_id")->where('id', $id)->first();

        if ($req->status == 2) {
            DB::table('t_rent')->where('id', $getRent->rent_id)->update([
                "status" => 4,
                "updated_at" => $date
            ]);
        } else if ($req->status == 1) {
            $getNext = DB::table('t_rent_approval')->where('status', 3)->where('rent_id', $getRent->rent_id)->orderBy('seq', 'ASC')->first();

            if (empty($getNext)) {
                DB::table('t_rent')->where('id', $getRent->rent_id)->update([
                    "status" => 1,
                    "updated_at" => $date
                ]);
            } else {
                DB::table('t_rent_approval')->where('id', $getNext->id)->update([
                    "status" => 0
                ]);
            }
        }

        DB::commit();
    }

    public function rentChart($data)
    {
        $get = DB::table('t_rent as tr')
        ->select("tc.name as car", DB::raw('count(*) as total'))
        ->leftJoin("t_car as tc", "tr.car_id", "=", "tc.id")
        ->whereIn('tr.status', [1,2])
        ->groupBy('tr.car_id');

        // dd(isset($data['start_date']));
        if (isset($data['start_date'])) {
            $get->where('tr.rent_date', '>=', $data['start_date']);
        }

        if (isset($data['end_date'])) {
            $get->where('tr.rent_date', '<=', $data['end_date']);
        }

        // dd($get->toSql());
        return $get;
    }
}
