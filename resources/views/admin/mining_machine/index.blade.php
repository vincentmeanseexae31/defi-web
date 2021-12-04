@extends('admin._layoutNew')
@section('page-head')
@endsection
@section('page-content')
{{--    <div style="margin-top: 10px;width: 100%;margin-left: 10px;">--}}
{{--        <button class="layui-btn layui-btn-normal layui-btn-radius" onclick="layer_show('添加数据','{{url('admin/market_add')}}')">添加矿机</button>--}}
{{--    </div>--}}
    <button class="layui-btn layui-btn-normal layui-btn-radius" id="add_mining_machine">添加矿机</button>
    <table id="demo" lay-filter="test"></table>
    <script type="text/html" id="barDemo">
        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
    </script>
    <script type="text/html" id="switchTpl">
        <input type="checkbox" name="is_up" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="is_up" @{{ d.is_up == 1 ? 'checked' : '' }} />
    </script>
    <script type="text/html" id="switchnewuser">
        <input type="checkbox" name="is_newuser" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="is_newuser" @{{ d.is_newuser == 1 ? 'checked' : '' }} />
    </script>
@endsection
@section('scripts')
    <script>
        layui.use(['table','form','layer'], function(){
            var table = layui.table;
            var $ = layui.jquery;
            var form = layui.form;

            //第一个实例
            table.render({
                elem: '#demo'
                ,url: '{{url('admin/mining_machine/list')}}' //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', Width:60, sort: true}
                    ,{field: 'mining_name', title: '套餐名字', Width:80}
                    // ,{field: 'subtitle', title: '副标题', Width:80}
                    // ,{field: 'describe', title: '套餐描述', minWidth:80}
                    // ,{field: 'bonus_num', title: '分红数', minWidth:80}
                    // ,{field: 'day_bonus', title: '每日分红', minWidth:80}
                    // ,{field: 'days', title: '分红天数', minWidth:80}
                    // ,{field: 'rate', title: '年化利息', minWidth:80}
                    ,{field: 'num', title: 'trx数量', minWidth:80}
                    // ,{field: 'out_num', title: '销售数量', minWidth:80}
                    // ,{field: 'stock_num', title: '库存数量', minWidth:80}
                    // ,{field: 'create_time', title: '创建时间', minWidth:80, templet: '#typetml'}
                    // ,{field: 'is_up', title:'是否上架', width: 90, templet: '#switchTpl'}
                    // ,{field: 'is_newuser', title:'是否新用户专享', width: 90, templet: '#switchnewuser'}
                    // ,{field: 'is_up', title: '是否上架', minWidth:150, templet: '#typetml'}
                    ,{title:'操作',minWidth:100,toolbar: '#barDemo'}
                ]]
            });
            $('#add_mining_machine').click(function(){
                // layer_show('添加管理员', '/admin/mining_machine/add');
                var index = layer.open({
                    title:'添加矿机'
                    ,type:2
                    ,content: '/admin/mining_machine/add'
                    ,area: ['800px', '600px']
                    ,maxmin: true
                    ,anim: 3
                });
                layer.full(index);
            });
            //监听锁定操作
            form.on('switch(is_up)', function(obj){
                var id = this.value;
                console.log(id);
                $.ajax({
                    url:'{{url('admin/mining_machine/up')}}',
                    type:'post',
                    dataType:'json',
                    data:{id:id},
                    success:function (res) {
                        layer.msg(res.message);

                    }
                });
            });
            form.on('switch(is_newuser)', function(obj){
                var id = this.value;
                console.log(id);
                $.ajax({
                    url:'{{url('admin/mining_machine/newuser')}}',
                    type:'post',
                    dataType:'json',
                    data:{id:id},
                    success:function (res) {
                        layer.msg(res.message);

                    }
                });
            });
            table.on('tool(test)', function(obj){
                var data = obj.data;
                if(obj.event === 'del'){
                    layer.confirm('真的删除行么', function(index){
                        $.ajax({
                            url:'{{url('admin/mining_machine/del')}}',
                            type:'post',
                            dataType:'json',
                            data:{id:data.id},
                            success:function (res) {
                                if(res.type == 'error'){
                                    layer.msg(res.message);
                                }else{
                                    obj.del();
                                    layer.close(index);
                                }
                            }
                        });
                    });
                } else if(obj.event === 'edit'){
                    {{--layer_show('编辑矿机','{{url('admin/mining_machine/add')}}?id='+data.id);--}}
                    var index = layer.open({
                            title:'添加矿机'
                            ,type:2
                            ,content: '/admin/mining_machine/add?id='+data.id
                            ,area: ['800px', '600px']
                            ,maxmin: true
                            ,anim: 3
                        });
                    layer.full(index);
                }
            });
            //监听提交
            form.on('submit(mobile_search)', function(data){
                var account_number = data.field.account_number;
                table.reload('mobileSearch',{
                    where:{account_number:account_number},
                    page: {curr: 1}         //重新从第一页开始
                });
                return false;
            });
        });
    </script>
@endsection