<?php
require __DIR__.'/_bootstrap.php';
$u=require_user(); $d=input(); $packageId=(int)($d['package_id']??0);
if($packageId<=0) out(false,'Valid package_id required',[],422);
$pdo=db();
try {
 $pdo->beginTransaction();
 $s=$pdo->prepare('SELECT * FROM share_packages WHERE id=? FOR UPDATE');$s->execute([$packageId]);$p=$s->fetch();
 if(!$p){$pdo->rollBack();out(false,'Share package not found',[],404);}
 $s=$pdo->prepare('SELECT balance FROM wallets WHERE user_id=? FOR UPDATE');$s->execute([$u['id']]);$w=$s->fetch();
 if(!$w || (float)$w['balance'] < (float)$p['investment_amount']){$pdo->rollBack();out(false,'Insufficient wallet balance',[],422);}
 $ref='SHARE-'.date('YmdHis').'-'.bin2hex(random_bytes(4));
 $pdo->prepare('UPDATE wallets SET balance=balance-? WHERE user_id=?')->execute([$p['investment_amount'],$u['id']]);
 $pdo->prepare('INSERT INTO wallet_ledger(user_id,type,amount,reference,description) VALUES(?,?,?,?,?)')->execute([$u['id'],'debit',$p['investment_amount'],$ref,'Share package purchase #'.$packageId]);
 $pdo->prepare('INSERT INTO share_holdings(user_id,package_id,status,purchased_at) VALUES(?,?,?,NOW())')->execute([$u['id'],$packageId,'active']);
 $holding=(int)$pdo->lastInsertId();
 $pdo->commit();
 out(true,'Share purchased',['holding_id'=>$holding,'reference'=>$ref,'status'=>'active']);
} catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();out(false,'Unable to purchase share',[],500);}
