<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Utils\AppUtils;
use App\Http\Utils\SpecialApiUtils;
use App\Http\Utils\TemplateRouteUtils;
use App\Http\Utils\TemplateUtils;
use Carbon\Carbon;
use GuzzleHttp\RequestOptions;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cookie;
use Response;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpWord;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Session;

/**
 * Class UserController
 * @package App\Http\Controllers\API
 */
class TemplateAPIController extends AppBaseController
{

    private $templateDirectory;

    public function __construct()
    {
        $this->templateDirectory = config('app.server_env') . '/' . config('app.edition_flg')
            . '/' . config('app.server_flg');

    }


    /**
     * Display a listing of the template.
     * GET|HEAD /users
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        try {
            $filter_file_name = $request->get('file_name', '');
            $user = $request->user();

            if (!$user) {
                return $this->sendError('Permission denied.', 403);
            }

            $limit = AppUtils::normalizeLimit($request->get('limit', 10), 10);
            $orderBy = $request->get('orderBy', "");
            $orderDir = AppUtils::normalizeOrderDir($request->get('orderDir', "DESC"));

            $arrOrder = ['file_name' => 'file_name', 'template_create_at' => 'template_create_at', 'template_update_user' => 'template_update_user', 'template_update_at' => 'template_update_at'];
            $orderBy = isset($arrOrder[$orderBy]) ? $arrOrder[$orderBy] : '';

            $user_info = DB::table('mst_user_info')
                ->where('mst_user_id', $user->id)->first();

            if (!$user_info) {
                return $this->sendError('Permission denied.', 403);
            }

            $multiple_department_position_flg = DB::table('mst_company')->where('id', $user->mst_company_id)->select('multiple_department_position_flg')->first()->multiple_department_position_flg ?? 0;

            $template_files = DB::table('template_file')
                ->select(
                    'id',
                    'mst_company_id',
                    'mst_user_id',
                    'file_name',
                    'storage_file_name',
                    'location',
                    'document_type',
                    'document_access_flg',
                    'template_create_at',
                    'template_create_user',
                    'template_update_at',
                    'template_route_id',
                    DB::raw('IFNULL(template_update_user, template_create_user) as template_update_user')
                )
                ->where('create_user_type', TemplateUtils::CREATE_APP)
                ->where(function ($where) use ($user, $user_info, $multiple_department_position_flg) {
                    $where->where('mst_user_id', $user->id)
                        ->where('mst_company_id', $user->mst_company_id)
                        ->where('document_access_flg', TemplateUtils::INDIVIDUAL_ACCESS_TYPE)
                        ->orWhere(function ($orWhere) use ($user) {
                            $orWhere->where('mst_company_id', $user->mst_company_id);
                            $orWhere->where('document_access_flg', TemplateUtils::COMPANY_ACCESS_TYPE);
                        })
                        ->orWhere(function ($orWhere) use ($user, $user_info, $multiple_department_position_flg) {
                            // PAC_5-2098 Start
                            if ($multiple_department_position_flg === 1) {
                                // PAC_5-1599 追加部署と役職 Start
                                $orWhere->whereExists(function ($query) use ($user_info) {
                                    $query->select(DB::raw('mui.id'))
                                        ->from('mst_user_info as mui')
                                        ->where('mui.mst_user_id', DB::raw('template_file.mst_user_id'))
                                        ->where(function ($query) use ($user_info) {
                                            $selected_department_ids = [
                                                $user_info->mst_department_id_1,
                                                $user_info->mst_department_id_2,
                                                $user_info->mst_department_id
                                            ];
                                            $selected_department_ids = array_unique(array_filter($selected_department_ids));
                                            if (empty($selected_department_ids)) {
                                                $query->whereNull('mui.mst_department_id');
                                                $query->whereNull('mui.mst_department_id_1');
                                                $query->whereNull('mui.mst_department_id_2');
                                            } else {
                                                $query->orWhereIn('mui.mst_department_id', $selected_department_ids);
                                                $query->orWhereIn('mui.mst_department_id_1', $selected_department_ids);
                                                $query->orWhereIn('mui.mst_department_id_2', $selected_department_ids);
                                            }
                                        });
                                });
                                // PAC_5-1599 End
                            } else {
                                $orWhere->whereExists(function ($query) use ($user_info) {
                                    $query->select(DB::raw('mui.id'))
                                        ->from('mst_user_info as mui')
                                        ->where('mui.mst_user_id', DB::raw('template_file.mst_user_id'))
                                        ->where('mui.mst_department_id', $user_info->mst_department_id);
                                });
                            }
                            // PAC_5-2098 End
                            $orWhere->where('mst_company_id', $user->mst_company_id);
                            $orWhere->where('document_access_flg', TemplateUtils::DEPARTMENT_ACCESS_TYPE);
                        });
                })
                //PAC_5-2608 テンプレート管理者登録条件
                ->orWhere(function ($orWhere) use ($user) {
                    $orWhere->where('mst_company_id', $user->mst_company_id);
                    $orWhere->where('auth_flg', TemplateUtils::AUTH_FLG);
                });

            if (!$orderBy) {
                $template_files
                    ->orderBy('template_update_at', 'DESC')
                    ->orderBy('template_create_at', 'DESC');
            } else {
                $template_files
                    ->orderBy($orderBy, $orderDir);
            }
            if ($filter_file_name) {
                $template_files->where('file_name', 'like', '%' . $filter_file_name . '%');
            }
            $template_files = $template_files->paginate($limit);

            $items = $template_files->items();

            $template_file_ids = array_map(function ($item) {
                return $item->id;
            }, $items);

            $template_placeholder_datas = DB::table('template_placeholder_data')
                ->whereIn('template_file_id', $template_file_ids)
                ->get();

            $mst_placeholder = DB::table('mst_template_placeholder')
                ->get();

            // PAC_5-1599 追加部署と役職 Start
            $company = DB::table('mst_company')
                ->where('id', $user->mst_company_id)
                ->first();

            $department_list = DB::table('mst_department')
                ->where('mst_company_id', $user->mst_company_id)
                ->select('id', 'department_name')
                ->get()
                ->toArray();
            $position_list = DB::table('mst_position')
                ->where('mst_company_id', $user->mst_company_id)
                ->select('id', 'position_name')
                ->get()
                ->toArray();

            $departments = [];
            $positions = [];
            array_map(function ($item) use (&$departments) {
                $departments[$item->id] = $item->department_name;
            }, $department_list);
            array_map(function ($item) use (&$positions) {
                $positions[$item->id] = $item->position_name;
            }, $position_list);

            $template_user_ids = [];
            $mst_user_ids = array_map(function ($item) use (&$template_user_ids) {
                $template_user_ids[$item->id] = $item->mst_user_id;
                return $item->mst_user_id;
            }, $items);
            $mst_users = DB::table('mst_user_info')
                ->whereIn('mst_user_id', $mst_user_ids)
                ->select('mst_user_id', 'mst_department_id', 'mst_department_id_1', 'mst_department_id_2')
                ->get()
                ->toArray();
            $mst_department_ids = [];
            // PAC_5-2098 Start
            array_map(function ($item) use (&$mst_department_ids, $multiple_department_position_flg) {
                if ($multiple_department_position_flg === 1) {
                    $mst_department_ids[$item->mst_user_id] = [
                        $item->mst_department_id,
                        $item->mst_department_id_1,
                        $item->mst_department_id_2,
                    ];
                } else {
                    $mst_department_ids[$item->mst_user_id] = [$item->mst_department_id];
                }
            }, $mst_users);
            // PAC_5-2098 End
            // PAC_5-1599 End

            foreach ($template_placeholder_datas as $value) {
                foreach ($mst_placeholder as $mp) {
                    if ($value->template_placeholder_name === $mp->special_template_placeholder) {
                        if ($mp->id === 1) {
                            $value->template_placeholder_value = date("Y/m/d H:i:s");
                        } else if ($mp->id === 2) {
                            $value->template_placeholder_value = date("Y/m/d");
                        } else if ($mp->id === 3) {
                            $value->template_placeholder_value = $user->family_name . $user->given_name;
                        } else if ($mp->id === 4) {
                            $value->template_placeholder_value = $user->email;
                        } else if ($mp->id === 5) {
                            if ($company) {
                                $value->template_placeholder_value = $company->company_name;
                            } else {
                                $value->template_placeholder_value = '';
                            }
                        } else if ($mp->id === 6) {
                            // PAC_5-2098 Start
                            if ($multiple_department_position_flg === 1) {
                                // PAC_5-1599 追加部署と役職 Start
                                if (in_array($user_info->mst_department_id, $mst_department_ids[$template_user_ids[$value->template_file_id]])) {
                                    $value->template_placeholder_value = $departments[$user_info->mst_department_id] ?? '';
                                } else if (in_array($user_info->mst_department_id_1, $mst_department_ids[$template_user_ids[$value->template_file_id]])) {
                                    $value->template_placeholder_value = $departments[$user_info->mst_department_id_1] ?? '';
                                } else if (in_array($user_info->mst_department_id_2, $mst_department_ids[$template_user_ids[$value->template_file_id]])) {
                                    $value->template_placeholder_value = $departments[$user_info->mst_department_id_2] ?? '';
                                } else {
                                    $value->template_placeholder_value = '';
                                }
                                // PAC_5-1599 End
                            } else {
                                if (in_array($user_info->mst_department_id, $mst_department_ids[$template_user_ids[$value->template_file_id]])) {
                                    $value->template_placeholder_value = $departments[$user_info->mst_department_id] ?? '';
                                } else {
                                    $value->template_placeholder_value = '';
                                }
                            }
                            // PAC_5-2098 End
                        } else if ($mp->id === 7) {
                            // PAC_5-2098 Start
                            if ($multiple_department_position_flg === 1) {
                                // PAC_5-1599 追加部署と役職 Start
                                if (in_array($user_info->mst_department_id, $mst_department_ids[$template_user_ids[$value->template_file_id]])) {
                                    $value->template_placeholder_value = $positions[$user_info->mst_position_id] ?? '';
                                } else if (in_array($user_info->mst_department_id_1, $mst_department_ids[$template_user_ids[$value->template_file_id]])) {
                                    $value->template_placeholder_value = $positions[$user_info->mst_position_id_1] ?? '';
                                } else if (in_array($user_info->mst_department_id_2, $mst_department_ids[$template_user_ids[$value->template_file_id]])) {
                                    $value->template_placeholder_value = $positions[$user_info->mst_position_id_2] ?? '';
                                } else {
                                    $value->template_placeholder_value = '';
                                }
                                // PAC_5-1599 End
                            } else {
                                if (in_array($user_info->mst_department_id, $mst_department_ids[$template_user_ids[$value->template_file_id]])) {
                                    $value->template_placeholder_value = $positions[$user_info->mst_position_id] ?? '';
                                } else {
                                    $value->template_placeholder_value = '';
                                }
                            }
                            // PAC_5-2098 End
                        } else if ($mp->id === 8) {
                            $value->template_placeholder_value = $user_info->phone_number;
                        } else if ($mp->id === 9) {
                            $value->template_placeholder_value = $user_info->fax_number;
                        } else if ($mp->id === 10) {
                            $value->template_placeholder_value = $user_info->address;
                        } else if ($mp->id === 11) {
                            $value->template_placeholder_value = $user_info->postal_code;
                        }
                        break;
                    } else {
                        $value->template_placeholder_value = '';
                    }
                }
            }
            Log::info('テンプレート一覧取得データ');
            Log::info($template_placeholder_datas);

            foreach ($items as $item) {
                $item->placeholderData = $template_placeholder_datas->filter(function ($_item) use ($item) {
                    return $_item && $_item->template_file_id == $item->id;
                });
            }

            $template_files = json_decode(json_encode($template_files));

            $template_files->data = $items;

            return $this->sendResponse($template_files, '送信文書の取得処理に成功しました。');


        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError('テンプレートファイル情報取得処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display a listing of the template.
     * GET|HEAD /users
     *
     * @param Request $request
     * @return Response
     */
    public function indexEdit(Request $request)
    {
        try {
            $filter_file_name = $request->get('file_name', '');
            $user = $request->user();
            //PAC_5-1527テンプレート途中編集用　受信からテンプレート編集を行う場合
            $circular_id = $request->get('circularId', '');

            if (!$user) {
                return $this->sendError('Permission denied.', 403);
            }

            $limit = AppUtils::normalizeLimit($request->get('limit', 10), 10);
            $orderBy = $request->get('orderBy', "");
            $orderDir = AppUtils::normalizeOrderDir($request->get('orderDir', "DESC"));

            $arrOrder = ['file_name' => 'file_name', 'template_create_at' => 'template_create_at', 'template_update_user' => 'template_update_user', 'template_update_at' => 'template_update_at'];
            $orderBy = isset($arrOrder[$orderBy]) ? $arrOrder[$orderBy] : '';

            $user_info = DB::table('mst_user_info')
                ->where('mst_user_id', $user->id)->first();

            if (!$user_info) {
                return $this->sendError('Permission denied.', 403);
            }

            $multiple_department_position_flg = DB::table('mst_company')->where('id', $user->mst_company_id)->select('multiple_department_position_flg')->first()->multiple_department_position_flg ?? 0;

            $template_files = DB::table('template_file')
                ->select(
                    'id',
                    'mst_company_id',
                    'mst_user_id',
                    'file_name',
                    'storage_file_name',
                    'location',
                    'document_type',
                    'document_access_flg',
                    'template_create_at',
                    'template_create_user',
                    'template_update_at',
                    DB::raw('IFNULL(template_update_user, template_create_user) as template_update_user')
                )
                ->where('create_user_type', TemplateUtils::CREATE_APP)
                ->where(function ($where) use ($user, $user_info, $multiple_department_position_flg) {
                    $where->where('mst_user_id', $user->id)
                        ->where('mst_company_id', $user->mst_company_id)
                        ->where('document_access_flg', TemplateUtils::INDIVIDUAL_ACCESS_TYPE)
                        ->orWhere(function ($orWhere) use ($user) {
                            $orWhere->where('mst_company_id', $user->mst_company_id);
                            $orWhere->where('document_access_flg', TemplateUtils::COMPANY_ACCESS_TYPE);
                        })
                        ->orWhere(function ($orWhere) use ($user, $user_info, $multiple_department_position_flg) {
                            // PAC_5-2098 Start
                            if ($multiple_department_position_flg === 1) {
                                // PAC_5-1599 追加部署と役職 Start
                                $orWhere->whereExists(function ($query) use ($user_info) {
                                    $query->select(DB::raw('mui.id'))
                                        ->from('mst_user_info as mui')
                                        ->where('mui.mst_user_id', DB::raw('template_file.mst_user_id'))
                                        ->where(function ($query) use ($user_info) {
                                            $selected_department_ids = [
                                                $user_info->mst_department_id_1,
                                                $user_info->mst_department_id_2,
                                                $user_info->mst_department_id
                                            ];
                                            $selected_department_ids = array_unique(array_filter($selected_department_ids));
                                            if (empty($selected_department_ids)) {
                                                $query->whereNull('mui.mst_department_id');
                                                $query->whereNull('mui.mst_department_id_1');
                                                $query->whereNull('mui.mst_department_id_2');
                                            } else {
                                                $query->orWhereIn('mui.mst_department_id', $selected_department_ids);
                                                $query->orWhereIn('mui.mst_department_id_1', $selected_department_ids);
                                                $query->orWhereIn('mui.mst_department_id_2', $selected_department_ids);
                                            }
                                        });
                                });
                                // PAC_5-1599 End
                            } else {
                                $orWhere->whereExists(function ($query) use ($user_info) {
                                    $query->select(DB::raw('mui.id'))
                                        ->from('mst_user_info as mui')
                                        ->where('mui.mst_user_id', DB::raw('template_file.mst_user_id'))
                                        ->where('mui.mst_department_id', $user_info->mst_department_id);
                                });
                            }
                            // PAC_5-2098 End
                            $orWhere->where('mst_company_id', $user->mst_company_id);
                            $orWhere->where('document_access_flg', TemplateUtils::DEPARTMENT_ACCESS_TYPE);
                        });
                });

            if (!$orderBy) {
                $template_files
                    ->orderBy('template_update_at', 'DESC')
                    ->orderBy('template_create_at', 'DESC');
            } else {
                $template_files
                    ->orderBy($orderBy, $orderDir);
            }
            if ($filter_file_name) {
                $template_files->where('file_name', 'like', '%' . $filter_file_name . '%');
            }
            $template_files = $template_files->paginate($limit);

            $items = $template_files->items();

            $template_file_ids = array_map(function ($item) {
                return $item->id;
            }, $items);
            $template_file_id = [];

            //PAC_5-1527テンプレート途中編集用　受信からテンプレート編集を行う場合
            if ($circular_id && $circular_id !== 'undefined') {
                $template_files = NULL;
                $template_file_id = DB::table('template_input_data')
                    ->select('template_id')->where('circular_id', $circular_id)->first();

                $template_edit_file = DB::table('template_edit_file as tef')
                    ->leftJoin('template_file as tf', 'tef.template_file_id', '=', 'tf.id')
                    ->select(
                        'tf.id',
                        'tef.mst_company_id',
                        'tef.mst_user_id',
                        'tef.file_name',
                        'tef.storage_file_name',
                        'tef.location',
                        'tf.document_type',
                        'tf.document_access_flg',
                        'tef.template_edit_at as template_create_at',
                        'tef.template_edit_user as template_create_user',
                        'tf.template_update_at'
                    )
                    ->orderBy('tef.edit_number', 'desc')
                    ->where('circular_id', $circular_id)
                    ->first();

                array_push($items, $template_edit_file);

                $template_files = DB::table('template_edit_file as tef')
                    ->leftJoin('template_file as tf', 'tef.template_file_id', '=', 'tf.id')
                    ->select(
                        'tf.id',
                        'tef.mst_company_id',
                        'tef.mst_user_id',
                        'tef.file_name',
                        'tef.storage_file_name',
                        'tef.location',
                        'tf.document_type',
                        'tf.document_access_flg',
                        'tef.template_edit_at as template_create_at',
                        'tef.template_edit_user as template_create_user',
                        'tf.template_update_at'
                    )
                    ->orderBy('tef.edit_number', 'desc')
                    ->where('circular_id', $circular_id)
                    ->first();

                array_push($template_file_ids, $template_files->id);
            }

            if (!$circular_id || $circular_id === 'undefined') {
                $template_placeholder_datas = DB::table('template_placeholder_data')
                    ->whereIn('template_file_id', $template_file_ids)
                    ->get();
            } else {
                $template_placeholder_datas = DB::table('template_placeholder_data')
                    ->leftJoin('template_input_data', 'template_placeholder_data.id', '=', 'template_input_data.placeholder_id')
                    ->where('template_input_data.circular_id', $circular_id)
                    ->get();
            }

            $mst_placeholder = DB::table('mst_template_placeholder')
                ->get();

            // PAC_5-1599 追加部署と役職 Start
            $company = DB::table('mst_company')
                ->where('id', $user->mst_company_id)
                ->first();

            $department_list = DB::table('mst_department')
                ->where('mst_company_id', $user->mst_company_id)
                ->select('id', 'department_name')
                ->get()
                ->toArray();
            $position_list = DB::table('mst_position')
                ->where('mst_company_id', $user->mst_company_id)
                ->select('id', 'position_name')
                ->get()
                ->toArray();

            $departments = [];
            $positions = [];
            array_map(function ($item) use (&$departments) {
                $departments[$item->id] = $item->department_name;
            }, $department_list);
            array_map(function ($item) use (&$positions) {
                $positions[$item->id] = $item->position_name;
            }, $position_list);

            $template_user_ids = [];
            $mst_user_ids = [];
            if ($circular_id === 'undefined') {
                $mst_user_ids = array_map(function ($item) use (&$template_user_ids) {
                    $template_user_ids[$item->id] = $item->mst_user_id;
                    return $item->mst_user_id;
                }, $items);
            } else {
                $template_user_ids[$template_file_id->template_id] = $template_files->mst_user_id;
                array_push($mst_user_ids, $template_files->mst_user_id);
            }

            $mst_users = DB::table('mst_user_info')
                ->whereIn('mst_user_id', $mst_user_ids)
                ->select('mst_user_id', 'mst_department_id', 'mst_department_id_1', 'mst_department_id_2')
                ->get()
                ->toArray();
            $mst_department_ids = [];
            // PAC_5-2098 Start
            array_map(function ($item) use (&$mst_department_ids, $multiple_department_position_flg) {
                if ($multiple_department_position_flg === 1) {
                    $mst_department_ids[$item->mst_user_id] = [
                        $item->mst_department_id,
                        $item->mst_department_id_1,
                        $item->mst_department_id_2,
                    ];
                } else {
                    $mst_department_ids[$item->mst_user_id] = [$item->mst_department_id];
                }
            }, $mst_users);
            // PAC_5-2098 End
            // PAC_5-1599 End

            if (!$circular_id || $circular_id === 'undefined') {
                foreach ($template_placeholder_datas as $value) {
                    foreach ($mst_placeholder as $mp) {
                        if ($value->template_placeholder_name === $mp->special_template_placeholder) {
                            if ($mp->id === 1) {
                                $value->template_placeholder_value = date("Y/m/d H:i:s");
                            } else if ($mp->id === 2) {
                                $value->template_placeholder_value = date("Y/m/d");
                            } else if ($mp->id === 3) {
                                $value->template_placeholder_value = $user->family_name . $user->given_name;
                            } else if ($mp->id === 4) {
                                $value->template_placeholder_value = $user->email;
                            } else if ($mp->id === 5) {
                                if ($company) {
                                    $value->template_placeholder_value = $company->company_name;
                                } else {
                                    $value->template_placeholder_value = '';
                                }
                            } else if ($mp->id === 6) {
                                // PAC_5-2098 Start
                                if ($multiple_department_position_flg === 1) {
                                    // PAC_5-1599 追加部署と役職 Start
                                    if (in_array($user_info->mst_department_id, $mst_department_ids[$template_user_ids[$value->template_file_id]])) {
                                        $value->template_placeholder_value = $departments[$user_info->mst_department_id] ?? '';
                                    } else if (in_array($user_info->mst_department_id_1, $mst_department_ids[$template_user_ids[$value->template_file_id]])) {
                                        $value->template_placeholder_value = $departments[$user_info->mst_department_id_1] ?? '';
                                    } else if (in_array($user_info->mst_department_id_2, $mst_department_ids[$template_user_ids[$value->template_file_id]])) {
                                        $value->template_placeholder_value = $departments[$user_info->mst_department_id_2] ?? '';
                                    } else {
                                        $value->template_placeholder_value = '';
                                    }
                                    // PAC_5-1599 End
                                } else {
                                    if (in_array($user_info->mst_department_id, $mst_department_ids[$template_user_ids[$value->template_file_id]])) {
                                        $value->template_placeholder_value = $departments[$user_info->mst_department_id] ?? '';
                                    } else {
                                        $value->template_placeholder_value = '';
                                    }
                                }
                                // PAC_5-2098 End
                            } else if ($mp->id === 7) {
                                // PAC_5-2098 Start
                                if ($multiple_department_position_flg === 1) {
                                    // PAC_5-1599 追加部署と役職 Start
                                    if (in_array($user_info->mst_department_id, $mst_department_ids[$template_user_ids[$value->template_file_id]])) {
                                        $value->template_placeholder_value = $positions[$user_info->mst_position_id] ?? '';
                                    } else if (in_array($user_info->mst_department_id_1, $mst_department_ids[$template_user_ids[$value->template_file_id]])) {
                                        $value->template_placeholder_value = $positions[$user_info->mst_position_id_1] ?? '';
                                    } else if (in_array($user_info->mst_department_id_2, $mst_department_ids[$template_user_ids[$value->template_file_id]])) {
                                        $value->template_placeholder_value = $positions[$user_info->mst_position_id_2] ?? '';
                                    } else {
                                        $value->template_placeholder_value = '';
                                    }
                                    // PAC_5-1599 End
                                } else {
                                    if (in_array($user_info->mst_department_id, $mst_department_ids[$template_user_ids[$value->template_file_id]])) {
                                        $value->template_placeholder_value = $positions[$user_info->mst_position_id] ?? '';
                                    } else {
                                        $value->template_placeholder_value = '';
                                    }
                                }
                                // PAC_5-2098 End
                            } else if ($mp->id === 8) {
                                $value->template_placeholder_value = $user_info->phone_number;
                            } else if ($mp->id === 9) {
                                $value->template_placeholder_value = $user_info->fax_number;
                            } else if ($mp->id === 10) {
                                $value->template_placeholder_value = $user_info->address;
                            } else if ($mp->id === 11) {
                                $value->template_placeholder_value = $user_info->postal_code;
                            }
                            break;
                        } else {
                            $value->template_placeholder_value = '';
                        }
                    }
                }
            } else {
                foreach ($template_placeholder_datas as $value) {
                    $value->template_placeholder_value = $value->template_placeholder_data;
                }
            }
            Log::info('テンプレート一覧取得データ');

            foreach ($items as $item) {
                $item->placeholderData = $template_placeholder_datas->filter(function ($_item) use ($item) {
                    return $_item && $_item->template_file_id == $item->id;
                });
            }

            $template_files = json_decode(json_encode($template_files));

            if ($circular_id === 'undefined') {
                $template_files->data = $items;
            } else {
                $template_files = $items;
            }

            return $this->sendResponse($template_files, '送信文書の取得処理に成功しました。');


        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError('テンプレートファイル情報取得処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $requestIds = $request->get('ids', []);
            $user = $request->user();

            if (!$user) {
                return $this->sendError('Permission denied.', 403);
            }

            $user_info = DB::table('mst_user_info')
                ->where('mst_user_id', $user->id)
                ->first();

            if (!$user_info) {
                return $this->sendError('Permission denied.', 403);
            }
            // PAC_5-2098 Start
            $multiple_department_position_flg = DB::table('mst_company')->where('id', $user->mst_company_id)->select('multiple_department_position_flg')->first()->multiple_department_position_flg ?? 0;
            // PAC_5-1599 追加部署と役職 Start
            $query = DB::table('mst_user as mu')
                ->join('mst_user_info as mui', function ($query) use ($user) {
                    $query->on('mu.id', 'mui.mst_user_id');
                    $query->where('mu.mst_company_id', $user->mst_company_id);
                });
            if ($multiple_department_position_flg === 1) {
                $query = $query->where(function ($query) use ($user_info) {
                    $selected_department_ids = [
                        $user_info->mst_department_id_1,
                        $user_info->mst_department_id_2,
                        $user_info->mst_department_id
                    ];
                    $selected_department_ids = array_unique(array_filter($selected_department_ids));
                    if (empty($selected_department_ids)) {
                        $query->whereNull('mui.mst_department_id');
                        $query->whereNull('mui.mst_department_id_1');
                        $query->whereNull('mui.mst_department_id_2');
                    } else {
                        $query->orWhereIn('mui.mst_department_id', $selected_department_ids);
                        $query->orWhereIn('mui.mst_department_id_1', $selected_department_ids);
                        $query->orWhereIn('mui.mst_department_id_2', $selected_department_ids);
                    }
                });
            } else {
                $query = $query->where('mui.mst_department_id', $user_info->mst_department_id);
            }
            $department_user_ids = $query->pluck('mu.id')
                // PAC_5-1599 End
                // PAC_5-2098 End
                ->toArray();

            if (count($requestIds)) {
                $query = DB::table('template_file')
                    ->where('mst_user_id', $user->id)
                    ->whereIn('id', $requestIds)
                    ->orWhere(function ($orWhere) use ($user, $requestIds) {
                        $orWhere->where('mst_company_id', $user->mst_company_id);
                        $orWhere->where('document_access_flg', TemplateUtils::COMPANY_ACCESS_TYPE);
                        $orWhere->whereIn('id', $requestIds);
                    })
                    ->orWhere(function ($orWhere) use ($department_user_ids, $requestIds) {
                        $orWhere->whereIn('mst_user_id', $department_user_ids);
                        $orWhere->where('document_access_flg', TemplateUtils::DEPARTMENT_ACCESS_TYPE);
                        $orWhere->whereIn('id', $requestIds);
                    });

                $count = $query->count();
                if ($count <= 0) {
                    DB::rollBack();
                    return $this->sendError('Not Found.', 404);
                }

                $deletionJudgment = DB::table('template_file')
                    ->select(
                        'id'
                    )
                    ->where('mst_user_id', $user->id)
                    ->whereIn('id', $requestIds)
                    ->count();

                Log::info('テンプレートデータ削除');
                Log::info($deletionJudgment . '件削除');

                if ($deletionJudgment > 0) {
                    DB::table('template_placeholder_data')
                        ->join('template_file', 'template_placeholder_data.template_file_id', '=', 'template_file.id')
                        ->where('template_file.mst_user_id', $user->id)
                        ->whereIn('template_placeholder_data.template_file_id', $requestIds)
                        ->delete();

                    DB::table('template_file')
                        ->where('mst_user_id', $user->id)
                        ->whereIn('id', $requestIds)
                        ->delete();

                    DB::commit();
                    Log::info('テンプレートデータ削除完了');
                    return $this->sendResponse(true, 'テンプレートファイル削除処理に成功しました。');
                } else {
                    /*throw new \Exception('異なるユーザーのファイルは削除できません')*/
                    //他のユーザーが登録したファイルは削除出来ないため例外処理へ
                    DB::rollBack();
                    return $this->sendResponse(true, '異なるユーザーのファイルは削除できません');
                }

            }
            /*DB::commit();
            return $this->sendResponse(true,'テンプレートファイル削除処理に成功しました。');*/
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError('テンプレートファイルの削除処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Upload and store the template files
     * requirement parameter
     *  ・file : file like xxx.xlsx and xxx.docx
     *  ・document_access_flg : 0 or 1 or 2
     */
    public function uploadTemplate(Request $request)
    {
        $login_user = $request->user();
        try {
            Log::info('テンプレートアップロード開始');
            $file = $request->file('file');
            $access_flg = $request['document_access_flg'];
            $originName = $file->getClientOriginalName();
            $fileextension = $file->getClientOriginalExtension();

            $userName = $login_user->family_name . $login_user->given_name;
            $altFileName = explode(".", (microtime(true) . ""))[0] . '_' . $login_user->id . '.' . $fileextension;

            //S3テンプレート用ディレクトリ存在確認
            $s3path = config('filesystems.prefix_path') . '/' . config('app.s3_storage_root_folder');
            $isFolderExist = Storage::disk('s3')->exists($s3path);
            if (!$isFolderExist) {
                Storage::disk('s3')->makeDirectory($s3path);
                Storage::disk('s3')->makeDirectory($s3path . '/template');

                $s3path = $s3path . '/' . 'template/' . $this->templateDirectory . $login_user->mst_company_id;
                Storage::disk('s3')->makeDirectory($s3path);
            } else {
                $s3path = $s3path . '/' . 'template/' . $this->templateDirectory;
                if (!$isFolderExist) {
                    Storage::disk('s3')->makeDirectory($s3path);
                    Storage::disk('s3')->makeDirectory($s3path . '/' . $login_user->mst_company_id);

                    $s3path = $s3path . '/' . $login_user->mst_company_id;
                } else {
                    $s3path = $s3path . '/' . $login_user->mst_company_id;
                    $isFolderExist = Storage::disk('s3')->exists($s3path);
                    if (!$isFolderExist) {
                        Storage::disk('s3')->makeDirectory($s3path);
                    }
                }
            }

            if (in_array($fileextension, ['xlsx', 'xls'])) {
                $extension = '0';
                //$move = $file->storeAs('template', $name);
                //
                $reader = new XlsxReader();
                $spreadsheet = $reader->load($file);
                // 読み込むシートを指定(1シート目)
                $sheet = $spreadsheet->getSheet(0);
                //行番号、ループ用
                $row = 1;

                $placeholderList = array();

                //セル番地とセルの情報を取得
                foreach ($sheet->getRowIterator() as $eachrow) {
                    foreach ($sheet->getColumnIterator() as $column) {
                        $column->getColumnIndex() . $eachrow->getRowIndex();
                        $sheetData = $sheet->getCell($column->getColumnIndex() . $row)->getValue();
                        //セル内にデータがある場合かつ、${で始まるデータ(プレースホルダー)とセル番地を保存
                        if ($sheetData) {
                            //対象のデータである「「${」から始まるデータ」ことを確認
                            $find = '${';
                            if (strpos($sheetData, $find) !== false) {
                                $phEnd = '}';
                                $start_position = strpos($sheetData, $find);
                                $phLength = strpos($sheetData, $phEnd) - $start_position + 1;
                                $placeholder = substr($sheetData, $start_position, $phLength);
                                $placeholderList += array($column->getColumnIndex() . $eachrow->getRowIndex() => $placeholder);
                            }
                        }
                    }
                    $row++;
                }

                //テスト用ファイル格納処理
                //$move = $file->storeAs('template', $altFileName);

                //S3アップロード処理
                Storage::disk('s3')->putfileAs($s3path . '/', $file, $altFileName, 'pub');
                //保存したS3完全URLの取得
                $s3url = Storage::disk('s3')->url($s3path . '/' . $altFileName);
                Log::info('テンプレート保存URL' . $s3url);

                DB::beginTransaction();

                $template_id = DB::table('template_file')
                    ->insertGetId(
                        [
                            'mst_company_id' => $login_user->mst_company_id,
                            'mst_user_id' => $login_user->id,
                            'file_name' => $originName,
                            'storage_file_name' => $altFileName,
                            'location' => $s3url,
                            'document_type' => $extension,
                            'document_access_flg' => $access_flg,
                            'template_create_at' => Carbon::now(),
                            'template_create_user' => $userName,
                            'is_generation_flg' => 1,
                            'create_user_type' => TemplateUtils::CREATE_APP,
                        ]);

                foreach ($placeholderList as $cell => $value) {
                    DB::table('template_placeholder_data')
                        ->insert([
                            'template_file_id' => $template_id,
                            'template_placeholder_name' => $value,
                            'cell_address' => $cell,
                            'template_create_at' => Carbon::now(),
                            'template_create_user' => $userName,
                        ]);
                }

                DB::commit();
                Log::info('テンプレートアップロード完了');

                return $this->sendResponse(['template_id' => $template_id, 'name' => $originName], 'テンプレートファイル登録処理に成功しました。');

            } elseif (in_array($fileextension, ['docx', 'doc'])) {
                $extension = '1';
                $contents = "";
                $zip = new \ZipArchive();

                if ($zip->open($file) === true) {
                    $xml = $zip->getFromName("word/document.xml");
                    if ($xml) {
                        $dom = new \DOMDocument();
                        $dom->loadXML($xml);
                        $paragraphs = $dom->getElementsByTagName("p");
                        foreach ($paragraphs as $p) {
                            $texts = $p->getElementsByTagName("t");
                            foreach ($texts as $t) {
                                $contents .= $t->nodeValue;
                            }
                        }
                    }
                }
                $contents_copy = $contents;
                //
                $find = '${';
                $placeholderList = array();

                $counter = substr_count($contents, $find);

                for ($i = 0; $i < $counter; $i++) {
                    $phEnd = '}';
                    $start_position = strpos($contents, $find);
                    $phLength = strpos($contents, $phEnd) - $start_position + 1;
                    if ($start_position !== false) {
                        $placeholder = substr($contents, $start_position, $phLength);
                        $placeholderList += array($i => $placeholder);
                        $contents = substr($contents, strpos($contents, $phEnd) + 1, strlen($contents) - $start_position);
                    }
                }

                //テスト用ファイル格納処理
                //$move = $file->storeAs('template', $altFileName);

                //S3アップロード処理
                Storage::disk('s3')->putfileAs($s3path . '/', $file, $altFileName, 'pub');
                //保存したS3完全URLの取得
                $s3url = Storage::disk('s3')->url($s3path . '/' . $altFileName);
                Log::info('テンプレート保存URL' . $s3url);

                DB::beginTransaction();

                $template_id = DB::table('template_file')
                    ->insertGetId(
                        [
                            'mst_company_id' => $login_user->mst_company_id,
                            'mst_user_id' => $login_user->id,
                            'file_name' => $originName,
                            'storage_file_name' => $altFileName,
                            'location' => $s3url,
                            'document_type' => $extension,
                            'document_access_flg' => $access_flg,
                            'template_create_at' => Carbon::now(),
                            'template_create_user' => $userName,
                            'is_generation_flg' => 1,
                            'create_user_type' => TemplateUtils::CREATE_APP,
                        ]);

                foreach ($placeholderList as $value) {
                    DB::table('template_placeholder_data')
                        ->insert([
                            'template_file_id' => $template_id,
                            'template_placeholder_name' => $value,
                            'template_create_at' => Carbon::now(),
                            'template_create_user' => $userName,
                        ]);
                }

                DB::commit();
                Log::info('テンプレートアップロード完了');

                return $this->sendResponse(['template_id' => $template_id, 'name' => $originName], 'テンプレートファイル登録処理に成功しました。');
            } else {
                return $this->sendError('ファイル形式が異なります。', \Illuminate\Http\Response::HTTP_BAD_REQUEST);
            }


        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError('ファイルをアップロードできませんでした。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     */
    public function getFile($templateId, Request $request)
    {
        $user = $request->user();

        try {
            $file = DB::table('template_file')
                ->where('id', $templateId)
                ->get();

            $path = config('filesystems.prefix_path') . '/' . config('app.s3_storage_root_folder').'/'.'template/'.$user->mst_company_id;
            if ( Storage::disk('s3')->exists($path)){
                $relative_path = config('filesystems.prefix_path') . '/' . config('app.s3_storage_root_folder').'/'.'template/'.$user->mst_company_id.'/'.$file[0]->storage_file_name;
                $getFile = Storage::disk('s3')->get($relative_path);
                $isStore = Storage::disk('local')->put($file[0]->storage_file_name, $getFile);
            }

            $filePath = storage_path('app/' . $file[0]->storage_file_name);

            $result = ['status' => \Illuminate\Http\Response::HTTP_OK, 'file_name' => $file[0]->file_name,
                'file_data' => \base64_encode(\file_get_contents($filePath))];

            Storage::disk('local')->delete($file[0]->storage_file_name);

            return $result;

        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return ['status' => \Illuminate\Http\Response::HTTP_BAD_REQUEST, 'message' => "ファイルを取得できませんでした。"];
        }
    }

    public function getContentTemplate($templateId, Request $request)
    {
        try {
//            Log::info('getContentTemplate');
            $user = $request->user();
            $file = DB::table('template_file')
                ->where('id', $templateId)
                ->get();
            $local_path = TemplateUtils::localTemplatePath($user->mst_company_id, $user->id);
            $fileData = [];
            //権限フラグが1：利用者の場合、S3からファイルを取得
            if (count($file) && $file[0]->create_user_type == TemplateUtils::CREATE_APP) {

                //ファイルの存在を確認してS3から取得をする
                $s3_path = $file[0]->location ? str_replace(env('AWS_URL', ''), '', $file[0]->location) : $file[0]->location;
                if (Storage::disk('s3')->exists($s3_path)) {
                    $getFile = Storage::disk('s3')->get($s3_path);
                    Storage::disk('local')->put($local_path . $file[0]->storage_file_name, $getFile);
                    Log::info('テンプレート取得: ' . $s3_path);
                }

                $filePath = storage_path('app/' . $local_path . $file[0]->storage_file_name);

                //return $result;
                $fileEncode = \base64_encode(\file_get_contents($filePath));
                Storage::disk('local')->delete($file[0]->storage_file_name);
            } //権限フラグが2：管理者の場合、base64情報を取得
            else {
                //SRS-015 テンプレート文書データ取得
                $client = SpecialApiUtils::getAuthorizeClient();
                if (!$client) {
                    Log::error(__('message.false.auth_client'));
                }

                $response = $client->post("/sp/api/get-template-circular-data", [
                    RequestOptions::JSON => [
                        'company_id' => $user->mst_company_id,
                        "env_flg" => config('app.server_env'),
                        "edition_flg" => config('app.edition_flg'),
                        "server_flg" => config('app.server_flg'),
                        "template_info" => [
                            "template_file_id" => $templateId,
                        ],
                    ]
                ]);
//                Log::info('get-template-circular-data');

                $response_dencode = json_decode($response->getBody(), true);  //配列へ
//                Log::info('$response_dencode');
//                Log::info($response_dencode);
                if ($response->getStatusCode() == 200) {
                    $response_body = json_decode($response->getBody(), true);  //配列へ
                    $fileData = $response_body['result']['template_file'];
                } else {
                    Log::error('Api storeBoard companyId:' . $user->mst_company_id);
                    Log::error($response_dencode);
                    return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
                }

                $fileEncode = $fileData[0]['file_data'];
                $file = $fileData;
            }
            return $this->sendResponse(['file' => $file, 'file_data' => $fileEncode], 'get file done');

        } catch
        (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return ['status' => \Illuminate\Http\Response::HTTP_BAD_REQUEST, 'message' => "ファイルを取得できませんでした。"];
        }
    }

    public function getContentTemplateSpecial($templateId, Request $request)
    {
        try {
            $user = $request->user();

            //SRS-015 テンプレート文書データ取得
            $client = SpecialApiUtils::getAuthorizeClient();
            if (!$client) {
                Log::error(__('message.false.auth_client'));
            }

            $response = $client->post("/sp/api/get-template-circular-data", [
                RequestOptions::JSON => [
                    'company_id' => $user->mst_company_id,
                    "env_flg" => config('app.server_env'),
                    "edition_flg" => config('app.edition_flg'),
                    "server_flg" => config('app.server_flg'),
                    "template_info" => [
                        "template_file_id" => $templateId,
                    ],
                ]
            ]);
            $response_dencode = json_decode($response->getBody(), true);  //配列へ
            if ($response->getStatusCode() == 200) {
                $response_body = json_decode($response->getBody(), true);  //配列へ
                $fileData = $response_body['result']['template_file'];
            } else {
                Log::error('Api storeBoard companyId:' . $user->mst_company_id);
                Log::error($response_dencode);
                return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
            }

            $fileEncode = $fileData[0]['file_data'];
            $file = $fileData;
            return $this->sendResponse(['file' => $file, 'file_data' => $fileEncode], 'get file done');

        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return ['status' => \Illuminate\Http\Response::HTTP_BAD_REQUEST, 'message' => "ファイルを取得できませんでした。"];
        }
    }

    public function getContentTemplateEdit($circularId, Request $request)
    {
        try {
            $user = $request->user();
            $mst_company_id = $user->mst_company_id;
            $file = [];
            $file_edit_exists = DB::table('template_edit_file')->where('circular_id', $circularId)->where('mst_company_id', $mst_company_id)->exists();

            if ($file_edit_exists) {
                $file_edit = DB::table('template_edit_file')
                    ->where('circular_id', $circularId)
                    ->where('mst_company_id', $mst_company_id)
                    ->orderBy('edit_number', 'desc')
                    ->first();
                array_push($file, $file_edit);
                $local_path = TemplateUtils::localTemplatePath($user->mst_company_id, $user->id);
                $fileData = [];

                //ファイルの存在を確認してS3から取得をする
                $s3_path = $file[0]->location ? str_replace(env('AWS_URL', ''), '', $file[0]->location) : $file[0]->location;
                if (Storage::disk('s3')->exists($s3_path)) {
                    $getFile = Storage::disk('s3')->get($s3_path);
                    Storage::disk('local')->put($local_path . $file[0]->storage_file_name, $getFile);
                    Log::info('テンプレート取得: ' . $s3_path);
                }

                $filePath = storage_path('app/' . $local_path . $file[0]->storage_file_name);
                //$result = ['status' => \Illuminate\Http\Response::HTTP_OK, 'file' => $file, 'file_data' => \base64_encode(\file_get_contents($filePath)) ];

                //return $result;
                $fileEncode = \base64_encode(\file_get_contents($filePath));
                Storage::disk('local')->delete($file[0]->storage_file_name);

                return $this->sendResponse(['file' => $file, 'file_data' => $fileEncode], 'get file done');
            } else {
                return $this->sendResponse('', 'ファイルを取得できませんでした。');
            }

        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return ['status' => \Illuminate\Http\Response::HTTP_BAD_REQUEST, 'message' => "ファイルを取得できませんでした。"];
        }
    }

    /**
     * edit the template files
     *
     * request parameters
     *  -- templateId and data to replace
     *
     * response data
     *  ・file_name: file name
     *  ・file_data: file data by encoding base64
     */
    public function edit($templateId, Request $request)
    {
        try {
            $user = $request->user();
            $special_sit_flg = $request->get('special_sit_flg');
            $template_edit_flg = $request->get('circular_temp_edit');
            //PAC_5-1527 テンプレート途中編集
            $circular_id = $request->get('circular_id', '');

            if (!$special_sit_flg) {
                //特設サイト以外の場合、s3からファイル取得
                $placeholderList = DB::table('template_file')
                    ->leftjoin('template_placeholder_data', 'template_file.id', '=', 'template_placeholder_data.template_file_id')
                    ->where('template_file.id', $templateId)
                    ->get()
                    ->toArray();

                if (count($placeholderList) > 0) {
                    // object =>array
                    $placeholderList = array_map('get_object_vars', $placeholderList);
                    $local_path = TemplateUtils::localTemplatePath($user->mst_company_id, $user->id);
                    $s3_path = $placeholderList[0]['location'] ? str_replace(env('AWS_URL', ''), '', $placeholderList[0]['location']) : $placeholderList[0]['location'];
                    if (Storage::disk('s3')->exists($s3_path)) {
                        $getFile = Storage::disk('s3')->get($s3_path);
                        Storage::disk('public')->put($local_path . $placeholderList[0]['storage_file_name'], $getFile);
                        Log::info('テンプレート取得: ' . $s3_path);
                    }
                    $filePath = storage_path('app/public/' . $local_path . $placeholderList[0]['storage_file_name']);
                    $document_type = $placeholderList[0]['document_type'];
                    $file_name = $placeholderList[0]['file_name'];
                } else {
                    return ['status' => \Illuminate\Http\Response::HTTP_BAD_REQUEST, 'message' => "テンプレートファイル存在しません。"];
                }
            } else {
                //特設サイトの場合、APIからファイル取得
                //SRS-015 テンプレート文書データ取得
                $client = SpecialApiUtils::getAuthorizeClient();
                if (!$client) {
                    Log::error(__('message.false.auth_client'));
                }

                $response = $client->post("/sp/api/get-template-circular-data", [
                    RequestOptions::JSON => [
                        'company_id' => $user->mst_company_id,
                        "env_flg" => config('app.server_env'),
                        "edition_flg" => config('app.edition_flg'),
                        "server_flg" => config('app.server_flg'),
                        "user_id" => $user->id,
                        "template_info" => [
                            "template_file_id" => $templateId,
                        ],
                    ]
                ]);

                $response_dencode = json_decode($response->getBody(), true);  //配列へ
                if ($response->getStatusCode() == 200) {
                    $response_body = json_decode($response->getBody(), true);  //配列へ
                    $fileData = $response_body['result']['template_file'];
                    $placeholderList = $response_body['result']['placeholder_datas'];
                } else {
                    Log::error('Api storeBoard companyId:' . $user->mst_company_id);
                    Log::error($response_dencode);
                    return ['status' => \Illuminate\Http\Response::HTTP_BAD_REQUEST, 'message' => "テンプレートファイル存在しません。"];
                }
                $fileDetail = base64_decode($fileData[0]['file_data']);
                $company_id = $user->mst_company_id;
                $edition_flg = config('app.edition_flg');
                $env_flg = config('app.server_env');
                $server_flg = config('app.server_flg');
                $user_id = $user->id;
                $file_name = $fileData[0]['storage_file_name'];
                $file_path = storage_path("app/special/$edition_flg/$env_flg/$server_flg/$company_id/$user_id/");
                if (!is_dir($file_path)) {
                    mkdir($file_path, 0755, true);
                }
                preg_match('/[^.]*$/', $fileData[0]['storage_file_name'], $matches);
                $realFileExtension = strtolower($matches[0]);
                $unique = strtoupper(md5(uniqid(session_create_id(), true)));
                $filePath = $file_path . "/$unique.$realFileExtension";
                file_put_contents($filePath, $fileDetail);
                $file_name_arr = explode('.', $file_name);
                $document_type = in_array($file_name_arr[count($file_name_arr) - 1], ['xls', 'xlsx']) ? 0 : 1;
            }
            //テンプレートファイル編集開始
            if ($document_type === 0) {
                Log::info('Excelファイル編集開始');
                if (!$placeholderList || !$placeholderList[0]['template_placeholder_name']) {
                    return ['status' => \Illuminate\Http\Response::HTTP_OK, 'file_name' => $file_name, 'file_data' => \base64_encode(\file_get_contents($filePath)), 'no_placeHolder' => 1, 'message' => 'ファイルを編集しました。'];
                }

                $reader = new XlsxReader();
                $reader->setReadDataOnly(false);
                $spreadsheet = $reader->load($filePath);
                $sheet = $spreadsheet->getActiveSheet();

                foreach ($placeholderList as $value) {
                    $placeholder_Value = $request[$value['template_placeholder_name']][0];
                    $confirm_flg_value = $request[$value['template_placeholder_name']][1];
                    if ($template_edit_flg) {
                        if (!empty($placeholder_Value) || ($confirm_flg_value != null && empty($placeholder_Value)) || ($confirm_flg_value != 0 && empty($placeholder_Value))) {
                            $cellData = $sheet->getCell($value['cell_address'])->getValue();
                            $newCellData = str_replace($value['template_placeholder_name'], $placeholder_Value, $cellData);
                            $sheet->setCellValue($value['cell_address'], $placeholder_Value);
                        }
                    } else {
                        $cellData = $sheet->getCell($value['cell_address'])->getValue();
                        $newCellData = str_replace($value['template_placeholder_name'], $placeholder_Value, $cellData);
                        $sheet->setCellValue($value['cell_address'], $placeholder_Value);
                    }
                }

                $writer = new XlsxWriter($spreadsheet);
                $path = explode(".", (microtime(true) . ""))[0] . '_' . $user->id . '.xlsx';
                $writer->save($path);

                $result = ['status' => \Illuminate\Http\Response::HTTP_OK, 'file_name' => $file_name,
                    'file_data' => \base64_encode(\file_get_contents(public_path() . '/' . $path)), 'storage_file_name' => $path, 'no_placeHolder' => 0, 'message' => 'ファイルを編集しました。'];
                unlink($filePath);
//                Storage::disk('public')->delete($placeholderList[0]['storage_file_name']);
                Log::info('編集済みExcelファイル送信');

                return $result;
            } else {
                Log::info('Wordファイル編集開始');
                if (!$placeholderList || !$placeholderList[0]['template_placeholder_name']) {

                    $result = ['status' => \Illuminate\Http\Response::HTTP_OK, 'file_name' => $file_name,
                        'file_data' => \base64_encode(\file_get_contents($filePath)), 'no_placeHolder' => 1, 'message' => 'ファイルを編集しました。'];
                    Log::debug('編集項目の無いWordファイル送信');

                    return $result;
                }

                $templateProcessor = new PhpWord\TemplateProcessor($filePath);
                foreach ($placeholderList as $value) {
                    $placeholder_Value = $request[$value['template_placeholder_name']][0];
                    $confirm_flg_value = $request[$value['template_placeholder_name']][1];
                    if ($template_edit_flg) {
                        if (!empty($placeholder_Value) || ($confirm_flg_value != null && empty($placeholder_Value)) || ($confirm_flg_value != 0 && empty($placeholder_Value))) {
                            $templateProcessor->setValue($value['template_placeholder_name'], $placeholder_Value);
                        }
                    } else {
                        $templateProcessor->setValue($value['template_placeholder_name'], $placeholder_Value);
                    }
                }
                $path = explode(".", (microtime(true) . ""))[0] . '_' . $user->id . '.docx';
                $templateProcessor->saveAs($path);
                ob_end_clean(); //バッファ消去

                $result = ['status' => \Illuminate\Http\Response::HTTP_OK, 'file_name' => $file_name,
                    'file_data' => \base64_encode(\file_get_contents(public_path() . '/' . $path)), 'storage_file_name' => $path, 'templateId' => $templateId, 'no_placeHolder' => 0, 'message' => 'ファイルを編集しました。'];

                Log::info('編集済みWordファイル送信');

                return $result;
            }

        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return ['status' => \Illuminate\Http\Response::HTTP_BAD_REQUEST, 'message' => "ファイルを編集できませんでした。"];
        }
    }

    public function saveInputData(Request $request)
    {
        try {
            $user = $request->user();
            $templateId = $request['templateId'];
            $circularId = $request['circularId'];

            $placeholderList = DB::table('template_file')
                ->leftjoin('template_placeholder_data', 'template_file.id', '=', 'template_placeholder_data.template_file_id')
                ->where('template_file.id', $templateId)
                ->get();

            $template_input_exist = DB::table('template_input_data')
                ->where('circular_id', $circularId)
                ->exists();

            Log::info('template編集保存開始');
            Log::debug($placeholderList);
            $templateInfo = array();
            if (count($placeholderList) > 0 && $placeholderList[0]->create_user_type == TemplateUtils::CREATE_APP && !$request->get('special_sit_flg')) {

                DB::beginTransaction();
                //PAC_1527 template_input_data途中編集用　削除→再追加処理
                if ($template_input_exist) {
                    DB::table('template_input_data')->where('circular_id', $circularId)->delete();
                }

                if ($placeholderList[0]->file_name && is_null($placeholderList[0]->template_placeholder_name)) {
                    Log::info('template編集保存完了');

                    return $this->sendResponse(true, 'テンプレート入力情報はありません。');
                }

                foreach ($placeholderList as $value) {
                    $data = [
                        'template_id' => $templateId,
                        'circular_id' => $circularId,
                        'placeholder_id' => $value->id,
                        'user_id' => $user->id,
                        'template_placeholder_name' => $value->template_placeholder_name,
                        'template_placeholder_data' => $request[$value->template_placeholder_name][0],
                        'create_at' => Carbon::now(),
                        'create_user' => $user->family_name . $user->given_name,
                        'confirm_flg' => $request[$value->template_placeholder_name][1] ? 2 : 0,
                    ];

                    if ($this->isDate($request[$value->template_placeholder_name][0])) {
                        $data += array('data_type' => 0);
                        $data += array('date_data' => Carbon::parse($request[$value->template_placeholder_name][0]));
                    } else if (is_numeric($request[$value->template_placeholder_name][0])) {
                        $data += array('data_type' => 1);
                        $data += array('num_data' => $request[$value->template_placeholder_name][0]);
                    } else {
                        $data += array('data_type' => 2);
                        $data += array('text_data' => $request[$value->template_placeholder_name][0]);
                    }

                    $value->placeholder_id = DB::table('template_input_data')
                        ->insertGetId($data);
                }

                DB::commit();
            } else {
                //SRS-015 テンプレート文書データ取得
                $client = SpecialApiUtils::getAuthorizeClient();
                if (!$client) {
                    Log::error(__('message.false.auth_client'));
                }

                $response = $client->post("/sp/api/get-template-circular-data", [
                    RequestOptions::JSON => [
                        'company_id' => $user->mst_company_id,
                        "env_flg" => config('app.server_env'),
                        "edition_flg" => config('app.edition_flg'),
                        "server_flg" => config('app.server_flg'),
                        "user_id" => $user->id,
                        "template_info" => [
                            "template_file_id" => $templateId,
                        ],
                    ]
                ]);

                $response_dencode = json_decode($response->getBody(), true);  //配列へ
                if ($response->getStatusCode() == 200) {
                    $response_body = json_decode($response->getBody(), true);  //配列へ
                    $fileData = $response_body['result']['template_file'];
                    $placeholderList = $response_body['result']['placeholder_datas'];
                } else {
                    Log::error('Api storeBoard companyId:' . $user->mst_company_id);
                    Log::error($response_dencode);
                    return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
                }

                // SRS-016 入力済み文書作成
                // api呼出
                $input_info = [];
                foreach ($placeholderList as $value) {
                    if ($this->isDate($request[$value['template_placeholder_name']][0])) {
                        $data = [
                            "placeholder_id" => $value['placeholder_id'],
                            "input_data_type" => 0,
                            "input_data" => $request[$value['template_placeholder_name']][0],
                        ];
                    } else if (is_numeric($request[$value['template_placeholder_name']][0])) {
                        $data = [
                            "placeholder_id" => $value['placeholder_id'],
                            "input_data_type" => 1,
                            "input_data" => $request[$value['template_placeholder_name']][0]
                        ];
                    } else {
                        $data = [
                            "placeholder_id" => $value['placeholder_id'],
                            "input_data_type" => 2,
                            "input_data" => $request[$value['template_placeholder_name']][0],
                        ];
                    }
                    array_push($input_info, $data);
                }
                $response = $client->post("/sp/api/create-entered-circular", [
                    RequestOptions::JSON => [
                        'company_id' => $user->mst_company_id,
                        "env_flg" => config('app.server_env'),
                        "edition_flg" => config('app.edition_flg'),
                        "server_flg" => config('app.server_flg'),
                        "user_id" => $user->id,
                        "template_info" => [
                            "template_file_id" => $templateId,
                            "company_id" => $fileData[0]['company_id'],
                            "env_flg" => $fileData[0]['env_flg'],
                            "edition_flg" => $fileData[0]['edition_flg'],
                            "server_flg" => $fileData[0]['server_flg'],
                        ],
                        "input_info" => $input_info,
                    ]
                ]);
                $response_dencode = json_decode($response->getBody(), true);  //配列へ
                if ($response->getStatusCode() == 200) {
                    $response_body = json_decode($response->getBody(), true);  //配列へ
                    if ($response_body['status'] != 'success') {
                        Log::error('Api storeBoard companyId:' . $user->mst_company_id);
                        Log::error($response_dencode);
                        return $this->sendError('テンプレート入力情報保存に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                    }

                    DB::table('special_site_circular')->insert([
                        'circular_id' => $circularId,
                        'receive_mst_company_id' => $fileData[0]['company_id'],
                        'receive_edition_flg' => $fileData[0]['edition_flg'],
                        'receive_env_flg' => $fileData[0]['env_flg'],
                        'receive_server_flg' => $fileData[0]['server_flg'],
                        'circular_token' => $response_body['result']['entered_circular_token'],
                        'special_template_id' => $templateId,
                    ]);

                } else {
                    Log::error('Api storeBoard companyId:' . $user->mst_company_id);
                    Log::error($response_dencode);
                    return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
                }
            }

            Log::info('template編集保存完了');

            return $this->sendResponse(true, 'テンプレート入力情報保存に成功しました。')->cookie('templateInfo', json_encode($templateInfo), 600);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return ['status' => \Illuminate\Http\Response::HTTP_BAD_REQUEST, 'message' => "ファイルを編集できませんでした。"];
        }
    }


    public function saveInputEditTemplate(Request $request)
    {
        try {
            $user = $request->user();
            $circular_id = $request->get('circularInfo');
            $storage_file_name = $request->get('storage_file_name');
            $templateId = $request->get('templateId');

            //PAC_5-1527 テンプレート途中編集　編集ファイル保存
            $placeholderList = DB::table('template_file')
                ->where('id', $templateId)
                ->get();

            $edit_s3path = config('filesystems.prefix_path') . '/' . config('app.s3_storage_root_folder');
            $isFolderExist = Storage::disk('s3')->exists($edit_s3path);
            if (!$isFolderExist) {
                Storage::disk('s3')->makeDirectory($edit_s3path);
                Storage::disk('s3')->makeDirectory($edit_s3path . '/template_edit');

                $edit_s3path = $edit_s3path . '/' . 'template_edit/' . $this->templateDirectory . $user->mst_company_id;
                Storage::disk('s3')->makeDirectory($edit_s3path);
            } else {
                $edit_s3path = $edit_s3path . '/' . 'template_edit/' . $this->templateDirectory;
                if (!$isFolderExist) {
                    Storage::disk('s3')->makeDirectory($edit_s3path);
                    Storage::disk('s3')->makeDirectory($edit_s3path . '/' . $user->mst_company_id);

                    $edit_s3path = $edit_s3path . '/' . $user->mst_company_id;
                } else {
                    $edit_s3path = $edit_s3path . '/' . $user->mst_company_id;
                    $isFolderExist = Storage::disk('s3')->exists($edit_s3path);
                    if (!$isFolderExist) {
                        Storage::disk('s3')->makeDirectory($edit_s3path);
                    }
                }
            }

            $server_path = public_path() . '/' . $storage_file_name;

            Storage::disk('s3')->putfileAs($edit_s3path, $server_path, $storage_file_name, 'pub');
            $edit_s3url = Storage::disk('s3')->url($edit_s3path . '/' . $storage_file_name);
            $userName = $user->family_name . $user->given_name;
            DB::beginTransaction();

            $edit_number = DB::table('template_edit_file')
                ->select('edit_number')
                ->where('circular_id', $circular_id)
                ->orderBy('edit_number', 'desc')
                ->first();

            if ($edit_number) {
                $temp_edit_data = $edit_number->edit_number + 1;
            } else {
                $temp_edit_data = 1;
            }

            $temp_edit_data = [
                'mst_company_id' => $user->mst_company_id,
                'mst_user_id' => $user->id,
                'file_name' => $placeholderList[0]->file_name,
                'storage_file_name' => $storage_file_name,
                'location' => $edit_s3url,
                'template_file_id' => $templateId,
                'circular_id' => $circular_id,
                'edit_number' => $temp_edit_data,
                'status' => 1,
                'template_edit_at' => Carbon::now(),
                'template_edit_user' => $userName,
            ];

            if ($storage_file_name) {
                $template_edit_id = DB::table('template_edit_file')
                    ->insertGetId($temp_edit_data);
            }

            DB::commit();

            $result = ['status' => \Illuminate\Http\Response::HTTP_OK,
                'file_data' => \base64_encode(\file_get_contents(public_path() . '/' . $storage_file_name)), 'message' => 'ファイルを編集しました。'];

            if ($storage_file_name) {
                //s3に保存したサーバファイルを削除
                unlink($server_path);
            }

            return $result;

        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return ['status' => \Illuminate\Http\Response::HTTP_BAD_REQUEST, 'message' => "ファイルを編集できませんでした。"];
        }
    }

    private function isDate($date)
    {
        // e.g. 2020/01/01 is true
        if (preg_match('/^(\d{1,4})\/(0[1-9]|1[012])\/(0[1-9]|[12][0-9]|3[01])$/', $date)) {
            return true;
            // e.g. 2020/01/01 00:00:00 is true
        } else if (preg_match('/^(\d{1,4})\/(0[1-9]|1[012])\/(0[1-9]|[12][0-9]|3[01]) ([0-1][0-9]|2[0-4]):[0-5][0-9]:[0-5][0-9]$/', $date)) {
            return true;
        } else {
            return false;
        }

        return false;
    }

    public function CsvDownloadUserForm(Request $request)
    {

        try {
            $user = $request->user();
            $templateId = $request['templateId'];
            $circularId = $request['circularId'];
            $emailFormList = $request['emailFormList'];

            foreach ($emailFormList as $email) {
                DB::beginTransaction();

                $user_info = DB::table('mst_user')
                    ->where('state_flg', 1)
                    ->where('email', $email)
                    ->select('id', 'mst_company_id')
                    ->get();
                $user_info = $array = json_decode(json_encode($user_info), true);

                $template_info = DB::table('template_file')
                    ->where('id', $templateId)
                    ->select('template_create_at', 'template_create_user', 'template_update_at', 'template_update_user')
                    ->get();
                $template_info = $array = json_decode(json_encode($template_info), true);

                $data = [
                    'template_id' => $templateId,
                    'circular_id' => $circularId,
                    'mst_user_id' => $user_info[0]["id"],
                    'mst_company_id' => $user_info[0]["mst_company_id"],
                    'csv_permit_user' => $email,
                    'template_create_at' => $template_info[0]["template_create_at"],
                    'template_create_user' => $template_info[0]["template_create_user"],
                    'template_update_at' => $template_info[0]["template_update_at"],
                    'template_update_user' => $template_info[0]["template_update_user"]
                ];

                DB::table('template_csv_permit_user')
                    ->insert($data);

                DB::commit();
            }
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return ['status' => \Illuminate\Http\Response::HTTP_BAD_REQUEST, 'message' => "エラー"];
        }
    }

    public function getCsvFlg(Request $request)
    {
        try {
            $user = $request->user();

            $mst_user = DB::table('mst_user')
                ->where('id', $user->id)
                ->get();

            $mst_company = DB::table('mst_company')
                ->where('id', $mst_user[0]->mst_company_id)
                ->get();

            $template_csv_flg = $mst_company[0]->template_csv_flg;

            return $this->sendResponse($template_csv_flg, __('message.success.data_get', ['attribute' => 'CSV出力']));

        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return ['status' => \Illuminate\Http\Response::HTTP_BAD_REQUEST, 'message' => "エラー"];
        }
    }

    public function templateCsvCheckEmail(Request $request)
    {
        try {
            $user = $request->user();
            $input_email = $request->input_email;
            $email_list = $request->email_list;

            $data = [
                'message' => '',
                'error_flg' => false
            ];

            //空白チェック
            if (!isset($input_email)) {
                $data = array(
                    'message' => '必須項目です',
                    'error_flg' => True
                );
                return $this->sendResponse($data, 'CSV出力ユーザチェック');
            }

            //申請者のメールアドレス
            if ($input_email === $user->email) {
                $data = array(
                    'message' => '申請者のメールアドレスは登録済みです',
                    'error_flg' => True
                );
                return $this->sendResponse($data, 'CSV出力ユーザチェック');
            }

            //メールアドレス重複チェック
            if (in_array($input_email, $email_list)) {
                $data = array(
                    'message' => 'メールアドレスが重複しています',
                    'error_flg' => True
                );
                return $this->sendResponse($data, 'CSV出力ユーザチェック');
            }

            //自社ユーザ存在チェック
            $company_exists = DB::table('mst_user')
                ->where('mst_company_id', $user->mst_company_id)
                ->where('state_flg', 1)
                ->where('email', $input_email)
                ->exists();

            if (!($company_exists)) {
                $data = array(
                    'message' => '自社以外のユーザは登録できません',
                    'error_flg' => True
                );
                return $this->sendResponse($data, 'CSV出力ユーザチェック');
            };

            //template_flg = 0ユーザチェック
            $mst_user = DB::table('mst_user')
                ->where('email', $input_email)
                ->first();

            $mst_user_info = DB::table('mst_user_info')
                ->where('mst_user_id', $mst_user->id)
                ->first();

            if (!($mst_user_info->template_flg)) {
                $data = array(
                    'message' => 'テンプレート機能が使用できないユーザです',
                    'error_flg' => True
                );
                return $this->sendResponse($data, 'CSV出力ユーザチェック');
            }

            return $this->sendResponse($data, 'CSV出力ユーザチェック');

        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return ['status' => \Illuminate\Http\Response::HTTP_BAD_REQUEST, 'message' => "エラー"];
        }
    }

    public function getTemplateInputComplete(Request $request)
    {
        try {
            $circular_id = $request['circular_id'];
            $template_input_data = DB::table('template_input_data')->where('circular_id', $circular_id)->get();

            $complete_data = [];
            $complete_data['templateId'] = $template_input_data[0]->template_id;
            $complete_data['circularId'] = $circular_id;
            $complete_data['circular_temp_edit'] = true;

            foreach ($template_input_data as $data) {
                $complete_data[$data->template_placeholder_name][0] = $data->template_placeholder_data;
                $complete_data[$data->template_placeholder_name][1] = 2;
            }

            $template_edit_file_exists = DB::table('template_edit_file')->where('circular_id', $circular_id)->exists();
            if ($template_edit_file_exists) {
                $template_edit_file_before = DB::table('template_edit_file')
                    ->where('circular_id', $circular_id)
                    ->orderBy('edit_number', 'desc')
                    ->first();
                $target_path = str_replace(env('AWS_URL', '') . '/', '', $template_edit_file_before->location);
                Storage::disk('s3')->delete($target_path);
            }

            Log::info('テンプレート途中編集完了データの取得に成功しました');
            return $this->sendResponse($complete_data, 'テンプレート途中編集完了データの取得に成功しました');

        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return Response::json(['status' => false, 'message' => $ex->getMessage(), 'data' => null], 500);
        }
    }

    public function sendTemplateEditFlg(Request $request)
    {
        try {
            DB::beginTransaction();
            $circular_id = $request['circular_id'];
            DB::table('circular')->where('id', $circular_id)->update(['template_edit_flg' => 1]);
            DB::commit();

            Log::info('テンプレート途中編集機能の有効化に成功しました');
            return $this->sendResponse($circular_id, 'テンプレート途中編集機能の有効化に成功しました');

        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return Response::json(['status' => false, 'message' => $ex->getMessage(), 'data' => null], 500);
        }
    }

    public function releaseTemplateEditFlg(Request $request)
    {
        try {
            DB::beginTransaction();
            $circular_id = $request['circularId'];
            DB::table('circular')->where('id', $circular_id)->update(['template_edit_flg' => 0]);
            DB::commit();

            Log::info('テンプレート途中編集機能の無効化に成功しました');
            return $this->sendResponse($circular_id, 'テンプレート途中編集機能の無効化に成功しました');

        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return Response::json(['status' => false, 'message' => $ex->getMessage(), 'data' => null], 500);
        }
    }

    public function getCircularTempEdit(Request $request)
    {
        try {
            $circular_id = $request['circular_id'];
            $circular_exists = DB::table('circular')->where('id', $circular_id)->exists();
            $result = false;
            if ($circular_exists) {
                $circular = DB::table('circular')->where('id', $circular_id)->get();
                $result = $circular[0]->template_edit_flg;
            }

            Log::info('テンプレート途中編集取得に成功しました');
            return $this->sendResponse($result, 'テンプレート途中編集取得に成功しました');

        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return Response::json(['status' => false, 'message' => $ex->getMessage(), 'data' => null], 500);
        }
    }

    public function templateEditS3delete(Request $request)
    {
        try {
            $circular_id = $request->get('circularId');
            $template_edit_s3file = DB::table('template_edit_file')->select('location')->where('circular_id', $circular_id)->get();

            if ($template_edit_s3file) {
                foreach ($template_edit_s3file as $path) {
                    $location = $path->location;
                    $target_path = str_replace(env('AWS_URL', '') . '/', '', $location);
                    Storage::disk('s3')->delete($target_path);
                }
            }
            DB::table('template_edit_file')
                ->where('circular_id', $circular_id)
                ->update(['delete_flg' => 1]);

            Log::info('テンプレート途中編集s3ファイルの削除に成功しました');
            return $this->sendResponse($circular_id, 'テンプレート途中編集s3ファイルの削除に成功しました');

        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return Response::json(['status' => false, 'message' => $ex->getMessage(), 'data' => null], 500);
        }
    }

    public function templateStampInfoDelete(Request $request)
    {
        try {
            $all = $request->all();
            $circular_id = $request['circular_id'];
            $circular_document = DB::table('circular_document')->where('circular_id', $circular_id)->get();
            $stamp_exixts = DB::table('stamp_info')->where('circular_document_id', $circular_document[0]->id)->exists();
            if ($stamp_exixts) {
                DB::table('stamp_info')->where('circular_document_id', $circular_document[0]->id)->delete();
                return $this->sendResponse($circular_document[0]->id, 'テンプレート途中編集更新に成功しました');
            } else {
                return $this->sendResponse($circular_document[0]->id, 'テンプレート途中編集更新に成功しました');
            }


        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return Response::json(['status' => false, 'message' => $ex->getMessage(), 'data' => null], 500);
        }
    }

    public function getTemplateNextUserCompletedFlg(Request $request)
    {
        try {
            $user = $request->user();
            $circular_id = $request->get('circularId');
            $circular_edit_exists = DB::table('circular')
                ->where('id', $circular_id)
                ->where('template_edit_flg', 1)
                ->exists();

            $templateNextUserCompletedFlg = false;
            if ($circular_edit_exists) {
                $circular_completed_user = DB::table('circular_user')
                    ->where('circular_id', $circular_id)
                    ->where('parent_send_order', 0)
                    ->orderBy('child_send_order', 'desc')
                    ->first();

                if ($user->id == $circular_completed_user->mst_user_id) {
                    $templateNextUserCompletedFlg = true;
                } else {
                    $templateNextUserCompletedFlg = false;
                }
            } else {
                $templateNextUserCompletedFlg = false;
            }
            return $this->sendResponse($templateNextUserCompletedFlg, 'テンプレート途中編集完了フラグの取得に成功しました');


        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return Response::json(['status' => false, 'message' => $ex->getMessage(), 'data' => null], 500);
        }
    }

    public function saveTemplateEditStamp(Request $request)
    {
        try {
            $user = $request->user();
            $files = $request->get('files', []);

            $circular_document_id = $request['active_id'];
            $stamps_info = $files[0]['stamps'];
            $circular_user = $request['current_circular_user'];

            $user_name = null;
            if ($user != null && isset($user->id)) {
                $user_name = $user->family_name . ' ' . $user->given_name;
            } else if ($circular_user != null && isset($circular_user->mst_user_id)) {
                $user_name = $circular_user->name;
            }

            $circular_document = DB::table('circular_document')->where('id', $circular_document_id)->get();
            $template_edit_stamp_id = [];
            foreach ($stamps_info as $stamp) {
                $edit_stamp_id = DB::table('template_edit_stamp_info')
                    ->insertGetId(
                        [
                            'circular_id' => $circular_document[0]->circular_id,
                            'repeated' => $stamp['repeated'],
                            'pageno' => $stamp['page'],
                            'stamp_data' => $stamp['stamp_data'],
                            'x_axis' => $stamp['x_axis'],
                            'y_axis' => $stamp['y_axis'],
                            'width' => $stamp['width'],
                            'height' => $stamp['height'],
                            'stamp_url' => $stamp['stamp_url'],
                            'stamp_id' => $stamp['id'],
                            'stamp_flg' => $stamp['stamp_flg'],
                            'time_stamp_permission' => $stamp['time_stamp_permission'],
                            'serial' => $stamp['serial'],
                            'rotateAngle' => $stamp['rotateAngle'],
                            'opacity' => array_key_exists('opacity', $stamp) ? $stamp['opacity'] : 1,
                            'created_at' => Carbon::now(),
                            'create_user' => $user_name
                        ]);
                array_push($template_edit_stamp_id, $edit_stamp_id);
            }
            Log::info('テンプレート途中編集スタンプ情報の保存に成功しました');
            return $this->sendResponse($template_edit_stamp_id, 'テンプレート途中編集スタンプ情報の保存に成功しました');

        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return Response::json(['status' => false, 'message' => $ex->getMessage(), 'data' => null], 500);
        }
    }

    public function saveTemplateEditText(Request $request)
    {
        try {
            $user = $request->user();
            $files = $request->get('files', []);
            $circular_user = $request['current_circular_user'];

            $user_name = null;
            if ($user != null && isset($user->id)) {
                $user_name = $user->family_name . ' ' . $user->given_name;
            } else if ($circular_user != null && isset($circular_user->mst_user_id)) {
                $user_name = $circular_user->name;
            }

            $circular_document_id = $request['active_id'];
            $texts_info = $files[0]['texts'];

            $circular_document = DB::table('circular_document')->where('id', $circular_document_id)->get();
            $template_edit_stamp_id = [];
            foreach ($texts_info as $text) {
                $edit_stamp_id = DB::table('template_edit_text_info')
                    ->insertGetId(
                        [
                            'circular_id' => $circular_document[0]->circular_id,
                            'fontColor' => $text['fontColor'],
                            'fontFamily' => $text['fontFamily'],
                            'fontSize' => $text['fontSize'],
                            'page' => $text['page'],
                            'text' => $text['text'],
                            'x_axis' => $text['x_axis'],
                            'y_axis' => $text['y_axis'],
                            'created_at' => Carbon::now(),
                            'create_user' => $user_name
                        ]);
                array_push($template_edit_stamp_id, $edit_stamp_id);
            }
            Log::info('テンプレート途中編集スタンプ情報の保存に成功しました');
            return $this->sendResponse($template_edit_stamp_id, 'テンプレート途中編集スタンプ情報の保存に成功しました');

        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return Response::json(['status' => false, 'message' => $ex->getMessage(), 'data' => null], 500);
        }
    }

    public function getTemplateEditStamp(Request $request)
    {
        try {
            $circular_id = $request->get('circularId', '');
            $stamp_info_exists = DB::table('template_edit_stamp_info')->where('circular_id', $circular_id)->exists();
            if ($stamp_info_exists) {
                $template_edit_stamp_info = DB::table('template_edit_stamp_info')
                    ->select(
                        'height',
                        'stamp_id as id',
                        'opacity',
                        'pageno as page',
                        'repeated',
                        'rotateAngle',
                        'serial',
                        'sid',
                        'stamp_data',
                        'stamp_flg',
                        'stamp_url',
                        'time_stamp_permission',
                        'width',
                        'x_axis',
                        'y_axis'
                    )
                    ->where('circular_id', $circular_id)
                    ->get();
                Log::info('テンプレート途中編集スタンプ情報の取得に成功しました');
                return $this->sendResponse($template_edit_stamp_info, 'テンプレート途中編集スタンプ情報の取得に成功しました');
            } else {
                return [];
            }
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return Response::json(['status' => false, 'message' => $ex->getMessage(), 'data' => null], 500);
        }
    }

    public function getTemplateEditText(Request $request)
    {
        try {
            $circular_id = $request->get('circularId', '');
            $text_info_exists = DB::table('template_edit_text_info')->where('circular_id', $circular_id)->exists();
            if ($text_info_exists) {
                $template_edit_text_info = DB::table('template_edit_text_info')
                    ->select(
                        'fontColor',
                        'fontFamily',
                        'fontSize',
                        'page',
                        'text',
                        'x_axis',
                        'y_axis'
                    )
                    ->where('circular_id', $circular_id)
                    ->get();

                Log::info('テンプレート途中編集テキスト情報の取得に成功しました');
                return $this->sendResponse($template_edit_text_info, 'テンプレート途中編集スタンプ情報の取得に成功しました');
            } else {
                return [];
            }
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return Response::json(['status' => false, 'message' => $ex->getMessage(), 'data' => null], 500);
        }
    }

    public function tempEditStampInfoFix(Request $request)
    {
        try {
            $circular_id = $request->get('circularId', '');
            $edit_stamp = DB::table('template_edit_stamp_info')->where('circular_id', $circular_id)->get();
            $circular_document = DB::table('circular_document')->where('circular_id', $circular_id)->get();
            $stamp_info = DB::table('stamp_info')->where('circular_document_id', $circular_document[0]->id)->get();
            $stamp_id = [];

            foreach ($stamp_info as $key_stamp => $info) {
                foreach ($edit_stamp as $key_edit => $data) {
                    if ($info->serial == $data->serial) {
                        DB::table('stamp_info')->where('id', $info->id)->update(['create_at' => $data->created_at]);
                        unset($edit_stamp[$key_edit]);
                        array_push($stamp_id, $info->id);
                        break;
                    }
                }
            }


            return $this->sendResponse($stamp_id, 'CSV出力ユーザチェック');

        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return Response::json(['status' => false, 'message' => $ex->getMessage(), 'data' => null], 500);
        }
    }


    //PAC_5-1527 テンプレート途中編集機能用情報取得
    public function getTemplateInfo(Request $request)
    {
        try {
            $user = $request->user();
            $circularId = $request[0];
            Log::info('$circularId' . var_export($request[0], true));

            $template_file_id = DB::table('template_input_data')
                ->select('template_id')
                ->where('circularId', $circularId)
                ->first();

            if ($template_file_id) {
                $template_file = DB::table('template_file')
                    ->where('template_id', $template_file_id)
                    ->get();

                $template_placeholder_datas = DB::table('template_placeholder_data')
                    ->where('template_id', $template_file_id)
                    ->get();

                $company = DB::table('mst_company')
                    ->where('id', $user->mst_company_id)
                    ->first();

                $department_list = DB::table('mst_department')
                    ->where('mst_company_id', $user->mst_company_id)
                    ->select('id', 'department_name')
                    ->get()
                    ->toArray();

                $position_list = DB::table('mst_position')
                    ->where('mst_company_id', $user->mst_company_id)
                    ->select('id', 'position_name')
                    ->get()
                    ->toArray();

                $user_info = DB::table('mst_user_info')
                    ->where('mst_user_id', $user->id)->first();

                foreach ($template_placeholder_datas as $value) {
                    foreach ($mst_placeholder as $mp) {
                        if ($value->template_placeholder_name === $mp->special_template_placeholder) {
                            if ($mp->id === 1) {
                                $value->template_placeholder_value = date("Y/m/d H:i:s");
                            } else if ($mp->id === 2) {
                                $value->template_placeholder_value = date("Y/m/d");
                            } else if ($mp->id === 3) {
                                $value->template_placeholder_value = $user->family_name . $user->given_name;
                            } else if ($mp->id === 4) {
                                $value->template_placeholder_value = $user->email;
                            } else if ($mp->id === 5) {
                                if ($company) {
                                    $value->template_placeholder_value = $company->company_name;
                                } else {
                                    $value->template_placeholder_value = '';
                                }
                            } else if ($mp->id === 6) {
                                // PAC_5-2098 Start
                                if ($multiple_department_position_flg === 1) {
                                    // PAC_5-1599 追加部署と役職 Start
                                    if (in_array($user_info->mst_department_id, $mst_department_ids[$template_user_ids[$value->template_file_id]])) {
                                        $value->template_placeholder_value = $departments[$user_info->mst_department_id] ?? '';
                                    } else if (in_array($user_info->mst_department_id_1, $mst_department_ids[$template_user_ids[$value->template_file_id]])) {
                                        $value->template_placeholder_value = $departments[$user_info->mst_department_id_1] ?? '';
                                    } else if (in_array($user_info->mst_department_id_2, $mst_department_ids[$template_user_ids[$value->template_file_id]])) {
                                        $value->template_placeholder_value = $departments[$user_info->mst_department_id_2] ?? '';
                                    } else {
                                        $value->template_placeholder_value = '';
                                    }
                                    // PAC_5-1599 End
                                } else {
                                    if (in_array($user_info->mst_department_id, $mst_department_ids[$template_user_ids[$value->template_file_id]])) {
                                        $value->template_placeholder_value = $departments[$user_info->mst_department_id] ?? '';
                                    } else {
                                        $value->template_placeholder_value = '';
                                    }
                                }
                                // PAC_5-2098 End
                            } else if ($mp->id === 7) {
                                // PAC_5-2098 Start
                                if ($multiple_department_position_flg === 1) {
                                    // PAC_5-1599 追加部署と役職 Start
                                    if (in_array($user_info->mst_department_id, $mst_department_ids[$template_user_ids[$value->template_file_id]])) {
                                        $value->template_placeholder_value = $positions[$user_info->mst_position_id] ?? '';
                                    } else if (in_array($user_info->mst_department_id_1, $mst_department_ids[$template_user_ids[$value->template_file_id]])) {
                                        $value->template_placeholder_value = $positions[$user_info->mst_position_id_1] ?? '';
                                    } else if (in_array($user_info->mst_department_id_2, $mst_department_ids[$template_user_ids[$value->template_file_id]])) {
                                        $value->template_placeholder_value = $positions[$user_info->mst_position_id_2] ?? '';
                                    } else {
                                        $value->template_placeholder_value = '';
                                    }
                                    // PAC_5-1599 End
                                } else {
                                    if (in_array($user_info->mst_department_id, $mst_department_ids[$template_user_ids[$value->template_file_id]])) {
                                        $value->template_placeholder_value = $positions[$user_info->mst_position_id] ?? '';
                                    } else {
                                        $value->template_placeholder_value = '';
                                    }
                                }
                                // PAC_5-2098 End
                            } else if ($mp->id === 8) {
                                $value->template_placeholder_value = $user_info->phone_number;
                            } else if ($mp->id === 9) {
                                $value->template_placeholder_value = $user_info->fax_number;
                            } else if ($mp->id === 10) {
                                $value->template_placeholder_value = $user_info->address;
                            } else if ($mp->id === 11) {
                                $value->template_placeholder_value = $user_info->postal_code;
                            }
                            break;
                        } else {
                            $value->template_placeholder_value = '';
                        }
                    }
                }
            }

            // return $this->sendResponse($data,'CSV出力ユーザチェック');

        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return ['status' => \Illuminate\Http\Response::HTTP_BAD_REQUEST, 'message' => "エラー"];
        }
    }

    public function updateTemplateRoute($templateId, Request $request)
    {
        $user = $request->user();
        try {
            $template_route_id = $request['template_route_id'];
            $templates = DB::table('circular_user_templates')
                ->where('id', $template_route_id)
                ->where('state', AppUtils::TEMPLATE_VALID)
                ->get();
            // データがない
            if (empty($template_route_id) || !count($templates)) {
                Log::error('テンプレートの承認ルート設定に失敗しました。templateId:' . $templateId . 'template_route_id:' . $template_route_id);
                return $this->sendError('この承認ルートが存在しません。');
            }
            DB::table('template_file')->where('id', $templateId)
                ->update([
                    'template_route_id' => $template_route_id,
                    'template_update_user' => $user->family_name . $user->given_name,
                    'template_update_at' => Carbon::now(),
                ]);
            return $this->sendResponse($templateId, 'テンプレートの承認ルート設定に成功しました。');

        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError("テンプレートの承認ルート設定に失敗しました。");
        }
    }

    public function getTemplateRouteInfo($templateId,Request $request)
    {
        $user = $request->user();
        try {
            $templateRouteId = $request->get('templateRouteId');
            if ($templateId && $templateRouteId) {
                $isExistTemplateRoute = DB::table('template_file')
                    ->join('circular_user_templates','template_file.template_route_id','=','circular_user_templates.id')
                    ->where('template_file.id', $templateId)
                    ->where('circular_user_templates.id', $templateRouteId)
                    ->where('state', TemplateRouteUtils::TEMPLATE_ROUTE_STATE_VALID)
                    ->exists();
                //circular_user_templates
                $multiple_department_position_flg = DB::table('mst_company')->where('id', $user->mst_company_id)->select('multiple_department_position_flg')->first()->multiple_department_position_flg ?? 0;

                $arrTemplates = DB::table('template_file')
                    ->join('circular_user_templates', 'template_file.template_route_id', '=', 'circular_user_templates.id')
                    ->join('circular_user_template_routes', 'circular_user_templates.id', '=', 'circular_user_template_routes.template')
                    ->join('mst_position', function ($query) {
                        $query->on('circular_user_template_routes.mst_position_id', '=', 'mst_position.id');
                        $query->where('mst_position.state', 1);
                    })
                    ->join('mst_department', function ($query) {
                        $query->on('circular_user_template_routes.mst_department_id', '=', 'mst_department.id');
                        $query->where('mst_department.state', 1);
                    })
                    ->leftjoin('mst_user_info', function ($query) use ($multiple_department_position_flg) {
                        if ($multiple_department_position_flg === 1) {
                            // 部署と役職
                            $query->on(function ($query) {
                                $query->on('mst_position.id', '=', 'mst_user_info.mst_position_id')
                                    ->on('mst_department.id', '=', 'mst_user_info.mst_department_id');
                            })->orOn(function ($query) {
                                $query->on('mst_position.id', '=', 'mst_user_info.mst_position_id_1')
                                    ->on('mst_department.id', '=', 'mst_user_info.mst_department_id_1');
                            })->orOn(function ($query) {
                                $query->on('mst_position.id', '=', 'mst_user_info.mst_position_id_2')
                                    ->on('mst_department.id', '=', 'mst_user_info.mst_department_id_2');
                            });
                        } else {
                            $query->on('mst_position.id', '=', 'mst_user_info.mst_position_id')
                                ->on('mst_department.id', '=', 'mst_user_info.mst_department_id');
                        }
                    })
                    ->join('mst_user', function ($query) use ($user) {
                        $query->on('mst_user_info.mst_user_id', '=', 'mst_user.id');
                        $query->where('mst_user.state_flg', 1);
                        $query->where('mst_user.mst_company_id', $user->mst_company_id);
                    })
                    ->where('circular_user_templates.mst_company_id', $user->mst_company_id)
                    ->where('circular_user_templates.state', AppUtils::TEMPLATE_VALID)
                    ->where('template_file.id', $templateId);

                $isNoneTemplateRouteUser = $arrTemplates->select(DB::raw('circular_user_templates.id, circular_user_template_routes.id route_id, circular_user_templates.name, mst_position.position_name,
                mst_department.department_name,mst_department.id as department_id,circular_user_template_routes.child_send_order,
                circular_user_template_routes.mode, circular_user_template_routes.option, circular_user_template_routes.wait,
                    mst_user.family_name, mst_user.given_name, mst_user.id as user_id, mst_user.email, mst_user_info.id as user_info_id'))
                    ->orderBy('circular_user_templates.id', 'asc')
                    ->orderBy('circular_user_template_routes.child_send_order', 'asc')
                    ->exists();
                return $this->sendResponse([$isExistTemplateRoute,$isNoneTemplateRouteUser], 'テンプレートの承認ルートが存在します。');
            }else{
                return $this->sendResponse([], 'テンプレートのパラメータが存在しません。');
            }
        }catch (\Exception $e){
            return $this->sendResponse([], 'テンプレートの承認ルートが存在しません。');
        }
    }
}