<?php

namespace App\Jobs\FormIssuance;

use Illuminate\Support\Facades\DB;

/**
 * 帳票のテンプレートやリクエストが有効かどうかを確認するクラス
 */
class FormFlagChecker {
    private $id;
	private $company_id;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id, $company_id) {
        $this->id = $id;
		$this->company_id = $company_id;
    }
	
	/**
	 * @return 
	 */
	protected function get_flags() {
		return DB::table("frm_imp_mgr AS mgr")
				->leftJoin("frm_template AS tpl", function($join) {
					$join->on("mgr.frm_template_id", "=", "tpl.id");
					$join->on("mgr.mst_company_id", "=", "tpl.mst_company_id");
				})
				->where("mgr.id", $this->id)
				->where("mgr.mst_company_id", $this->company_id)
				->select("mgr.cancel_req_datetime as cancel", 
						"tpl.id as template_id",
						"tpl.disabled_at as disabled", 
						"mgr.frm_template_ver as expected_version", 
						"tpl.version as template_version")
				->first();
	}

	public function check_flags() {
		$res = $this->get_flags();
		if (!$res) {
			throw new RequiredDataNotFoundException();
		} else {
			if ($res->template_id === null) {
				throw new DisabledTemplateException(
					DisabledTemplateException::NOT_FOUND
				);
			}
			if ($res->cancel !== null) {
				throw new CancelRequestException();
			}
			if ($res->disabled !== null) {
				throw new DisabledTemplateException();
			}
			if ($res->expected_version !== $res->template_version) {
				throw new DisabledTemplateException(
					DisabledTemplateException::UNMATCHED_VERSION
				);
			}
		}	
		return true;
	}

}
