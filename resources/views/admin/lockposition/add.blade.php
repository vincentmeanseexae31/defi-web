@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <form class="layui-form" action="">
        <div class="layui-form-item">
            <label class="layui-form-label">用户账号</label>
            <div class="layui-input-block">
                <input type="text" name="username" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="{{$result->account_number}}">
            </div>
        </div>


        <div class="layui-form-item">
            <label class="layui-form-label">锁仓金额</label>
            <div class="layui-input-block">
                <input type="number" class="layui-input" id="lock_money" autocomplete="off" name="lock_money" value="{{$result->lock_money}}" >
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">到期时间</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" id="locktime" name="locktime" value="{{$result->locktime}}" placeholder="日期">
            </div>
        </div>

        <input type="hidden" name="id" value="{{$result->id}}">
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit="" lay-filter="demo1">立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
    </form>

@endsection

@section('scripts')
    <script>

        layui.use(['form','laydate'],function () {
            var form = layui.form
                ,$ = layui.jquery
                ,laydate = layui.laydate
                ,index = parent.layer.getFrameIndex(window.name);
            laydate.render({
                elem: '#locktime'
            });
            //监听提交
            form.on('submit(demo1)', function(data){
                var data = data.field;
                $.ajax({
                    url:'{{url('admin/lockposition/add')}}'
                    ,type:'post'
                    ,dataType:'json'
                    ,data : data
                    ,success:function(res){
                        if(res.type=='error'){
                            layer.msg(res.message);
                        }else{
                            parent.layer.close(index);
                            parent.window.location.reload();
                        }
                    }
                });
                return false;
            });
        });
    </script>

@endsection