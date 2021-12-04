<?php


namespace App\Http\Controllers\Api;

use App\Models\{AccountLog,
    Currency,
    MiningMachine,
    News,
    UserMining,
    Token,
    Users,
    UsersWallet,
    Setting,
    MiningReturnsBonus};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\Array_;

class MiningMachineController extends Controller
{
    /***
     * 获取矿机列表
     */
    public function getMiningList(Request $request){
        $limit = $request->get('limit', 10);
        $query= MiningMachine::where('is_newuser',0)
            ->where('is_up',1)
            ->orderBy('sorts')
            ->paginate($limit);
        // $lists=$lists->items();
        // $data = array('data' => $lists->items(), 'page' => $lists->currentPage(), 'pages' => $lists->lastPage(), 'total' => $lists->total());
        $list=$query->items();
        $data=[];
        foreach($list as $item)
        {
            $data[]=[
                'id' => $item['id'],
                'catalog' => 'Rtx',
                'name' => $item['mining_name'],
                'title' => $item['mining_name'],
                'quantity' => 0,
                'totalQuantity' => 0,
                'buyCoin' => 'USDT',
                'buyAmount' => $item['num'],
                'produceCoin' => 'IPFS',
                'produceAmount' => 0,
                'produceScale' => 0,
                'lifeCycle' => 365,
                'effectiveCycle' => 10,
                'effectiveTime' => 1625042629000,
                'powerVol' => 10,
                'powerUnit' => 'T',
                'rewardCoin' => '',
                'rewardAmount' => 0.0,
                'pledgeAmount' => 0.0,
                'gasAmount' => 0.0,
                'fee' => 0.2,
                'status' => 1,
                'intro' => 'cxv 地方大师傅第三方多少范德萨发',
                'tags' => '["合约期365","超长收益"]',
                'ctime' => 1623660266000,
                'mtime' => 1629944809000,
            ];
        }
        $dataTable=[
            'code'=>200,
            'msg'=>0,
            'pages'=>$query->currentPage(),
            'total'=>$query->total(),
            'rows'=>$data
        ];
        $result=['catelogs'=>['RTX'],"dataTable"=>$dataTable];
        return $this->success(null,$result);
    }

    /***
     * 矿机详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function miningDetail(Request $request){
        $id=$request->get('id');
        if (empty($id)){
            return $this->error('id不能为空');
        }
        $detail=MiningMachine::find($id);
        return $this->success($detail);
    }

    /***
     * 新用户矿机列表
     */
    public function getNewUserMiningList(Request $request){
        $limit = $request->get('limit', 10);
        $lists= MiningMachine::where('is_newuser',1)
            ->where('is_up',1)
            ->orderBy('sorts')
            ->paginate($limit);
        $result = array('data' => $lists->items(), 'page' => $lists->currentPage(), 'pages' => $lists->lastPage(), 'total' => $lists->total());
        return $this->success($result);
    }

    /**
     *我的矿机列表
     */
    public function myMiningList(Request $request){
        $limit=$request->get('limit',10);
        $user_id = Users::getUserId();
        $lists= UserMining::where('user_id',$user_id)
            ->orderBy('id','desc')
            ->paginate($limit);
        $result = array('data' => $lists->items(), 'page' => $lists->currentPage(), 'pages' => $lists->lastPage(), 'total' => $lists->total());
        return $this->success($result);
    }

    /***
     * 我的
     */
    public function my(){
        //        $usdt_price=Currency::getCnyPrice(3);
        $usdt_price=Setting::getValueByKey('mining_usdt_price', '0');
        $user_id = Users::getUserId();
        $start_time=strtotime(date('Y-m-d',strtotime('-1 day')));
        $end_time=strtotime(date('Y-m-d'));

        $yesterday_profit=AccountLog::where("user_id", $user_id)//昨日收益
        ->whereIn('type',[AccountLog::MINING_BONUS,AccountLog::MINING_PARENT_BONUS])
            ->where('created_time','>=',$start_time)
            ->where('created_time','<=',$end_time)->sum('value');

        $total_data=AccountLog::where("user_id", $user_id)
            ->whereIn('type',[AccountLog::MINING_BONUS,AccountLog::MINING_PARENT_BONUS])
            ->orderBy('id', 'DESC')->select();
        $total_profit= $total_data->sum('value');//总收益

        return $this->success(array(
            'yesterday_profit'=>$yesterday_profit,
            'yesterday_profit_cny'=>bcmul($yesterday_profit,$usdt_price,8),
            'total_profit'=>$total_profit,
            'total_profit_cny'=>bcmul($total_profit,$usdt_price,8)
        ));

    }

