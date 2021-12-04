<?php

namespace App\Http\Controllers\Api;

use App\Console\Commands\MiningBuyBonus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use App\Models\{AppApi, UserChat, Users, UserReal, Token, AccountLog, UsersWallet, UsersWalletcopy, Currency, InviteBg, Setting, UserCashInfo, ExchangeShiftTo, MiningReturnsBonus, UserMining, UsersWalletOut,BondConfig, ChargeHash};
use App\DAO\RPC;
use App\DAO\UserDAO;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;
use App\Events\WithdrawSubmitEvent;

class BackendController extends Controller
{
    /**
     * 登录
     */
    public function Login(Request $request){
        $trx_address=$request->get('address','');
        if(empty($trx_address)){
            return $this->error('请输入地址');
        }
        $user=Users::getByTrxAddress($trx_address);
        if(empty($user)){
            return $this->error('用户不存在');
        }
        $parent_address='';
        $parent_user=Users::getById($user->parent_id);
        if($parent_user!=null)
        {
            $parent_address=$parent_user->trx_address;
        }
        $result=[
            'parent_address'=>$parent_address,
            'address'=>$user->trx_address,
            'token'=>Token::setToken($user->id)
        ];
        return $this->success('操作成功',$result);
    }
    /**
     * 后台控制面板首页
     */
    public function Dashboard(Request $request){       
        $user_id = Users::getUserId();
        $withdraw_wallet_model=UsersWallet::where('user_id',$user_id)->first();
        if(empty($withdraw_wallet_model)){
            return $this->error('参数错误');
        }
        $withdraw_wallet=$withdraw_wallet_model->change_balance;
        $total_package=UserMining::where('user_id',$user_id)->sum('num');
        $total_bonus=MiningReturnsBonus::where('user_id',$user_id)->sum('num');
        $upline_bonus=MiningReturnsBonus::where('user_id',$user_id)->where('up_down',1)->sum('num');
        $downline_bonus= MiningReturnsBonus::where('user_id',$user_id)->where('up_down',2)->sum('num');
        $sponsor_bonus=MiningReturnsBonus::where('user_id',$user_id)->where('type',1)->sum('num');
        $withdraw_amount=UsersWalletOut::where('user_id',$user_id)->sum('number');
        $user=Users::where('id',$user_id)->first();
        $user_name=$user->trx_address;
        $join_date=$user->join_date;
        $parent_user=Users::where('id',$user->parent_id)->first();
        $my_sponsor='';
        if(!empty($parent_user)){
            $my_sponsor=$parent_user->trx_address;
        }
        $total_referrals= Users::where('parent_id',$user_id)->count();
        // $total_direct=explode(',',$user->parents_path);
        // $total_referrals=count($total_direct);
        $res=[
            'withdraw_wallet'=>$withdraw_wallet,
            'total_package'=>$total_package,
            'total_bonus'=>$total_bonus,
            'upline_bonus'=>$upline_bonus,
            'downline_bonus'=>$downline_bonus,
            'sponsor_bonus'=>$sponsor_bonus,
            'withdraw_amount'=>$withdraw_amount,
            'user_name'=>$user_name,
            'join_date'=>$join_date,
            'my_sponsor'=>$my_sponsor,
            'total_referrals'=>$total_referrals

        ];
        return $this->success('操作成功',$res);
    }
    /**
     * 提现记录
     */
    public function with_draw_list(){
        $user_id=Users::getUserId();
        $wallet_out=UsersWalletOut::where('user_id',$user_id)->get()->toArray();
        $res=[];
        foreach ($wallet_out as $key => $value) {
            if($value['status']==1){
                $status='apply';
            }else if($value['status']==2){
                $status='approved';
            }else{
                $status='failed';
            }
            // $status=$value['status']==1?'apply':'approved';
            array_push($res,[
                'date'=>$value['create_time'],
                'amount_requested'=>$value['number'],
                'method'=>'TRX',
                'earning_amount'=>$value['number'],
                'amount_receivable'=>$value['receivable'],
                'amount_reinvest'=>$value['reinvest'],
                'Account'=>'Account:'.$value['address'],
                'TxId'=>$value['txid'],
                'status'=> $status.' At - '.$value['update_time']
            ]);
        }
        return $this->success('操作成功',$res);
    }
    /**
     * 购买记录
     */
    public function package_history(){
        $user_id=Users::getUserId();
        $user_mining=UserMining::where('user_id',$user_id)->get()->toArray();
        $res=[];
        foreach ($user_mining as $key => $value) {
            array_push($res,[
                'package_name'=>$value['mining_name'],
                'price'=>$value['num'],
                'amount_paid'=>$value['num'],
                'activation_date'=>$value['create_time']
            ]);
        }
        return $this->success('操作成功',$res);
    }


