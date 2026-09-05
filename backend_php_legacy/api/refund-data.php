<?php
require __DIR__.'/_bootstrap.php';
$u=require_user();
$d=input(); $orderId=(int)($d['order_id']??0);
if($orderId<=0) out(false,'Valid order_id required',[],422);
$pdo=db();
try {
  $pdo->beginTransaction();
  $s=$pdo->prepare('SELECT * FROM data_orders WHERE id=? AND user_id=? FOR UPDATE');
  $s->execute([$orderId,$u['id']]); $o=$s->fetch();
  if(!$o) { $pdo->rollBack(); out(false,'Order not found',[],404); }
  if($o['status']!=='failed') { $pdo->rollBack(); out(false,'Only failed orders can be refunded',[],409); }
  $ref='REFUND-'.$orderId.'-'.bin2hex(random_bytes(4));
  $pdo->prepare('UPDATE wallets SET balance=balance+? WHERE user_id=?')->execute([$o['amount'],$u['id']]);
  $pdo->prepare('INSERT INTO wallet_ledger(user_id,type,amount,reference,description) VALUES(?,?,?,?,?)')
      ->execute([$u['id'],'refund',$o['amount'],$ref,'Refund for failed data order #'.$orderId]);
  $pdo->prepare('UPDATE data_orders SET status="refunded" WHERE id=?')->execute([$orderId]);
  $pdo->commit();
  out(true,'Order refunded',['reference'=>$ref]);
} catch(Throwable $e) {
  if($pdo->inTransaction())$pdo->rollBack();
  out(false,'Unable to refund order',[],500);
}