    /***
     * 购买矿机 购买的动态分红
     */
    public function buy_old(Request $request){
        DB::beginTransaction();
        try{
            $minig_id=$request->get('id');
            $buy_num=$request->get('buy_num');
            if(empty($buy_num)||$buy_num<1){
                return $this->error('买卖数量不能小于1');
            }
            $user_id = Users::getUserId();
            $ming_machine=MiningMachine::find($minig_id);
            if (empty($ming_machine)){
                return $this->error('矿机不存在');
            }
            $user_wallet = UsersWallet::where("user_id", $user_id)
                ->where("currency", 3)
                ->lockForUpdate()
                ->first();
            $usdt_balance=$user_wallet->change_balance;
            if(bccomp($usdt_balance,$ming_machine->num*$buy_num)<0){
                return $this->error('余额不足');
            }
            $mining_machine=MiningMachine::find($minig_id);
            $out_num=bcadd($mining_machine->out_num,$buy_num,0);
            if($out_num>$mining_machine->stock_num){
                return $this->error('矿机库存不足');
            }


            $result = change_wallet_balance($user_wallet, 2, -$ming_machine->num*$buy_num, AccountLog::MINING_BALANCE_OUT, '购买矿机');
            if ($result !== true) {
                throw new \Exception($result);
            }
            for ($x=0;$x<$buy_num;$x++){
                $num=$ming_machine->num;//usdt数量
                $rate=$ming_machine->rate;//年化收益率
                $days=$ming_machine->days;//天数
                $day_bonus=bcmul($rate/365,$num,8);
                $bonus_num=bcmul($day_bonus,$days,8);
                $user_mining=new UserMining();
                $user_mining->mining_id=$minig_id;
                $user_mining->user_id=$user_id;
                $user_mining->mining_name=$ming_machine->mining_name;
                $user_mining->num=$num;
                $user_mining->rate=$rate;
                $user_mining->days=$days;
                $user_mining->day_bonus=$day_bonus;
                $user_mining->currency_id=3;
                $start_date=date('Ymd',time());
//                $days=bcadd($ming_machine->days,1,0);
                $days=$ming_machine->days;
                $end_date=date('Ymd',strtotime('+'.$days.' day'));
                $user_mining->start_date=$start_date;
                $user_mining->end_date=$end_date;
                $user_mining->bonus_num=$bonus_num;
                $user_mining->is_sum=0;
                $user_mining->is_return=0;
                $user_mining->status=1;
                $user_mining->create_time=time();
                $user_mining->is_newuser=$ming_machine->is_newuser;
                $user_mining->save();



            }

            $mining_machine->out_num=$out_num;
            $mining_machine->save();
            DB::commit();
            return $this->success('购买成功');
        }catch (\Exception $exception) {
            DB::rollBack();
            return $this->error($exception->getMessage());
        }
    }

