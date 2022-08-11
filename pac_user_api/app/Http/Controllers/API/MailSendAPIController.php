<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Utils\AdvertisementUtils;
use App\Http\Utils\StatusCodeUtils;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Http\Utils\AppUtils;
use App\Http\Utils\MailUtils;
use App\Mail\SendTrialSuccessMail;
use App\Mail\SendAccessCodeNoticeMail;
use App\Mail\SendAssignCompanyStamp;
use App\Mail\SendChangePasswordMail;
use App\Mail\SendCircularDeleteMail;
use App\Mail\SendCircularPullBackMail;
use App\Mail\SendCircularReNotificationMail;
use App\Mail\SendCircularUserMail;
use App\Mail\SendExportDepartmentAlert;
use App\Mail\SendFinishMail;
use App\Mail\SendIpRestrictionMail;
use App\Mail\SendMailAlertDiskQuota;
use App\Mail\SendMailAlertFileExpired;
use App\Mail\SendMailAlertLongTermStorage;
use App\Mail\SendMailInitPassword;
use App\Mail\SendMfaMail;
use App\Mail\SendTrialDuplicateErrorMail;
use App\Mail\SendInitPasswordMail;
use App\Mail\SendTrialUserInitPasswordMail;
use App\Mail\SendBatchHistoryMail;
use App\Mail\UserRegistrationCompleteMail;
use App\Mail\SendBoxRefreshTokenUpdateFailedMail;
use App\Mail\SendTimestampsNotifyMail;
use App\Mail\SendToDoListDeadlineNoticeMail;


class MailSendAPIController extends AppBaseController
{
	public function mailSend(Request $request)
	{
		$params = $request->all();

		if (!isset($params['id']) || !$params['id']) {
			return response()->json(['message' => '送信idが入力されていません。', 'status' => 401], 401);
		}

		try {
			// 処理データ
			$item = DB::table('mail_send_resume')->find($params['id']);

			// 存在チェック
			if (!$item) {
				return response()->json(['message' => '送信idが間違っています。', 'status' => 402], 402);
			}

			$codes = array_column(MailUtils::MAIL_DICTIONARY,'CODE');

			// 指定したテンプレート存在しない
			if (!in_array($item->template, $codes)) {
				return response()->json(['message' => '指定したレコードのテンプレートが正しくない。', 'status' => 403], 403);
			}
            $arr = array_values(MailUtils::MAIL_DICTIONARY);

			// 送信
			$template = 'App\Mail\\' . $arr[array_search($item->template, $codes)]['SERVICE'];
			$data = json_decode($item->param, true);
			// PAC_5-2490
            // サムネイル画像を見えなければ、回覧IDから、サムネイル画像を再作成します
			if(isset($data['image_path']) && $data['image_path'] != null && $data['image_path'] != '' && (!file_exists($data['image_path']) || false === realpath($data['image_path']))){
                $circular = DB::table('circular')
                    ->select('first_page_data')
                    ->where('id', $data['circular_id'])
                    ->first();
                file_put_contents($data['image_path'], base64_decode(AppUtils::decrypt($circular->first_page_data)));
            }
			//PAC_5-3095 ファイルメール便　広告付きメールが送れない。
            if ((isset($data['top_advertisement']) && $data['top_advertisement'] != null && !file_exists($data['top_advertisement']['path'])) ||
                (isset($data['middle_advertisement']) && $data['middle_advertisement'] != null && !file_exists($data['middle_advertisement']['path'])) ||
                (isset($data['end_advertisement']) && $data['end_advertisement'] != null && !file_exists($data['end_advertisement']['path']))) {
                AdvertisementUtils::getDiskMailAdvertisement($data['mst_company_id']);
            }

            // PAC_5-2490
			$data['mail_subject'] = $item->subject;
			$data['mail_code'] = $item->template;
			$isPlain = $item->email_format == 0;
			Mail::to(explode(',',$item->to_email))->send(new $template($data, $isPlain));

		} catch (\Exception $ex) {
			Log::error($ex->getMessage() . $ex->getTraceAsString());
			return response()->json(['message' => $ex->getMessage(), 'status' => 500], 500);
		}

		return response()->json(['message' => 'メール送信成功', 'status' => StatusCodeUtils::HTTP_OK], StatusCodeUtils::HTTP_OK);
	}
}
