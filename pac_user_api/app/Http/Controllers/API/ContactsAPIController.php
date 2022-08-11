<?php

namespace App\Http\Controllers\API;
 
use App\Http\Utils\AppUtils;
use App\Http\Utils\ContactUtils;
use App\Http\Requests\API\CreateContactAPIRequest;
use App\Http\Utils\EnvApiUtils;
use App\Models\Contact;
use Carbon\Carbon;
use GuzzleHttp\RequestOptions;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Response;
use Session;

/**
 * Class ContactsAPIController
 * @package App\Http\Controllers\API
 */

class ContactsAPIController extends AppBaseController
{
    var $table = 'address';
    var $model = null;

    public function __construct(Contact $contact)
    {
        $this->modal = $contact;
    }

    /**
     * 本環境個人共通アドレス帳取得
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->id) {
            $user = $request['user'];
        }
        $filter = $request->get('filter', null);
        $type = $request->get('type', ContactUtils::TYPE_PERSONAL);
        $contacts = $this->getContacts($user->mst_company_id, $user->id, $type, $filter);

        return $this->sendResponse($contacts, 'Contacts retrieved successfully');
    }

    /**
     * hash値より、他の環境個人共通アドレス帳取得
     * @param Request $request
     * @return Response
     */
    public function getContactsByHash(Request $request)
    {
        $currentCircularUser = $request['current_circular_user'];
        if (!$currentCircularUser || $currentCircularUser->mst_company_id === null) {
            return $this->sendResponse([], 'アドレス帳の取得処理に成功しました。');
        }

        $filter = $request->get('filter', null);
        $type = $request->get('type', ContactUtils::TYPE_PERSONAL);
        $contacts = [];
        if ($currentCircularUser->edition_flg == config('app.edition_flg')) {
            //新エディション
            if ($currentCircularUser->env_flg == config('app.server_env') && $currentCircularUser->server_flg == config('app.server_flg')) {
                //本環境の場合、DBから取得
                $contacts = $this->getContacts($currentCircularUser->mst_company_id, $currentCircularUser->mst_user_id, $type, $filter);
            } else {
                //他の環境の場合、APIから取得
                $client = EnvApiUtils::getAuthorizeClient($currentCircularUser->env_flg, $currentCircularUser->server_flg);

                if (!$client) {
                    Log::error('他の環境からアドレス帳取得時に、API接続失敗しました。');
                    return $this->sendResponse([], 'アドレス帳の取得処理に成功しました。');
                }
                $response = $client->get("getCurrentContacts", [
                    RequestOptions::JSON => ['email' => $currentCircularUser->email, 'filter' => $filter, 'type' => $type,]
                ]);
                if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_CREATED && $response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
                    Log::error('アドレス帳取得失敗しました。');
                    Log::error($response->getBody());
                } else {
                    Log::info($response->getBody());
                    $result = json_decode($response->getBody());
                    $contacts = $result->data;
                }
            }
        } else {
            // todo 現行の場合、APIから取得
        }

