<?php
require __DIR__.'/_bootstrap.php';
require_once __DIR__.'/../providers/VtuNgProvider.php';

$u=require_user();
$d=input();
$orderId=(int)($d['order_id']??0);
if($orderId<=0) out(false,'Valid order_id required',[],422);

$pdo=db();
$s=$pdo->prepare('SELECT * FROM data_orders WHERE id=? AND user_id=?');
$s->execute([$orderId,$u['id']]); $order=$s->fetch();
if(!$order) out(false,'Order not found',[],404);
if(!in_array($order['status'],['pending','processing'],true)) out(false,'Order cannot be processed in its current state',[],409);

$pdo->prepare('UPDATE data_orders SET status="processing" WHERE id=? AND status="pending"')->execute([$orderId]);

$provider=new VtuNgProvider();
$result=$provider->purchaseData(
  $order['network'], $order['plan_name'], $order['recipient_phone'],
  (float)$order['amount'], $order['id'].''
);

if(($result['success']??false)===true){
  $pdo->prepare('UPDATE data_orders SET status="successful",provider_reference=? WHERE id=?')
      ->execute([$result['provider_reference']??null,$orderId]);
  $pdo->prepare('INSERT INTO notifications(user_id,title,body) VALUES(?,?,?)')
      ->execute([$u['id'],'Data purchase successful','Your '.$order['plan_name'].' data purchase was completed.']);
  out(true,'Data order completed',['status'=>'successful']);
}

if(($result['status']??'')==='not_configured' || ($result['status']??'')==='adapter_pending'){
  $pdo->prepare('UPDATE data_orders SET status="pending" WHERE id=?')->execute([$orderId]);
  out(false,'Provider is not configured yet. The order remains pending.',['status'=>'pending'],503);
}

$pdo->prepare('UPDATE data_orders SET status="failed" WHERE id=?')->execute([$orderId]);
out(false,'Provider failed to process the order',['status'=>'failed'],502);
