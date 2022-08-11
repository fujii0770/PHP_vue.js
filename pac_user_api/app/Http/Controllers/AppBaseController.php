<?php

namespace App\Http\Controllers;

use App\Http\Utils\StatusCodeUtils;
use InfyOm\Generator\Utils\ResponseUtil;
use Response;

/**
 * @SWG\Swagger(
 *   basePath="/api/v1",
 *   @SWG\Info(
 *     title="Laravel Generator APIs",
 *     version="1.0.0",
 *   )
 * )
 * This class should be parent class for other API controllers
 * Class AppBaseController
 */
class AppBaseController extends Controller
{
    public function sendResponse($result, $message)
    {
        return Response::json(ResponseUtil::makeResponse($message, $result));
    }

    public function sendError($error, $code = 404)
    {
        return Response::json(ResponseUtil::makeError($error), $code);
    }

    public function sendApiResponse($message, $code = \Illuminate\Http\Response::HTTP_OK)
    {
        return Response::json(['status' => $code, 'message' => $message]);
    }

    public function sendApiError($error, $code = 404)
    {
        return Response::json(['status' => $code, 'message' => $error], $code);
    }

    public function sendSuccess($message)
    {
        return Response::json([
            'success' => true,
            'message' => $message,
        ], StatusCodeUtils::HTTP_OK);
    }
}
