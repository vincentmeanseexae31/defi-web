<?php

namespace App\Http\Controllers\Api;

use App\Console\Commands\MiningBuyBonus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use App\Models\{AppApi, UserChat, Users, UserReal, Token, AccountLog, BondConfig, UsersWallet, UsersWalletcopy, Currency, InviteBg, Setting, UserCashInfo, ExchangeShiftTo, MiningReturnsBonus, UserMining, UsersWalletOut};
use App\DAO\RPC;
use Composer\Package\Locker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;

class CommunityController extends Controller
{
    public function CommunityDetail(Request $request){
   
        $address=$request->get('address','');
        $res=null;
        if(Cache::has($address))
        {
            $res= Cache::get($address);
        }
        else{
            $user=Users::where('trx_address',$address)->first();
            if(empty($user)){
                return $this->error('User not exist');
            }
            $main_user=Users::getMainUser($user->id);
            $upline_list=Users::getUpUsers($user->id);
            $down_users=Users::getDownUsers($user->id);
            $user_count=Users::count();
            $withdraw_amount=UsersWalletOut::GetAllOutCount();
            $user_total_invest=UserMining::where('user_id',$user->id)->sum('num');
            $investment_amount=UserMining::sum('num');
            $upline_user=null;
            $parent_user=Users::where('id',$user->parent_id)->first();
            {
                if($parent_user!=null)
                {
                    $parent_user_address=$parent_user->trx_address;
                    $upline_user=$parent_user_address;    
                }
                    
            }
      
            $wallet_balance=UsersWallet::where('user_id',$user->id)->first()->change_balance;
            
            $upline_income=MiningReturnsBonus::where('user_id',$user->id)->where('up_down',1)->sum('num');//来自上层的收入
            $downline_income=MiningReturnsBonus::where('user_id',$user->id)->where('up_down',2)->sum('num');//来自下层的收入
            $sponsor_income=MiningReturnsBonus::where('user_id',$user->id)->where('type',1)->sum('num');//赞助收入
            $user_withdraw=UsersWalletOut::where('user_id',$user->id)->sum('number');//提现
            $user_investment=$user_total_invest;//投资
            $withdraw_config=Setting::getWithDrawConfig();
            $config=BondConfig::Intnace();
            $withdraw_config_list=$config->getUserWithDrawalConfig();
            $invite_users=Users::where('id',$user->id)->value('zhitui_real_number');//总直邀人数
            $withdrawalBili= $config->getUserWithdrawalBiLi($user_total_invest,$invite_users);
            $withdraw_config['withdraw_percentage']=bcmul($withdrawalBili['withdrawal_scale'],100);
            $withdraw_config['withdraw_reinvestment_percentage']=bcmul($withdrawalBili['ft_scale'],100);
            $res=[
                'main_user'=>$main_user,
                'upline_list'=>$upline_list,
                'downline_list'=>$down_users,
                'user_count'=>$user_count,
                'withdraw_amount'=>$withdraw_amount,
                'investment_amount'=>$investment_amount,
                'upline_user'=>$upline_user,
                'wallet_balance'=>$wallet_balance,
                'upline_income'=>$upline_income,
                'downline_income'=>$downline_income,
                'sponsor_income'=>$sponsor_income,
                'user_withdraw'=>$user_withdraw,
                'user_investment'=>$user_investment,
                'withdrawRule'=>$withdraw_config,
                'withdraw_config_list'=>$withdraw_config_list
            ];        
            Cache::put($address, $res,Carbon::now()->addSecond(10));
        }
        
        return $this->success($res);
        
    }

    

    public function SponsorExist(Request $request){
        $address=$request->get('address','');
        $user=Users::where('trx_address',$address)->first();
        if(empty($user)){
            return $this->error('Sponsor not exist');
        }
        return $this->success(true,true);
    }
}