<?php
require __DIR__.'/_bootstrap.php';
$u=require_user();
$d=input();

$network=trim((string)($d['network']??''));
$plan=trim((string)($d['plan_name']??''));
$phone=trim((string)($d['recipient_phone']??''));
$amount=(float)($d['amount']??0);

if($network===''||$plan===''||$phone===''||$amount<=0) out(false,'Network, plan, recipient phone and a valid amount are required',[],422);

$pdo=db();
try {
  $pdo->beginTransaction();
  $s=$pdo->prepare('SELECT balance FROM wallets WHERE user_id=? FOR UPDATE');
  $s->execute([$u['id']]);
  $w=$s->fetch();
  if(!$w || (float)$w['balance'] < $amount) {
    $pdo->rollBack();
    out(false,'Insufficient wallet balance',[],422);
  }

  $ref='DATA-'.date('YmdHis').'-'.bin2hex(random_bytes(4));
  $pdo->prepare('UPDATE wallets SET balance=balance-? WHERE user_id=?')->execute([$amount,$u['id']]);
  $pdo->prepare('INSERT INTO wallet_ledger(user_id,type,amount,reference,description) VALUES(?,?,?,?,?)')
      ->execute([$u['id'],'debit',$amount,$ref,"Data purchase: $plan"]);
  $pdo->prepare('INSERT INTO data_orders(user_id,network,plan_name,amount,recipient_phone,status) VALUES(?,?,?,?,?,?)')
      ->execute([$u['id'],$network,$plan,$amount,$phone,'pending']);
  $orderId=(int)$pdo->lastInsertId();
  $pdo->commit();
  out(true,'Order created and awaiting provider processing',['order_id'=>$orderId,'reference'=>$ref,'status'=>'pending']);
} catch(Throwable $e) {
  if($pdo->inTransaction())$pdo->rollBack();
  out(false,'Unable to create data order',[],500);
}
