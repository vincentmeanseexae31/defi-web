<?php


namespace App\Http\Controllers\Admin;

use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Models\Financial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Validator;

class FinancialController extends Controller
{
    use ValidatesRequests;

    public function index()
    {
//        $mining_machine=MiningMachine::all();
        return view("admin.financial.index");
    }
    public function  list(Request $request){
        $limit=$request->input('limit','0');
//        $keyword = '%' . $keyword . '%';
//        $mining_query = MiningMachine::where(function ($query) use ($keyword) {
//            !empty($keyword) && $query->where('title', 'like', $keyword);
//        })->orderBy('id', 'desc');

//        $mining_machine = $limit != 0 ? $mining_query->paginate($limit) : $mining_query->get();

        $list=Financial::query();
        $list=$list->orderBy('sorts','asc')->paginate($limit);

        foreach($list as &$item)
        {
            $item['create_time']=date('Y-m-d H:i',$item['create_time']);
        }
        return response()->json(['code' => 0, 'data' => $list->items(), 'count' => $list->total()]);
    }

    public function up(Request $request){
        $id=$request->input('id');
        $mining_machine=Financial::find($id);
        if (empty($mining_machine)) {
            return $this->error('参数错误');
        }
        if ($mining_machine->is_up == 1) {
            $mining_machine->is_up = 0;
        } else {
            $mining_machine->is_up = 1;
        }
        try {
            $mining_machine->save();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    /***
     * 新用户专享
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function newuser(Request $request){
        $id=$request->input('id');
        $mining_machine=Financial::find($id);
        if (empty($mining_machine)) {
            return $this->error('参数错误');
        }
        if ($mining_machine->is_newuser == 1) {
            $mining_machine->is_newuser = 0;
        } else {
            $mining_machine->is_newuser = 1;
        }
        try {
            $mining_machine->save();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }
    public function add(){
        $id = Input::get('id',null);
        if(empty($id)) {
            $mining_machine = new Financial();
        }else{
            $mining_machine = Financial::find($id);
            if($mining_machine == null) {
                abort(404);
            }
        }
        return view('admin.financial.add', ['financial' => $mining_machine]);
    }

    public function  postAdd(Request $request){
        $id=$request->get('id');
        if (empty($id)){
            $mining_machine=new Financial();
        }else{
            $mining_machine=Financial::find($id);
            if ($mining_machine==null){
                return redirect()->back();
            }
        }

//        $this->validate($request, [
//            'mining_name' => 'required|min:1|max:64',
//            'describe'=>'required|min:1|max:125',
//            'price'=>'required',
//            'stock_num'=>'required',
//            'out_num'=>'required',
//            'sorts'=>'required',
//            'bonus'=>'',
//            'days'=>''
//        ]);
        $validator = Validator::make(Input::all(), [
            'describe'=>'required|min:1|max:125',
            'rate'=>'required|numeric',
            // 'stock_num'=>'required|numeric',
            // 'out_num'=>'required|numeric',
            'sorts'=>'required|numeric',
            'days'=>'required|numeric',
            // 'buy_calculate'=>'required|numeric'
        ], [
            'describe.required'  => '描述必须填写',
            'rate.numeric'   => '分红必须为数字'
        ]);
        if($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        $mining_machine->financial_name=$request->input('financial_name');
        $mining_machine->describe=$request->input('describe');
//        $mining_machine->bonus=$request->input('bonus');
        $rate=$request->input('rate');
        $days=$request->input('days');

        $num=$request->input('num');
        $total_bonus=bcmul($num*$rate,$days,4);
        // $total_bonus=bcmul($rate/365*$days,$num,4);
        $day_bonus=bcdiv($total_bonus,$days,4);
        $mining_machine->bonus_num= $total_bonus;
        $mining_machine->rate=$request->input('rate');
        $mining_machine->days=$request->input('days');
        $mining_machine->subtitle=$request->input('subtitle','');
        $mining_machine->day_bonus=$day_bonus;
        $mining_machine->num=$request->input('num');
        $mining_machine->currency_id=$request->input('currency_id');
        $mining_machine->financial_image=$request->input('financial_image');
        $mining_machine->financial_image2=$request->input('financial_image2');
        $mining_machine->is_up=$request->input('is_up','0');
        // $mining_machine->is_newuser=$request->input('is_newuser','0');
        // $mining_machine->stock_num=$request->input('stock_num');
        // $mining_machine->out_num=$request->input('out_num');
        $mining_machine->is_newuser=0;
        $mining_machine->stock_num=9999;
        $mining_machine->out_num=0;
        $mining_machine->sorts=$request->input('sorts');
        $mining_machine->label1=$request->input('label1');
        $mining_machine->label2=$request->input('label2');
        $mining_machine->label3=$request->input('label3');
        $mining_machine->buy_calculate=$request->input('buy_calculate');
        $mining_machine->create_time=time();
        try {
            $mining_machine->save();
        }catch (\Exception $ex){
            $validator->errors()->add('error', $ex->getMessage());
            return $this->error($validator->errors()->first());
        }
        if (empty($id)){
            return $this->success('添加成功');
        }
        return $this->success('编辑成功');
//        return $result ? $this->success('添加成功!') : $this->error('添加失败!');
    }
    
    public function del(Request $request)
    {
        $mining_machine = Financial::find($request->get('id'));
        if($mining_machine == null) {
            abort(404);
        }
        $bool = $mining_machine->delete();
        if($bool){
            return $this->success('删除成功');
        }else{
            return $this->error('删除失败');
        }
    }

}