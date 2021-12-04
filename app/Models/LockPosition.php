<?php


namespace App\Models;


class LockPosition extends Model
{
    protected $table = 'lock_position';
    public $timestamps = false;
    protected $appends = [
        'account_number'
    ];
    protected $casts = [
        'locktime' => 'date:Y-m-d',
        'addtime' => 'datetime:Y-m-d H:00',
    ];
    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id');
    }
    public function getAccountNumberAttribute()
    {
        return $this->hasOne(Users::class, 'id', 'user_id')->value('account_number');
    }
}