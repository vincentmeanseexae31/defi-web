<?php

namespace App\Http\Controllers\Admin;
use App\Utils\RPC;
use App\Models\UserDayReport;
use Illuminate\Http\Request;
 

class ReportController extends Controller
{
    public function user_index(Request $request)
    { 
        return view('admin.report.user');
    }

    public function sync(Request $request)
    {
        $st=$request->input('st', null);
        $et=$request->input('et',null);          
        $address_url = 'http://127.0.0.1:5566/static/user_report?st='.$st.'&et=' .$et ;
        $res = RPC::apihttp($address_url,null,null,30);
    
        return $this->success($res);
    
    }

    public function user_list(Request $request)
    { 
        try {
            $field=$request->input('field', 'id');
            $order=$request->input('order', 'desc');
            if($order=='') $order='desc';
            $limit = $request->input('limit', 10);
            $data =UserDayReport
            
            ::join('users','users.id','=','user_day_report.uid')
            ->select('users.account_number','user_day_report.*')
            
            ->where(function ($query) use ($request) {
                    $account = $request->input('account', null);
                    $start_time = ($request->input('start_time', null));
                    $end_time = ($request->input('end_time', null));
                    // $scene != -1 && $query->where('scene', $scene);
                    $account && $query->where('account_number',$account);
                    $start_time && $query->where('day', '>=', $start_time);
                    $end_time && $query->where('day', '<=', $end_time);
                })->orderBy($field, $order)->paginate($limit);

            foreach($data as &$item)
            {
                $item['day']=str_replace('00:00:00','',$item['day']);
                if($item['create_time']>0) $item['create_time']=date('Y/m/d H:i:s',$item['create_time']);
                if($item['update_time']>0) $item['update_time']=date('Y/m/d H:i:s',$item['update_time']);
            }


            return $this->layuiData($data); 
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }
 
}