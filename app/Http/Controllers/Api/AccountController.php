<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Input;
use Symfony\Component\HttpFoundation\Request;
use App\Models\{AccountLog, Transaction, Users};

class AccountController extends Controller
{
    public function list()
    {
        $address = Users::getUserId(Input::get('address', ''));
        $limit = Input::get('limit', '12');
        $page = Input::get('page', '1');
        if (empty($address)) {
            return $this->error("参数错误");
        }
        $user = Users::where("id", $address)->first();
        if (empty($user)) {
            return $this->error("数据未找到");
        }
        $data = AccountLog::where("user_id", $user->id)->orderBy('id', 'DESC')->paginate($limit);
        return $this->success(array(
            "user_id" => $user->id,
            "data" => $data->items(),
            "limit" => $limit,
            "page" => $page,
        ));
    }

    public function show_profits(Request $request)
    {
        $user_id = Users::getUserId();
        $limit = $request->input('limit', 10);
        $prize_pool = AccountLog::whereHas('user', function ($query) use ($request) {
            $account_number = $request->input('account_number');
            if ($account_number) {
                $query->where('account_number', $account_number);
            }
        })->where(function ($query) use ($request) {
            //$scene = $request->input('scene', -1);
            $start_time = strtotime($request->input('start_time', null));
            $end_time = strtotime($request->input('end_time', null));
            //$scene != -1 && $query->where('scene', $scene);
            $start_time && $query->where('created_time', '>=', $start_time);
            $end_time && $query->where('created_time', '<=', $end_time);
        })->where("type", AccountLog::PROFIT_LOSS_RELEASE)->where("user_id", "=", $user_id)->orderBy('id', 'desc')->paginate($limit);

        return $this->success($prize_pool);
    }
}