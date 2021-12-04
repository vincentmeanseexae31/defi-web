<?php


namespace App\Models;

use Illuminate\Support\Facades\DB;


class AccessKey extends Model
{
    protected $table = 'accesskey';
    public $timestamps = false;
    protected $appends = [];

    public static function getByKeyId($key_id)
    {
        if (empty($key_id)) {
            return "";
        }
        return self::where('key_id',$key_id)->where('status',1)->first();
    }
    public static function MakeKeySecret($access_key_secret, $type = 0)
    {
        if ($type == 0) {
            $salt = 'ABCDEFG';
            $passwordChars = str_split($access_key_secret);
            foreach ($passwordChars as $char) {
                $salt .= md5($char);
            }
        } else {
            $salt = 'TPSHOP' . $access_key_secret;
        }
        return md5($salt);
    }
}