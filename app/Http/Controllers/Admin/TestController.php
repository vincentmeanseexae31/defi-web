<?php

namespace App\Http\Controllers\Admin;

use App\Models\BondConfig;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
 
class TestController extends Controller
{

    public function index()
    {
       $config= BondConfig::Intnace();
       $result=$config->getUserWithdrawalBiLi(5002,5);
       $result=$config->getUserProfitByZanZhu(2,1001,1);
       $result=$config->getUserProfitBySheQu(1,500);
       $result=$config->getFundDynamicConfig(500,1);
       echo(date('Y年m月d日 h时i分s秒',1635441830));

       $parms= explode(',', '8,1');
       $tc_id=$parms[0];//套餐ID
       $tc_num=$parms[1];//套餐数量
        echo('  '.$tc_id.'-'.$tc_num.'  ');
   
       return $result;
    }
   
    public function txbl()
    {   
        $amount=INPUT::get('amount');
        $pushCount=INPUT::get('push_count');
        $config= BondConfig::Intnace();
        $result=$config->getUserWithdrawalBiLi($amount,$pushCount);
        return $result;
    }
 
}