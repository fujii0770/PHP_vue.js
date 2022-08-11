<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::middleware(['JsonApiMiddleware'])->group(function () {
    Route::post('/store-log', function (Request $request) {
        $ip_address = $request->get('ip_address')? $request->get('ip_address'):$request->getClientIp();
        $create_at =  $request->get('create_at')? $request->get('create_at'):date("Y-m-d H:i:s");
        try{
            if (isset($request['auth_flg']) && $request->get('user_id') && $request->get('mst_display_id') && $request->get('mst_operation_id')){
                DB::table('operation_history')->insert([
                    'auth_flg' => $request->get('auth_flg'),
                    'user_id' => $request->get('user_id'),
                    'mst_display_id' => $request->get('mst_display_id'),
                    'mst_operation_id' => $request->get('mst_operation_id'),
                    'result' => $request->get('result'),
                    'detail_info' => !empty($request->get('detail_info')) ? $request->get('detail_info') : '',
                    'ip_address' => $ip_address,
                    'create_at' => $create_at,
                ]);
            }else{
                Log::warning("Invalid request: ",[$request->all()]);
            }
        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            Log::error("Request: ",[$request->all()]);
            return response()->json(['status' => false]);
        }
       return response()->json(['status' => true]);
    })->middleware('auth.apikey');
});