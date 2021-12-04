@extends('admin._layoutNew')
@section('page-head')
    <link rel="stylesheet" type="text/css" href="{{URL("layui/css/layui.css")}}" media="all">
{{--    <link rel="stylesheet" type="text/css" href="{{URL("admin/common/bootstrap/css/bootstrap.css")}}" media="all">--}}
{{--    <link rel="stylesheet" type="text/css" href="{{URL("admin/common/global.css")}}" media="all">--}}
{{--    <link rel="stylesheet" type="text/css" href="{{URL("admin/css/personal.css")}}" media="all">--}}
@endsection
@section('page-content')
    <form class="layui-form">
        <input type="hidden" name="id" value="@if (isset($financial['id'])){{ $financial['id'] }}@endif">
        {{ csrf_field() }}
        <div class="layui-form-item">
            <label class="layui-form-label">产品名称</label>
            <div class="layui-input-block">
                <input class="layui-input newsName" name="financial_name" lay-verify="required" placeholder="请输入产品名称" type="text" value="@if (isset($financial['financial_name'])){{$financial['financial_name']}}@endif">
            </div>
        </div>
        <!-- <div class="layui-form-item">
            <label class="layui-form-label">副标题</label>
            <div class="layui-input-block">
                <input class="layui-input newsName" name="subtitle" lay-verify="required" placeholder="请输入副标题" type="text" value="@if (isset($financial['subtitle'])){{$financial['subtitle']}}@endif">
            </div>
        </div> -->
        <div class="layui-form-item">
            <label class="layui-form-label">描述</label>
            <div class="layui-input-block">
                <input class="layui-input newsName" name="describe" lay-verify="required" placeholder="请输入描述" type="text" value="@if (isset($financial['describe'])){{$financial['describe']}}@endif">
            </div>
        </div>
        <div class="layui-form-item layui-form-text">
            <label class="layui-form-label">封面图片</label>
            <div class="layui-input-block">
                <button class="layui-btn" type="button" id="financial_image_btn">选择图片</button>
                <br>
                <img src="{{$financial->financial_image ?? ''}}" id="img_financial_image" class="cover" style="display: @if(!empty($financial->financial_image)){{"block"}}@else{{"none"}}@endif;max-width: 200px;height: auto;margin-top: 5px;">
                <input type="hidden" name="financial_image" id="financial_image" value="{{$financial->financial_image ?? ''}}">
            </div>
        </div>
        <div class="layui-form-item layui-form-text">
            <label class="layui-form-label">内容图片</label>
            <div class="layui-input-block">
                <button class="layui-btn" type="button" id="financial_image2_btn">选择图片</button>
                <br>
                <img src="{{$financial->financial_image2 ?? ''}}" id="img_financial_image2" class="cover" style="display: @if(!empty($financial->financial_image2)){{"block"}}@else{{"none"}}@endif;max-width: 200px;height: auto;margin-top: 5px;">
                <input type="hidden" name="financial_image2" id="financial_image2" value="{{$financial->financial_image2 ?? ''}}">
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">TRX数量</label>
                <div class="layui-input-block">
                    <input class="layui-input newsName" name="num" lay-verify="required" placeholder="请输入价格" type="number" value="@if (isset($financial['num'])){{$financial['num']}}@endif">
                </div>
            </div>
            <!-- <div class="layui-inline">
                <label class="layui-form-label">库存数量</label>
                <div class="layui-input-block">
                    <input class="layui-input newsName" name="stock_num" lay-verify="required" placeholder="请输入库存数量" type="number" value="@if (isset($financial['stock_num'])){{$financial['stock_num']}}@endif">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">销售数量</label>
                <div class="layui-input-block">
                    <input class="layui-input newsName" name="out_num" lay-verify="required" placeholder="请输入总分红" type="number" value="@if (isset($financial['out_num'])){{$financial['out_num']}}@endif">
                </div>
            </div> -->
            <div class="layui-inline">
                <label class="layui-form-label">排序</label>
                <div class="layui-input-block">
                    <input class="layui-input newsName" name="sorts" lay-verify="required" placeholder="请输入排序" type="number" value="@if (isset($financial['sorts'])){{$financial['sorts']}}@endif">
                </div>
            </div>
        </div>
        <div class="layui-form-item">
{{--            <div class="layui-inline">--}}
{{--                <label class="layui-form-label">总分红</label>--}}
{{--                <div class="layui-input-block">--}}
{{--                    <input class="layui-input newsName" name="bonus" lay-verify="required" placeholder="请输入总分红" type="number" value="@if (isset($financial['bonus'])){{$financial['bonus']}}@endif">--}}
{{--                </div>--}}
{{--            </div>--}}
            <div class="layui-inline">
                <label class="layui-form-label">分红天数</label>
                <div class="layui-input-block">
                    <input class="layui-input newsName" name="days" lay-verify="required" placeholder="请输入分红天数" type="number" value="@if (isset($financial['days'])){{$financial['days']}}@endif">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">日化比率</label>
                <div class="layui-input-block">
                    <input class="layui-input newsName" name="rate" lay-verify="required" placeholder="请输入分红比率" type="number" value="@if (isset($financial['rate'])){{$financial['rate']}}@endif">
                </div>
            </div>
            <!-- <div class="layui-inline">
                <label class="layui-form-label">买入算力</label>
                <div class="layui-input-block">
                    <input class="layui-input newsName" name="buy_calculate" lay-verify="required" placeholder="请输入分红比率" type="number" value="@if (isset($financial['buy_calculate'])){{$financial['buy_calculate']}}@endif">
                </div>
            </div> -->
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
            <label class="layui-form-label">属性</label>
                <div class="layui-input-block">
                    <input name="is_up" class="tuijian" title="上架" type="checkbox" value="1" @if (isset($financial['is_up']) && $financial['is_up'] == 1) checked @endif >
                    <!-- <input name="is_newuser" class="tuijian" title="新用户专享" type="checkbox" value="1" @if (isset($financial['is_newuser']) && $financial['is_newuser'] == 1) checked @endif > -->
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">标签1</label>
                <div class="layui-input-block">
                    <input class="layui-input newsName" name="label1" placeholder="请输入标签1" type="text" value="@if (isset($financial['label1'])){{$financial['label1']}}@endif">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">标签2</label>
                <div class="layui-input-block">
                    <input class="layui-input newsName" name="label2"  placeholder="请输入标签2" type="text" value="@if (isset($financial['label2'])){{$financial['label2']}}@endif">
                </div>
            </div>
            <!-- <div class="layui-inline">
                <label class="layui-form-label">标签3</label>
                <div class="layui-input-block">
                    <input class="layui-input newsName" name="label3"  placeholder="请输入标签3" type="text" value="@if (isset($financial['label3'])){{$financial['label3']}}@endif">
                </div>
            </div> -->
        </div>

        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit lay-filter="submit">立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
    </form>
