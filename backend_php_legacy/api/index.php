<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
if ($_SERVER['REQUEST_METHOD']==='OPTIONS'){http_response_code(204);exit;}
function body():array{$x=json_decode(file_get_contents('php://input')?:'{}',true);return is_array($x)?$x:[];}
function out(bool $ok,string $msg,array $data=[],int $code=200):never{http_response_code($code);echo json_encode(['success'=>$ok,'message'=>$msg]+$data);exit;}
function b64(string $s):string{return rtrim(strtr(base64_encode($s),'+/','-_'),'=');}
function make_token(int $uid):string{$secret=getenv('JWT_SECRET')?:'CHANGE_ME';$p=b64(json_encode(['uid'=>$uid,'exp'=>time()+604800]));return $p.'.'.b64(hash_hmac('sha256',$p,$secret,true));}
function auth_uid():int{$h=$_SERVER['HTTP_AUTHORIZATION']??'';if(!preg_match('/Bearer\s+(.+)/i',$h,$m))out(false,'Authentication required',[],401);[$p,$s]=array_pad(explode('.',$m[1],2),2,'');$e=b64(hash_hmac('sha256',$p,getenv('JWT_SECRET')?:'CHANGE_ME',true));if(!$p||!hash_equals($e,$s))out(false,'Invalid token',[],401);$d=json_decode(base64_decode(strtr($p,'-_','+/')),true);if(!$d||($d['exp']??0)<time())out(false,'Token expired',[],401);return (int)$d['uid'];}
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)?:'/';$path=preg_replace('#^.*?/api#','/api',$path);$m=$_SERVER['REQUEST_METHOD'];$db=db();
if($path==='/api/health'&&$m==='GET')out(true,'Data Connect API is online',['service'=>'dataconnect','version'=>'7.2']);
if($path==='/api/auth/register'&&$m==='POST'){ $b=body();$phone=trim((string)($b['phone']??''));$u=trim((string)($b['username']??''));$pw=(string)($b['password']??'');if(strlen($phone)<10||strlen($u)<2||strlen($pw)<6)out(false,'Phone, username and a 6+ character password are required',[],422);$q=$db->prepare('SELECT id FROM users WHERE phone=? OR username=? LIMIT 1');$q->execute([$phone,$u]);if($q->fetch())out(false,'Phone or username is already registered',[],409);$db->beginTransaction();$q=$db->prepare('INSERT INTO users(phone,username,password_hash) VALUES(?,?,?)');$q->execute([$phone,$u,password_hash($pw,PASSWORD_DEFAULT)]);$id=(int)$db->lastInsertId();$db->prepare('INSERT INTO wallets(user_id,balance) VALUES(?,0)')->execute([$id]);$db->commit();out(true,'Account created',['token'=>make_token($id),'user'=>['id'=>$id,'phone'=>$phone,'username'=>$u,'role'=>'customer']]);}
if($path==='/api/auth/login'&&$m==='POST'){ $b=body();$id=trim((string)($b['identity']??''));$pw=(string)($b['password']??'');$q=$db->prepare('SELECT id,phone,username,password_hash,role FROM users WHERE phone=? OR username=? LIMIT 1');$q->execute([$id,$id]);$u=$q->fetch();if(!$u||!password_verify($pw,$u['password_hash']))out(false,'Invalid phone/username or password',[],401);unset($u['password_hash']);out(true,'Login successful',['token'=>make_token((int)$u['id']),'user'=>$u]);}
if($path==='/api/me'&&$m==='GET'){ $q=$db->prepare('SELECT id,phone,username,role,created_at FROM users WHERE id=?');$q->execute([auth_uid()]);$u=$q->fetch();if(!$u)out(false,'User not found',[],404);out(true,'OK',['user'=>$u]);}
if($path==='/api/wallet'&&$m==='GET'){ $q=$db->prepare('SELECT balance FROM wallets WHERE user_id=?');$q->execute([auth_uid()]);out(true,'OK',['wallet'=>$q->fetch()?:['balance'=>0]]);}
if($path==='/api/transactions'&&$m==='GET'){ $q=$db->prepare('SELECT type,amount,reference,description,created_at FROM wallet_ledger WHERE user_id=? ORDER BY id DESC LIMIT 100');$q->execute([auth_uid()]);out(true,'OK',['transactions'=>$q->fetchAll()]);}
if($path==='/api/notifications'&&$m==='GET'){ $q=$db->prepare('SELECT id,title,body,is_read,created_at FROM notifications WHERE user_id=? ORDER BY id DESC LIMIT 100');$q->execute([auth_uid()]);out(true,'OK',['notifications'=>$q->fetchAll()]);}
out(false,'Endpoint not found',[],404);
