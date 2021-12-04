<?php

namespace App\Http\Controllers\Api;

use App\Models\AccountLog;
use App\Models\Admin;
use App\Models\AdminRole;
use App\Models\AdminRolePermission;
use App\Models\Transaction;
use App\Models\TransactionHistory;
use App\Models\TransactionIn;
use App\Models\TransactionOut;
use App\Models\Users;
use App\Models\UsersWallet;
use function foo\func;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\Array_;
use Validator;
use Illuminate\Support\Facades\Input;
use Symfony\Component\Process\Process;
use App\Models\AdminToken;
use App\Models\TransactionComplete;

class TradeApiController extends Controller
{

    public function TransactionComplete(Request $request)
    {
//        $key_id = $request->input('key_id', '');
//        $key_secret=$request->input('key_secret','');
//        $accessKey= AccessKey::getByKeyId($key_id);
//        if (empty($accessKey)) {
//            return $this->error('key_id验证失败');
//        }
    }
    public function  getFinished(Request $request){
        $limit = $request->get('limit', 20);
        $page = $request->get('page', 1);
        $user_id=$request->get('user_id','');
        $check_money=$request->get('check_money','');
        if (empty($user_id)){
            return $this->error('用户id不能为空');
        }
        if (empty($check_money)){
            return $this->error('报备金额不能为空');
        }
        $start_time = strtotime(date('Y-m-d'));
        $id_arr=array();
        $transactionIn=TransactionIn::where('user_id',$user_id)
            ->where('create_time',">=",$start_time)
            ->where('price',$check_money)
            ->get('history_id')->toArray();
        foreach ($transactionIn as $key=>$value){
            array_push($id_arr,$value['history_id']);
        }
        $transactionOut=TransactionOut::where('user_id',$user_id)
            ->where('create_time',">=",$start_time)
            ->where('price',$check_money)
            ->get('history_id')->toArray();
        foreach ($transactionOut as $key=>$value){
            array_push($id_arr,$value['history_id']);
        }
        $res_data=TransactionHistory::where("user_id",$user_id)
            ->get('id')
            ->toArray();
        $res=TransactionHistory::where("user_id",$user_id)
            ->where("create_time",">=",$start_time)
            ->whereNotIn('id',$id_arr)
            ->orderBy('number','desc')
            ->paginate($limit);
//        $res=DB::select('select price,number from transaction_history group by price,number having count(*)>2');
        $res_data=TransactionHistory::where("user_id",$user_id)
            ->where("create_time",">=",$start_time)
            ->select()->get()->toArray();
        $ids=Array();
        //去除未完成的
        foreach ($id_arr as $key=>$value){
            foreach ($res_data as $key_item=>$value_item){
                if ($value_item['id']==$value){
                    unset($res_data[$key_item]);
                }
            }
        }
        foreach ($res_data as $key=>$value){
            $price=$value['price'];
            $number=$value['number'];
            $way=$value['way'];
            $id=$value['id'];
            unset($res_data[$key]);
            foreach ($res_data as $key_item=>$value_item){
                $price_item=$value_item['price'];
                $number_item=$value_item['number'];
                $way_item=$value_item['way'];
                if ($price_item==$price&&$number==$number_item&&$way!=$way_item){
                    array_push($ids,$id);
                    unset($res_data[$key_item]);
                }
            }
        }

        $res=TransactionHistory::where("user_id",$user_id)
            ->where("create_time",">=",$start_time)
            ->where('currency',16)
            ->where('legal',3)
            ->where('price',$check_money)
            ->whereIn('id',$ids)
            ->whereNotIn('id',$id_arr)
            ->orderBy('number','desc')
            ->select('id','price','number','create_time')
            ->paginate($limit);

        return $this->success($res);
    }
    public function  getUserId(Request $request){
        $account_number=$request->get('account_number','');
        $user= Users::getByAccountNumber($account_number);
        if (empty($user)){
            return $this->error("没有找到用户");
        }
        $data=[
            'user_id'=>$user->id,
            'account_number'=>$user->account_number
        ];
        return $this->success($data);
    }

    /**
     * 分红转入
     * @param Request 参数
     * @return \Illuminate\Http\JsonResponse
     */
    public  function  transMoney(Request $request){
        $user_id=$request->get('user_id');
        $number=$request->get('number');
        if($number<0){
            return $this->error("数量不能为负数");
        }
        try{
            DB::beginTransaction();
            $user_wallet = UsersWallet::where('user_id', $user_id)
                ->lockForUpdate()
                ->where('currency', 16)
                ->first();//LED:16
            if (!$user_wallet) {
                throw new \Exception('钱包不存在');
            }
            $from_account_log_type=AccountLog::WAllET_DIVIDENT_IN;
            $memo = '分红转入';
            $result = change_wallet_balance($user_wallet, 2, $number, $from_account_log_type, $memo);
            if ($result !== true) {
                throw new \Exception($result);
            }
            DB::commit();
            return $this->success('转入成功');
        }catch (\Exception $e) {
            DB::rollBack();
            return $this->error('操作失败:' . $e->getMessage());
        }
    }

}
