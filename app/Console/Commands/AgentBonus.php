<?php


namespace App\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\{AccountLog,
    AgentBonusLog,
    AgentBonusTask,
    Currency,
    LegalDeal,
    MiningMachine,
    MiningReturnsBonus,
    Setting,
    UserMining,
    Users,
    UsersWallet};

class AgentBonus extends Command
{
    /**
     * 矿机分红
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'AgentBonus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '代理分红';

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
        $this->info('开始执行代理分红脚本-' . $now->toDateTimeString());
        // $timeout = Setting::getValueByKey('legal_timeout', 0);
        // if ($timeout <= 0) {
        //     return;
        // }
        // $before = $now->subMinutes(15)->timestamp;
//        DB::beginTransaction();
//        try {
//
//            DB::commit();
//            $this->info('执行成功');
//        } catch (\Exception $exception) {
//            DB::rollback();
//            $this->error($exception->getMessage());
//        }
        $this->sum_agent_log();
    }
    public function  sum_agent_log(){

        $now = Carbon::now();
        $date = date('Ymd', time() -  24 * 60 * 60);
        $end_date=date('Ymd',time());
        $time = time();
        $this->info('统计代理分红开始-' . $now->toDateTimeString());

        //统计分红比率
        DB::beginTransaction();
        try{
            $returns=AgentBonusTask::where('status',0)->get();
            foreach ($returns as $return) {
                # code...
                $user_id=$return['user_id'];
                $user=Users::where('id',$user_id)->first();
                if(!$user){
                    $return->finish_time=time();
                    $return->status=1;
                    $return->save();
                    continue;
                }
                if($return['collect_uid']==0){
                    $parent_user_id=$user['parent_id'];
                    if(empty($parent_user_id)){
                        $return->finish_time=time();
                        $return->status=1;
                        $return->save();
                        continue;
                    }
                }else{
                    $parent_user_id=$return['collect_uid'];//如果有收割人则分给收割人
                }
                $parent_user=Users::where('id',$parent_user_id)->first();
                if($parent_user['is_agent']==1&&$parent_user['agent_rate']>0){
                    if($return['amount']>0){
                        $bonus_num=bcmul($parent_user['agent_rate'],$return['amount'],4);
                        if($bonus_num>0.0001){
                            $user_wallet=UsersWallet::where('user_id',$parent_user_id)->first();
                            $result=change_wallet_balance($user_wallet,1,$bonus_num,AccountLog::FINANCIAL_AGENT_BONUS,'代理分红');
                            AgentBonusLog::create([
                                'agent_user_id'=>$parent_user_id,
                                'from_user_id'=>$user_id,
                                'from_address'=>$return['address'],
                                'num'=>$return['amount'],
                                'bonus_num'=>$bonus_num,
                                'rate'=>$parent_user['agent_rate'],
                                'addtime'=>time()
                            ]);
                        }
                    }
                }
                
                $return->status=1;
                $return->finish_time=time();
                $return->save();
            }
            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
        }
        $this->info('统计代理分红结束-' . $now->toDateTimeString());
    }    
}