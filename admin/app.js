const API=localStorage.getItem('dc_admin_api')||'https://YOUR-DOMAIN.com/api';
let token=localStorage.getItem('dc_admin_token')||'';let me=null;
const $=s=>document.querySelector(s), money=n=>'₦'+Number(n||0).toLocaleString('en-NG',{minimumFractionDigits:2,maximumFractionDigits:2});
async function api(path,opts={}){let r=await fetch(API.replace(/\/$/,'')+'/'+path.replace(/^\//,''),{...opts,headers:{'Content-Type':'application/json',Authorization:'Bearer '+token,...(opts.headers||{})}});let d=await r.json().catch(()=>({success:false,message:'Invalid server response'}));if(r.status===401){logout();throw Error('Session expired')}if(!d.success)throw Error(d.message||'Request failed');return d}
function esc(s){return String(s??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]))}
$('#loginForm').onsubmit=async e=>{e.preventDefault();$('#loginMsg').textContent='Signing in…';try{let d=await fetch(API+'/login.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({phone:$('#phone').value,password:$('#password').value})}).then(r=>r.json());if(!d.success)throw Error(d.message);if(!['admin','staff'].includes(d.user.role))throw Error('This account is not authorized for the control center.');token=d.token;me=d.user;localStorage.setItem('dc_admin_token',token);localStorage.setItem('dc_admin_api',API);showApp();load('overview')}catch(err){$('#loginMsg').textContent=err.message}};
function showApp(){$('#login').classList.add('hidden');$('#app').classList.remove('hidden');$('#adminName').textContent=me?.username||'Authorized staff'}
function logout(){localStorage.removeItem('dc_admin_token');token='';$('#app').classList.add('hidden');$('#login').classList.remove('hidden')}
$('#logout').onclick=logout;
document.querySelectorAll('nav button').forEach(b=>b.onclick=()=>{document.querySelectorAll('nav button').forEach(x=>x.classList.remove('active'));b.classList.add('active');load(b.dataset.page)});
async function load(page){$('#title').textContent=page[0].toUpperCase()+page.slice(1);let c=$('#content');c.innerHTML='<div class="empty">Loading…</div>';try{if(page==='overview')return overview(c);if(page==='users')return users(c);if(page==='withdrawals')return withdrawals(c);if(page==='airtime')return airtime(c);if(page==='marketers')return marketers(c);if(page==='plans')return plans(c)}catch(e){c.innerHTML='<div class="card">'+esc(e.message)+'</div>'}}
async function overview(c){let d=await api('admin-dashboard.php'),x=d.counts;c.innerHTML=`<div class="cards">${[['Users',x.users],['Customers',x.customers],['Shareholders',x.shareholders],['Marketers',x.marketers],['Pending withdrawals',x.pending_withdrawals],['Pending airtime',x.pending_airtime],['Pending data',x.pending_data],['Active shares',x.active_shares]].map(a=>`<div class="card metric"><span>${a[0]}</span><strong>${Number(a[1]).toLocaleString()}</strong></div>`).join('')}</div><div class="cards" style="margin-top:14px"><div class="card metric"><span>Total wallet balance</span><strong>${money(d.total_wallet_balance)}</strong><small>Across all users</small></div><div class="card metric"><span>Today's data sales</span><strong>${money(d.today_data_sales)}</strong><small>Successful orders</small></div></div><div class="panel"><div class="panel-head"><b>Recent wallet activity</b></div><div class="table-wrap"><table><thead><tr><th>User</th><th>Type</th><th>Amount</th><th>Reference</th><th>Date</th></tr></thead><tbody>${d.recent_transactions.map(r=>`<tr><td>${esc(r.username)}<br><small>${esc(r.phone)}</small></td><td><span class="pill">${esc(r.type)}</span></td><td>${money(r.amount)}</td><td>${esc(r.reference)}</td><td>${esc(r.created_at)}</td></tr>`).join('')}</tbody></table></div></div>`}
async function users(c){c.innerHTML=`<div class="panel"><div class="panel-head"><b>All users</b><input class="search" id="userSearch" placeholder="Search username or phone"></div><div id="usersTable"><div class="empty">Loading…</div></div></div>`;async function refresh(){let d=await api('admin-users.php?q='+encodeURIComponent($('#userSearch').value));$('#usersTable').innerHTML='<div class="table-wrap"><table><thead><tr><th>User</th><th>Role</th><th>Wallet</th><th>Shares</th><th>Pending withdrawals</th><th>Action</th></tr></thead><tbody>'+d.users.map(u=>`<tr><td><b>${esc(u.username)}</b><br><small>${esc(u.phone)}</small></td><td><span class="pill">${esc(u.role)}</span></td><td>${money(u.balance)}</td><td>${u.share_count}</td><td>${u.pending_withdrawals}</td><td><button class="btn blue" onclick="adjust(${u.id},'${esc(u.username)}')">Wallet</button></td></tr>`).join('')+'</tbody></table></div>'}$('#userSearch').oninput=refresh;await refresh()}
window.adjust=async(id,name)=>{let type=prompt(`Wallet action for ${name}: enter credit or debit`);if(!['credit','debit'].includes(type))return;let amount=prompt('Amount (NGN):');let reason=prompt('Reason:');if(!amount||!reason)return;try{await api('admin-wallet-adjust.php',{method:'POST',body:JSON.stringify({user_id:id,type,amount:Number(amount),reason})});alert('Wallet updated successfully.');load('users')}catch(e){alert(e.message)}};
async function withdrawals(c){let d=await api('staff-withdrawals.php');c.innerHTML=tablePanel('Pending withdrawals',['User','Amount','Created','Action'],d.withdrawals.map(w=>[`${esc(w.username)}<br><small>${esc(w.phone)}</small>`,money(w.amount),esc(w.created_at),`<button class="btn blue" onclick="wd(${w.id},'approve')">Approve</button> <button class="btn red" onclick="wd(${w.id},'reject')">Reject</button>`]))}
window.wd=async(id,decision)=>{let reason=decision==='reject'?prompt('Reason for rejection:')||'Request rejected':'';try{await api('staff-withdrawal-decision.php',{method:'POST',body:JSON.stringify({withdrawal_id:id,decision,reason})});load('withdrawals')}catch(e){alert(e.message)}};
async function airtime(c){let d=await api('staff-airtime-requests.php');c.innerHTML=tablePanel('Pending airtime',['User','Network','Amount','Recipient','Created'],d.requests.map(r=>[`${esc(r.username)}<br><small>${esc(r.user_phone)}</small>`,esc(r.network),money(r.amount),esc(r.recipient_phone),esc(r.created_at)]))}
async function marketers(c){let d=await api('staff-marketers.php');c.innerHTML=tablePanel('Marketers',['ID','Name','Location','Monthly pay','Status','Created'],d.marketers.map(m=>[esc(m.marketer_id),esc(m.name),esc(m.location),money(m.monthly_pay),`<span class="pill">${esc(m.status||m.approval_status)}</span>`,esc(m.created_at)]))}
async function plans(c){let d=await api('admin-plans.php');c.innerHTML=`<div class="grid2"><div class="panel"><div class="panel-head"><b>Data plans</b></div><div class="table-wrap"><table><thead><tr><th>Network</th><th>Plan</th><th>Price</th><th>Status</th><th></th></tr></thead><tbody>${d.plans.map(p=>`<tr><td>${esc(p.network)}</td><td>${esc(p.plan_name)}</td><td>${money(p.amount)}</td><td>${p.active?'<span class="pill good">Active</span>':'<span class="pill bad">Off</span>'}</td><td><button class="btn" onclick="togglePlan(${p.id})">Toggle</button></td></tr>`).join('')}</tbody></table></div></div><div class="panel"><div class="panel-head"><b>Add plan</b></div><form class="form-grid" id="planForm"><label>Network<input name="network" required placeholder="MTN"></label><label>Plan name<input name="plan_name" required placeholder="1GB"></label><label>Amount<input name="amount" type="number" min="1" required></label><button class="primary">Save plan</button></form></div></div>`;$('#planForm').onsubmit=async e=>{e.preventDefault();let f=new FormData(e.target);try{await api('admin-plans.php',{method:'POST',body:JSON.stringify({action:'save',network:f.get('network'),plan_name:f.get('plan_name'),amount:Number(f.get('amount')),active:1})});load('plans')}catch(err){alert(err.message)}}}
window.togglePlan=async id=>{try{await api('admin-plans.php',{method:'POST',body:JSON.stringify({action:'toggle',id})});load('plans')}catch(e){alert(e.message)}};
function tablePanel(title,heads,rows){return `<div class="panel"><div class="panel-head"><b>${title}</b></div><div class="table-wrap"><table><thead><tr>${heads.map(h=>`<th>${h}</th>`).join('')}</tr></thead><tbody>${rows.length?rows.map(r=>`<tr>${r.map(v=>`<td>${v}</td>`).join('')}</tr>`).join(''):`<tr><td colspan="${heads.length}" class="empty">No records found.</td></tr>`}</tbody></table></div></div>`}
if(token){fetch(API+'/me.php',{headers:{Authorization:'Bearer '+token}}).then(r=>r.json()).then(d=>{if(d.success&&['admin','staff'].includes(d.user?.role)){me=d.user;showApp();load('overview')}}).catch(()=>{})}

/* Data Connect V13.7 Roles */
const DC_V13_7_ROLES = {
 CUSTOMER: "customer",
 MARKETER: "marketer",
 STAFF_SIC: "staff_sic",
 DISPENSER: "dispenser",
 ADMIN: "admin"
};


/* DataConnect balance visibility sync
 * Dashboard eye state is shared with the Data screen.
 */
(function () {
  const KEY = "dataconnect_balance_hidden";
  function isHidden() { return localStorage.getItem(KEY) === "1"; }
  function maskValue(v) {
    if (v == null) return v;
    return "••••••••";
  }
  window.DataConnectBalanceVisibility = {
    setHidden(hidden) {
      localStorage.setItem(KEY, hidden ? "1" : "0");
      document.dispatchEvent(new CustomEvent("dataconnect:balance-visibility", {
        detail: { hidden: !!hidden }
      }));
    },
    isHidden,
    display(value) { return isHidden() ? maskValue(value) : value; }
  };
  document.addEventListener("DOMContentLoaded", function () {
    document.dispatchEvent(new CustomEvent("dataconnect:balance-visibility", {
      detail: { hidden: isHidden() }
    }));
  });
})();
