@extends('admin._layoutNew')
@section('page-head')
<style>
    [hidden] {
        display: none;
    }

    .layui-form-label {
        width: 150px;
    }
</style>
@stop
@section('page-content')
<div class="larry-personal-body clearfix">
    <form class="layui-form col-lg-5">
        <div class="layui-tab">
            <ul class="layui-tab-title">
                <!-- <li class="layui-this">通知设置</li> -->
                <li class="layui-this">基础设置</li>
                <!-- <li>上传设置</li> -->
                <!-- <li>交易手续费</li> -->
                <!-- <li>杠杆设置</li> -->
                <!-- <li>代理商设置</li> -->
                <!-- <li>安全中心</li> -->
                <li>套餐设置</li>
            </ul>
            <div class="layui-tab-content">
                <!--通知设置开始-->
                <!-- <div class="layui-tab-item layui-show">
                    <div id="email">
                        @include('admin.setting.email')
                    </div>
                    <div id="sms">
                        @include('admin.setting.sms')
                    </div>
                </div> -->
                <!--基础设置开始-->
                <div class="layui-tab-item layui-show">
                    @include('admin.setting.common')
                </div>
                <!--上传设置开始-->
                <!-- <div class="layui-tab-item">
                    @include('admin.setting.upload')
                </div> -->
                <!--交易设置开始-->
                <!-- <div class="layui-tab-item">
                    @include('admin.setting.trade')
                </div> -->
                <!--杠杆设置开始-->
                <!-- <div class="layui-tab-item">
                    @include('admin.setting.lever')
                </div> -->
                <!--代理商设置开始-->
                <!-- <div class="layui-tab-item">
                    @include('admin.setting.agent')
                </div> -->
                <!--安全中心设置开始-->
                <!-- <div class="layui-tab-item">
                    @include('admin.setting.safe')
                </div> -->
                <!--矿机设置-->
                <div id="taocan" class="layui-tab-item">
                    @include('admin.setting.mining')
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit lay-filter="website_submit">立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
    </form>
