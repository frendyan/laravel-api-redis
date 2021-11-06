<?php

namespace App\Http\Controllers;

use App\Models\Financing;
use App\Models\ReturnSchedule;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

use function PHPUnit\Framework\isEmpty;

class FinancingController extends Controller
{
    public function index()
    {
        try {
            $data = Financing::latest()->get();
            return response()->json(
                [
                    'message' => 'Financing fetched', 'data' => $data
                ]
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'status' => 'failed',
                    'message' => $e->getMessage()
                ],
                500
            );
        }
    }

    public function store(Request $request)
    {
        try {

            //validate input
            $validator = Validator::make($request->all(), [
                'financing_amount' => 'required|integer',
                'yearly_margin' => 'required|integer',
                'tenor' => 'required|integer',
                'main_payment_periode' => 'required|integer',
                'margin_payment_periode' => 'required|integer',
                'financing_start_date' => 'required|'
            ]);

            //return validation fail message
            if ($validator->fails()) {
                return response()->json($validator->errors());
            }

            //generate unique financing id
            $loop = true;
            while ($loop) {
                $financing_id = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 1, 5);
                $result = Financing::where('financing_id', $financing_id)->get();
                if (isEmpty($result)) {
                    $loop = false;
                }
            }

            //create return schedule base calculation variable
            $arr[0] = strtotime($request->financing_start_date); //StartDate
            $arr[1] = date("Y-m-d", strtotime("+" . $request->tenor . " month", $arr[0])); //endDateFinal
            $arr[2] = ($request->yearly_margin * $request->financing_amount) / 100; //margin total
            $arr[3] = number_format((float)$request->financing_amount / ($request->tenor / $request->main_payment_periode), 3, '.', ''); //pay per periode
            $arr[4] = number_format((float)($request->yearly_margin / 100) * $request->financing_amount / ($request->tenor / $request->margin_payment_periode), 3, '.', ''); //margin pay per periode

            //start database transactions
            $finance = new Financing;
            DB::transaction(function () use ($request, $financing_id, $finance, $arr) {

                //Save Finance
                $finance->financing_id = $financing_id;
                $finance->financing_amount = $request->financing_amount;
                $finance->yearly_margin = $request->yearly_margin;
                $finance->tenor = $request->tenor;
                $finance->main_payment_periode = $request->main_payment_periode;
                $finance->margin_payment_periode = $request->margin_payment_periode;
                $finance->financing_start_date = $request->financing_start_date;
                $finance->financing_due_date = $arr[1];
                $finance->save();

                /*
                    Create payment schedule
                */
                $amountLeft = $request->financing_amount;
                $marginLeft = $arr[2];

                //Save initial date
                $re = new ReturnSchedule;
                $re->financing_id = $finance->id;
                $re->pay_date = $request->financing_start_date;
                $re->k_pokok = 0;
                $re->k_margin = 0;
                $re->k_total = 0;
                $re->k_pokok_left = $amountLeft;
                $re->k_margin_left = $marginLeft;
                $re->save();

                //generate remaining schedule using for loop
                for ($i = 1; $i <= $request->tenor; $i++) {
                    $total = 0;

                    //create save record or ignore flag
                    $save = false;

                    //get next payment date
                    $payDate = date("Y-m-d", strtotime("+" . $i . " month", $arr[0]));

                    //create obj
                    $re = new ReturnSchedule;
                    $re->financing_id = $finance->id;
                    $re->pay_date = $payDate;
                    $re->k_pokok = 0;
                    $re->k_margin = 0;

                    // check if it's main payment date
                    if ($i % $request->main_payment_periode == 0) {
                        $re->k_pokok = $arr[3];
                        $amountLeft -= $arr[3];
                        $total += $arr[3];
                        $save = true;
                    }

                    // check if it's margin payment date
                    if ($i % $request->margin_payment_periode == 0) {
                        $re->k_margin = $arr[4];
                        $marginLeft -= $arr[4];
                        $total += $arr[4];
                        $save = true;
                    }

                    $re->k_total = $total;

                    //if it's last payment date then set main payment left and margin payment left to 0
                    if ($i == $request->tenor) {
                        $re->k_pokok_left = 0;
                        $re->k_margin_left = 0;
                    } else {
                        $re->k_pokok_left = $amountLeft;
                        $re->k_margin_left = $marginLeft;
                    }

                    if ($save) {
                        $re->save();
                    }
                }
            });

            $data = Financing::find($finance->id);

            return response()->json(
                [
                    'status' => 'success',
                    'message' => 'Financing created successfully',
                    'data' => $data,
                ],
                200
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'status' => 'failed',
                    'message' => $e->getMessage()
                ],
                500
            );
        }
    }

    public function show($id)
    {
        try {

            $cachedData = Redis::get('data_' . $id);


            if (isset($cachedData)) {
                $data = json_decode($cachedData, FALSE);

                return response()->json(
                    [
                        'status' => 'success',
                        'message' => 'Financing Detail REDIS fetch success',
                        'data' => $data,
                    ],
                    201
                );
            } else {
                $data = Financing::find($id);
                if (is_null($data)) {
                    return response()->json('Data not found', 404);
                }

                $data = Financing::find($id)->returnSchedules();
                Redis::set('data_' . $id, $data);

                return response()->json(
                    [
                        'status' => 'success',
                        'message' => 'Financing Detail DB fetch success',
                        'data' => $data,
                    ],
                    200
                );
            }
        } catch (\Exception $e) {

            return response()->json(
                [
                    'status' => 'failed',
                    'message' => $e->getMessage()
                ],
                500
            );
        }
    }
}