        return $this->sendResponse($contacts, 'Contacts retrieved successfully');
    }

    /**
     * 環境を跨いだ場合、現在の環境の個人共通アドレス帳取得
     * @param Request $request
     * @return mixed
     */
    public function getCurrentContacts(Request $request)
    {
        try {
            $email = $request['email'];
            $filter = $request['filter'];
            $type = $request['type'];
            $user = DB::table('mst_user')->where('email', $email)->where('state_flg', '!=', AppUtils::STATE_DELETE)->first();
            $contacts = $this->getContacts($user ? $user->mst_company_id : 0, $user ? $user->id : 0, $type, $filter);

            return $this->sendResponse($contacts, '個人共通アドレス帳の取得処理に成功しました。');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 本環境個人共通アドレス帳取得共通メソッド
     * @param $company_id
     * @param $user_id
     * @param $type
     * @param $filter
     * @return mixed
     */
    private function getContacts($company_id, $user_id, $type, $filter)
    {
        $where = ['1 = 1'];
        $where_arg = [];
        if ($filter) {
            $where[] = '(name like ? OR email like ? OR position_name like ? OR group_name like ? OR company_name like ?)';
            $where_arg[] = "%$filter%";
            $where_arg[] = "%$filter%";
            $where_arg[] = "%$filter%";
            $where_arg[] = "%$filter%";
            $where_arg[] = "%$filter%";
        }

        $where[] = 'type = ?';
        $where_arg[] = $type;

        if ($type == ContactUtils::TYPE_PERSONAL) {
            $contactsNoCom = $this->modal
                ->whereRaw(implode(" AND ", $where), $where_arg)
                ->where('mst_user_id', $user_id)
                ->whereNull('group_name')
                ->select('id', 'name', 'email', 'group_name', 'state')
                ->orderBy('group_name', 'asc')
                ->orderBy('email', 'asc')
                ->get();

            $contacts = $this->modal
                ->whereRaw(implode(" AND ", $where), $where_arg)
                ->where('mst_user_id', $user_id)
                ->whereNotNull('group_name')
                ->select('id', 'name', 'email', 'group_name', 'state')
                ->orderBy('group_name', 'asc')
                ->orderBy('email', 'asc')
                ->get();

            $contacts = array_merge($contacts->toArray(), $contactsNoCom->toArray());

        } else {
            $contactsNoGrp = $this->modal
                ->whereRaw(implode(" AND ", $where), $where_arg)
                ->where('mst_company_id', $company_id)
                ->whereNull('company_name')
                ->select('id', 'name', 'email', 'company_name as group_name', 'state')
                ->orderBy('group_name', 'asc')
                ->orderBy('email', 'asc')
                ->get();

            $contacts = $this->modal
                ->whereRaw(implode(" AND ", $where), $where_arg)
                ->where('mst_company_id', $company_id)
                ->whereNotNull('company_name')
                ->select('id', 'name', 'email', 'company_name as group_name', 'state')
                ->orderBy('group_name', 'asc')
                ->orderBy('email', 'asc')
                ->get();

            $contacts = array_merge($contacts->toArray(), $contactsNoGrp->toArray());
        }
        return $contacts;
    }

    public function getAddresses(Request $request){
        try {
            $email = $request['email'];

            $user = DB::table('mst_user')->where('email', $email)
                ->where('state_flg', AppUtils::STATE_VALID)
                ->first();

            if(!$user) {
                return $this->sendError('権限がありません。', \Illuminate\Http\Response::HTTP_UNAUTHORIZED);
            }
            $contacts = $this->modal->where('state', AppUtils::STATE_VALID)
                ->where(function ($query) use ($user){
                    $query->where('mst_user_id', $user->id)
                        ->orWhere('mst_company_id', $user->mst_company_id);
                });
            $commonGroups = [];
            $privateGroups = [];
            foreach ($contacts as $contact){
                if ($contact->type){
                    if (!key_exists($contact->company_name, $commonGroups)){
                        $commonGroups[$contact->company_name] = [['name' => $contact->name, 'email' => 'email']];
                    }else{
                        $commonGroups[$contact->company_name][] = ['name' => $contact->name, 'email' => 'email'];
                    }
                }else{
                    if (!key_exists($contact->company_name, $privateGroups)){
                        $privateGroups[$contact->group_name] = [['name' => $contact->name, 'email' => 'email']];
                    }else{
                        $privateGroups[$contact->group_name][] = ['name' => $contact->name, 'email' => 'email'];
                    }
                }
            }
            $retCommonGroups = [];
            $retPrivateGroups = [];
            foreach ($commonGroups as $key => $value){
                $retCommonGroups[] = ['group_name' => $key, 'users' => $value];
            }
            foreach ($privateGroups as $key => $value){
                $retPrivateGroups[] = ['group_name' => $key, 'users' => $value];
            }

            return ['status' =>\Illuminate\Http\Response::HTTP_OK, 'message' =>'Contact retrieved successfully。', 'common_groups' => $retCommonGroups, 'private_groups' => $retPrivateGroups];

        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return ['status' =>\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR, 'message' =>$ex->getMessage()];
        }
    }

    /**
     * Display the specified Contact.
     * GET|HEAD /contacts/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $contact =  $this->modal->find($id);

        if (empty($contact)) {
            return $this->sendError('Contact not found');
        }

        return $this->sendResponse($contact, 'Contact retrieved successfully');
    }

    /**
     * Store a newly created Contact in storage.
     * POST /contacts
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(CreateContactAPIRequest $request)
    {
        $user = $request->user();

        $input = $request->all();

        $input['mst_company_id'] = $user->mst_company_id;
        $input['mst_user_id'] = $user->id;
        $input['type'] = ContactUtils::TYPE_PERSONAL;
        $input['state'] = ContactUtils::STATE_ENABLE;
        $input['create_user'] = $user->email;
        $input['update_user'] = $user->email;

        try{
            $contact = new $this->modal($input);
            $contact->save();
            return $this->sendResponse($contact->toArray(), 'アドレス帳の登録処理に成功しました。');
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('アドレス帳更新処理に失敗しました。');
        }
    }

    

    /**
     * Update the specified Contact in storage.
     * PUT/PATCH /contacts/{id}
     *
     * @param int $id
     * @param Request $request
     *
     * @return Response
     */
    public function update($id, CreateContactAPIRequest $request)
    {
        $user = $request->user();

        $input = $request->all();
        
        $contact =  $this->modal->find($id);
        $contact->update_user = $user->email;

        if (empty($contact)) {
            return $this->sendError('アドレス帳更新処理に失敗しました。');
        }

        try{
            $contact->fill($input);
            $contact->save();
            return $this->sendResponse($contact->toArray(), 'アドレス帳の登録処理に成功しました。');
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('アドレス帳更新処理に失敗しました。');
        }
    }

    /**
     * Remove the specified Contact from storage.
     * DELETE /contacts/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        $contact =  $this->modal->find($id);

        if (empty($contact)) {
            return $this->sendError('アドレス帳削除処理に失敗しました。');
        }

        Session::flash('group_name', $contact->group_name);
        Session::flash('name', $contact->name);
        Session::flash('email', $contact->email);
        
        try{
            $contact->delete();
            return $this->sendResponse($contact->toArray(), 'アドレス帳の削除処理に成功しました。');
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('アドレス帳削除処理に失敗しました。');
        }
    } 
}
