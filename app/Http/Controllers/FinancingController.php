<?php

namespace App\Http\Controllers;

use App\Models\Financing;
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

            //get financing_start_date + tenor


            //get yearly_margin

            //

            //start database transactions
            $finance = new Financing;
            DB::transaction(function () use ($request, $financing_id, $finance) {
                // $data = Financing::create([
                $finance->financing_id = $financing_id;
                $finance->financing_amount = $request->financing_amount;
                $finance->yearly_margin = $request->yearly_margin;
                $finance->tenor = $request->tenor;
                $finance->main_payment_periode = $request->main_payment_periode;
                $finance->margin_payment_periode = $request->margin_payment_periode;
                $finance->financing_start_date = $request->financing_start_date;
                $finance->save();
                // ]);
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

                $data = Financing::find($id);
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
