<?php

namespace App\Http\Utils;

use App\Models\Admin;
use App\Models\User;
use App\Models\AdminPasswordResets;
use App\Models\UserPasswordResets;
use phpDocumentor\Reflection\Types\Integer;

/**
 * 管理者・利用者のパスワードのユーティリティクラス
 */
class PasswordUtils {

	protected $admins;
	protected $users;
	protected $admin_password_resets;
	protected $user_password_resets;

	/**
	 * コンストラクタ
	 *
	 * @param Admin $admins
	 * @param User $users
	 * @param AdminPasswordResets $admin_password_resets
	 * @param UserPasswordResets $user_password_resets
	 */
	function __construct(Admin $admins, User $users, AdminPasswordResets $admin_password_resets, UserPasswordResets $user_password_resets)
	{
		$this->admins 					= $admins;
		$this->users 					= $users;
		$this->admin_password_resets 	= $admin_password_resets;
		$this->user_password_resets 	= $user_password_resets;
	}

	/**
	 * 管理者のパスワード設定コード発行
	 *
	 * @param $mst_admin_id
	 * @return string
	 */
	public function createAdminPasswordSettingCode(int $mst_admin_id): string {
		// 時間を「YYYYMMddhhmmss」の形で取得。
		$time = date('YmdHis');
		// コードを生成(ハッシュ化前)。
		$code = $mst_admin_id . 'admin' . $time;
		// ハッシュ化。
		$code = mhash(MHASH_MD5, $code);
		$code = base64_encode($code);
		$code = substr($code, 0, 22);
		// 対象ユーザーのメールアドレス取得。
		$admin = $this->admins->select('email')->where('id', $mst_admin_id)->first();
		$email = $admin->email;
		// 登録されているメールからの変更データを削除
		$this->admin_password_resets->where('email', '=', $email)->delete();
		// codeを対象のDBに格納。
		$this->admin_password_resets->updateOrInsert(
			['email' => $email],
			['token' => 'code','created_at' => $time,'code' => $code]
		);

		return $code;
	}

	/**
	 * 利用者のパスワード設定コード発行
	 *
	 * @param $mst_user_id
	 * @return string
	 */
	public function createUserPasswordSettingCode($mst_user_id): string {
		// 時間を「YYYYMMddhhmmss」の形で取得。
		$time = date('YmdHis');
		// コードを生成(ハッシュ化前)。
		$code = $mst_user_id . 'user' . $time;
		// ハッシュ化。
		$code = mhash(MHASH_MD5, $code);
		$code = base64_encode($code);
		$code = substr($code, 0, 22);
		// 対象ユーザーのメールアドレス取得。
		$user = $this->users->select('email')->where('id', $mst_user_id)->first();
		$email = $user->email;
		// 登録されているメールからの変更データを削除
		$this->user_password_resets->where('email', '=', $email)->delete();
		// codeを対象のDBに格納。
		$this->user_password_resets->updateOrInsert(
			['email' => $email],
			['token' => 'code','created_at' => $time,'code' => $code]
		);

		return $code;
	}

}