    public function buy(Request $request){
        DB::beginTransaction();
        try{
            $minig_id=$request->get('id');
            $buy_num=$request->get('buy_num');
            if(empty($buy_num)||$buy_num<1){
                return $this->error('买卖数量不能小于1');
            }
            $user_id = Users::getUserId();
            $ming_machine=MiningMachine::find($minig_id);
            if (empty($ming_machine)){
                return $this->error('套餐不存在');
            }
            $user_wallet = UsersWallet::where("user_id", $user_id)
                ->where("currency", 3)
                ->lockForUpdate()
                ->first();
            $usdt_balance=$user_wallet->change_balance;
            if(bccomp($usdt_balance,$ming_machine->num*$buy_num)<0){
                return $this->error('余额不足');
            }
            $mining_machine=MiningMachine::find($minig_id);
            $out_num=bcadd($mining_machine->out_num,$buy_num,0);
            if($out_num>$mining_machine->stock_num){
                return $this->error('套餐库存不足');
            }


            $result = change_wallet_balance($user_wallet, 2, -$ming_machine->num*$buy_num, AccountLog::MINING_BALANCE_OUT, '购买套餐');
            if ($result !== true) {
                throw new \Exception($result);
            }
            for ($x=0;$x<$buy_num;$x++){
                $num=$ming_machine->num;//usdt数量
                // $rate=$ming_machine->rate;//年化收益率
                // $days=$ming_machine->days;//天数
                // $day_bonus=bcmul($rate/365,$num,8);
                // $bonus_num=bcmul($day_bonus,$days,8);
                $user_mining=new UserMining();
                $user_mining->mining_id=$minig_id;
                $user_mining->user_id=$user_id;
                $user_mining->mining_name=$ming_machine->mining_name;
                $user_mining->num=$num;
                // $user_mining->rate=$rate;
                // $user_mining->days=$days;
                // $user_mining->day_bonus=$day_bonus;
                $user_mining->currency_id=3;
                $start_date=date('Ymd',time());
//                $days=bcadd($ming_machine->days,1,0);
                $days=$ming_machine->days;
                $end_date=date('Ymd',strtotime('+'.$days.' day'));
                $user_mining->start_date=$start_date;
                $user_mining->end_date=$end_date;
                // $user_mining->bonus_num=$bonus_num;
                $user_mining->is_sum=0;
                $user_mining->is_return=0;
                $user_mining->status=1;
                $user_mining->create_time=time();
                $user_mining->is_newuser=$ming_machine->is_newuser;
                $user_mining->save();



            }

            $mining_machine->out_num=$out_num;
            $mining_machine->save();
            DB::commit();
            return $this->success('购买成功');
        }catch (\Exception $exception) {
            DB::rollBack();
            return $this->error($exception->getMessage());
        }
    }



    /***
     * 总分红
     */
    public function bonusTotal(Request $request){

    }

    /***
     * 收益列表
     */
    public function bonusList(Request $request){
        $limit=$request->get('limit','15');
        $page=$request->get('page','1');
        $user_id = Users::getUserId();
        $mingReturnBonus=MiningReturnsBonus::where('user_id',$user_id)
            ->orderBy('id', 'desc')
            ->paginate($limit, ['*'], 'page', $page);
        if (empty($mingReturnBonus)) {
            return $this->error('您还没有分红记录');
        }
        return $this->success(array(
            "list" => $mingReturnBonus->items(), 'count' => $mingReturnBonus->total(),
            "page" => $page, "limit" => $limit
        ));

    }
    /***
     * 昨日收益
     */
    public function yesterdayProfit(Request $request){
        //        $usdt_price=Currency::getCnyPrice(3);
        $usdt_price=Setting::getValueByKey('mining_usdt_price', '0');
        $limit=$request->get('limit','15');
        $page=$request->get('page','1');
        $user_id = Users::getUserId();//33邀请返佣 309矿机分红
//        $start_time=strtotime(date('Y-m-d',strtotime('-1 day')));
//        $end_time=strtotime(date('Y-m-d'));
        $start_time=strtotime(date('Y-m-d'));
        $end_time=strtotime(date('Y-m-d',strtotime('+1 day')));
        $data = AccountLog::where("user_id", $user_id)
            ->whereIn('type',[AccountLog::MINING_BONUS,AccountLog::MINING_PARENT_BONUS])
            ->where('created_time','>=',$start_time)
            ->where('created_time','<=',$end_time)
            ->orderBy('id', 'DESC')->paginate($limit);
        $yesterday_total=AccountLog::where("user_id", $user_id)
            ->whereIn('type',[AccountLog::MINING_BONUS,AccountLog::MINING_PARENT_BONUS])
            ->where('created_time','>=',$start_time)
            ->where('created_time','<=',$end_time)->select();
        $total_profit= $yesterday_total->sum('value');//收益
        $total_invite=$yesterday_total->where('type',AccountLog::MINING_PARENT_BONUS)->sum('value');//邀请收益

        return $this->success(array(
            'total_invite'=>$total_invite,
            'total_invite_cny'=>bcmul($total_invite,$usdt_price,8),
            'total_profit'=>$total_profit,
            'total_profit_cny'=>bcmul($total_profit,$usdt_price,8),
            "data" => $data->items(),
            "limit" => $limit,
            "page" => $page,
        ));
    }

