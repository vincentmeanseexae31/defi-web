<?php

namespace App\Console\Commands;

use App\Models\AccountLog;
use App\Models\ChargeHash;
use App\Models\Currency;
use App\Models\UsersWallet;
use Illuminate\Support\Facades\DB;

use Illuminate\Console\Command;

class test extends Command
{
    protected $signature = 'test';
    protected $description = 'test2222';


    public function handle()
    {

        $this->comment("开始执行");
        $chargeAudit = ChargeHash::findOrFail(7);
        $chargeAudit->reject_reason=time();
        $chargeAudit->save();
        $this->comment("全部结束");
    }

  
}