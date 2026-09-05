<?php
namespace App\Http\Controllers;

use App\Services\DataConnectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;

class ApiController extends Controller
{
    public function __construct(private DataConnectService $svc) {}

    public function dispatch(Request $request, string $endpoint)
    {
        if ($request->isMethod('OPTIONS')) return response()->json([], 204);
        return match ($endpoint) {
            'health' => $this->health(),
            'register' => $this->register($request),
            'login' => $this->login($request),
            'me' => $this->me($request),
            'wallet' => $this->wallet($request),
            'transactions' => $this->transactions($request),
            'notifications' => $this->notifications($request),
            'dashboard' => $this->dashboard($request),
            'data-plans' => $this->dataPlans($request),
            'purchase-data' => $this->purchaseData($request),
            'process-data-order' => $this->processDataOrder($request),
            'refund-data' => $this->refundData($request),
            'airtime-requests' => $this->airtimeRequests($request),
            'request-airtime' => $this->requestAirtime($request),
            'share-packages' => $this->sharePackages($request),
            'share-holdings' => $this->shareHoldings($request),
            'share-returns' => $this->shareReturns($request),
            'buy-share' => $this->buyShare($request),
            'withdrawals' => $this->withdrawals($request),
            'withdrawal-request' => $this->withdrawalRequest($request),
            'marketer-apply' => $this->marketerApply($request),
            'staff-dashboard' => $this->staffDashboard($request),
            'staff-airtime-requests' => $this->staffAirtimeRequests($request),
            'staff-airtime-approve' => $this->staffAirtimeDecision($request, true),
            'staff-airtime-reject' => $this->staffAirtimeDecision($request, false),
            'staff-withdrawals' => $this->staffWithdrawals($request),
            'staff-withdrawal-decision' => $this->staffWithdrawalDecision($request),
            'staff-marketers' => $this->staffMarketers($request),
            'staff-marketer-decision' => $this->staffMarketerDecision($request),
            'post-daily-share-returns' => $this->postDailyReturns($request),
            default => $this->out(false,'Endpoint not found',[],404),
        };
    }

    private function out(bool $ok, string $message, array $data=[], int $code=200)
    {
        return response()->json(['success'=>$ok,'message'=>$message]+$data, $code);
    }

    private function user(Request $r, array $roles=[]): object
    {
        $token = $r->bearerToken();
        if (!$token) abort(response()->json(['success'=>false,'message'=>'Authentication required'],401));
        $u = DB::table('users')->where('api_token',$token)->first();
        if (!$u) abort(response()->json(['success'=>false,'message'=>'Invalid token'],401));
        if ($u->token_expires_at && now()->greaterThan($u->token_expires_at)) {
            abort(response()->json(['success'=>false,'message'=>'Session expired'],401));
        }
        if ($roles && !in_array($u->role,$roles,true)) {
            abort(response()->json(['success'=>false,'message'=>'Forbidden'],403));
        }
        return $u;
    }

    private function health()
    {
        try { DB::select('SELECT 1'); return $this->out(true,'Data Connect API and MySQL are reachable'); }
        catch (\Throwable $e) { return $this->out(false,'Database unavailable',[],503); }
    }

