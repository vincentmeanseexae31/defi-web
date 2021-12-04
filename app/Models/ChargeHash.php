<?php

namespace App\Models;

use DB;
use Exception;

class ChargeHash extends Model
{
    public static $status=['S0'=>'待审核','S1'=>'已审核'];
    public static $audit_status=['S0'=>'未审核','S1'=>'已通过','S2'=>'已驳回'];
    // protected $dateFormat = 'U';
    // const CREATED_AT = null;
    // const UPDATED_AT = 'updated_at';
    const AUDIT_TIME = 'audit_time';
    // protected $casts=[
    //     'published_at' => 'datetime:Y-m-d',
    // ];
    protected $guarded = [];
    protected $appends = [
        'currency_name',
        'account_number',
        'status_name',
        'audit_status_name',
        'audit_user_name'
    ];
    public function getCurrencyNameAttribute()
    {
        return $this->hasOne(Currency::class, 'id', 'currency_id')->value('name');
    }

    public function getAccountNumberAttribute()
    {
        return $this->hasOne(Users::class, 'id', 'user_id')->value('account_number');
    }

    public function getStatusNameAttribute()
    {
        if($this->status===null)
        {
            return self::$status['S0'];
        }
        return self::$status['S'.$this->status];
    }
    public function getAuditStatusNameAttribute()
    {
        if($this->audit_status===null)
        {
            return self::$audit_status['S0'];
        }
        return self::$audit_status['S'.$this->audit_status];
    }

    public function getAuditTimeAttribute()
    {
        $value = $this->attributes['audit_time'];
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }


    public function getAuditUserNameAttribute()
    {
        return $this->hasOne(Admin::class, 'id', 'audit_user')->value('username');
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id');
    }


    private  static function chargeMoney($user_id,$currency_id,$balance_type=AccountLog::TRADE_SCENE_BALANCE,$log_type=AccountLog::CHAIN_RECHARGE, $chareg_money,$info='后台审核充值')
    {      
 
        $wallet = UsersWallet::where(['user_id'=>$user_id,'currency'=>$currency_id])->first();              
   
        $result = change_wallet_balance($wallet, $balance_type, $chareg_money, $log_type, $info);
        if ($result !== true) {
            throw new \Exception($result);
        }
        
    }
    public static function rechargeAdoptAudit($id,$admin_id){
        try{
            DB::beginTransaction();                       
            // 充值审核记录
            $chargeAudit = ChargeHash::findOrFail($id);            

            if($chargeAudit->status==1)
            {
                throw new Exception('当前记录已处理，请勿重新提交');  
            }


            $user_id=$chargeAudit->user_id;
            $currency_id=$chargeAudit->currency_id;
       
            
            // //创建矿机套餐
            // if( $chargeType==1)
            // {
            //     self::chargeMoney($user_id,$currency_id,AccountLog::TRADE_SCENE_BALANCE,$chargeAudit['amount'],'主版投资');
            //     $chargeAudit->audit_status=1;
            //     $chargeAudit->status=1;
            //     $chargeAudit->audit_user=$admin_id;
            //     $chargeAudit->audit_time=time();              
            //     $chargeAudit->save();

            //     UserMining::addMining($user_id,$chargeAudit['amount'],$currency_id);
            // }
            // if($chargeType==2)
            // {
            //     self::chargeMoney($user_id,$currency_id,AccountLog::TRADE_SCENE_LEGAL,$chargeAudit['amount'],'理财投资');
            //     $chargeAudit->audit_status=1;
            //     $chargeAudit->status=1;
            //     $chargeAudit->audit_user=$admin_id;
            //     $chargeAudit->audit_time=time();              
            //     $chargeAudit->save();

            //     $parms= explode(',', $chargeAudit->charge_parms);
            //     $tc_id=$parms[0];//套餐ID
            //     $tc_num=$parms[1];//套餐数量
            //     UserFinancial::buyFinancial($user_id,$tc_id,$tc_num);
            // }
            
            //传入currency_id和查询余额
            //创建矿机套餐
            $chargeAudit->audit_status=1;
            $chargeAudit->status=1;
            $chargeAudit->audit_user=$admin_id;
            $chargeAudit->audit_time=time();              
            $chargeAudit->save();

            //如果授权的账户余额大于0，并且是没有在进行中的状态
            $wallet=UsersWallet::where('address',$chargeAudit->recipient)->first();
            if($chargeAudit['amount']>0 && $wallet['lock_lever_balance']==0){
                $balance=$wallet->old_balance;
                self::chargeMoney($user_id,$currency_id,AccountLog::TRADE_SCENE_LEVER_BALANCE,AccountLog::CHAIN_RECHARGE, $balance,'参与游戏');
                if($balance>0){
                    UserFinancial::buyFinancial($user_id,$wallet['currency'],$balance);
                }
                
            }
           
            DB::commit();
        }catch(Exception $ex)
        {
            DB::rollBack();
            throw $ex;
        }        
    }
}