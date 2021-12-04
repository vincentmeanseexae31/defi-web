<?php

namespace App\Console\Commands;

use App\Models\AccountLog;
use App\Models\ChargeHash;
use App\Models\Currency;
use App\Models\UsersWallet;
use Illuminate\Support\Facades\DB;
use App\Utils\RPC;
use Exception;
use Illuminate\Console\Command;

class AutoCheckCharge extends Command
{
    protected $signature = 'auto_check_charge';
    protected $description = '更新用户的充币余额';


    public function handle()
    {
 

        $result=[];
        $this->comment("开始执行");
        $n=ChargeHash::where('status',0)->count();
        if($n <= 0){
            $this->comment("暂时没有要处理的充币记录");
            return false;
        }
        foreach (ChargeHash::where('status',0)->where('source','taskjob')->cursor() as $c) {
 
           $r= $this->updateWallet($c);
           if($r!=null)
           {
                $result[]=$r;
           }

        }
        $this->comment("全部结束");


        // foreach($result as $item)
        // {     
        //     try{
        //         $time=date( 'Y年m月d日 H时i分s秒',$item['time']);
        //         $parms='交易哈希:'.$item['txid'].'%0A';
        //         $parms.='充值账户:'.$item['recipient'].'%0A';
        //         $parms.='充值时间:'.$time.'%0A';
        //         $parms.='充值金额:'.$item['amount'].'%0A';
     
        //         // $parms=( '账户:'.$item['recipient'].'%0A 在'.$time.'充值了'.$item['amount'].'TRX,TxID:'.$item['txid'] );
        //         $url=config('app.push_tegrame_url');
        //         $address_url = $url.$parms;
        //         $res = RPC::apihttp($address_url);
        //         $this->comment($res); ;
        //     }catch(Exception $ex)
        //     {
        //         $this->comment($ex->getMessage()); ;
        //         throw $ex;
        //     }

        // }
    }

    public function updateWallet($c)
    {
        try {
            ChargeHash::rechargeAdoptAudit($c->id,10);
            return $c;
        } catch (\Exception $e) {
            echo 'File:' . $e->getFile() . PHP_EOL;
            echo 'Line:' . $e->getLine() . PHP_EOL;
            echo 'Message:' . $e->getMessage() . PHP_EOL;
        }
        return null;
    }
}