<?php


namespace App\Http\Controllers\Admin;

use App\Models\Users;
use Illuminate\Http\Request;
use App\Models\LockPosition;
use function PHPSTORM_META\elementType;
use Validator;

class LockPositionController extends Controller
{
    public function index()
    {
        return view('admin.lockposition.index');
    }
    public function lists(Request $request){
        $limit = $request->input('limit', 10);
        $page=$request->input('page',1);
        $lockposition= LockPosition::select()->paginate($limit, ['*'], 'page', $page);
        return $this->layuiData($lockposition);
    }
    public function  add(Request $request){

        $id = $request->get('id', 0);
        if (empty($id)) {
            $result = new LockPosition();
        } else {
            $result = LockPosition::select(['*'])->find($id);
//            $result=$result->parse($result->addtime)->format('Y-m-d');
//            $result=$result->locktime->format('Y-m-d');
        }
        return view('admin.lockposition.add')->with('result', $result);


//        $id = LockPosition::get('id',null);
//        if(empty($id)) {
//            $adminUser = new LockPosition();
//        }else{
//            $adminUser = LockPosition::find($id);
//            if($adminUser == null) {
//                abort(404);
//            }
//        }
//        return view('admin.lockposition.add');
    }
    public  function  postAdd(Request $request){
        $id=$request->input('id');
        $username=$request->input('username','');
        $validator=Validator::make($request->all(),[
            'username'=>'required',
            'lock_money'=>'required|numeric'
        ],[
            'username.required'=>'用户名必须填写',
            'lock_money.required'=>'锁仓金额必须填写',
            'lock_money.numeric'=>'锁仓金额必须为数字'
        ]);
        if(empty($id)){
            $lockposition=new LockPosition();

        }else{
            $lockposition=LockPosition::find($id);
            if($lockposition==null){
                return redirect()->back();
            }
        }

        $user=Users::where('account_number',$username)->first();
        $validator->after(function($validator) use ($user) {
            if (empty($user)) {
                $validator->errors()->add('username', '用户不存在');
            } else {
                $user_id = $user->id;
                if (LockPosition::where('user_id', $user_id)->exists()) {
                    $validator->errors()->add('username', '用户已经存在');
                }
            }
        });


        if($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        $lockposition->user_id=$user->id;
        $lockposition->lock_money=$request->input('lock_money','');
        $lockposition->locktime=strtotime($request->input('locktime'));
        $lockposition->addtime=time();

        try {
            $lockposition->save();
        }catch (\Exception $ex){
            $validator->errors()->add('error', $ex->getMessage());
            return $this->error($validator->errors()->first());
        }
        return $this->success('添加成功');

    }

    public function del(Request $request){
        $id = $request->get('id',"");
        if(empty($id))return $this->error('参数错误');
        $bank = LockPosition::find($id);
        try {
            $bank->delete();
            return $this->success('删除成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }
}