    /***
     * 我的邀请
     */
    public function myInvite(Request $request){
//                $usdt_price=Currency::getCnyPrice(3);
        $usdt_price=Setting::getValueByKey('mining_usdt_price', '0');
        $limit=$request->get('limit','15');
        $page=$request->get('page','1');
        $user_id = Users::getUserId();
        $total_invite=Users::where('parent_id',$user_id)->count();
        $data=Users::where('parent_id',$user_id)
            ->orderBy('id','DESC')
            ->paginate($limit);
        $data=$data->items();
        $list=array();
        foreach ($data as $key =>$value){
            $val=[];
            $val['user_id']=$value['id'];
            $val['account_number']=$value['account_number'];
            $val['mining_total']=UserMining::where('user_id',$value['id'])->where('status',1)->sum('num');
            $val['register_time']=$value['time'];
            $list[]     = $val;
        }
        return $this->success(array(
            'total_invite'=>$total_invite,
            'total_invite_cny'=>bc($total_invite,$usdt_price,0),
            "data" => $list,
            "limit" => $limit,
            "page" => $page,
        ));

    }

    /***
     * 累计收益
     * @param Request $request
     */
    public function totalProfit(Request $request){
        $limit=$request->get('limit','15');
        $page=$request->get('page','1');
        $user_id = Users::getUserId();
        $data = AccountLog::where("user_id", $user_id)
            ->whereIn('type',[AccountLog::FINANCIAL_BONUS,AccountLog::FINANCIAL_PARENT_BONUS])
            ->orderBy('id', 'DESC')->paginate($limit);
        $total_data=AccountLog::where("user_id", $user_id)
            ->whereIn('type',[AccountLog::FINANCIAL_BONUS,AccountLog::FINANCIAL_PARENT_BONUS])
            ->orderBy('id', 'DESC')->select();
        $total_profit= $total_data->sum('value');//总收益
//        $usdt_price=Currency::getCnyPrice(3);
        $usdt_price=Setting::getValueByKey('mining_usdt_price', '0');
        $total_invite=$total_data->where('type',AccountLog::FINANCIAL_PARENT_BONUS)->sum('value');//推广收益

        $start_time=strtotime(date('Y-m-d',strtotime('-1 day')));
        $end_time=strtotime(date('Y-m-d'));
        $yesterday_profit=AccountLog::where("user_id", $user_id)//昨日收益
            ->whereIn('type',[AccountLog::FINANCIAL_BONUS,AccountLog::FINANCIAL_PARENT_BONUS])
            ->where('created_time','>=',$start_time)
            ->where('created_time','<=',$end_time)->sum('value');

        $list=array();
        foreach ($data as $value){
            $arr=[];
            $arr['name']=$value['type']==313?'理财收益':'邀请分红';
            $arr['created_time']=$value['created_time'];
            $arr['value']=$value['value'];
            $list[]=$arr;
        }
        return $this->success(array(
            'total_profit'=>$total_profit,
            'total_profit_cny'=>bcmul($total_profit,$usdt_price,8),
            'total_invite'=>$total_invite,
            'total_invite_cny'=>bcmul($total_invite,$usdt_price,8),
            'yesterday_profit'=>$yesterday_profit,
            'yesterday_profit_cny'=>bcmul($yesterday_profit,$usdt_price,8),
            "data" => $list,
            "limit" => $limit,
            "page" => $page,
        ));

    }

}