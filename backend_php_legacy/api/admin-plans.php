<?php
require __DIR__.'/_bootstrap.php';
require_role(['admin','staff']);
$pdo=db();
if($_SERVER['REQUEST_METHOD']==='GET'){
 $rows=$pdo->query('SELECT id,network,plan_name,amount,active FROM data_plans ORDER BY network,amount')->fetchAll();
 out(true,'OK',['plans'=>$rows]);
}
$admin=require_role(['admin']);$d=input();$action=(string)($d['action']??'');
if($action==='save'){
 $id=(int)($d['id']??0);$network=trim((string)($d['network']??''));$name=trim((string)($d['plan_name']??''));$amount=(float)($d['amount']??0);$active=(int)!!($d['active']??1);
 if($network===''||$name===''||$amount<=0)out(false,'Network, plan name and valid amount are required',[],422);
 if($id>0){$s=$pdo->prepare('UPDATE data_plans SET network=?,plan_name=?,amount=?,active=? WHERE id=?');$s->execute([$network,$name,$amount,$active,$id]);}
 else {$s=$pdo->prepare('INSERT INTO data_plans(network,plan_name,amount,active) VALUES(?,?,?,?)');$s->execute([$network,$name,$amount,$active]);$id=(int)$pdo->lastInsertId();}
 audit_log((int)$admin['id'],'save','data_plan',$id,['network'=>$network,'plan_name'=>$name,'amount'=>$amount,'active'=>$active]);out(true,'Plan saved',['id'=>$id]);
}
if($action==='toggle'){$id=(int)($d['id']??0);if($id<=0)out(false,'Invalid plan',[],422);$pdo->prepare('UPDATE data_plans SET active=1-active WHERE id=?')->execute([$id]);audit_log((int)$admin['id'],'toggle','data_plan',$id);out(true,'Plan updated');}
out(false,'Unsupported action',[],422);