    public function team_info(){
        $allUsers = Users::get()->toArray();
        $user_id=Users::getUserId();
        $members=$this->GetTeamMember($allUsers, $user_id);//实名认证过的团队人数
        return $this->success('操作成功',$members);
    }

    //递归查询用户下级所有人数
    public function GetTeamMember($members, $mid)
    {
        $Teams = array();//最终结果
        $mids = array($mid);//第一次执行时候的用户id
        do {
            $othermids = array();
            $state = false;
            foreach ($mids as $valueone) {
                foreach ($members as $key => $valuetwo) {
                    if ($valuetwo['parent_id'] == $valueone) {
                        //实名认证通过的团队人数
                        $Teams[] = $valuetwo['id'];//找到我的下级立即添加到最终结果中
                        $othermids[] = $valuetwo['id'];//将我的下级id保存起来用来下轮循环他的下级
                        //                        array_splice($members,$key,1);//从所有会员中删除他
                        $state = true;
                    }
                }
            }
            $mids = $othermids;//foreach中找到的我的下级集合,用来下次循环
        } while ($state == true);
        //$Teams=Users::where("parents_path","like","%$mid%")->where("is_realname","=",2)->count();
        $Teams = Users::whereIn("id", $Teams)->get();
        return $Teams;
    }
    /**
     * 层级关系
     */
    public function sponsor_tree(Request $request){
        $address=$request->get('address','');
        $user=Users::where('trx_address',$address)->first();        
        $data=Users::where('parent_id',$user->id)->get();
        // $wallet=UsersWallet::where('currency',18)->where('address',$address)->get();
        foreach($data as &$item)
        {
            $item['charge_amount']=ChargeHash::where('recipient',$item->account_number)->where('currency_id',18)->where('audit_status',1)->sum('amount');
            $item['withdraw_amount']=UsersWalletOut::where('address',$item->account_number)->where('status',2)->sum('number');
            $item['account_number']=self::func_substr_replace($item['account_number']);//trx_address
            $item['trx_address']=($item['trx_address']);//trx_address
        }
       
        return $data;
        // $user_id=$user->id;
        // $users=Users::where('id',$user_id)->first();
        // $data = Users::orderBy('id','asc')->where('parents_path','like', '%' . $users->id . '%')->get()->toArray();
        // $list=$this->getSubTree($data,$users->id);
        // return $this->success($list);
    }

    public function direct_referrals(){
        $user_id=Users::getUserId();
        $users=Users::where('parent_id',$user_id)->get();
        return $this->success('操作成功',$users);
    }

    public function singleleg_tree(){
        $user_id=Users::getUserId();
        $user=Users::where('id',$user_id)->first();
        $parent_id= UserDAO::getParentsPathDesc($user,7);
        $parents=Users::whereIn('id',$parent_id)->get()->toArray();
        $new_parents=array();
        foreach ($parents as $key => $value) {            
            # code...
            array_push($new_parents,[
                'address'=>self::func_substr_replace($value['trx_address']),
                'time'=>$value['time']
            ]);
        }
        return $this->success('操作成功',$new_parents);
    }

     // 隐藏部分字符串
     public static function func_substr_replace($str, $replacement = '*', $start = 5, $length = 20)
     {
         $len = mb_strlen($str,'utf-8');
         if ($len > intval($start+$length)) {
             $str1 = mb_substr($str,0,$start,'utf-8');
             $str2 = mb_substr($str,intval($start+$length),NULL,'utf-8');
         } else {
             $str1 = mb_substr($str,0,1,'utf-8');
             $str2 = mb_substr($str,$len-1,1,'utf-8');    
             $length = $len - 2;
         }
         $new_str = $str1;
         for ($i = 0; $i < $length; $i++) { 
             $new_str .= $replacement;
         }
         $new_str .= $str2;
 
         return $new_str;
     }


