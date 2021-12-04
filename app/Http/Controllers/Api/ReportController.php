<?php

namespace App\Http\Controllers\Api;
use App\Utils\RPC;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use App\Models\{Currency, CurrencyMatch, TransactionComplete, MarketHour, CurrencyQuotation, CurrencyPlate};
use App\Models\Users;
class ReportController extends Controller
{
    public function user_report()
    {
        $user_id= Users::getUserId();
        if($user_id)
        {
            $date=date('Y-m-d',time());
            $address_url = 'http://127.0.0.1:5566/show/user_report?day='.$date.'&uid=' .$user_id ;
            $res = RPC::apihttp($address_url,null,null,10);
            $res = @json_decode($res, true);
            $res['call_date']=$date;
  
            return $this->success('success',$res);
        }

    }
 
}