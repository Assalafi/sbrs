<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;

class RemitaSplitService
{
    public static function keyForType(?string $id): string { return 'payment_type_'.preg_replace('/[^a-z0-9]/','',strtolower((string)$id)); }
    public static function defaults(): array { return ['enabled'=>false,'beneficiaries'=>[
        ['key'=>'primary','label'=>'e-Revenue collection','account_number'=>'','bank_code'=>'','percentage'=>40],
        ['key'=>'additional_1','label'=>'Additional account 1','account_number'=>'','bank_code'=>'','percentage'=>30],
        ['key'=>'additional_2','label'=>'Additional account 2','account_number'=>'','bank_code'=>'','percentage'=>30],
    ]]; }
    public static function settings(): array {
        $d=self::defaults(); $v=json_decode((string)Setting::get('remita_split_settings',''),true);
        if(!is_array($v)) return $d; $out=array_merge($d,$v); $rows=[];
        foreach($d['beneficiaries'] as $i=>$default){$row=array_merge($default,is_array($out['beneficiaries'][$i]??null)?$out['beneficiaries'][$i]:[]); if(($row['account_number']??'')!==''){try{$row['account_number']=Crypt::decryptString($row['account_number']);}catch(\Throwable){}} $row['percentage']=(float)($row['percentage']??0);$rows[]=$row;}
        $out['enabled']=filter_var($out['enabled']??false,FILTER_VALIDATE_BOOLEAN);$out['beneficiaries']=$rows;return $out;
    }
    public static function catalog(): array {
        try { return PaymentType::query()->orderBy('sort_order')->orderBy('name')->get()->map(fn($t)=>['key'=>self::keyForType((string)$t->id),'label'=>(string)$t->name,'service_type_id'=>(string)($t->remita_service_type_id?:setting('remita_service_type_id','')),'description'=>(string)($t->description?:'SBRS payment'),'active'=>(bool)$t->is_active,'split_enabled'=>(bool)($t->getAttribute('remita_split_enabled')??false),'surcharge'=>(string)($t->getAttribute('remita_surcharge')??'0.00')])->values()->all(); } catch(\Throwable){ return []; }
    }
    public static function normaliseSettings($value): ?array {
        if(is_string($value))$value=json_decode($value,true);if(!is_array($value))return null;$rows=array_values((array)($value['beneficiaries']??[]));if(count($rows)!==3)return null;$out=[];$total=0;
        foreach($rows as $i=>$row){if(!is_array($row)||trim((string)($row['label']??''))==='')return null;$account=preg_replace('/\D+/','',(string)($row['account_number']??''));$bank=preg_replace('/\D+/','',(string)($row['bank_code']??''));$pct=(float)($row['percentage']??0);if($pct<0||$pct>100)return null;if(filter_var($value['enabled']??false,FILTER_VALIDATE_BOOLEAN)&&(!preg_match('/^\d{6,20}$/',$account)||!preg_match('/^\d{3,12}$/',$bank)))return null;$total+=$pct;$out[]=['key'=>$i===0?'primary':'additional_'.$i,'label'=>mb_substr(trim($row['label']),0,120),'account_number'=>$account?Crypt::encryptString($account):'','bank_code'=>$bank,'percentage'=>$pct];}
        return abs($total-100)<=.001?['enabled'=>filter_var($value['enabled']??false,FILTER_VALIDATE_BOOLEAN),'beneficiaries'=>$out]:null;
    }
    public static function prepare(Payment $payment,string $orderId,array $payer,?string $serviceTypeId=null): array {
        $base=(float)($payment->base_amount??$payment->amount);$type=$payment->paymentType;$service=(string)($serviceTypeId?:$type?->remita_service_type_id?:setting('remita_service_type_id',''));if($service==='')throw new \InvalidArgumentException('No Remita service type is configured.');$settings=self::settings();$enabled=(bool)$settings['enabled']&&(bool)($type?->getAttribute('remita_split_enabled')??false);$charge=$enabled?(float)($type?->getAttribute('remita_surcharge')??0):0;$total=round($base+$charge,2);$items=$enabled&&$charge>0?self::lineItems($base,$charge,$settings['beneficiaries']):[];$payload=['serviceTypeId'=>$service,'amount'=>number_format($total,2,'.',''),'orderId'=>$orderId,'payerName'=>trim((string)($payer['name']??'STUDENT')),'payerEmail'=>trim((string)($payer['email']??'')),'payerPhone'=>preg_replace('/[^0-9+]/','',(string)($payer['phone']??'')),'description'=>trim((string)($payment->description??'SBRS Fee Payment'))];if($items)$payload['lineItems']=$items;$hash=hash('sha512',(string)setting('remita_merchant_id','').$service.$orderId.number_format($total,2,'.','').(string)setting('remita_api_key',''));return ['payload'=>$payload,'hash'=>$hash,'service_type_id'=>$service,'base_amount'=>$base,'surcharge_amount'=>$charge,'total_amount'=>$total,'split_enabled'=>$enabled&&$charge>0,'line_items'=>$items,'line_item_summary'=>array_map(fn($i)=>array_merge($i,['beneficiaryAccount'=>str_repeat('*',max(0,strlen($i['beneficiaryAccount'])-4)).substr($i['beneficiaryAccount'],-4)]),$items)];
    }
    public static function apply(Payment $payment,array $p): void {$payment->update(['base_amount'=>$p['base_amount'],'surcharge_amount'=>$p['surcharge_amount'],'total_amount'=>$p['total_amount'],'split_enabled'=>(bool)$p['split_enabled'],'split_details'=>$p['line_item_summary']?json_encode($p['line_item_summary']):null]);$payment->splitItems()->delete();foreach($p['line_items'] as $i){$a=preg_replace('/\D+/','',(string)$i['beneficiaryAccount']);$payment->splitItems()->create(['line_item_id'=>$i['lineItemsId'],'beneficiary_name'=>$i['beneficiaryName'],'beneficiary_account'=>$a?str_repeat('*',max(0,strlen($a)-4)).substr($a,-4):null,'bank_code'=>$i['bankCode'],'beneficiary_amount'=>$i['beneficiaryAmount'],'is_primary'=>$i['lineItemsId']==='beneficiary1']);}}
    private static function lineItems(float $base,float $charge,array $rows): array {$shares=[];$sum=0;foreach($rows as $r){$sum+=(float)$r['percentage'];if(!$r['account_number']||!$r['bank_code'])throw new \InvalidArgumentException('All split beneficiary accounts are required.');$shares[]=(int)floor(round($charge*100)*((float)$r['percentage']/100));}if(abs($sum-100)>.001)throw new \InvalidArgumentException('Split percentages must total 100%.');$shares[0]+=round($charge*100)-array_sum($shares);$out=[];foreach($rows as $i=>$r){$amount=$shares[$i]+($i===0?round($base*100):0);if($amount<=0)continue;$out[]=['lineItemsId'=>'beneficiary'.($i+1),'beneficiaryName'=>$r['label'],'beneficiaryAccount'=>preg_replace('/\D+/','',$r['account_number']),'bankCode'=>preg_replace('/\D+/','',$r['bank_code']),'beneficiaryAmount'=>number_format($amount/100,2,'.',''),'deductFeeFrom'=>$i===0?'1':'0'];}return $out;}
}
