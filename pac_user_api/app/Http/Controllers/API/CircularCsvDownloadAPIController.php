<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\SearchCircularUserAPIRequest;
use App\Http\Utils\DownloadUtils;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Facades\Log;
use Session;
use Response;

/**
 * Class CircularUserController
 * @package App\Http\Controllers\API
 */
class CircularCsvDownloadAPIController extends AppBaseController
{

    public function csvDownload(SearchCircularUserAPIRequest $request)
    {
        try {
            $user = $request->user();
            // circulars_completed_yyyyMMDDHHmmss.csv
            $file_name = 'circulars_completed_' . \Carbon\Carbon::now()->format('YmdHis') . '.csv';

            $circular_type = $request->get('circular_type', 'completed');
            switch ($circular_type) {
                case 'completed':
                    $function = 'getCompletedCircularData';
                    break;
            }
            $result = DownloadUtils::downloadRequest(
                $user, 'App\Http\Utils\CsvCircularDownloadControllerUtils', $function, $file_name,
                $user, $request->all()
            );
            if(!($result === true)){
                return $this->sendError(__('message.false.download_request.download_ordered', ['attribute' => $result]), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return response()->json(['status' => true, 'message' => __('message.success.download_request.download_ordered', ['attribute' => $file_name])]);

        } catch (\Throwable $th) {
            Log::error($th->getMessage() . $th->getTraceAsString());
            return $this->sendError(__('message.false.download_request.download_ordered', ['attribute' => $th->getMessage()]), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
