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

try {
  $pdo->beginTransaction();
  $ref='REFUND-'.$orderId.'-'.bin2hex(random_bytes(4));
  $pdo->prepare('UPDATE wallets SET balance=balance+? WHERE user_id=?')->execute([$order['amount'],$u['id']]);
  $pdo->prepare('INSERT INTO wallet_ledger(user_id,type,amount,reference,description) VALUES(?,?,?,?,?)')->execute([$u['id'],'refund',$order['amount'],$ref,'Automatic refund for failed data order #'.$orderId]);
  $pdo->prepare('UPDATE data_orders SET status="refunded" WHERE id=?')->execute([$orderId]);
  notify_user((int)$u['id'],'Data purchase refunded','The provider could not complete your data order. The amount has been returned to your wallet.');
  $pdo->commit();
} catch(Throwable $e) { if($pdo->inTransaction())$pdo->rollBack(); }
out(false,'Provider failed; wallet refund was attempted',['status'=>'refunded'],502);
