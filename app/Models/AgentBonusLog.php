<?php

namespace App\Models;


use Illuminate\Support\Facades\DB;

class AgentBonusLog extends Model
{
    protected $table = 'agent_bonus_log';
    public $timestamps = false;
    protected $appends = [
        'addtime'
    ];
    protected $fillable=[
        'agent_user_id',
        'from_user_id',
        'from_address',
        'num',
        'bonus_num',
        'rate',
        'addtime'
    ];

    public function getAddTimeAttribute()
    {
        return date('Y-m-d H:i:s', $this->attributes['addtime']);
    }
}