 <div style="margin-top: 10px;width: 100%;margin-left: 0px;">
     <!-- <div class="layui-form-item">
            <label class="layui-form-label">充币合计</label>
            <div class="layui-input-block" style="width:90%">
                <blockquote class="layui-elem-quote layui-quote-nm" id="sum">0</blockquote>
            </div>
        </div> -->
     <form class="layui-form layui-form-pane layui-inline" action="">

         <div class="layui-inline">
             <label class="layui-form-label">币种&nbsp;&nbsp;</label>
             <div class="layui-input-inline" style="width:130px;">
                 <select name="currency_id" id="type_type" lay-search>
                     <option value="" class="ww">全部</option>
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
                 <input type="text" class="layui-input" id="start_time" value="" name="start_time">
             </div>
         </div>
         <div class="layui-inline">
             <label class="layui-form-label">结束日期：</label>
             <div class="layui-input-inline" style="width:120px;">
                 <input type="text" class="layui-input" id="end_time" value="" name="end_time">
             </div>
         </div>
         <div class="layui-inline">
             <div class="layui-input-inline">
                 <button class="layui-btn" lay-submit="" lay-filter="search"><i class="layui-icon">&#xe615;</i></button>
             </div>
         </div>
     </form>
 </div>
 <table id="data_table" lay-filter="data_table"></table>

 <script type="text/html" id="table_op">
     <a class="layui-btn  layui-btn-xs" lay-event="success">通过</a>
     <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="reject">驳回</a>
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
             elem: '#data_table',
             url: '/admin/account/recharge_audit/lists?status=0',
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
                         width: 120
                     }, {
                         field: 'currency_name',
                         title: '币种',
                         width: 110
                     }, 
                    //  {
                    //      field: 'certificate_pic',
                    //      title: '凭证',
                    //      width: 100
                    //  }
                     
                     , {
                         field: 'amount',
                         title: '金额',
                         width: 127
                     },
                     // {
                     //     field: 'sender',
                     //     title: '发送地址',
                     //     width: 100
                     // }, {
                     //     field: 'recipient',
                     //     title: '接受地址',
                     //     width: 100
                     // }, 
                     {
                         field: 'created_at',
                         title: '提交时间',
                         width: 180
                     },
                     // {
                     //     field: 'status_name',
                     //     title: '审核状态',
                     //     width: 180
                     // },
                     {
                         fixed: 'right',
                         width: 150,
                         align: 'center',
                         toolbar: '#table_op'
                     }
                 ]
             ],
             done: function(res) {
                 // $("#sum").text(res.extra_data);s
             }
         })

         //监听工具条
         table.on('tool(data_table)', function(obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"

             var data = obj.data;
             var layEvent = obj.event;
             var tr = obj.tr;

             if (layEvent === 'success') { //编辑
                 var index = layer.open({
                     title: '通过',
                     type: 2,
                     content: "{{url('admin/account/recharge_audit/edit')}}?id=" + data.id,
                     maxmin: true
                 });
                 layer.full(index);
             }
             if (layEvent === 'reject') { //拒绝通过
                var sb='  <div class="layui-form-item layui-form-text">';
                    sb+='<textarea id="reject_reason" name="reject_reason" placeholder="请输入拒绝内容" class="layui-textarea"></textarea>';
                    sb+='  </div>';
                   layer.confirm(
                     '确定拒绝此次的充值申请吗?'+sb, {
                         icon: 3,
                         title: '提示',
                         area:['500px','250px']
                     },
                     function(index) {
                   
                         reject_reason= $('#reject_reason').val();
                         if(index==1)
                         {
                            $.ajax({
                                url: '/admin/account/recharge_audit/check_reject',
                                type: 'post',
                                dataType: 'json',
                                data: {id:data.id,reject_reason:reject_reason},
                                success: function(res) {
                                    if (res.type == 'error') {
                                        layer.msg(res.message);
                                    } else {
                                        layer.close(index);
                                        window.location.reload();
                                    }
                                }
                            });
                         }
                        // layer.close(index);
                     }
                 );
                 // layer.full(index);
             }
         });

         //监听提交
         form.on('submit(search)', function(data) {
             data['audit_status'] = 0;
             table.reload('data_table', {
                 where: data.field,
                 page: {
                     curr: 1
                 } //重新从第一页开始
             });
             return false;
         });

     });
 </script>