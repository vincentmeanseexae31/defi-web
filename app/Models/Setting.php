<?php

/**
 * User: LDH
 */

namespace App\Models;


use Illuminate\Support\Facades\DB;
use App\Utils\RPC;

/**
 * 系统设置项
 * @package App
 */
class Setting extends Model
{
    protected $table = 'settings';
    public $timestamps = false;
    protected $fillable = ['key', 'value'];

    public static function getValueByKey($key = "", $defalut = "")
    {
        if (empty($key)) {
            return $defalut;
        }
        $settings = self::orderBy('id', 'DESC')->firstOrCreate(['key' => $key], [
            'value' => $defalut,
        ]);
        return $settings->value;
    }

    public static function updateValueByKey($key = "", $value = "")
    {
        if (empty($key))
            return false;
        $settings = self::orderBy('id', 'DESC')->updateOrCreate(['key' => $key], [
            'value' => $value,
        ]);
        return true;
    }

    public static function getValueByExplode($key = "")
    {
        if (empty($key))
            return "";
        $settings = self::where('key', $key)->orderBy('id', 'DESC')->first();
        if (empty($settings)) {
            return "";
        } else {
            $settings = explode("|", $settings->value);
            return $settings;
        }
    }

    public static function getBondConfig()
    {
       $data= self::whereIn('key',['withdrawalConfig','bondZanZhuConfig','bondSheQuConfig','fundDynamicConfig'])->get();
    //    $data= self::where('key',"withdrawalConfig")->get();
       $dic=[];
       foreach($data as &$item)
       {   
           $dic[]=[
               $item['key']=>json_decode($item['value'])
           ];
       }
       return $dic;
    }

    public static function getWithDrawConfig(){
        $data= self::whereIn('key',['withdraw_min_amount','withdraw_max_amount','withdraw_direct_referral','withdraw_percentage','withdraw_reinvestment_percentage','withdraw_status'])->get();

        $dic=[];
        foreach($data as &$item)
        {
            if($item['key']=='withdraw_min_amount'){
                $dic['withdraw_min_amount']=$item['value'];
            }
            if($item['key']=='withdraw_max_amount'){
                $dic['withdraw_max_amount']=$item['value'];
            }

            if($item['key']=='withdraw_direct_referral'){
                $dic['withdraw_direct_referral']=$item['value'];
            }

            if($item['key']=='withdraw_percentage'){
                $dic['withdraw_percentage']=$item['value'];
            }

            if($item['key']=='withdraw_reinvestment_percentage'){
                $dic['withdraw_reinvestment_percentage']=$item['value'];
            }
            if($item['key']=='withdraw_status'){
                $dic['withdraw_status']=$item['value'];
            }
        }
        return $dic;
    }

   /**
    * 获取用户收益
    */
    public static function getUserProfit($type='shequ',$cengji,$totalTouzi)
    {
        $config=self::getBondConfig();
    }

    public static function sendSmsForSmsBao($mobile, $content)
    {
        try {
            $username = self::getValueByKey('smsBao_username', '');
            $password = self::getValueByKey('password', '=');
            if (empty($mobile)) {
                throw new \Exception('请填写手机号');
            }

            if (empty($content)) {
                throw new \Exception('请填写发送内容');
            }
            $format_content = '【MTS】';
            $api            = 'http://api.smsbao.com/sms';
            $send_url       = $api . "?u=" . $username . "&p=" . md5($password) . "&m=" . $mobile . "&c=" . urlencode($format_content . $content);
            $return_message = RPC::apihttp($send_url);
            if ($return_message == 0) {
                return true;
            } else {
                $statusStr = array(
                    "-1" => "参数不全",
                    "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
                    "30" => "密码错误",
                    "40" => "账号不存在",
                    "41" => "余额不足",
                    "42" => "帐户已过期",
                    "43" => "IP地址限制",
                    "50" => "内容含有敏感词"
                );
                throw new \Exception($statusStr[$return_message]);
            }
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }
}