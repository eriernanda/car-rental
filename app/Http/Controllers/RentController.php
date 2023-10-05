<?php

namespace App\Http\Controllers;

use App\Helpers\GlobalHelper;
use App\Helpers\JsonFormatter;
use App\Http\Controllers\Controller;
use App\Models\RentModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DataTables;
use DB;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;

class RentController extends Controller
{
    public function rentView()
    {
        return view('rental.all');
    }

    public function rentAjaxData(Request $request)
    {
        try {
            $rent = new RentModel();
            $rows = $rent->RentDatatable($request);

            $data = DataTables::of($rows)->make(true);

            $i = 1;
            foreach ($data->original['data']as $key => $val) {
                $data->original['data'][$key]['num']          = $i;
                $data->original['data'][$key]['id']           = Crypt::encryptString($val['id']);
                $data->original['data'][$key]['rent_date']    = date("d-m-Y", strtotime($val['rent_date']));

                $i++;
            }

            return JsonFormatter::datatables($data);
        } catch (Exception $err) {
            return JsonFormatter::error(null, $err->getMessage(), 500);
        }
    }

    public function addRentView()
    {
        $data["car"]    = DB::table("t_car")->where("flag", 1)->get();
        $data["driver"] = DB::table("t_driver")->where("flag", 1)->get();
        $data["user"]   = DB::table("t_account")->where("role", "M")->where("flag", 1)->get();

        return view('rental.add', compact('data'));
    }


    public function addRentSubmit(Request $request)
    {
        try {
            $validateData = Validator::make($request->all(), [
                'rn_car'        => 'required',
                'rn_driver'     => 'required',
                'rn_date'       => 'required',
                'rn_approval'   => 'required'
            ]);

            if($validateData->fails()) {
                return JsonFormatter::error(null, $validateData->errors(), 422);
            }

            if (count($request->rn_approval) < 2) {
                return JsonFormatter::error(null, "Must Include Atleast 2 Approval", 422);
            }

            DB::beginTransaction();

            if (GlobalHelper::checkCarAvailability($request->rn_car, $request->rn_date)) {
                return JsonFormatter::error('', 'Car Not Available', 404);
            }

            if (GlobalHelper::checkDriverAvailability($request->rn_driver, $request->rn_date)) {
                return JsonFormatter::error('', 'Driver Not Available', 404);
            }

            $rent = new RentModel();
            if (!$rent->rentCar($request->all())) {
                DB::rollback();

                return JsonFormatter::error('', 'Failed to Rent', 400);
            }

            DB::commit();
            return JsonFormatter::success('', 'Success to Rent', 200);

        } catch (Exception $err) {
            return JsonFormatter::error(null, $err->getMessage(), 500);
        }
    }

    public function returnRentSubmit(Request $request)
    {
        try {
            $validateData = Validator::make($request->all(), [
                'id' => 'required'
            ]);

            if($validateData->fails()) {
                return JsonFormatter::error(null, $validateData->errors(), 422);
            }

            $id = Crypt::decryptString($request->id);

            $rent = new RentModel();
            $rent->returnRent($id);

            return JsonFormatter::success(null, "Success");
        } catch (Exception $err) {
            return JsonFormatter::error(null, $err->getMessage(), 500);
        }
    }

    public function approveView()
    {
        return view('rental.approval');
    }

    public function approveAjaxData(Request $request)
    {
        try {
            $rent = new RentModel();
            $rows = $rent->approvalDatatable($request);

            $data = DataTables::of($rows)->make(true);

            $i = 1;
            foreach ($data->original['data']as $key => $val) {
                $data->original['data'][$key]['num']          = $i;
                $data->original['data'][$key]['id']           = Crypt::encryptString($val['id']);
                $data->original['data'][$key]['rent_date']    = date("d-m-Y", strtotime($val['rent_date']));

                $i++;
            }

            return JsonFormatter::datatables($data);
        } catch (Exception $err) {
            return JsonFormatter::error(null, $err->getMessage(), 500);
        }
    }

    public function approveSubmit(Request $request)
    {
        try {
            $validateData = Validator::make($request->all(), [
                'id' => 'required',
                'status' => 'required'
            ]);

            if($validateData->fails()) {
                return JsonFormatter::error(null, $validateData->errors(), 422);
            }

            $rent = new RentModel();
            $rent->approveRent($request);

            return JsonFormatter::success(null, "Success");
        } catch (Exception $err) {
            return JsonFormatter::error(null, $err->getMessage(), 500);
        }
    }

    public function exportExcel(Request $request)
    {
        $rent = new RentModel();
        $data = $rent->rentDatatable($request)->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'No.');
        $sheet->setCellValue('B1', 'Car');
        $sheet->setCellValue('C1', 'Driver');
        $sheet->setCellValue('D1', 'Request Date');
        $sheet->setCellValue('E1', 'Last Status');

        $i = 1;

        foreach ($data as $value) {
            $mapStatus = array(
                1 => "Active",
                2 => "Returned",
                3 => "Approval Process",
                4 => "Rejected",
            );

            $sheet->setCellValue('A' . ($i+1), $i);
            $sheet->setCellValue('B' . ($i+1), $value->car);
            $sheet->setCellValue('C' . ($i+1), $value->driver);
            $sheet->setCellValue('D' . ($i+1), date("Y-m-d H:i:s", strtotime($value->created_at)));
            $sheet->setCellValue('E' . ($i+1), $mapStatus[$value->status] ?? "-");

            $i++;
        }
        $writer = new Xlsx($spreadsheet);

        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="Report_File.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
    }

    public function rentChart(Request $request)
    {
        try {
            $rent = new RentModel();
            $data = $rent->rentChart($request->all())->get();

            return JsonFormatter::success($data);
        } catch (Exception $err) {
            return JsonFormatter::error(null, $err->getMessage(), 500);
        }
    }
}