</div>
@stop
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js"></script>
<script>
   
    
    $(function() {

      
        var withdrawalConfig=[];
        $("#withdrawalConfig").each(function(){
            $obj=$(this);
            if($obj.val()=='')
            {
                withdrawalConfig=[
                    {amount_range: {st: 500,et: 5000},push_count:{op:'<',op_val:5},withdrawal_scale:0.4,ft_scale:0.6},
                    {amount_range: {st: 5000,et: 15000},push_count:{op:'>=',op_val:5},withdrawal_scale:0.5,ft_scale:0.5},
                    {amount_range: {st: 15000,et: 50000},push_count:{op:'>=',op_val:8},withdrawal_scale:0.6,ft_scale:0.4},
                    {amount_range: {st: 15000,et: 100000000},push_count:{op:'>=',op_val:10},withdrawal_scale:0.7,ft_scale:0.3},
                ];
            }
            else{
                withdrawalConfig=JSON.parse($obj.val()); 
            }
        });


        var staticBonusConfig=[];
        $("#staticBonusConfig").each(function(){
            $obj=$(this);
            if($obj.val()=='')
            {
                staticBonusConfig=[
                    {amount_range: {st: 1,et: 1999},bonus_scale:0.01},
                    {amount_range: {st: 2000,et: 4999},bonus_scale:0.013},
                    {amount_range: {st: 5000,et: 9999},bonus_scale:0.016},
                    {amount_range: {st: 10000,et: 19999},bonus_scale:0.02},
                    {amount_range: {st: 20000,et: 49999},bonus_scale:0.023},
                    {amount_range: {st: 50000,et: 999999999},bonus_scale:0.025}
                ];
            }
            else{
                staticBonusConfig=JSON.parse($obj.val()); 
            }
        });


        var bondZanZhuConfig = [];
        $("#bondZanZhuConfig").each(function(){
            $obj=$(this);
            if($obj.val()=='')
            {
                bondZanZhuConfig=[
                    {seq:1,amount_range: {st: 500,et: 1000},push_count:{op:'>=',op_val:0},obtain_count:4,obtain_scale:0.2},
                    {seq:2,amount_range: {st: 1000,et: 2000},push_count:{op:'>=',op_val:0},obtain_count:4,obtain_scale:0.1},
                    {seq:3,amount_range: {st: 2000,et: 3000},push_count:{op:'>=',op_val:0},obtain_count:4,obtain_scale:0.03},
                    {seq:4,amount_range: {st: 3000,et: 5000},push_count:{op:'>=',op_val:0},obtain_count:4,obtain_scale:0.03},
                    {seq:5,amount_range: {st: 5000,et: 15000},push_count:{op:'>=',op_val:5},obtain_count:7,obtain_scale:0.1},
                    {seq:6,amount_range: {st: 150000,et: 500000},push_count:{op:'>=',op_val:8},obtain_count:7,obtain_scale:0.02},
                    {seq:7,amount_range: {st: 150000,et: 100000000},push_count:{op:'>=',op_val:10},obtain_count:7,obtain_scale:0.02},
                ];
            }else{
                bondZanZhuConfig=JSON.parse($obj.val()); 
            }
        });
    
        var bondSheQuConfig=[];
        var bondSheQuStruct=[
                {st: 500,et: 1000,obtain_scale:0.01},
                {st: 1000,et: 2000,obtain_scale:0.01},
                {st: 2000,et: 3000,obtain_scale:0.01},
            ];
        $("#bondSheQuConfig").each(function(){
            $obj=$(this);
            if($obj.val()=='')
            {
                for(var i=-20;i<0;i++)
                {
                    var struct=JSON.stringify(bondSheQuStruct);
                    struct=JSON.parse(struct);
                    bondSheQuConfig.push({seq:i,amount_range: struct })
                }
                for(var i=1;i<=30;i++)
                {   
                    var struct=JSON.stringify(bondSheQuStruct);
                    struct=JSON.parse(struct);
                    bondSheQuConfig.push({seq:i,amount_range:struct})
                }            
            }else{
     
                bondSheQuConfig=JSON.parse($obj.val());
            }
        });

        var fundDynamicConfig = [];
        $("#fundDynamicConfig").each(function(){
            $obj=$(this);
            if($obj.val()=='')
            {
                fundDynamicConfig.push(
                    {
                        user_level:'S1',
                        amount_range: {st: 0,et: 3000000},
                        push_count:{op:'==',op_val:1},
                        obtain:[
                            {obtain_count:1,obtain_scale:0.02},
                            {obtain_count:2,obtain_scale:0.01},
                            {obtain_count:3,obtain_scale:0.008}
                        ]
                    } ,
                    {
                        user_level:'S2',push_count:{op:'==',op_val:1},
                        amount_range: {st: 3000000,et: 5000000},
                        obtain:[
                            {obtain_count:1,obtain_scale:0.03},
                            {obtain_count:2,obtain_scale:0.015},
                            {obtain_count:3,obtain_scale:0.01}
                        ]
                    } ,
                    {
                        user_level:'S3',push_count:{op:'==',op_val:1},
                        amount_range: {st: 5000000,et: 1000000000},
                        obtain:[
                            {obtain_count:1,obtain_scale:0.05},
                            {obtain_count:2,obtain_scale:0.03},
                            {obtain_count:3,obtain_scale:0.015},
                            {obtain_count:4,obtain_scale:0.01},
                            {obtain_count:5,obtain_scale:0.008},
                            {obtain_count:6,obtain_scale:0.006},
                            {obtain_count:7,obtain_scale:0.004},
                            {obtain_count:8,obtain_scale:0.002},
                        ]
                    },
                );

            }else{     
                fundDynamicConfig=JSON.parse($obj.val());
            }
        });

        $("#taocan").each(function() {
      
            var obj = $(this)[0];
            var app = new Vue({
                el: "#taocan",
                data: {
                    bondZanZhuConfig:bondZanZhuConfig,
                    bondSheQuConfig:bondSheQuConfig,
                    bondSheQuStruct:bondSheQuStruct,
                    withdrawalConfig:withdrawalConfig,
                    fundDynamicConfig:fundDynamicConfig,
                    staticBonusConfig:staticBonusConfig
                },
                computed:{
                    bondZanZhuConfigJson:function(){
                        return JSON.stringify(this.bondZanZhuConfig) ;
                    },
                    bondSheQuConfigJson:function(){
                        return JSON.stringify(this.bondSheQuConfig) ;
                    },
                    withdrawalConfigJson:function(){
                        return JSON.stringify(this.withdrawalConfig) ;
                    },
                    staticBonusConfigJson:function(){
                        return JSON.stringify(this.staticBonusConfig);
                    },
                    fundDynamicConfigJson:function(){
                        return JSON.stringify(this.fundDynamicConfig);
                    },
                    sumZanZhuScale:function(){
                        var total=0.0;
                        for(var i in this.bondZanZhuConfig)
                        {
                            var item=this.bondZanZhuConfig[i];
                            total+=parseFloat(item.obtain_scale);
                        }
                        return total.toFixed(2);
                    },
                    sumScale:function(){
                        var total=[0.0,0.0,0.0]
                   
                        for(var i in this.bondSheQuConfig)
                        {
                            var item=this.bondSheQuConfig[i];                            
                            total[0]+= parseFloat(item.amount_range[0].obtain_scale) ;
                            total[1]+= parseFloat(item.amount_range[1].obtain_scale);
                            total[2]+= parseFloat(item.amount_range[2].obtain_scale);
                        }

                  
                        for(var i in total)
                        {
                            total[i]=total[i].toFixed(2);
                        }
                        return total;
                    }
                },

                methods:{
                    add_fundDynamicConfig:function()
                    {               
                        this.fundDynamicConfig.push( {
                        user_level:'S'+(this.fundDynamicConfig.length+1),
                        amount_range: {st: 0,et: 0},
                        push_count:{op:'==',op_val:1},
                        obtain:[            {obtain_count:1,obtain_scale:0.05},]
                    } ,); 
                    },
                    del_fundDynamicConfig:function(index)
                    {               
                        this.fundDynamicConfig.splice(index,1);
                    },
                    add_fundDynamicConfig_sub:function(index)
                    {               
                        this.fundDynamicConfig[index].obtain.push({});
                    },
                    del_fundDynamicConfig_sub:function(index,r)
                    {                      
                        this.fundDynamicConfig[index].obtain.splice(r,1);
                    }
                }
            })
        });

    })
