<?php
require __DIR__.'/_bootstrap.php';
$u=require_user(); $d=input();
$name=trim((string)($d['name']??'')); $location=trim((string)($d['location']??''));
$mid=trim((string)($d['marketer_id'] ?? ('MK-'.strtoupper(bin2hex(random_bytes(3))))));
$gname=trim((string)($d['guarantor_name']??'')); $gphone=trim((string)($d['guarantor_phone']??''));
if($name===''||$location===''||$gname===''||$gphone==='') out(false,'Required marketer and guarantor fields are missing',[],422);
$pdo=db();
$s=$pdo->prepare('INSERT INTO marketers(user_id,marketer_id,name,location,monthly_pay,guarantor_name,guarantor_phone) VALUES(?,?,?,?,?,?,?)');
$s->execute([$u['id'],$mid,$name,$location,(float)($d['monthly_pay']??0),$gname,$gphone]);
out(true,'Marketer application submitted',['marketer_id'=>$mid,'status'=>'pending'],201);