@endsection
@section('scripts')
{{--    <script type="text/javascript" src="{{ URL('vendor/ueditor/1.4.3/ueditor.config.js') }}"></script>--}}
{{--    <script type="text/javascript" src="{{ URL('vendor/ueditor/1.4.3/ueditor.all.js') }}"> </script>--}}
{{--    <script type="text/javascript" src="{{ URL('vendor/ueditor/1.4.3/lang/zh-cn/zh-cn.js') }}"></script>--}}
    <script>
        layui.use(['element', 'form', 'layer', 'jquery', 'layedit', 'laydate','upload'], function(){
            var upload = layui.upload;
            var layer = layui.layer;
            var form = layui.form;
            var $ = layui.$;

            form.on('submit(submit)', function(data){
                var data = data.field;

                $.ajax({
                    type: 'POST'
                    ,url: '/admin/financial/add'
                    ,data: data
                    ,success: function(data) {
                        console.log(data.type);
                        if(data.type == 'ok') {
                            layer.msg(data.message, {
                                icon: 1,
                                time: 1000,
                                end: function() {
                                    console.log('进来了');
                                    console.log(window.name);
                                    var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                                    parent.layer.close(index);
                                    parent.window.location.reload();
                                }
                            });
                        } else {
                            layer.msg(data.message, {icon:2});
                        }
                    }
                    ,error: function(data) {
                        console.log(data);
                        //重新遍历获取JSON的KEY
                        var str = '服务器验证失败,错误信息:' + '<br>';
                        for(var o in data.responseJSON.errors) {
                            str += data.responseJSON.errors[o] + '<br>';
                        }
                        layer.msg(str, {icon:2});
                    }
                });
                parent.layui.layer.close();
                return false; //阻止表单跳转。如果需要表单跳转，去掉这段即可。
            });
            //执行实例
            var uploadInst = upload.render({
                elem: '#financial_image_btn' //绑定元素
                ,url: '{{URL("api/upload")}}' //上传接口
                ,done: function(res){
                    console.log(res);
                    //上传完毕回调
                    if (res.code == 200){
                        $("#financial_image").val(res.msg)
                        $("#img_financial_image").show()
                        $("#img_financial_image").attr("src",res.msg)
                    } else{
                        alert(res.msg)
                    }
                }
                ,error: function(){
                    //请求异常回调
                }
            });

            //执行实例
            var uploadInst1 = upload.render({
                elem: '#financial_image2_btn' //绑定元素
                ,url: '{{URL("api/upload")}}' //上传接口
                ,done: function(res) {
                    console.log(res);
                    //上传完毕回调
                    if (res.code == 200){
                        $("#financial_image2").val(res.msg)
                        $("#img_financial_image2").show()
                        $("#img_financial_image2").attr("src",res.msg)
                    } else{
                        alert(res.msg)
                    }
                }
                ,error: function(){
                    //请求异常回调
                }
            });
        });
    </script>
@endsection