    private function register(Request $r)
    {
        $phone=trim((string)$r->input('phone',''));
        $username=trim((string)$r->input('username',''));
        $password=(string)$r->input('password','');
        if ($phone==='' || $username==='' || strlen($password)<6)
            return $this->out(false,'Phone, username and a password of at least 6 characters are required',[],422);
        try {
            $u=$this->svc->register($phone,$username,$password);
            $token=bin2hex(random_bytes(32));
            DB::table('users')->where('id',$u['id'])->update(['api_token'=>$token,'token_expires_at'=>now()->addDays(7)]);
            return $this->out(true,'Account created',['user_id'=>$u['id'],'token'=>$token,'user'=>$u],201);
        } catch (\Throwable $e) {
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) throw $e;
            report($e); return $this->out(false,'Unable to create account',[],500);
        }
    }

    private function login(Request $r)
    {
        $phone=trim((string)$r->input('phone',''));
        $password=(string)$r->input('password','');
        $u=DB::table('users')->where('phone',$phone)->first();
        if (!$u || !password_verify($password,$u->password_hash))
            return $this->out(false,'Invalid phone number or password',[],401);
        $token=bin2hex(random_bytes(32));
        DB::table('users')->where('id',$u->id)->update(['api_token'=>$token,'token_expires_at'=>now()->addDays(7)]);
        unset($u->password_hash, $u->api_token, $u->token_expires_at);
        return $this->out(true,'Login successful',['token'=>$token,'user'=>(array)$u]);
    }

    private function me(Request $r)
    {
        $u=$this->user($r);
        return $this->out(true,'OK',['user'=>['id'=>$u->id,'phone'=>$u->phone,'username'=>$u->username,'role'=>$u->role]]);
    }

    private function wallet(Request $r)
    {
        $u=$this->user($r);
        $w=DB::table('wallets')->where('user_id',$u->id)->first();
        return $this->out(true,'OK',['balance'=>(float)($w->balance??0),'wallet'=>['balance'=>(float)($w->balance??0)]]);
    }

    private function transactions(Request $r)
    {
        $u=$this->user($r);
        $rows=DB::table('wallet_ledger')->where('user_id',$u->id)->orderByDesc('id')->limit(100)->get();
        return $this->out(true,'OK',['transactions'=>$rows]);
    }

    private function notifications(Request $r)
    {
        $u=$this->user($r);
        $rows=DB::table('notifications')->where('user_id',$u->id)->orderByDesc('id')->limit(100)->get();
        return $this->out(true,'OK',['notifications'=>$rows]);
    }

    private function dashboard(Request $r)
    {
        $u=$this->user($r);
        $balance=(float)(DB::table('wallets')->where('user_id',$u->id)->value('balance')??0);
        $shares=(int)DB::table('share_holdings')->where('user_id',$u->id)->where('status','active')->count();
        $unread=(int)DB::table('notifications')->where('user_id',$u->id)->where('is_read',false)->count();
        return $this->out(true,'OK',['user'=>['id'=>$u->id,'phone'=>$u->phone,'username'=>$u->username,'role'=>$u->role],
            'wallet_balance'=>$balance,'active_shares'=>$shares,'unread_notifications'=>$unread]);
    }

    private function dataPlans(Request $r)
    {
        $this->user($r);
        return $this->out(true,'OK',['plans'=>DB::table('data_plans')->where('active',true)->orderBy('network')->orderBy('price')->get()]);
    }

    private function purchaseData(Request $r)
    {
        $u=$this->user($r);
        $network=trim((string)$r->input('network','')); $plan=trim((string)$r->input('plan_name',''));
        $phone=trim((string)$r->input('recipient_phone','')); $amount=(float)$r->input('amount',0);
        if ($network===''||$plan===''||$phone===''||$amount<=0)
            return $this->out(false,'Network, plan, recipient phone and a valid amount are required',[],422);
        try {
            [$orderId,$ref]=DB::transaction(function() use($u,$network,$plan,$phone,$amount) {
                $ref=$this->svc->reference('DATA');
                $this->svc->debitWallet($u->id,$amount,$ref,"Data purchase: $plan");
                $id=DB::table('data_orders')->insertGetId([
                    'user_id'=>$u->id,'network'=>$network,'plan_name'=>$plan,'amount'=>$amount,
                    'recipient_phone'=>$phone,'status'=>'pending','created_at'=>now()
                ]);
                return [$id,$ref];
            });
            return $this->out(true,'Order created and awaiting provider processing',['order_id'=>$orderId,'reference'=>$ref,'status'=>'pending']);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { throw $e; }
        catch (\Throwable $e) { report($e); return $this->out(false,'Unable to create data order',[],500); }
    }

    private function processDataOrder(Request $r)
    {
        $u=$this->user($r); $id=(int)$r->input('order_id',0);
        if ($id<=0) return $this->out(false,'Valid order_id required',[],422);
        $order=DB::table('data_orders')->where('id',$id)->where('user_id',$u->id)->first();
        if (!$order) return $this->out(false,'Order not found',[],404);
        if (!in_array($order->status,['pending','processing'],true))
            return $this->out(false,'Order cannot be processed in its current state',[],409);
        DB::table('data_orders')->where('id',$id)->where('status','pending')->update(['status'=>'processing']);
        $result=$this->svc->provider->purchaseData($order->network,$order->plan_name,$order->recipient_phone,(float)$order->amount,(string)$id);
        if (($result['success']??false)===true) {
            DB::table('data_orders')->where('id',$id)->update(['status'=>'successful','provider_reference'=>$result['provider_reference']??null]);
            $this->svc->notify($u->id,'Data purchase successful','Your '.$order->plan_name.' data purchase was completed.');
            return $this->out(true,'Data order completed',['status'=>'successful']);
        }
        if (in_array($result['status']??'', ['not_configured','adapter_pending'], true)) {
            DB::table('data_orders')->where('id',$id)->update(['status'=>'pending']);
            return $this->out(false,'Provider is not configured yet. The order remains pending.',['status'=>'pending'],503);
        }
        DB::transaction(function() use($u,$order,$id) {
            $ref=$this->svc->reference('REFUND');
            $this->svc->creditWallet($u->id,(float)$order->amount,'refund',$ref,'Automatic refund for failed data order #'.$id);
            DB::table('data_orders')->where('id',$id)->update(['status'=>'refunded']);
            $this->svc->notify($u->id,'Data purchase refunded','The provider could not complete your data order. The amount has been returned to your wallet.');
        });
        return $this->out(false,'Provider failed; wallet refund was attempted',['status'=>'refunded'],502);
    }

    private function refundData(Request $r)
    {
        $u=$this->user($r); $id=(int)$r->input('order_id',0);
        if ($id<=0) return $this->out(false,'Valid order_id required',[],422);
        $result=DB::transaction(function() use($u,$id) {
            $o=DB::table('data_orders')->where('id',$id)->where('user_id',$u->id)->lockForUpdate()->first();
            if (!$o) return ['error'=>[false,'Order not found',404]];
            if ($o->status!=='failed') return ['error'=>[false,'Only failed orders can be refunded',409]];
            $ref=$this->svc->reference('REFUND');
            $this->svc->creditWallet($u->id,(float)$o->amount,'refund',$ref,'Refund for failed data order #'.$id);
            DB::table('data_orders')->where('id',$id)->update(['status'=>'refunded']);
            return ['ok'=>true,'ref'=>$ref];
        });
        if (isset($result['error'])) return $this->out(...$result['error']);
        return $this->out(true,'Order refunded',['reference'=>$result['ref']]);
    }

    private function airtimeRequests(Request $r)
    {
        $u=$this->user($r);
        return $this->out(true,'OK',['requests'=>DB::table('airtime_requests')->where('user_id',$u->id)->orderByDesc('id')->limit(100)->get()]);
    }

    private function requestAirtime(Request $r)
    {
        $u=$this->user($r);
        $network=trim((string)$r->input('network','')); $phone=trim((string)$r->input('recipient_phone','')); $amount=(float)$r->input('amount',0);
        if ($network===''||$phone===''||$amount<=0) return $this->out(false,'Network, recipient phone and valid amount are required',[],422);
        $id=DB::table('airtime_requests')->insertGetId(['user_id'=>$u->id,'network'=>$network,'amount'=>$amount,'recipient_phone'=>$phone,'status'=>'pending','created_at'=>now()]);
        return $this->out(true,'Airtime request submitted for dispenser approval',['request_id'=>$id,'status'=>'pending'],201);
    }

    private function sharePackages(Request $r)
    {
        $this->user($r);
        return $this->out(true,'OK',['packages'=>DB::table('share_packages')->where('active',true)->orderBy('investment_amount')->get()]);
    }

    private function shareHoldings(Request $r)
    {
        $u=$this->user($r);
        $rows=DB::table('share_holdings as h')->join('share_packages as p','p.id','=','h.package_id')
            ->where('h.user_id',$u->id)->orderByDesc('h.id')
            ->select('h.id','h.package_id','h.status','h.purchased_at','p.name','p.investment_amount','p.daily_return','p.duration_days')->get();
        return $this->out(true,'OK',['holdings'=>$rows]);
    }

    private function shareReturns(Request $r)
    {
        $u=$this->user($r);
        return $this->out(true,'OK',['returns'=>DB::table('share_return_ledger')->where('user_id',$u->id)->orderByDesc('id')->limit(100)->get()]);
    }

    private function buyShare(Request $r)
    {
        $u=$this->user($r); $pid=(int)$r->input('package_id',0);
        if ($pid<=0) return $this->out(false,'Valid package_id required',[],422);
        try {
            $holding=DB::transaction(function() use($u,$pid) {
                $p=DB::table('share_packages')->where('id',$pid)->where('active',true)->lockForUpdate()->first();
                if (!$p) abort(response()->json(['success'=>false,'message'=>'Share package not found'],404));
                $ref=$this->svc->reference('SHARE');
                $this->svc->debitWallet($u->id,(float)$p->investment_amount,$ref,'Share package purchase #'.$pid);
                return DB::table('share_holdings')->insertGetId([
                    'user_id'=>$u->id,'package_id'=>$pid,'status'=>'active','purchased_at'=>now()
                ]);
            });
            return $this->out(true,'Share purchased',['holding_id'=>$holding,'reference'=>DB::table('wallet_ledger')->where('user_id',$u->id)->orderByDesc('id')->value('reference'),'status'=>'active']);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { throw $e; }
        catch (\Throwable $e) { report($e); return $this->out(false,'Unable to purchase share',[],500); }
    }

    private function withdrawals(Request $r)
    {
        $u=$this->user($r);
        return $this->out(true,'OK',['withdrawals'=>DB::table('withdrawal_requests')->where('user_id',$u->id)->orderByDesc('id')->limit(100)->get()]);
    }

    private function withdrawalRequest(Request $r)
    {
        $u=$this->user($r); $amount=(float)$r->input('amount',0);
        if (!in_array($amount,[500,1000,2000,5000,10000],true)) return $this->out(false,'Invalid withdrawal amount',[],422);
        try {
            $id=DB::transaction(function() use($u,$amount) {
                if (!DB::table('share_holdings')->where('user_id',$u->id)->where('status','active')->exists())
                    abort(response()->json(['success'=>false,'message'=>'Withdrawal is available only to active shareholders'],403));
                if (DB::table('withdrawal_requests')->where('user_id',$u->id)->where('status','pending')->exists())
                    abort(response()->json(['success'=>false,'message'=>'A withdrawal request is already pending'],409));
                $ref=$this->svc->reference('WDREQ');
                $this->svc->debitWallet($u->id,$amount,$ref,'Withdrawal reserve');
                $id=DB::table('withdrawal_requests')->insertGetId([
                    'user_id'=>$u->id,'amount'=>$amount,'status'=>'pending','reference'=>$ref,'created_at'=>now()
                ]);
                $this->svc->notify($u->id,'Withdrawal submitted','Your withdrawal request is pending staff approval.');
                return $id;
            });
            return $this->out(true,'Withdrawal request submitted',['withdrawal_id'=>$id,'reference'=>DB::table('withdrawal_requests')->where('id',$id)->value('reference'),'status'=>'pending']);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { throw $e; }
        catch (\Throwable $e) { report($e); return $this->out(false,'Unable to submit withdrawal request',[],500); }
    }

    private function marketerApply(Request $r)
    {
        $u=$this->user($r);
        $name=trim((string)$r->input('name','')); $location=trim((string)$r->input('location',''));
        $mid=trim((string)$r->input('marketer_id','')) ?: 'MK-'.strtoupper(bin2hex(random_bytes(3)));
        $gname=trim((string)$r->input('guarantor_name','')); $gphone=trim((string)$r->input('guarantor_phone',''));
        if ($name===''||$location===''||$gname===''||$gphone==='') return $this->out(false,'Required marketer and guarantor fields are missing',[],422);
        DB::table('marketers')->insert([
            'user_id'=>$u->id,'marketer_id'=>$mid,'name'=>$name,'location'=>$location,
            'monthly_pay'=>(float)$r->input('monthly_pay',0),'guarantor_name'=>$gname,'guarantor_phone'=>$gphone,
            'approval_status'=>'pending'
        ]);
        return $this->out(true,'Marketer application submitted',['marketer_id'=>$mid,'status'=>'pending'],201);
    }

    private function staffDashboard(Request $r)
    {
        $this->user($r,['dispenser','staff','admin']);
        return $this->out(true,'OK',[
            'counts'=>[
                'pending_airtime'=>(int)DB::table('airtime_requests')->where('status','pending')->count(),
                'pending_withdrawals'=>(int)DB::table('withdrawal_requests')->where('status','pending')->count(),
                'pending_marketers'=>(int)DB::table('marketers')->where('approval_status','pending')->count(),
                'pending_data'=>(int)DB::table('data_orders')->where('status','pending')->count(),
            ]
        ]);
    }

    private function staffAirtimeRequests(Request $r)
    {
        $this->user($r,['dispenser','staff','admin']);
        $rows=DB::table('airtime_requests as ar')->join('users as u','u.id','=','ar.user_id')
            ->where('ar.status','pending')->orderBy('ar.id')->limit(100)
            ->select('ar.id','ar.network','ar.amount','ar.recipient_phone','ar.status','ar.created_at','u.id as user_id','u.username','u.phone as user_phone')->get();
        return $this->out(true,'OK',['requests'=>$rows]);
    }

    private function staffAirtimeDecision(Request $r, bool $approve)
    {
        $staff=$this->user($r,['dispenser','staff','admin']); $id=(int)$r->input('request_id',0);
        if ($id<=0) return $this->out(false,'Valid request_id required',[],422);
        try {
            DB::transaction(function() use($staff,$id,$approve) {
                $row=DB::table('airtime_requests')->where('id',$id)->lockForUpdate()->first();
                if (!$row) abort(response()->json(['success'=>false,'message'=>'Request not found'],404));
                if ($row->status!=='pending') abort(response()->json(['success'=>false,'message'=>'Request is no longer pending'],409));
                DB::table('airtime_requests')->where('id',$id)->update([
                    'status'=>$approve?'approved':'rejected','dispenser_id'=>$staff->id,
                    'approved_at'=>$approve?now():null
                ]);
                $title=$approve?'Airtime request approved':'Airtime request rejected';
                $body=$approve?'Your airtime request has been approved and is being processed.':trim((string)$r->input('reason','Request rejected by staff'));
                $this->svc->notify($row->user_id,$title,$body);
                $this->svc->audit($staff->id,$approve?'approve':'reject','airtime_request',$id);
            });
            return $this->out(true,$approve?'Request approved':'Request rejected',['request_id'=>$id,'status'=>$approve?'approved':'rejected']);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { throw $e; }
        catch (\Throwable $e) { report($e); return $this->out(false,$approve?'Unable to approve request':'Unable to reject request',[],500); }
    }

    private function staffWithdrawals(Request $r)
    {
        $this->user($r,['staff','admin']);
        $rows=DB::table('withdrawal_requests as w')->join('users as u','u.id','=','w.user_id')
            ->where('w.status','pending')->orderBy('w.id')->limit(100)
            ->select('w.id','w.user_id','w.amount','w.status','w.created_at','u.username','u.phone')->get();
        return $this->out(true,'OK',['withdrawals'=>$rows]);
    }

    private function staffWithdrawalDecision(Request $r)
    {
        $staff=$this->user($r,['staff','admin']); $id=(int)$r->input('withdrawal_id',0); $decision=(string)$r->input('decision',''); $reason=trim((string)$r->input('reason',''));
        if ($id<=0 || !in_array($decision,['approve','reject'],true)) return $this->out(false,'Valid withdrawal_id and decision are required',[],422);
        try {
            return DB::transaction(function() use($staff,$id,$decision,$reason) {
                $w=DB::table('withdrawal_requests')->where('id',$id)->lockForUpdate()->first();
                if (!$w) return $this->out(false,'Withdrawal not found',[],404);
                if ($w->status!=='pending') return $this->out(false,'Withdrawal is no longer pending',[],409);
                if ($decision==='reject') {
                    $ref=$this->svc->reference('WDREF');
                    $this->svc->creditWallet($w->user_id,(float)$w->amount,'refund',$ref,'Withdrawal reserve released #'.$id);
                    DB::table('withdrawal_requests')->where('id',$id)->update(['status'=>'rejected','reviewed_by'=>$staff->id,'reviewed_at'=>now(),'reason'=>$reason]);
                    $this->svc->notify($w->user_id,'Withdrawal rejected',$reason ?: 'Your withdrawal request was rejected. The reserved amount was returned to your wallet.');
                    $this->svc->audit($staff->id,'reject','withdrawal',$id,['reason'=>$reason,'refund_reference'=>$ref]);
                    return $this->out(true,'Withdrawal rejected and funds returned',['status'=>'rejected','refund_reference'=>$ref]);
                }
                $ref=$w->reference ?: $this->svc->reference('WD');
                DB::table('wallet_ledger')->insert(['user_id'=>$w->user_id,'type'=>'withdrawal','amount'=>$w->amount,'reference'=>$ref,'description'=>'Approved shareholder withdrawal #'.$id,'created_at'=>now()]);
                DB::table('withdrawal_requests')->where('id',$id)->update(['status'=>'approved','reviewed_by'=>$staff->id,'reviewed_at'=>now(),'reference'=>$ref]);
                $this->svc->notify($w->user_id,'Withdrawal approved','Your withdrawal has been approved and is ready for payout.');
                $this->svc->audit($staff->id,'approve','withdrawal',$id,['reference'=>$ref]);
                return $this->out(true,'Withdrawal approved',['status'=>'approved','reference'=>$ref]);
            });
        } catch (\Throwable $e) { report($e); return $this->out(false,'Unable to process withdrawal',[],500); }
    }

    private function staffMarketers(Request $r)
    {
        $this->user($r,['staff','admin']);
        $rows=DB::table('marketers as m')->join('users as u','u.id','=','m.user_id')
            ->orderByDesc('m.id')->limit(200)
            ->select('m.id','m.marketer_id','m.name','m.location','m.monthly_pay','m.guarantor_name','m.guarantor_phone','m.approval_status as status','m.created_at','u.phone')->get();
        return $this->out(true,'OK',['marketers'=>$rows]);
    }

    private function staffMarketerDecision(Request $r)
    {
        $staff=$this->user($r,['staff','admin']); $id=(int)$r->input('marketer_id',0); $decision=(string)$r->input('decision','');
        if ($id<=0 || !in_array($decision,['approve','reject'],true)) return $this->out(false,'Valid marketer_id and decision are required',[],422);
        $status=$decision==='approve'?'approved':'rejected';
        $updated=DB::table('marketers')->where('id',$id)->where('approval_status','pending')->update(['approval_status'=>$status,'reviewed_by'=>$staff->id,'reviewed_at'=>now()]);
        if (!$updated) return $this->out(false,'Marketer application not found or already reviewed',[],409);
        $uid=DB::table('marketers')->where('id',$id)->value('user_id');
        $this->svc->notify($uid,'Marketer application '.$status,'Your marketer application has been '.$status.'.');
        $this->svc->audit($staff->id,$status,'marketer',$id);
        return $this->out(true,'Marketer updated',['status'=>$status]);
    }

    private function postDailyReturns(Request $r)
    {
        $actor=$this->user($r,['admin']);
        try {
            $credited=DB::transaction(function() {
                $today=now()->toDateString(); $count=0;
                $rows=DB::table('share_holdings as h')->join('share_packages as p','p.id','=','h.package_id')
                    ->where('h.status','active')->lockForUpdate()
                    ->select('h.id','h.user_id','h.purchased_at','p.daily_return','p.duration_days')->get();
                foreach($rows as $h) {
                    $days=floor((now()->timestamp-strtotime($h->purchased_at))/86400);
                    if ($days<0 || $days >= (int)$h->duration_days) continue;
                    if (DB::table('share_return_ledger')->where('holding_id',$h->id)->where('return_date',$today)->exists()) continue;
                    $ref=$this->svc->reference('SHRET');
                    $this->svc->creditWallet($h->user_id,(float)$h->daily_return,'share_return',$ref,'Daily share return for holding #'.$h->id);
                    DB::table('share_return_ledger')->insert(['holding_id'=>$h->id,'user_id'=>$h->user_id,'return_date'=>$today,'amount'=>$h->daily_return,'reference'=>$ref,'created_at'=>now()]);
                    $this->svc->notify($h->user_id,'Share return credited','Your daily share return has been credited to your Data Connect wallet.');
                    $count++;
                }
                return $count;
            });
            $this->svc->audit($actor->id,'post_daily_returns','share_return',null,['credited'=>$credited]);
            return $this->out(true,'Daily returns posted',['credited'=>$credited]);
        } catch (\Throwable $e) { report($e); return $this->out(false,'Unable to post daily returns',[],500); }
    }
}
