<?php
require __DIR__.'/_bootstrap.php';
$actor=require_role(['admin']);
$pdo=db();
try {
 $pdo->beginTransaction();
 $rows=$pdo->query("SELECT h.id,h.user_id,h.purchased_at,p.daily_return,p.duration_days
                    FROM share_holdings h JOIN share_packages p ON p.id=h.package_id
                    WHERE h.status='active' FOR UPDATE")->fetchAll();
 $credited=0;
 foreach($rows as $h){
   $days=(int)floor((time()-strtotime($h['purchased_at']))/86400);
   if($days<0 || $days >= (int)$h['duration_days']) continue;
   $check=$pdo->prepare('SELECT id FROM share_return_ledger WHERE holding_id=? AND return_date=?');
   $today=date('Y-m-d'); $check->execute([$h['id'],$today]);
   if($check->fetch()) continue;
   $ref='SHRET-'.date('Ymd').'-'.$h['id'].'-'.bin2hex(random_bytes(2));
   $pdo->prepare('UPDATE wallets SET balance=balance+? WHERE user_id=?')->execute([$h['daily_return'],$h['user_id']]);
   $pdo->prepare('INSERT INTO wallet_ledger(user_id,type,amount,reference,description) VALUES(?,?,?,?,?)')
       ->execute([$h['user_id'],'share_return',$h['daily_return'],$ref,'Daily share return for holding #'.$h['id']]);
   $pdo->prepare('INSERT INTO share_return_ledger(holding_id,user_id,return_date,amount,reference) VALUES(?,?,?,?,?)')
       ->execute([$h['id'],$h['user_id'],$today,$h['daily_return'],$ref]);
   notify_user((int)$h['user_id'],'Share return credited','Your daily share return has been credited to your Data Connect wallet.');
   $credited++;
 }
 $pdo->commit();
 audit_log($actor,'post_daily_returns','share_return',null,['credited'=>$credited]);
 out(true,'Daily returns posted',['credited'=>$credited]);
} catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();out(false,'Unable to post daily returns',[],500);}
