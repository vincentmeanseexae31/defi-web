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
    UsersWallet,
    BondConfig};

class MiningBuyBonus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'MiningBuyBonus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '套餐购买分红';

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $now = Carbon::now();
        $date = date('Ymd', time() -  24 * 60 * 60);
        $end_date=date('Ymd',time());
        $time = time();
        $this->info('统计分红开始-' . $now->toDateTimeString());
        $user_mining_list=UserMining::where('is_sum',0)->get();
        DB::beginTransaction();
        try {
            foreach ($user_mining_list as $key => $user_mining) {
                # code...
                $num=$user_mining->num;
                $total_num=$user_mining->total_num;
                $user_id=$user_mining->user_id;
                $is_futou=$user_mining->is_futou;
                $this->sum_mining_zanzhu($user_id,$num,$total_num,$user_mining->id);
                $this->sum_mining_shequ($user_id,$num,$is_futou,$user_mining->id);
                $user_mining->is_sum=1;
                $user_mining->sum_time=time();
                $user_mining->save();
            }           
            DB::commit();
            $this->return_mining();
        } catch (\Exception $exception) {
            DB::rollBack();
        }
        $this->info('统计分红结束-' . $now->toDateTimeString());
    }
      /**
     * 赞助分红
     */
    public function sum_mining_zanzhu($user_id,$num,$total_num,$user_mining_id){
        $user= Users::getById($user_id);
        if($user->parent_id!=0){
            $parent=Users::getById($user->parent_id);
            if($parent->parents_path!=''){
                $parent_path=$parent->parents_path.','.$parent->id;
            }
            else{
                $parent_path=$parent->id;
            }
            $level=1;
            //分红给上级
            $parent_id_arr=explode(',',$parent_path);
            rsort($parent_id_arr);
            $log='user_id:'.$user_id.' mining_id:'.$user_mining_id.' detail is  ';
            try {
                foreach ($parent_id_arr as $key => $parent_id) {
                    if($level<=7){
                        $parent_user=Users::getById($parent_id);
                        $zhitui_real_number=$parent_user->zhitui_real_number;//直推数
                        //矿机总投资额
                        $all_num=UserMining::where('user_id',$parent_id)->sum('num');
                        $config= BondConfig::Intnace();
                        //分润比例
                        $rate=$config->getUserProfitByZanZhu($level,$all_num,$zhitui_real_number);
                        if($rate>0){
                            $rate_num=bcmul($num,$rate);
                            $date=date('Ymd',time());
                            $returns_bonus=new MiningReturnsBonus;
                            $returns_bonus->date=$date;
                            $returns_bonus->user_id=$parent_user->id;
                            $returns_bonus->user_mining_id=$user_mining_id;
                            $returns_bonus->num=$rate_num;
                            $returns_bonus->price=1;
                            $returns_bonus->total=$num;
                            $returns_bonus->rate=$rate;
                            $returns_bonus->addtime=time();
                            $returns_bonus->is_return=0;
                            $returns_bonus->return_time=time();
                            $returns_bonus->type=1;
                            $returns_bonus->from_user_id=$user_id;
                            $returns_bonus->up_down=0;
                            $returns_bonus->save();        
                        }
                    }
                    $log=$log.'level'.$level.' user_id: '.$parent_id.' num: '.$rate_num;
                    $level++;
                }
                $this->info($log);
            } catch (\Throwable $th) {
            }
            
        }
    }

    /**
     * 社区奖励//首次购买
     */
    public function sum_mining_shequ($user_id, $num,$is_futou,$user_mining_id)
    {
        try {
            
            if ($is_futou== 0) {
                //首次购买
                //按照时间线来排的话，自己进入的时候买了500trx 则可以马上获得上面20层的分红，然后自己的入账按比例分给自己上面30层。宏观理解即：上社区20层，下社区30层
                //按id排序
                $parent_users_20 = Users::where('id', '>=', $user_id - 20)->where('id', '<', $user_id)->get();
                $parent_users_30 = Users::where('id', '>=', $user_id - 30)->where('id', '<', $user_id)->get();
                //给上面分30层
                foreach ($parent_users_30 as $key => $parent_user) {
                    $rate = 0.01;
                    $rate_num = bcmul($num, $rate,4);
                    MiningReturnsBonus::AddBonus($parent_user->id, $rate_num, $num, $rate, $user_id, 0,2,2,$user_mining_id);//给上面分红 type实际是下社区2
                }
                //上面20层分给自己
                foreach ($parent_users_20 as $key => $parent_user) {
                    # code...
                    $user_mining_num = UserMining::where(['user_id' => $parent_user->id])->sum('num');
                    $rate = 0.01;
                    $rate_num = bcmul($user_mining_num, $rate,4);
                    if($rate_num>0){
                        MiningReturnsBonus::AddBonus($user_id, $rate_num, $user_mining_num, $rate, $parent_user->id, 0,2,1,$user_mining_id);
                    }                    
                }
            } else {
                //复投
                //往上分20层，往下分30层
                $parent_user_20 = Users::where('id', '>=', $user_id - 20)->where('id', '<', $user_id)->get();
                $son_user_30 = Users::where('id', '>', $user_id)->where('id', '<=', $user_id + 30)->get();
                $rate = 0.01;
                $rate_num = bcmul($num, $rate,4);
                //给上面分20层
                foreach ($parent_user_20 as $parent_user) {
                    MiningReturnsBonus::AddBonus($parent_user->id, $rate_num, $num, $rate, $user_id, 1,2,2,$user_mining_id);
                }
                //给下面分30层
                foreach ($son_user_30 as $son) {
                    MiningReturnsBonus::AddBonus($son->id, $rate_num, $num, $rate, $user_id, 1,2,1,$user_mining_id);
                }
            }
        } catch (\Throwable $th) {
        }
    }

    public function  return_mining(){
        $now = Carbon::now();
        //查询退还列表
        $returns= MiningReturnsBonus::where('is_return',0)->get();
        $this->info('退还开始-' . $now->toDateTimeString());
        DB::beginTransaction();
        try{
            foreach ($returns as $return){
                $type=$return['type'];
                $user_wallet = UsersWallet::where('user_id', $return['user_id'])
                    ->lockForUpdate()
                    ->where('currency', 18)
                    ->first();//Trx:18
                if (!$user_wallet) {
                    throw new \Exception('钱包不存在');
                }
                $from_account_log_type=AccountLog::MINING_BONUS;
                $memo='套餐分红';

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
            }
            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
        }
        $this->info('退还结束-' . $now->toDateTimeString());
    }
}
