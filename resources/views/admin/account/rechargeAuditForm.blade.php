@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
<form class="layui-form" action="">
 
                <div class="layui-form-item">
                    <label class="layui-form-label">账号</label>
                    <div class="layui-input-block">               
                    <input type="text"  autocomplete="off" class="layui-input layui-disabled" value="{{ $result['account_number'] }}" placeholder=""  disabled>
                    
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">代币</label>
                    <div class="layui-input-block">               
 
                    <input type="text"   autocomplete="off" class="layui-input layui-disabled" value="{{ $result['currency_name'] }}" placeholder=""  disabled>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">充值金额</label>
                    <div class="layui-input-block">               
 
                    <input type="text"   autocomplete="off" class="layui-input layui-disabled" value="{{ $result['amount'] }}" placeholder=""  disabled>
                    </div>
                </div>
                <div class="layui-form-item layui-form-text">
                    <label class="layui-form-label">凭证</label>
                    <div class="layui-input-block">               
                        <img   src="{{ $result['certificate_pic'] }}" id="img_thumbnail" class="thumbnail" style="display: block;max-width: 200px;height: auto;margin-top: 5px;">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">提交时间</label>
                    <div class="layui-input-block">               
                    <input type="text"   autocomplete="off" class="layui-input layui-disabled" value="{{ $result['created_at'] }}" placeholder=""  disabled>
                    </div>
                </div>
 
          <input type="hidden" name="id" value="{{ $result['id'] }}">
  
            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button class="layui-btn" lay-submit="" lay-filter="success">提交审核</button>
                </div>
            </div>
</form>

@endsection

@section('scripts')
<script>
    layui.use(['element', 'form', 'laydate', 'upload','layer'], function() {
        var form = layui.form,
            $ = layui.jquery,
            laydate = layui.laydate,
            upload = layui.upload,
            element = layui.element

            $("#img_thumbnail").each(function(){
                $obj=$(this).clone();
                $id=$obj.attr('id');
                $copy_id=$id+'_copy';
                $('#'+$copy_id).remove(); 
                // $obj.removeClass('thumbnail').removeAttr('style').attr('id',$copy_id).appendTo('body');;
                                 
                $(this).click(function(){
                    layer.photos({
                        photos: {
                            "status": 1,
                            "msg": "",
                            "title": "JSON请求的相册",
                            "id": 8,
                            "start": 0,
                            "data": [
                                    {
                                    "alt": "layer",
                                    "pid": 109,
                                    "src": $obj.attr('src'),
                                    "thumb": ""
                                    }                                                               
                                ]
                            }
                        ,anim: 5 //0-6的选择，指定弹出图片动画类型，默认随机
                    });
                });
                
            });

           

        var index = parent.layer.getFrameIndex(window.name)
         
        //监听提交
        form.on('submit(success)', function(data) {
            var data = data.field;
            $.ajax({
                url: '/admin/account/recharge_audit/check_adopt',
                type: 'post',
                dataType: 'json',
                data: data,
                success: function(res) {
                    if (res.type == 'error') {
                        layer.msg(res.message);
                    } else {
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