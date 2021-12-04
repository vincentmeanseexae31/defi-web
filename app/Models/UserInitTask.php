<?php
/**
 * Created by PhpStorm.
 * User: swl
 * Date: 2018/7/3
 * Time: 10:23
 */

namespace App\Models;

class UserInitTask extends Model
{
    protected $table = 'user_init_task';
    public $timestamps = false;
    protected $hidden = [];
    protected $appends = [];

    public function getCreateTimeAttribute()
    {
        $value = $this->attributes['add_time'];
        return $value ? date('Y-m-d H:i:s', $value ) : '';
    }

}