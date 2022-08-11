<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EpsTAPIController extends AppBaseController
{

    public function deleteEpsTAppAndItems($id, Request $request)
    {
        $user = $request->user();
        try {
            DB::beginTransaction();
            $data['update_at'] = Carbon::now();
            $data['update_user'] = $user->email;
            $data['deleted_at'] = Carbon::now();

            DB::table('eps_t_app')
                ->where([
                    'id' => $id,
                    'mst_company_id' => $user->mst_company_id,
                ])->update($data);

            DB::table('eps_t_app_items')
                ->where([
                    't_app_id' => $id,
                    'mst_company_id' => $user->mst_company_id,
                ])->update($data);
            // Todo action delete file is soft delete or hard delete

            DB::commit();

            return $this->sendSuccess('経費申請書削除処理に成功しました。');
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error("$this->controllerName@deleteEpsTAppAndItems");
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR],
                \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);

        }

    }

}
