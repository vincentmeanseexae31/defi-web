 
<div style="margin-top: 10px;width: 100%;margin-left: 0px;">
                <!-- <div class="layui-form-item">
            <label class="layui-form-label">充币合计</label>
            <div class="layui-input-block" style="width:90%">
                <blockquote class="layui-elem-quote layui-quote-nm" id="sum">0</blockquote>
            </div>
        </div> -->
                <form class="layui-form layui-form-pane layui-inline" action="">
                    <!-- <input type="hidden" name="audit_status" value=1> -->
                    <div class="layui-inline">
                        <label class="layui-form-label">币种&nbsp;&nbsp;</label>
                        <div class="layui-input-inline" style="width:130px;">
                            <select name="currency_id" id="type_type" lay-search>
                                <option  value="" class="ww">全部</option>
                                @foreach ($currencies as $currency)
                                <option value="{{$currency->id}}" class="ww">{{$currency->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">用户账号&nbsp;&nbsp;</label>
                        <div class="layui-input-inline" style="width:130px;">
                            <input type="text" name="account_number" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">开始日期：</label>
                        <div class="layui-input-inline" style="width:120px;">
                            <input type="text" class="layui-input"  id="start_audit_time" value="" name="start_time">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">结束日期：</label>
                        <div class="layui-input-inline" style="width:120px;">
                            <input type="text" class="layui-input" id="end_audit_time" value="" name="end_time">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <div class="layui-input-inline">
                            <button class="layui-btn" lay-submit="" lay-filter="search_ysh"><i class="layui-icon">&#xe615;</i></button>
                        </div>
                    </div>
                </form>
            </div>
            <table id="data_table_ysh" lay-filter="data_table_ysh"></table>
             
            <script id="audit_status_tpl" type="text/html">
            @{{d.audit_status==2 ? '<span class="layui-badge">'+'拒绝'+'</span>' : '' }}
            @{{d.audit_status==1 ? '<span class="layui-badge layui-bg-green">'+'通过'+'</span>' : '' }}
     
               
            </script>
            <script type="text/html" id="table_op_ysh">
                <a class="layui-btn  layui-btn-xs"  lay-event="view">查看</a>
            
                <!-- <a class="layui-btn layui-btn-xs" lay-event="more">更多 <i class="layui-icon layui-icon-down"></i></a> -->
            </script>
<script>
    layui.use(['table', 'form', 'element', 'laydate'], function() {
        var table = layui.table,
            $ = layui.jquery,
            form = layui.form,
            laydate = layui.laydate,
            layer = layui.layer
   
        //第一个实例
        var data_table = table.render({
            elem: '#data_table_ysh',
            url: '/admin/account/recharge_audit/lists?status=1',
            page: true,
            cols: [
                [{
                    field: 'id',
                    title: 'id',
                    width: 90
                }, {
                    field: 'user_id',
                    title: '用户id',
                    width: 70
                }, {
                    field: 'account_number',
                    title: '账号',
                    width: 380
                }, {
                    field: 'currency_name',
                    title: '币种',
                    width: 120
                },  
                {
                    field: 'amount',
                    title: '金额',
                    width: 120
                }, 
                {
                    field: 'audit_user_name',
                    title: '审核人',
                    width: 120
                }, 
                // {
                //     field: 'created_at',
                //     title: '创建时间',
                //     width: 165
                // }, 
                {
                    field: 'audit_time',
                    title: '审核时间',
                    width: 165
                }, 
                {
                    field: 'audit_status_name',
                    title: '审核结果',
                    templet:'#audit_status_tpl',
                    width: 120
                }, 
                {
                    field: 'reject_reason',
                    title: '拒绝原因',                             
                }, 
                {
                    fixed: 'right',
                    width: 150,
                    align: 'center',
                    toolbar: '#table_op_ysh'
                }]
            ],
            done: function(res) {
                // $("#sum").text(res.extra_data);s
            }
        })
 
        //监听工具条
        table.on('tool(data_table_ysh)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            
            var data = obj.data;
            var layEvent = obj.event;
            var tr = obj.tr;
        
            if (layEvent === 'view') { //编辑
                var index = layer.open({
                    title: '查看'
                    , type: 2
                    , content: "{{url('admin/account/recharge_audit/view')}}?id=" + data.id
                    , maxmin: true
                });
                layer.full(index);
            }
 
        });

        //监听提交
        form.on('submit(search_ysh)', function(data) {
            // data['audit_status']=1;
            table.reload('data_table_ysh', {
                where: data.field,
                page: {
                    curr: 1
                } //重新从第一页开始
            });
            return false;
        });

    });
</script>