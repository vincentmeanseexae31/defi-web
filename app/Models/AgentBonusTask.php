<?php

namespace App\Models;


use Illuminate\Support\Facades\DB;

class AgentBonusTask extends Model
{
    protected $table = 'agent_bonus_task';
    public $timestamps = false;
    protected $appends = [
        'finish_time',
        'create_time'
    ];


    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id');
    }

    public function getFinishTimeAttribute(){
        return date('Y-m-d H:i:s', $this->attributes['finish_time']);
    }

    public function getCreateTImeAttribute(){
        return date('Y-m-d H:i:s', $this->attributes['create_time']);
    }
}