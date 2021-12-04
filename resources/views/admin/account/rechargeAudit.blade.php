@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')

<div class="layui-tab">
    <ul class="layui-tab-title">
        <li class="layui-this">待审核</li>
        <li>已审核</li>
    </ul>
    <div class="layui-tab-content">
        <div class="layui-tab-item layui-show">
            
            @include('admin.account.rechargeDsh',['id'=>'ysh'])          
        </div>
        <div class="layui-tab-item">
            @include("admin.account.rechargeYsh",['id'=>'dsh'])
        </div>

    </div>
</div>

@endsection
@section('scripts')
<script>
    layui.use(['table', 'form', 'element', 'laydate'], function() {
        var table = layui.table,
            $ = layui.jquery,
            form = layui.form,
            laydate = layui.laydate,
            layer = layui.layer
    
    });
</script>
@endsection