    public function withdrawal(){

        $address=Input::get('address', '');
        $amount=Input::get('amount','');
        $user=Users::where('trx_address',$address)->first();
        if(empty($user)){
            return $this->error('地址错误');
        }
        $google_code=Input::get('code','');
        //谷歌验证码验证        
        $code_res= self::checkCaptcha($google_code);
        if(!$code_res){
            return $this->error('验证失败');
        }
        $user_id=$user->id;

        $currency=Currency::where('symbol','TRX')->first();
        $currency_id=$currency->id;

        $wallet = UsersWallet::where('user_id', $user_id)
        ->where('currency', $currency_id)
        ->lockForUpdate()
        ->first();
        try {
            DB::beginTransaction();
            throw_if(bc_comp_zero($amount) <= 0, new \Exception('输入的金额必须大于0'));
            $min_config=Setting::getValueByKey('withdraw_min_amount');
            $max_config=Setting::getValueByKey('withdraw_max_amount');
            throw_if(bc_comp($amount, $min_config) < 0, new \Exception('提币数量不能少于最小值'));
            throw_if(bc_comp($amount, $max_config) > 0 && bc_comp_zero($currency->max_number) > 0, new \Exception('提币数量不能高于最大值'));
            throw_if(bc_comp($amount, $wallet->change_balance) > 0, new \Exception('余额不足'));
        
            // $users_wallet=UsersWallet::where('user_id',$user_id)->first();
            // if(empty($users_wallet)){
            //     return $this->error('错误');
            // }
        
            $config= BondConfig::Intnace();
            //总投资额
            $total_deposit= UserMining::where('user_id',$user_id)->sum('num');
            $withdrawConfig=$config->getUserWithdrawalBiLi($total_deposit,$user->zhitui_real_number);
            $withdrawal_amount=bcmul($amount,$withdrawConfig['withdrawal_scale'],2);
            $ft_amount=bcsub($amount,$withdrawal_amount,2);
            if($withdrawal_amount<=0){
               throw new \Exception('投资额不能为0');
            }
        
      
            if (empty($currency_id)|| empty($address)) {
                throw new \Exception('参数错误');
            }
            $user = Users::findOrFail($user_id);            
   
            $rate = $currency->rate;
            $rate = bc_div($rate, 100);
           
            $walletOut = new UsersWalletOut();
            $walletOut->user_id = $user_id;
            $walletOut->currency = $currency_id;
            $walletOut->number = $withdrawal_amount;
            $walletOut->address = $address;
            $walletOut->rate = $rate;
            // $walletOut->real_number = bc_mul($withdrawal_amount, bc_sub(1, $rate));
            $walletOut->real_number = $withdrawal_amount;
            $walletOut->create_time = time();
            $walletOut->update_time = time();
            $walletOut->memo = '';
            $is_auto_audit_withdrawal=Setting::getValueByKey('is_auto_audit_withdrawal');
            if($is_auto_audit_withdrawal==1){
                $walletOut->status = 2; //1提交提币2已经提币3失败
            }else{
                $walletOut->status = 1;
            }
            
            $walletOut->receivable=$withdrawal_amount;
            $walletOut->reinvest=$ft_amount;
            $walletOut->save();

            $result = change_wallet_balance($wallet, 2, -$withdrawal_amount, AccountLog::WALLETOUT, '申请提币扣除余额');
            if ($result !== true) {
                throw new \Exception($result);
            }

            $result = change_wallet_balance($wallet, 2, $withdrawal_amount, AccountLog::WALLETOUT, '申请提币冻结余额', true);
            if ($result !== true) {
                throw new \Exception($result);
            }
            //复投
            UserMining::addMining($user_id,$ft_amount,18);
            
            event(new WithdrawSubmitEvent($walletOut));
            DB::commit();
            return $this->success('提币申请已成功，等待审核');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error(/*'File:' . $ex->getFile() . ',Line:'. $ex->getLine() . ',Message:'.*/$ex->getMessage());
        }
        
    }
    private static function checkCaptcha($code){
        $url='https://www.recaptcha.net/recaptcha/api/siteverify';//国内的js
        $data=[
            'secret' =>'6LeDgGccAAAAAI4YFYgbOjOLzkr0kIkK8lzJ5wZG' ,
            'response' => $code,
        ];
        $query = http_build_query($data);

        $options['http'] = array(
            'timeout'=>60,
            'method' => 'POST',
            'header' => 'Content-type:application/x-www-form-urlencoded',
            'content' => $query
        );
        $options['ssl'] = array(
            'verify_peer'=>false,
            'verify_peer_name'=>false
        );

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $res= json_decode($result);
        return $res->success;
    }

    public function getSubTree($data, $id = 0, $level = 0)
    {
        $list = array();
        foreach ($data as $key => $value) {
            $val=[];
            $val['parent_id'] = strval($value['parent_id']);
            if ($val['parent_id'] == $id) {
                $val['id'] = $value['id'];
                $val['name'] = $value['trx_address'];
                $val['level'] = $level;
                $val['children'] =self::getSubTree($data, $value['id'], $level + 1);
                $list[]     = $val;

            }
        }

        return $list;
    }
}