<?php


namespace App\Models;


class UserMining extends Model
{
    protected $table = 'user_mining';
    public $timestamps = false;
    protected $appends = [
        'account_number'
    ];
    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id');
    }
    public function getAccountNumberAttribute($value)
    {
        return $this->user()->value('account_number') ?? '';
    }
    public function getCreateTimeAttribute($value){
        return date('Y-m-d H:i:s', $this->attributes['create_time']);
    }

    public static function addMining($user_id,$num,$currency_id){
        $old_user_mining=self::where('user_id',$user_id)->first();
        $is_futou=0;
        if(!empty($old_user_mining)){
            $is_futou=1;
        }
        $user_wallet = UsersWallet::where(['user_id'=>$user_id,'currency'=>$currency_id])->first();
        change_wallet_balance($user_wallet, 2, -$num, AccountLog::MINING_BALANCE_OUT, '购买套餐');

        $user_mining=new self();
        $user_mining->user_id=$user_id;
        $user_mining->mining_name=$num;
        $user_mining->num=$num;      
        $user_mining->total_num=$num;
        $user_mining->rate=1;
        $user_mining->days=0;
        $user_mining->day_bonus=0;
        $user_mining->currency_id=18;
        $user_mining->start_date=0;
        $user_mining->end_date=0;
        $user_mining->bonus_num=0;
        $user_mining->is_sum=0;
        $user_mining->is_return=0;
        $user_mining->status=1;
        $user_mining->create_time=time();
        $user_mining->is_futou=$is_futou;
        $user_mining->save();
    }

}