</script>
<script type="text/javascript">
    layui.use(['element', 'form', 'upload', 'layer'], function() {
        var element = layui.element;
        var layer = layui.layer;
        var form = layui.form;
        var $ = layui.$;
        $('#handle_multi_set').click(function() {
            layer.open({
                type: 2,
                title: '杠杆交易手数和倍数设置',
                content: '/admin/levermultiple/index',
                area: ['600px', '430px'],
                id: 99
            });
        });
        // form.on('select(select_push_count_op)',function(data){
        //      console.log(data);
        // });

        form.on('submit(website_submit)', function(data) {
            var data = data.field;
            console.log(data);
            $.ajax({
                url: '/admin/setting/postadd',
                type: 'post',
                dataType: 'json',
                data: data,
                success: function(res) {
                    layer.msg(res.message);
                }
            });
            return false;
        });
        var template = `
                <tr>
                    <td>
                        <div class="layui-inline">
                            <div class="layui-input-inline" style="width: 90px;">
                                <input class="layui-input" name="generation[]" value="" required lay-verify="required">
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="layui-inline">
                            <div class="layui-input-inline" style="width: 90px;">
                                <input class="layui-input" name="reward_ratio[]" value="" required lay-verify="required">
                            </div>
                            <div class="layui-form-mid">
                                <span>%</span></div>
                            </div>
                        </td>
                        <td>
                            <div class="layui-input-inline" style="width: 90px;">
                                <input class="layui-input" name="need_has_trades[]" value="" required lay-verify="required">
                            </div>
                        </td>
                        <td>
                            <div class="layui-input-inline">
                            <button class="layui-btn layui-btn-sm layui-btn-danger" type="button" lay-event="del">删除</button>
                            </div>
                    </td>
                </tr>`;
        $('#addLeverTradeOption').click(function() {
            $('#leverTradeFeeOption').append(template);
        });
        $('#leverTradeFeeOption').on('click', 'button[lay-event]', function() {
            var that = this,
                event = $(this).attr('lay-event')
            if (event == 'del') {
                layer.confirm('真的确定要删除吗?', {
                    title: '删除确认',
                    icon: 3
                }, function(index) {
                    $(that).parent().parent().parent().remove();
                    layer.close(index);
                });
            }
        });

        $('#sms_set').click(function() {
            layer.open({
                type: 2,
                title: '短信模版管理',
                content: '/admin/sms_project/index',
                area: ['1200px', '600px'],
                id: 99
            });
        });

        $('#currency_set').click(function() {
            layer.open({
                type: 2,
                title: '币种管理',
                content: '/admin/currency',
                area: ['1200px', '800px'],
                id: 99
            });
        });
    });
</script>
@stop