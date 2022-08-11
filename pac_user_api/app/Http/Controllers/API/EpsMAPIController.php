<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\UpdateExpenseFormInputAPIRequest;
use App\Http\Utils\AppUtils;
use App\Http\Utils\ExpenseUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

class EpsMAPIController extends AppBaseController
{
    private $controllerName = 'EpsMAPIController';

    public function getEpsMPurposeInfo(Request $request)
    {
        $user = $request->user();
        try {
            $result = [];
            $query = DB::table('eps_m_purpose')
                ->join('eps_m_form_purpose', function ($join) {
                    $join->on('eps_m_purpose.mst_company_id', 'eps_m_form_purpose.mst_company_id');
                    $join->on('eps_m_purpose.purpose_name', 'eps_m_form_purpose.purpose_name');
                })
                ->join('eps_m_form', function ($join) {
                    $join->on('eps_m_form.mst_company_id', 'eps_m_form_purpose.mst_company_id');
                    $join->on('eps_m_form.form_code', 'eps_m_form_purpose.form_code');
                })
                ->select('eps_m_purpose.purpose_name',
                    'eps_m_form.form_name',
                    'eps_m_form.form_type',
                    'eps_m_form.form_code',
                    'eps_m_form.form_describe')
                ->where([
                    'eps_m_purpose.mst_company_id' => $user->mst_company_id,
                ]);

            $queryGetFormSettlement = clone $query;
            $mFormAdvance = $query->where([
                'eps_m_form.form_type' => AppUtils::EPS_M_FORM_FORM_TYPE_ADVANCE
            ])->get();
            $mFormSettlement = $queryGetFormSettlement->where([
                'eps_m_form.form_type' => AppUtils::EPS_M_FORM_FORM_TYPE_SETTLEMENT
            ])->get();
            $result['m_form_advance'] = $mFormAdvance;
            $result['m_form_settlement'] = $mFormSettlement;

            return $this->sendResponse($result, 'getEpsMPurposeInfo success');

        } catch (\Exception $ex) {
            Log::error("$this->controllerName@getEpsMPurposeInfo");
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function getMFormPurposeDataSelect(Request $request)
    {
        $input = $request->all();
        try {
            $user = $request->user();
            $data = DB::table('eps_m_purpose')
                ->where([
                    'eps_m_purpose.mst_company_id' => $user->mst_company_id,
                ]);

            if (isset($input['form_code'])) {
                $data = $data
                    ->join('eps_m_form_purpose', function ($join) {
                        $join->on('eps_m_purpose.mst_company_id', 'eps_m_form_purpose.mst_company_id');
                        $join->on('eps_m_purpose.purpose_name', 'eps_m_form_purpose.purpose_name');
                    })
                    ->where([
                    'eps_m_form_purpose.form_code' => $input['form_code']
                ]);

            }
            $data = $data->select('eps_m_purpose.purpose_name')->get();

            return $this->sendResponse($data, 'getMFormPurposeDataSelect success');
        } catch (\Exception $ex) {
            Log::error("$this->controllerName@getMFormPurposeDataSelect");
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function getEpsMWtsmName(Request $request)
    {
        $data = $request->all();
        $user = $request->user();

        try {
            $wtsmNameData = [];
            $itemsMax = [];
            $validityPeriod = [];
            if (isset($data['form_code'])) {
                $wtsmNameData = DB::table('eps_m_form_wtsm')
                    ->join('eps_m_form', function ($join) {
                        $join->on('eps_m_form.mst_company_id', 'eps_m_form_wtsm.mst_company_id');
                        $join->on('eps_m_form.form_code', 'eps_m_form_wtsm.form_code');
                    })->join('eps_m_wtsm', function ($join) {
                        $join->on('eps_m_wtsm.mst_company_id', 'eps_m_form_wtsm.mst_company_id');
                        $join->on('eps_m_wtsm.wtsm_name', 'eps_m_form_wtsm.wtsm_name');
                    })->select(
                        'eps_m_form_wtsm.form_code',
                        'eps_m_wtsm.wtsm_name',
                        'eps_m_wtsm.num_people_option',
                        'eps_m_wtsm.detail_option',
                        'eps_m_wtsm.voucher_option',
                        'eps_m_wtsm.tax_option',
                        'eps_m_wtsm.num_people_describe',
                        'eps_m_wtsm.detail_describe')
                    ->where([
                        'eps_m_form_wtsm.mst_company_id' => $user->mst_company_id,
                        'eps_m_form_wtsm.form_code' => $data['form_code']
                    ])->get();
                $itemsMax = DB::table('eps_m_form')
                    ->select('items_max')
                    ->where([
                        'eps_m_form.mst_company_id' => $user->mst_company_id,
                        'eps_m_form.form_code' => $data['form_code'],
                    ])->first();
                $validityPeriod = DB::table('eps_m_form')
                    ->where([
                        'eps_m_form.mst_company_id' => $user->mst_company_id,
                        'eps_m_form.form_code' => $data['form_code'],
                    ])->select('eps_m_form.validity_period_from',
                        'eps_m_form.validity_period_to'
                    )->first();
            }

            $result['wtsm_name_data'] = $wtsmNameData;
            $result['items_max'] = null;
            $result['validity_period'] = null;

            if ($itemsMax) {
                $result['items_max'] = $itemsMax->items_max;
            }
            if ($validityPeriod) {
                $result['validity_period'] = $validityPeriod;
            }

            return $this->sendResponse($result, 'Get ListTAppItems Success');

        } catch (\Exception $ex) {
            Log::error("$this->controllerName@getEpsMWtsmName");
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts
            [\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR],
                \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }

    public function getEpsMFormRelation(Request $request)
    {
        $data = $request->all();
        $formCode = isset($data['form_code']) ? $data['form_code'] : null;
        try {
            $result = null;
            if ($formCode) {
                // Todo: get eps_m_form_purpose.purpose_name
                $result = DB::table('eps_m_form')
                    ->join('eps_m_form_relation', function ($join) {
                        $join->on('eps_m_form_relation.mst_company_id', 'eps_m_form.mst_company_id');
                        $join->on('eps_m_form_relation.relation_form_code', 'eps_m_form.form_code');
                    })->where([
                    'eps_m_form_relation.form_code' => $formCode,
                    'eps_m_form.form_type' => AppUtils::EPS_M_FORM_FORM_TYPE_SETTLEMENT,
                    ])->select('eps_m_form.form_name',
                        'eps_m_form.form_code',
                        'eps_m_form.form_describe',
                        'eps_m_form.form_type'
                    )->first();

            }
            return $this->sendResponse($result, 'getEpsMFormRelation Success');
        } catch (\Exception $ex) {
            Log::error("$this->controllerName@getEpsMFormRelation");
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR],
                \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function updateExpenseFormInput(UpdateExpenseFormInputAPIRequest $request)
    {
        $data = $request->all();
        $user = $request->user();
        try {
            $mFormInfo = null;
            $result = null;
            $isError = false;
            $filePath = null;
            $formCode = $data['form_code'];
            $tAppId = $data['t_app_id'];

            $formType = AppUtils::EPS_M_FORM_FORM_TYPE_UNKNOWN;
            $mFormInfo = DB::table('eps_m_form')
                ->where([
                    'mst_company_id' => $user->mst_company_id,
                    'form_code' => $formCode,
                ])->select(
                    's3_path',
                    'form_type',
                    's3_file_name',
                    'origin_file_name')->first();
            if ($mFormInfo) {
                $result['file_name'] = $mFormInfo->origin_file_name;
                $result['storage_file_name'] = $mFormInfo->s3_file_name;
                $formType = $mFormInfo->form_type;
                $local_path = ExpenseUtils::localExpensePath($user->mst_company_id, $user->id);
                $s3PathFile = $mFormInfo->s3_path . '/' . $mFormInfo->s3_file_name;
                if (Storage::disk('s3')->exists($s3PathFile)) {
                    $getFile = Storage::disk('s3')->get($s3PathFile);
                    Log::info('Get Expense Form: ' . $s3PathFile);
                    Storage::disk('local')->put($local_path . $mFormInfo->s3_file_name, $getFile);
                    $filePath = storage_path('app/' . $local_path . $mFormInfo->s3_file_name);
                } else {
                    Log::error("$this->controllerName@updateExpenseFormInput: error s3_path: $s3PathFile");
                    $isError = true;
                }

            }
            $placeholderList = DB::table('expense_placeholder_data')
                ->where([
                    'eps_m_form_code' => $formCode,
                    'mst_company_id' => $user->mst_company_id,
                    'deleted_at' => null,
                ])->select('template_placeholder_name',
                    'cell_address')->get()->toArray();
            if (count($placeholderList) > 0) {
                $placeholderList = array_map('get_object_vars', $placeholderList);
                $companyName = null;
                $departmentName = null;

                $userName = $user->family_name . $user->given_name;
                $companyInfo = DB::table('mst_company')
                    ->where('mst_company.id', $user->mst_company_id)
                    ->select('mst_company.company_name')->first();

                $departmentInfo = DB::table('mst_department')
                    ->leftJoin('mst_user_info', 'mst_user_info.mst_department_id',
                        'mst_department.id')
                    ->where([
                        'mst_user_info.mst_user_id' => $user->id,
                        'mst_department.mst_company_id' => $user->mst_company_id,
                    ])->select('mst_department.department_name')
                    ->first();
                if ($companyInfo) {
                    $companyName = $companyInfo->company_name;
                }
                if ($departmentInfo) {
                    $departmentName = $departmentInfo->department_name;
                }

                $formInfo = DB::table('eps_m_form')
                    ->where([
                        'mst_company_id' => $user->mst_company_id,
                        'form_code' => $formCode,
                        'deleted_at' => null,
                    ])->select('form_type')->first();

                $tAppData = DB::table('eps_t_app')
                    ->where([
                        'mst_company_id' => $user->mst_company_id,
                        'id' => $tAppId,
                        'form_code' => $formCode,
                    ])->select('suspay_amt', 'eps_amt')->first();
                $suspayAmt = 0;
                $epsAmt = 0;
                $dataTAppItem = DB::table('eps_t_app_items')
                    ->where([
                        'mst_company_id' => $user->mst_company_id,
                        't_app_id' => $tAppId,
                        'deleted_at' => null,
                    ])->select('wtsm_name',
                        'expected_pay_date',
                        'expected_pay_amt',
                        'remarks',
                        'from_station',
                        'to_station',
                        'expected_pay_amt'
                    )
                    ->get()->toArray();
                if ($tAppData) {
                    $suspayAmt = $tAppData->suspay_amt;
                    $epsAmt = $tAppData->eps_amt;
                }
                if ($formInfo) {
                    $formType = $formInfo->form_type;
                }

                $indexCurrentItem = 0;
                $quantityOfElementGotFormItem = 0;
                $maxIndexTAppItem = count($dataTAppItem) - 1;
                $overTAppItem = false;
                foreach ($placeholderList as &$item) {
                    $dataInput = null;
                    $placeHolderName = $item['template_placeholder_name'];
                    $isItemData = false;
                    $fieldData = null;
                    if (strpos($placeHolderName, ExpenseUtils::EXPENSE_PLACE_HOLDER_DATA_WTSM_NAME) !== false) {
                        $fieldData = ExpenseUtils::EXPENSE_INPUT_FOR_PLACE_HOLDER_WTSM_NAME;;
                        $isItemData = true;
                    } else if (strpos($placeHolderName, ExpenseUtils::EXPENSE_PLACE_HOLDER_DATA_EXPECTED_PAY_DATE) !== false) {
                        $fieldData = ExpenseUtils::EXPENSE_INPUT_FOR_PLACE_HOLDER_EXPECTED_PAY_DATE;
                        $isItemData = true;
                    } else if (strpos($placeHolderName, ExpenseUtils::EXPENSE_PLACE_HOLDER_DATA_DESCRIBE) !== false) {
                        $fieldData = ExpenseUtils::EXPENSE_INPUT_FOR_PLACE_HOLDER_DESCRIBE;
                        $isItemData = true;
                    } else if (strpos($placeHolderName, ExpenseUtils::EXPENSE_PLACE_HOLDER_DATA_EXPECTED_PAY_AMT) !== false) {
                        $fieldData = ExpenseUtils::EXPENSE_INPUT_FOR_PLACE_HOLDER_EXPECTED_PAY_AMT;
                        $isItemData = true;
                    } else {
                        switch ($item['template_placeholder_name']) {
                            case ExpenseUtils::EXPENSE_PLACE_HOLDER_DATA_COMPANY_NAME:
                                $dataInput = $companyName;
                                break;
                            case ExpenseUtils::EXPENSE_PLACE_HOLDER_DATA_DEPARTMENT_NAME:
                                $dataInput = $departmentName;
                                break;
                            case ExpenseUtils::EXPENSE_PLACE_HOLDER_DATA_USER_NAME:
                                $dataInput = $userName;
                                break;
                            case ExpenseUtils::EXPENSE_PLACE_HOLDER_DATA_TOTAL:
                                if ($formType == AppUtils::EPS_M_FORM_FORM_TYPE_ADVANCE) {
                                    $dataInput = $suspayAmt;
                                } else {
                                    $dataInput = $epsAmt;
                                }
                                break;
                        }
                    }

                    if ($isItemData) {
                        $item['field'] = $fieldData;
                        $quantityOfElementGotFormItem++;
                        if (!$overTAppItem) {
                            $currentItem = $dataTAppItem[$indexCurrentItem];
                            if ($fieldData) {
                                if ($fieldData == ExpenseUtils::EXPENSE_INPUT_FOR_PLACE_HOLDER_DESCRIBE) {
                                    if (in_array($currentItem->wtsm_name, ExpenseUtils::EXPENSE_LIST_WTSM_NAME_TRANSPORT)) {
                                        if (isset($currentItem->from_station) && $currentItem->from_station) {
                                            $dataInput = $currentItem->from_station;
                                        }
                                        if ((isset($currentItem->from_station) && $currentItem->from_station ) &&
                                            (isset($currentItem->to_station) && $currentItem->to_station)) {
                                            $dataInput .= ' → ';
                                        }
                                        if (isset($currentItem->to_station) && $currentItem->to_station) {
                                            $dataInput .= $currentItem->to_station;
                                        }
                                    } else {
                                        $dataInput = $currentItem->remarks;
                                    }

                                } else {
                                    $dataInput = $currentItem->$fieldData;
                                }

                            }
                            if ($quantityOfElementGotFormItem == ExpenseUtils::NUMBER_FIELD_TAKE_FOR_M_FORM) {
                                $quantityOfElementGotFormItem = 0;
                                $indexCurrentItem++;
                            }
                            if ($indexCurrentItem > $maxIndexTAppItem) {
                                $overTAppItem = true;
                            }
                        }
                    }

                    $item['data'] = $dataInput;

                }
            }

            if (!$isError) {
                Log::info('Start editing Expense From Excel file');
                $reader = new XlsxReader();
                $reader->setReadDataOnly(false);
                $spreadsheet = $reader->load($filePath);
                $sheet = $spreadsheet->getActiveSheet();
                foreach ($placeholderList as $element) {
                    $value = $element['data'];
                    $cellAddress = $element['cell_address'];
                    $sheet->setCellValue($cellAddress, $value);
                }

                $writer = new XlsxWriter($spreadsheet);
                $path = explode(".", (microtime(true) . ""))[0] . '_' . $user->id . '.xlsx';
                $writer->save($path);
                $pathPublicFile = public_path() . '/' . $path;
                $result['file_data'] = \base64_encode(\file_get_contents($pathPublicFile));
                unlink($filePath);
                unlink($pathPublicFile);
                Log::info('Edited Expense From Excel file');
            }
            if ($isError) {
                return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
            }
            return $this->sendResponse($result, 'updateExpenseFormInput Success');

        } catch (\Exception $ex) {
            Log::error("$this->controllerName@updateExpenseFormInput");
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR],
                \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}
