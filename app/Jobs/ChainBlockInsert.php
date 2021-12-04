<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\BlockChain\Coin\BaseCoin;
use App\BlockChain\Coin\CoinManager;
use App\Models\ChargeHash;
use App\Models\Currency;
use App\Models\UsersWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChainBlockInsert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tx;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($tx)
    {
        $this->tx = $tx;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //$v = json_decode($this->tx, true);
        $v=$this->tx;

        try {
            $support_coin_list = ['eth', 'erc20', 'usdt', 'btc', 'xrp', 'eos'];
            if (!in_array(strtolower($v['coin']), $support_coin_list)) {
                return false;
            }
            DB::transaction(function () use ($v) {

                if (isset($v['token']) && $v['token']) {
                    $currency = Currency::where('contract_address', $v['token'])->where('type', strtolower($v['coin']))->first();
                } else {
                    $currency = Currency::where('type', strtolower($v['coin']))->first();
                }

                if ($currency) {

                    $has = UsersWallet::where('address', $v['to'])->where('currency', $currency->id)->first();
                    if ($has) {
                        $index=$v['index']?$v['index']:0;
                        $has_hash = ChargeHash::where('txid', $v['txid'])->where('index',$index)->first();
                        if (empty($has_hash)) {


                            $v['code'] = $currency->name;
                            $v['currency_id'] = $currency->id;
                            $v['index']=$index;
                            /**
                             * @var BaseCoin $coin_instance
                             */
                            $coin_instance = CoinManager::resolve(strtoupper($currency->type), $currency->decimal_scale, $currency->contract_address);
                            $coin_instance->insertHash($v);
                        }
                    }
                }
            });
        } catch (\Exception $e) {
            echo 'File:' . $e->getFile() . PHP_EOL;
            echo 'Line:' . $e->getLine() . PHP_EOL;
            echo 'Message:' . $e->getMessage() . PHP_EOL;
        }
    }
}