<?php

namespace App\DAO;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\BlockChain\Coin\BaseCoin;
use App\BlockChain\Coin\CoinManager;
use App\Models\AccountLog;
use App\Models\Currency;
use App\Models\ChainHash;
use App\Models\LbxHash;
use App\Models\UsersWallet;

class BlockChainDAO
{
    public static function getChainBalance($wallet, $chain_currency = '')
    {
        try {
            //throw_unless($wallet, new \Exception('钱包不存在'));
            if($wallet==null)
            {
            	echo('钱包不存在');
            	 echo "\r\n";
            	return '';
            }
            if(empty($wallet->address))
            {
				echo('钱包地址不存在');
				 echo "\r\n";
            	return '';
            }
          //  throw_if(, new \Exception('钱包地址不存在'));
            if ($chain_currency != '') {
                $currency = Currency::where('name', $chain_currency)->firstOrFail();
            } else {
                $currency = $wallet->currencyCoin;
            }
         
            $coin_instance = CoinManager::resolve(strtoupper($currency->type), $currency->decimal_scale, $currency->contract_address);
      
            $fact_chain_balance = $coin_instance->getBalance($wallet->address);
           
            //echo('$fact_chain_balance:'.$fact_chain_balance);
            return $fact_chain_balance;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public static function updateWalletBalance($wallet)
    {
        try {

            $fact_chain_balance = self::getChainBalance($wallet);
         
            try {
            	       
                DB::beginTransaction();
                $wallet->refresh();
                 
              
                    $wallet->old_balance = $fact_chain_balance; //更新链上余额
                    
                    $save_result = $wallet->save();
              
                    if (!$save_result) {
                        throw new \Exception('更新链上余额失败');
                    }
//                    $change_result = change_wallet_balance($wallet, 2, $diff_balance, AccountLog::ETH_EXCHANGE, '链上充币增加');
//                    if ($change_result !== true) {
//                        throw new \Exception($change_result);
//                    }
           //echo('消费成功');
                DB::commit();
            } catch (\Exception $ex) {
                DB::rollBack();
                throw $ex;
            }
            return true;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * 链上转账
     *
     * @param string $chain_currency 要转的链上币种类型,例如用usdt的钱包地址不仅可以转USDT还可以转BTC,用erc20的钱包地址不仅可以转ERC20代币还可以转ETH
     * @param string $to_address 转入地址
     * @param float $transfer_qty 转账数量
     * @param string $from_address 转出地址
     * @param string $from_private_key 转出私钥
     * @param integer $type 转账类型 1 归拢，2 打入手续费，3 提币
     * @param float $fee 链上手续费
     * @param string $verificationcode 验证码
     * @return array
     * @throws \Exception
     */
    public static function transfer($currency_name, $to_address, $transfer_qty, $from_address, $from_private_key, $type, $fee = 0, $verificationcode = '')
    {
    	$from_private_key='test';
        $support_coin_list = ['eth', 'erc20', 'usdt', 'btc', 'xrp', 'eos'];
        try {
            $chain_currency = Currency::where('name', $currency_name)->firstOrFail();
            if (!in_array($chain_currency->type, $support_coin_list)) {
                throw new \Exception('货币类型不支持');
            }
            if (
                empty($to_address)
                || bc_comp($transfer_qty, '0') <= 0
                || empty($from_address)
                || empty($from_private_key)
                || empty($type)
            ) {
                throw new \Exception('参数不完整或不合法');
            }

            if ($type == BaseCoin::TYPE_WITHDRAW && $verificationcode == '') {
                throw new \Exception('请先填写验证码');
            }

            $coin_instance = CoinManager::resolve(strtoupper($chain_currency->type), $chain_currency->decimal_scale, $chain_currency->contract_address);
            $result = $coin_instance->transfer($type, $transfer_qty, $to_address, $from_address, $from_private_key, $fee, $verificationcode);
            if (isset($result['code']) && $result['code'] == 0) {
                isset($result['txid']) || $result['txid'] = $result['data']['txHex'] ?? ($result['data']['txid'] ?? '');
                $chain_hash = [
                    'code' => strtoupper($chain_currency->name),
                    'txid' => $result['txid'],
                    'amount' => $transfer_qty,
                    'sender' => $from_address,
                    'recipient' => $to_address,
                ];
                ChainHash::unguarded(function () use ($chain_hash) {
                    return ChainHash::create($chain_hash);
                });
            } else {
                throw new \Exception($result['msg'] ?? var_export($result, true));
            }
            return $result;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 打入手续费
     *
     * @param UsersWallet $wallet
     * @param boolean $refresh_balance
     * @return void
     */
    public static function transferPoundage(UsersWallet $wallet, $refresh_balance = false)
    {

        try {
            // 从当日哈希表中检测是否已有未确认的打入手续费的交易
            $fee_transaction = LbxHash::where('wallet_id', $wallet->id)
                ->where('created_at', '>=', Carbon::today())
                ->where('type', 2)
                ->where('status', 0)
                ->first();
            if ($fee_transaction) {
                throw new \Exception('当前链上已有手续费交易正在确认,请勿重复打入手续费!交易哈希:' . $fee_transaction->txid);
            }

            //是否先刷新链上余额
            if ($refresh_balance) {
                $wallet->refresh();
                self::updateWalletBalance($wallet);
            }

            $wallet->refresh();
            if (bc_comp($wallet->old_balance, '0') <= 0) {
                throw new \Exception('用户链上余额为0,无须打入手续费');
            }
 
            $fee_currency = $currency = $wallet->currencyCoin;
            $currency_type = $currency->type;
            $fee_name = '';
            if ($currency_type == 'eth' || $currency_type == 'btc' || $currency_type == 'eos' || $currency_type == 'xrp') {
                throw new \Exception($wallet->currencyCoin->name . '币种无需额外打入归拢手续费');
            } elseif ($currency_type == 'erc20') {
                //从总账号往钱包打入eth
                $transfer_qty = $fee_currency->chain_fee;
                $from_address = $fee_currency->total_account;
                $from_private_key = 'abssd';
                $fee_name = 'eth';
            } elseif ($currency_type == 'usdt') {
                //从总账号往钱包打入btc
                $transfer_qty = bc_add($fee_currency->chain_fee, '0.00000546');
                $from_address = $fee_currency->total_account;
                $from_private_key = $fee_currency->origin_key;
                $fee_name = 'btc';
            } else {
                throw new \Exception('不支持' . $currency->name . '数字货币');
            }
            if (empty($from_address) ) {
                throw new \Exception($fee_name . '币种总账号信息未设置');
            }
            // 查询被打入手续费钱包内余额是否充足,当链上手续费余额大于需要转入的手续费时,提示无须再打入手续费
            $wallet_balance = self::getChainBalance($wallet, $fee_name);
         
            if (bc_comp($wallet_balance, $transfer_qty) >= 0) {
                throw new \Exception('钱包内' . $fee_name . '余额充足,无须打入');
            }
            // 当有余额时看相差多少,只打入相差的部分
            $transfer_qty = bc_sub($transfer_qty, bc_comp($wallet_balance, '0') >= 0 ? $wallet_balance : 0);
            // 查询总钱包余额并检测是否充足
            $total_currency = Currency::where('name', $fee_name)->firstOrFail();
            $fee_coin_instance = CoinManager::resolve(
                strtoupper($total_currency->type),
                $total_currency->decimal_scale,
                $total_currency->contract_address
            );
            $fee_chain_balance = $fee_coin_instance->getBalance($from_address);
          
            if (bc_comp($fee_chain_balance, $transfer_qty) < 0) {
                throw new \Exception("{$fee_currency->name}总账号内{$fee_name}余额不足");
            }
            $params  = [
                'currency_type' => $currency_type,
                'fee_name' => $fee_name,
                'to_address' => $wallet->address,
                'transfer_qty' => $transfer_qty,
                'from_address' => $from_address,
                'from_private_key' => $from_private_key,
                'type' => 2,
            ];
            $query_str = md5(http_build_query($params));
            if (Cache::has($query_str)) {
                throw new \Exception('当前链上已有手续费交易正在确认,请勿重复打入手续费!交易哈希:' . Cache::get($query_str));
            }

            DB::beginTransaction();
            $result = self::transfer($fee_name, $wallet->address, $transfer_qty, $from_address, $from_private_key, BaseCoin::TYPE_SEND_FEE, $fee_currency->chain_fee);
            if (isset($result['code']) && $result['code'] == 0) {
                Cache::put($query_str, $result['txid'], Carbon::now()->addMinutes(20));
                //记录链上哈希信息
                $lbx_hash_data = [
                    'wallet_id' => $wallet->id,
                    'txid' => $result['txid'],
                    'type' => 2, //打入手续费
                    'amount' => $transfer_qty,
                    'default_fee' => $fee_currency->chain_fee,
                    'status' => 0,
                ];
                LbxHash::unguarded(function () use ($lbx_hash_data) {
                    return LbxHash::create($lbx_hash_data);
                });
            } else {
                throw new \Exception('请求异常:' . var_export($result, true));
            }
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 钱包链上余额归拢到总账号
     *
     * @param \App\UsersWallet $wallet 要归拢的钱包
     * @param bool $refresh_balance 是否从链上刷新余额
     * @return string
     * @throws \Exception
     */
    public static function collect(UsersWallet $wallet, $refresh_balance = false)
    { 
        try {
            $currency = $wallet->currencyCoin;
            if (!$currency) {
                throw new \Exception('对应币种不存在');
            }
            $from_address = $wallet->address;
            $from_private_key = 'private_key';
            $to_address = $currency->total_account;
            $contract_address = $currency->contract_address;
            $currency_type = $currency->type;
            if (empty($to_address)) {
                throw new \Exception('总账号地址未设置');
            }
            if ($currency_type == 'erc20' && empty($contract_address)) {
                throw new \Exception('合约地址未设置');
            }
     
            // 根据币种手续费计算
            $base_transfer_use_qty = '0'; //除手续费消耗主链的数量
            switch ($currency_type) {
                case 'eth':
                    $fee_currency_name = 'eth';
                    $transfer_fee = $currency->chain_fee ?? '0.001';
                    break;
                case 'btc':
                    $fee_currency_name = 'btc';
                    $transfer_fee = $currency->chain_fee ?? '0.00006';
                    break;
                case 'erc20':
                    $fee_currency_name = 'eth';
                    $transfer_fee = $currency->chain_fee ?? '0.001';
                    break;
                case 'usdt':
                    $fee_currency_name = 'btc';
                    $base_transfer_use_qty = '0.00000546';
                    $transfer_fee = $currency->chain_fee ?? '0.00006';
                    break;
                default:
                    $fee_currency_name = '';
                    $transfer_fee = 0;
            }
            // 查询上次归拢是否完成
            $lbx_hash = LbxHash::where('status', 0)
                ->where('type', 0)
                ->where('wallet_id', $wallet->id)
                ->first();
            if ($lbx_hash) {
                throw new \Exception('当前有归拢操作未完成');
            }
            // 是否先刷新链上余额
            if ($refresh_balance) {
                self::updateWalletBalance($wallet);
            }
            $wallet->refresh();    
            if ($currency_type == 'erc20' || $currency_type == 'usdt') {
                //检测手续费是否充足:erc20扣eth, usdt扣btc
                $fee_balance = self::getChainBalance($wallet, $fee_currency_name);
         
                $base_total_use_qty = bc_add($base_transfer_use_qty, $transfer_fee); //手续费+链上交易额外消耗,例如USDT要额外消耗0.00000546BTC
                if (bc_comp($fee_balance, $base_total_use_qty) < 0) {
                    throw new \Exception('钱包内手续费可用余额(' . $fee_balance . ')不足,不能归拢');
                }
                $transfer_qty = $wallet->old_balance; //代币有多少归多少
            } else {
                $transfer_qty = bc_sub($wallet->old_balance, $transfer_fee); //主链归拢减去手续费
            }
            //如果链上余额为空或者只有手续费(ETH、BTC)就没必要做归拢
            if (bc_comp($transfer_qty, '0') <= 0) {
                throw new \Exception('余额为空或手续费不足,不能归拢');
            }
       
            DB::beginTransaction();
            $result = self::transfer($currency->name, $to_address, $transfer_qty, $from_address, $from_private_key, BaseCoin::TYPE_COLLECT, $currency->chain_fee);
            if (!isset($result['code']) || $result['code'] != 0) {
                throw new \Exception(var_export($result, true));
            }
            //记录链上哈希信息
            $lbx_hash_data = [
                'wallet_id' => $wallet->id,
                'txid' => $result['txid'],
                'type' => 0,
                'amount' => $transfer_qty,
                'default_fee' => $currency->chain_fee,
                'status' => 0,
            ];
            LbxHash::unguarded(function () use ($lbx_hash_data) {
                return LbxHash::create($lbx_hash_data);
            });
            $wallet->refresh();
            $wallet->txid = $result['txid'];
            $wallet->gl_time = time();
            $wallet->save();
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}