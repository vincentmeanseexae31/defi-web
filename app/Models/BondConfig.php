<?php

/**
 * User: LDH
 */

namespace App\Models;


use Illuminate\Support\Facades\DB;
use App\Utils\RPC;
use JsonSerializable;
use Symfony\Component\CssSelector\Node\FunctionNode;

class BondConfig  implements JsonSerializable
{
    public $withdrawalConfig;
    public $bondZanZhuConfig;
    public $bondSheQuConfig;
    public $fundDynamicConfig;
    public $staticBonusConfig;
 
    private static $intance;
    private static $lock;
    function __construct() {
        $data=Setting::whereIn('key',['withdrawalConfig','bondZanZhuConfig','bondSheQuConfig','fundDynamicConfig','staticBonusConfig'])->get();
        $dic=[];
        foreach($data as &$item)
        {   
            $dic[$item['key']]=json_decode($item['value']);
        }
         
        // $this->config=$dic;
        $this->withdrawalConfig=$dic['withdrawalConfig'];
        $this->fundDynamicConfig=$dic['fundDynamicConfig'];
        $this->staticBonusConfig=$dic['staticBonusConfig'];
        $this->bondZanZhuConfig=[];
        {
            foreach($dic['bondZanZhuConfig'] as $item)
            {
                $this->bondZanZhuConfig[$item->seq]=$item;
            }
        }
        $this->bondSheQuConfig=[];
        {
            //转成键值对
            foreach($dic['bondSheQuConfig'] as $item)
            {
                $this->bondSheQuConfig[$item->seq]=$item->amount_range;
            }
        }
 
    }

    public function getFundDynamicConfig($amount,$obtain_count)
    {
        foreach($this->fundDynamicConfig as $item)
        {
            $range=$item->amount_range;
            $min=$range->st;
            $max=$range->et;    
            $qtBl=null;
            if($amount>=$min && $amount<$max)
            {
                foreach($item->obtain as $sub_item)
                {                    
                    if($sub_item->obtain_count==$obtain_count) return $sub_item->obtain_scale;
                    if($sub_item->obtain_count==-1)
                    {
                        $qtBl=$sub_item;
                    }
                }
            }            
            if($qtBl!=null)
            {
                return $qtBl->obtain_scale;
            } 
        }
        return 0;
    }

    public function getStaticBonusConfig($amount){
        foreach($this->staticBonusConfig as $item){
            $range=$item->amount_range;
            $min=$range->st;
            $max=$range->et;
            if($amount>=$min && $amount<$max){
                return $item->bonus_scale;
            }
        }
        return 0;
    }


    /**
     *      * amount::总投资
     * invite_count::总直推人数
     */
    public function getUserWithdrawalBiLi($amount,$invite_count){
        $withdrawalConfig=$this->withdrawalConfig;
        $max=count($withdrawalConfig)-1;
        for($i=$max;$i>=0;$i=$i-1)
        {
            $item=$withdrawalConfig[$i];

            $range=$item->amount_range;
            $min=$range->st;
            $max=$range->et;    
            if($amount>=$min && $amount<$max)
            {
                $op=$item->push_count->op;
                $val=$item->push_count->op_val;
            
                $result=['withdrawal_scale'=>$item->withdrawal_scale,'ft_scale'=>$item->ft_scale];
                if($op=='==' && $invite_count==$val )  return $result;
                if($op=='<' && $invite_count<$val )  return $result;
                if($op=='<=' && $invite_count<=$val )  return $result;
                if($op=='>' && $invite_count>$val )  return $result;
                if($op=='>=' && $invite_count>=$val )  return $result;
                if($op=='<>' && $invite_count!=$val )  return $$result;
            }   
        }
        // foreach($this->withdrawalConfig as $item)
        // {
                   
        // }
        return ['withdrawal_scale'=>0,'ft_scale'=>0];
    }

    /**
     * 获取提现配置
     */
    public function getUserWithDrawalConfig(){
        // $list=$this->withdrawalConfig;
        // $newlist=[];
        // foreach($list as $item){
        //     $model=array();
        //     $model['Amount']=$item[];
        //     $model=['Amount'];
        // }
         return $this->withdrawalConfig;
    }

    /**
     * seq::第几代
     * amount::总投资额
     * invite_count::直推人数
     */
    public function getUserProfitByZanZhu($seq,$amount,$invite_count){
         foreach($this->bondZanZhuConfig as $item)
         {
             $range=$item->amount_range;
             $min=$range->st;
             $max=$range->et;    
             if($amount>=$min && $amount<$max)
             {
                 $op=$item->push_count->op;
                 $val=$item->push_count->op_val;
                 $cseq=$item->seq;
                 if($op=='==' && $invite_count==$val && $seq==$cseq)  return $item->obtain_scale;
                 if($op=='<' && $invite_count<$val && $seq==$cseq)  return $item->obtain_scale;
                 if($op=='<=' && $invite_count<=$val && $seq==$cseq)  return $item->obtain_scale;
                 if($op=='>' && $invite_count>$val && $seq==$cseq)  return $item->obtain_scale;
                 if($op=='>=' && $invite_count>=$val && $seq==$cseq)  return $item->obtain_scale;
                 if($op=='<>' && $invite_count!=$val && $seq==$cseq)  return $item->obtain_scale;
             }             
         }
         return 0;
    }

    /**
     * 社区分红比例
     * seq::当前用户跟你要分红用户的层级
     * amount::投资额度
     */
    public function getUserProfitBySheQu($seq,$amount)
    {
        if(isset($this->bondSheQuConfig[$seq]))
        {
            $amount_range=$this->bondSheQuConfig[$seq];
            foreach($amount_range as $range)
            {
                $min=$range->st;
                $max=$range->et;
                if($amount>=$min && $amount<$max)
                {
                    return $range->obtain_scale;
                }
            }
        }
        return 0;
      
    }

    public static function Intnace()
    {
        
        if(self::$intance==null)
        {
            self::$intance=new BondConfig();
        }
        return self::$intance;
    }

    public function jsonSerialize () {
 
        return $this;
    }
   
}