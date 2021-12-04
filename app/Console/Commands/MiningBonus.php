<?php


namespace App\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\{AccountLog,
    Currency,
    LegalDeal,
    MiningMachine,
    MiningReturnsBonus,
    Setting,
    UserMining,
    Users,
    UsersWallet};

class MiningBonus extends Command
{
    /**
     * 矿机分红
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mining_bonus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '矿机分红';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute t`he console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //15分钟
        $now = Carbon::now();
        $this->info('开始执行矿机分红脚本-' . $now->toDateTimeString());
        $timeout = Setting::getValueByKey('legal_timeout', 0);
        if ($timeout <= 0) {
            return;
        }
        $before = $now->subMinutes(15)->timestamp;
//        DB::beginTransaction();
//        try {
//
//            DB::commit();
//            $this->info('执行成功');
//        } catch (\Exception $exception) {
//            DB::rollback();
//            $this->error($exception->getMessage());
//        }
        $this->sum_mining();
    }
    public function  sum_mining(){

        $now = Carbon::now();
        $date = date('Ymd', time() -  24 * 60 * 60);
        $end_date=date('Ymd',time());
        $time = time();
        $this->info('统计分红开始-' . $now->toDateTimeString());

//        $user_machine=UserMining::where('start_date','<=',$date)
//            ->where('end_date','>=',$date)
//            ->where('is_sum',0)->select()->toArray();

        //统计分红比率
        DB::beginTransaction();
        try{
            //统计分红率
//            DB::update("UPDATE user_mining set bonus_num=rate*num WHERE `end_date`< $date AND `is_sum`=0");
            //写入分红
            DB::insert("INSERT INTO mining_returns_bonus (`date`,`user_id`,`user_mining_id`,`num`,`price`,`total`,`rate`,`addtime`,`is_return`,`return_time`,`type`)".
            "SELECT {$date},`user_id`,`id`,`num`*`rate`/365,1,`num`,`rate`/365,{$time},0,0,1 FROM user_mining ".
            "WHERE `start_date` <= {$date}  AND `is_sum` = 0");
            //写入本金
            DB::insert("INSERT INTO mining_returns_bonus (`date`,`user_id`,`user_mining_id`,`num`,`price`,`total`,`rate`,`addtime`,`is_return`,`return_time`,`type`)".
                "SELECT {$date},`user_id`,`id`,`num`,1,`num`,1,{$time},0,0,2 FROM user_mining ".
                "WHERE `end_date` <= {$end_date} AND `is_sum` = 0");
            //修改统计状态
            DB::update("UPDATE user_mining SET `is_sum` = 1,`sum_time` = {$time},status=0 WHERE `end_date` <= {$end_date} AND `is_sum` = 0");
            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
        }
        $this->info('统计分红结束-' . $now->toDateTimeString());
        $this->return_mining();
    }
    public function  return_mining(){
        $now = Carbon::now();
        $parent_bonus_rate = Setting::getValueByKey('mining_invite_rate', '0');//上级邀请奖励比率
        //查询退还列表
        $returns= MiningReturnsBonus::where('is_return',0)->get();
        $this->info('退还开始-' . $now->toDateTimeString());
        DB::beginTransaction();
        try{
            foreach ($returns as $return){
                $type=$return['type'];
                $user_wallet = UsersWallet::where('user_id', $return['user_id'])
                    ->lockForUpdate()
                    ->where('currency', 3)
                    ->first();//USDT:3
                if (!$user_wallet) {
                    throw new \Exception('钱包不存在');
                }
                if($type=="分红"){
                    $from_account_log_type=AccountLog::MINING_BONUS;
                    $memo='矿机分红';
                }else{
                    $from_account_log_type=AccountLog::MINING_CAPITAL;
                    $memo='矿机本金退还';
                }

//                $memo = '矿机分红 矿机ID:'.$return['user_mining_id'];

                $number=$return['num'];
                $result = change_wallet_balance($user_wallet, 2, $number, $from_account_log_type, $memo);
                if ($result !== true) {
                    throw new \Exception($result);
                }
                $this->info('退还 用户：'.$return['user_id'].'矿机ID：'.$return['user_mining_id'] .'时间：'. $now->toDateTimeString());
                $userMining=  UserMining::find($return['user_mining_id']);
                $userMining->is_return=1;
                $userMining->return_time=time();
                $userMining->save();

                $miningReturnsBonus=MiningReturnsBonus::find($return['id']);
                $miningReturnsBonus->is_return=1;
                $miningReturnsBonus->return_time=time();
                $miningReturnsBonus->save();

                //分红给上级用户
                if($type=="分红"){
                   $parent_bonus_num= bcmul($parent_bonus_rate,$number,8);
                   $user= Users::getById($userMining->user_id);

                   if($user->parent_id!=0){
                       $parent=Users::getById($user->parent_id);

                       $user_wallet = UsersWallet::where('user_id', $parent->id)
                           ->lockForUpdate()
                           ->where('currency', 3)
                           ->first();//USDT:3
//                        $memo='矿机下级分红 用户id:'.$userMining->user_id.' 矿机id:'.$userMining->mining_id;
                       //上级满足条件
                       $user_mining_num = Setting::getValueByKey('user_mining_num', '0');
                       $user_wallet_num=Setting::getValueByKey('user_wallet_num','0');
                       $user_ming_num=UserMining::where('user_id',$parent->id)->where('num','>=',$user_mining_num)->where('status',1)->count('num');
                       if($user_ming_num>0||$user_wallet->change_balance>$user_wallet_num){
                           $memo='邀请分红';
                           $result=change_wallet_balance($user_wallet,2,$parent_bonus_num,AccountLog::MINING_PARENT_BONUS,$memo);
                           if ($result !== true) {
                               throw new \Exception($result);
                           }
                       }
                   }
                }

            }
            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
        }
        $this->info('退还结束-' . $now->toDateTimeString());
    }
}