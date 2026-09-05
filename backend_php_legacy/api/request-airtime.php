<?php
require __DIR__.'/_bootstrap.php';
$u=require_user(); $d=input();
$network=trim((string)($d['network']??'')); $phone=trim((string)($d['recipient_phone']??''));
$amount=(float)($d['amount']??0);
if($network===''||$phone===''||$amount<=0) out(false,'Network, recipient phone and valid amount are required',[],422);
$s=db()->prepare('INSERT INTO airtime_requests(user_id,network,amount,recipient_phone,status) VALUES(?,?,?,?,?)');
$s->execute([$u['id'],$network,$amount,$phone,'pending']);
out(true,'Airtime request submitted for dispenser approval',['request_id'=>(int)db()->lastInsertId(),'status'=>'pending'],201);
