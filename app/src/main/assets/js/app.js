const app=document.getElementById('app');
const saved=JSON.parse(localStorage.getItem('dc_v07_state')||'null');
const state=Object.assign({page:'login',network:'MTN',plan:null,phone:'',balance:25450,balanceVisible:true,user:'',role:'Customer',loggedIn:false,notifications:3,shareholder:false,marketerApproved:false,staffOnline:true,airtimeRequests:[],orders:[]},saved||{});
function save(){localStorage.setItem('dc_v07_state',JSON.stringify(state))} function toggleBalance(){state.balanceVisible=!state.balanceVisible;save();go('home')} function balanceDisplay(){return state.balanceVisible?money(state.balance):'₦ ••••••••'} function togglePassword(){const input=document.getElementById('loginPassword');const btn=document.getElementById('togglePasswordBtn');if(!input||!btn)return;const showing=input.type==='text';input.type=showing?'password':'text';btn.textContent=showing?'Show':'Hide';btn.setAttribute('aria-label',showing?'Show password':'Hide password')}
const networks=['MTN','Airtel','Glo','9mobile'];
const dataPlans={MTN:[['500MB','₦700','7 days'],['1GB','₦1,350','30 days'],['2GB','₦2,700','30 days'],['3GB','₦4,050','30 days'],['5GB','₦6,750','30 days']],Airtel:[['500MB','₦700','7 days'],['1GB','₦1,350','30 days'],['2GB','₦2,700','30 days'],['3GB','₦4,050','30 days'],['5GB','₦6,750','30 days']],Glo:[['500MB','₦650','7 days'],['1GB','₦1,300','30 days'],['2GB','₦2,600','30 days'],['3GB','₦3,900','30 days'],['5GB','₦6,500','30 days']],['9mobile']:[['500MB','₦700','7 days'],['1GB','₦1,300','30 days'],['2GB','₦2,600','30 days'],['3GB','₦3,900','30 days'],['5GB','₦6,500','30 days']]};
const sharePackages=[['₦10,000','₦250/day','90 days'],['₦20,000','₦500/day','90 days'],['₦30,000','₦750/day','90 days'],['₦40,000','₦1,000/day','90 days'],['₦50,000','₦1,500/day','90 days'],['₦60,000','₦1,800/day','92 days']];
function backendStatus(){return `<div class="card" style="margin-top:12px"><b>Backend connection</b><div style="font-size:12px;color:var(--muted);margin-top:6px">V07.2 can use the PHP + MySQL API when an HTTPS API URL is configured. Demo mode remains available until the company provides hosting.</div></div>`}
function money(n){return '₦'+Number(n).toLocaleString('en-NG',{minimumFractionDigits:2})}
function toast(t){const x=document.createElement('div');x.className='toast';x.textContent=t;document.body.appendChild(x);requestAnimationFrame(()=>x.classList.add('show'));setTimeout(()=>{x.classList.remove('show');setTimeout(()=>x.remove(),250)},2200)}
function logo(){return `<div class="logo">DC</div>`}
function profileInitials(){const n=String(state.user||'Data Connect User').trim();return n.split(/\s+/).slice(0,2).map(x=>x[0]||'').join('').toUpperCase()||'DC'}
function profilePictureButton(){return `<button class="profile-picture-btn" onclick="go('account')" aria-label="Open profile" title="Profile">${state.profilePicture?`<img src="${esc(state.profilePicture)}" alt="Profile picture">`:`<span>${esc(profileInitials())}</span>`}</button>`}
function header(title,sub=''){return `<div class="back"><button onclick="go('home')">←</button><div><b>${title}</b>${sub?`<div style="font-size:11px;color:var(--muted);margin-top:2px">${sub}</div>`:''}</div></div>`}
function bottom(active='home'){return `<div class="bottom"><button class="nav ${active==='home'?'active':''}" onclick="go('home')"><i>⌂</i>Home</button><button class="nav ${active==='data'?'active':''}" onclick="go('data')"><i>◉</i>Data</button><button class="nav ${active==='airtime'?'active':''}" onclick="go('airtime')"><i>▣</i>Airtime</button><button class="nav ${active==='wallet'?'active':''}" onclick="go('wallet')"><i>▱</i>Wallet</button><button class="nav ${active==='account'?'active':''}" onclick="go('account')"><i>●</i>Account</button></div>`}
function showScreenLoader(message='Loading...'){
  let e=document.getElementById('dc-screen-loader');
  if(!e){
    e=document.createElement('div'); e.id='dc-screen-loader'; e.className='dc-screen-loader';
    e.innerHTML='<div class="dc-loader-card"><div class="dc-loader-logo">DC</div><div class="dc-loader-spinner"></div><div class="dc-loader-title">Data Connect</div><div class="dc-loader-message"></div><div class="dc-loader-bar"><span></span></div></div>';
    document.body.appendChild(e);
  }
  e.querySelector('.dc-loader-message').textContent=message;
  requestAnimationFrame(()=>e.classList.add('show'));
}
function hideScreenLoader(){const e=document.getElementById('dc-screen-loader');if(!e)return;e.classList.remove('show');setTimeout(()=>e.remove(),180)}
function go(p){state.page=p;save();showScreenLoader('Loading '+(p==='home'?'dashboard':p.replace(/[-_]/g,' '))+'...');setTimeout(()=>{render();setTimeout(hideScreenLoader,120)},180)}
function biometricAvailable(){try{return typeof AndroidBiometric!=='undefined'&&AndroidBiometric.isAvailable()}catch(e){return false}}
function biometricEnabled(){try{return biometricAvailable()&&AndroidBiometric.isEnabled()}catch(e){return false}}
function enableBiometricAfterLogin(token){try{if(token&&biometricAvailable()&&!biometricEnabled()){window.onNativeBiometricEnabled=function(ok){if(ok)toast('Fingerprint login enabled');};AndroidBiometric.enable(token)}}catch(e){}}
function biometricLogin(){try{if(!biometricEnabled())return toast('Log in with your password first to enable fingerprint login');window.onNativeBiometricResult=function(ok,token){if(!ok||!token)return toast('Fingerprint authentication cancelled or failed');DC_API.token=token;localStorage.setItem('dc_auth_token',token);state.loggedIn=true;save();toast('Fingerprint login successful');refreshBackendData().finally(()=>go('home'))};AndroidBiometric.authenticate()}catch(e){toast('Fingerprint login is not available on this device')}}

function auth(){const signup=state.page==='signup';app.innerHTML=`<main class="shell"><section class="auth"><div class="brand">${logo()}<div><strong>DATA CONNECT</strong><small>Smart Way to Buy Data</small></div></div><div class="authbox"><h1>${signup?'Create account':'Welcome back'}</h1><p>${signup?'Register with your phone number and username.':'Sign in securely to your Data Connect account.'}</p><div class="form">${signup?`<label class="label">Username</label><input id="username" class="input" placeholder="Enter username">`:''}<label class="label">Phone number</label><input id="loginPhone" class="input" inputmode="numeric" placeholder="08012345678"><label class="label" style="margin-top:14px">Password</label><div style="position:relative"><input id="loginPassword" class="input" type="password" placeholder="Enter password" style="padding-right:72px"><button type="button" id="togglePasswordBtn" onclick="togglePassword()" aria-label="Show password" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);border:0;background:transparent;cursor:pointer;font-size:12px;font-weight:700;padding:8px;color:var(--blue)">Show</button></div><button class="primary full" onclick="doLogin(${signup})">${signup?'Create Account':'Login'}</button>${!signup&&biometricEnabled()?`<button type="button" class="ghost full biometric-login" onclick="biometricLogin()">◉  Login with fingerprint</button>`:''}</div><div class="switch">${signup?'Already have an account?':'New to Data Connect?'} <b onclick="go('${signup?'login':'signup'}')">${signup?'Login':'Create account'}</b></div></div></section></main>`}
async function submitDataPurchase(){
  const p=dataPlans[state.network][state.plan??1];
  if(!state.phone || state.phone.length<10) return toast('Enter a valid recipient number');
  try {
    if(DC_API.base() && (DC_API.token || localStorage.getItem('dc_auth_token') || localStorage.getItem('data_connect_token'))){
      const result=await DC_API.purchaseData(state.network,p[0],Number(p[1].replace(/[^0-9.]/g,'')),state.phone);
      state.lastOrder=result;
      await refreshBackendData();
      save();
      return go('success');
    }
    go('pending');
    setTimeout(()=>go('success'),1200);
  } catch(e){ errorState(e.message||'Unable to create data order. Please try again.'); }
}
async function doLogin(signup){
  const phone=document.getElementById('loginPhone').value.trim();
  const password=document.getElementById('loginPassword').value.trim();
  if(phone.length<10||password.length<6)return toast('Enter a valid phone and a 6+ character password');
  try{
    if(DC_API.base()){
      const result=signup
        ? await DC_API.register(phone,document.getElementById('username').value.trim(),password)
        : await DC_API.login(phone,password);
      localStorage.setItem('dc_auth_token',result.token||'');
      state.user=result.user?.username||result.user?.phone||'Data Connect User';
      state.phone=result.user?.phone||phone;
      state.role=result.user?.role||'customer';
      state.loggedIn=true;
      save();
      toast(signup?'Account created successfully':'Login successful');
      await refreshBackendData();
      return go('home');
    }
    if(signup){
      const username=document.getElementById('username').value.trim();
      if(username.length<2)return toast('Enter a username');
      state.user=username;
      state.phone=phone;
      toast('Demo account created');
    }else{
      state.user=state.user||'Data Connect User';
      state.phone=phone;
      toast('Demo login successful');
    }
    state.loggedIn=true; save(); go('home');
  }catch(e){errorState(e.message||'Unable to connect to the Data Connect service.');}
}
async function refreshBackendData(){
  if(!DC_API.base()||!(localStorage.getItem('dc_auth_token')||localStorage.getItem('data_connect_token')))return;
  try{
    const [me,wallet]=await Promise.all([DC_API.me(),DC_API.wallet()]);
    state.user=me.user?.username||state.user;
    state.phone=me.user?.phone||state.phone;
    state.role=me.user?.role||state.role;
    state.balance=Number(wallet.wallet?.balance||0);
    save();
  }catch(e){console.warn(e)}
}
function normalizedRole(){return String(state.role||'customer').trim().toLowerCase().replace(/[_-]+/g,' ')}
function hasRole(){const roles=[...arguments].map(x=>x.toLowerCase());return roles.includes(normalizedRole())}
function canSeeMarketer(){return hasRole('marketer')}
function canSeeStaff(){return hasRole('staff','sic','staff/sic')}
function canSeeDispenser(){return hasRole('dispenser','data dispenser')}
function privilegedServices(){let x='';if(canSeeMarketer())x+=`<button class="service" onclick="go('marketer')"><span>🪪</span><b>Marketer</b></button>`;if(canSeeStaff())x+=`<button class="service" onclick="go('staff')"><span>🛡️</span><b>SIC / Staff</b></button>`;if(canSeeDispenser())x+=`<button class="service" onclick="go('dispenser')"><span>🧾</span><b>Dispenser</b></button>`;return x}
function home(){app.innerHTML=`<main class="shell"><section class="screen"><div class="top"><div class="brand">${logo()}<div><strong>DATA CONNECT</strong><small>Smart Way to Buy Data</small></div></div><div class="top-actions"><button class="iconbtn" onclick="go('notifications')" aria-label="Notifications">🔔 <sup>${state.notifications}</sup></button>${profilePictureButton()}</div></div><div class="hero"><div class="balance-head"><div class="eyebrow">Available Balance</div></div><div class="balance-row"><div class="balance">${balanceDisplay()}</div><button class="balance-eye" onclick="toggleBalance()" aria-label="${state.balanceVisible?'Hide balance':'Show balance'}" title="${state.balanceVisible?'Hide balance':'Show balance'}">${state.balanceVisible?'◉':'○'}</button></div><div class="hero-actions"><button class="whitebtn" onclick="toast('Wallet funding flow ready')">＋ Add Money</button><button class="whitebtn" onclick="go('withdraw')">↗ Withdraw</button></div></div><div class="section"><div class="sectionhead"><h2>Quick Services</h2><a onclick="go('transactions')">History</a></div><div class="grid"><button class="service" onclick="go('data')"><span>📡</span><b>Data Center</b></button><button class="service" onclick="go('airtime')"><span>📱</span><b>Airtime</b></button><button class="service" onclick="go('wallet')"><span>👛</span><b>Wallet</b></button><button class="service" onclick="go('shares')"><span>📈</span><b>Shares</b></button><button class="service" onclick="go('withdraw')"><span>💸</span><b>Withdrawal</b></button>${privilegedServices()}<button class="service" onclick="go('support')"><span>🎧</span><b>Customer Care</b></button></div></div><div class="section"><div class="sectionhead"><h2>Company Services</h2><a onclick="go('about')">View</a></div><div class="notice">Data Connect combines data, airtime, wallet, shareholder and marketer services in one mobile experience.</div></div><div class="section"><div class="sectionhead"><h2>Company Status</h2><a onclick="go('about')">Details</a></div><div class="notice"><b>V12 Progress:</b> Customer services, shareholder packages, Section B marketer profile, dispenser workflow, SIC staff chat and customer care are included in this demo. Live wallet ledger, transaction integrity and provider refund handling are now wired in V12.</div></div><div class="section"><div class="sectionhead"><h2>Recent Transactions</h2><a onclick="go('transactions')">See all</a></div><div class="list"><div class="row"><div class="ico">📡</div><div class="rowmain"><b>Data Purchase</b><small>MTN · 1GB · Today</small></div><div class="amount negative">−₦1,350</div></div><div class="row"><div class="ico">💰</div><div class="rowmain"><b>Wallet Funding</b><small>Bank transfer · Yesterday</small></div><div class="amount positive">+₦5,000</div></div></div></div></section>${bottom('home')}</main>`}
function data(){app.innerHTML=`<main class="shell"><section class="screen">${header('Data Center','Smart way to buy data')}<div class="hero"><div class="eyebrow">Wallet balance</div><div class="balance">${money(state.balance)}</div><div class="eyebrow">Choose a network</div></div><div class="cards">${networks.map((n,i)=>`<button class="option" onclick="state.network='${n}';go('plans')"><div class="bigico">${['🟡','🔴','🟢','🔵'][i]}</div><div><strong>${n}</strong><small>View available plans</small></div><div class="price">›</div></button>`).join('')}</div></section>${bottom('data')}</main>`}
function planSelection(){app.innerHTML=`<main class="shell"><section class="screen">${header(state.network+' Data','Choose a plan')}<div class="pillbar"><span class="pill active">All Plans</span><span class="pill">Daily</span><span class="pill">Weekly</span><span class="pill">Monthly</span></div><div class="cards">${dataPlans[state.network].map((p,i)=>`<button class="option" onclick="state.plan=${i};go('recipient')"><div class="bigico">📦</div><div><strong>${p[0]}</strong><small>${p[2]} validity</small></div><div class="price">${p[1]}</div></button>`).join('')}</div></section></main>`}
function recipient(){const p=dataPlans[state.network][state.plan??1];app.innerHTML=`<main class="shell"><section class="screen">${header('Recipient Number','Who should receive the data?')}<div class="form"><label class="label">Phone number</label><input id="phone" class="input" inputmode="numeric" maxlength="11" placeholder="08012345678" value="${state.phone}"><small class="sub">${state.network} · ${p[0]} · ${p[2]}</small><button class="primary full" onclick="state.phone=document.getElementById('phone').value;if(state.phone.length<10)return toast('Enter a valid phone number');go('summary')">Continue</button></div></section></main>`}
function summary(){const p=dataPlans[state.network][state.plan??1];app.innerHTML=`<main class="shell"><section class="screen">${header('Order Summary','Review before purchase')}<div class="summary"><div class="sumrow"><span>Network</span><b>${state.network}</b></div><div class="sumrow"><span>Data plan</span><b>${p[0]}</b></div><div class="sumrow"><span>Recipient</span><b>${state.phone}</b></div><div class="sumrow"><span>Validity</span><b>${p[2]}</b></div><div class="sumrow"><span>Wallet balance</span><b>${money(state.balance)}</b></div><div class="sumrow total"><span>Total</span><b>${p[1]}</b></div></div><button class="primary full" onclick="go('confirm')">Continue to Confirm</button><button class="ghost full" onclick="go('recipient')">Edit</button></section></main>`}
function confirm(){const p=dataPlans[state.network][state.plan??1];app.innerHTML=`<main class="shell"><section class="screen">${header('Confirm Purchase','Secure transaction')}<div class="state"><div class="stateico">🔐</div><h2>Confirm purchase?</h2><p class="sub">${p[0]} ${state.network} data for <b>${state.phone}</b> at <b>${p[1]}</b>.</p><button class="primary full" onclick="submitDataPurchase()">Confirm Purchase</button><button class="ghost full" onclick="go('summary')">Cancel</button></div></section></main>`}
function pending(){app.innerHTML=`<main class="shell"><section class="screen"><div class="state"><div class="stateico pulse">📡</div><h2>Processing purchase</h2><p class="sub">Connecting to the Data Center service…</p><div class="summary"><div class="sumrow"><span>Reference</span><b>DC-${Date.now().toString().slice(-8)}</b></div><div class="sumrow"><span>Status</span><b>Processing</b></div></div></div></section></main>`}
function success(){const p=dataPlans[state.network][state.plan??1];const o=state.lastOrder||{};app.innerHTML=`<main class="shell"><section class="screen"><div class="state"><div class="stateico">✓</div><h2>Transaction Successful</h2><p class="sub">${o.message||'Your transaction has been recorded successfully. Delivery status is shown below.'}</p><div class="summary"><div class="sumrow"><span>Amount</span><b>${p[1]}</b></div><div class="sumrow"><span>Network</span><b>${state.network}</b></div><div class="sumrow"><span>Recipient</span><b>${state.phone}</b></div><div class="sumrow"><span>Reference</span><b>${o.reference||('DC-'+Date.now().toString().slice(-8))}</b></div><div class="sumrow"><span>Status</span><b class="positive">${o.status||'Submitted'}</b></div></div><button class="primary full" onclick="go('notifications')">View Notification</button><button class="ghost full" onclick="go('home')">Done</button></div></section></main>`}
function airtime(){const selectedNetwork=state.airNetwork||'MTN';const selectedAmount=state.airAmount||'₦500';app.innerHTML=`<main class="shell"><section class="screen">${header('Airtime','Fast and secure airtime purchase')}<div class="notice"><b>Dispenser approval:</b> Every airtime sale is submitted for manual review before the client is credited.</div><div class="form"><label class="label">Network</label>${customDropdown('airNetworkDropdown',selectedNetwork,networks.map((n,i)=>({value:n,label:n,icon:['🟡','🔴','🟢','🔵'][i]})),'selectAirtimeNetwork')}<label class="label airtime-label-gap">Recipient phone number</label><input id="airPhone" class="input" inputmode="numeric" maxlength="11" placeholder="08012345678"><label class="label airtime-label-gap">Amount</label>${customDropdown('airAmountDropdown',selectedAmount,['₦500','₦1,000','₦2,000','₦5,000','₦10,000'].map(v=>({value:v,label:v,icon:'₦'})),'selectAirtimeAmount')}<button class="primary full" onclick="submitAirtime()">Submit for Approval</button></div></section>${bottom('airtime')}</main>`}
function customDropdown(id,selected,options,handler){const current=options.find(o=>o.value===selected)||options[0];return `<div class="custom-select" id="${id}"><button type="button" class="select-trigger" aria-haspopup="listbox" aria-expanded="false" onclick="toggleDropdown('${id}')"><span class="select-value">${current.icon?`<span class="select-icon">${current.icon}</span>`:''}<span><b>${esc(current.label)}</b>${id==='airNetworkDropdown'?'<small>Choose your mobile network</small>':'<small>Select purchase amount</small>'}</span></span><span class="select-chevron">⌄</span></button><div class="select-menu" role="listbox">${options.map(o=>`<button type="button" class="select-option ${o.value===selected?'selected':''}" role="option" aria-selected="${o.value===selected}" onclick="${handler}('${o.value.replace(/'/g,"\\'")}')"><span class="option-icon">${o.icon||''}</span><span><b>${esc(o.label)}</b>${id==='airNetworkDropdown'?'<small>Available for airtime purchase</small>':''}</span>${o.value===selected?'<span class="option-check">✓</span>':''}</button>`).join('')}</div></div>`}
function toggleDropdown(id){const target=document.getElementById(id);if(!target)return;document.querySelectorAll('.custom-select.open').forEach(x=>{if(x!==target)x.classList.remove('open')});target.classList.toggle('open');const trigger=target.querySelector('.select-trigger');trigger.setAttribute('aria-expanded',target.classList.contains('open')?'true':'false')}
function closeDropdowns(){document.querySelectorAll('.custom-select.open').forEach(x=>{x.classList.remove('open');x.querySelector('.select-trigger')?.setAttribute('aria-expanded','false')})}
function selectAirtimeNetwork(value){state.airNetwork=value;save();closeDropdowns();airtime()}
function selectAirtimeAmount(value){state.airAmount=value;save();closeDropdowns();airtime()}
function submitAirtime(){const phone=document.getElementById('airPhone').value.trim();if(phone.length<10)return toast('Enter a valid phone number');state.airtimeRequests.push({network:state.airNetwork||'MTN',phone,amount:state.airAmount||'₦500',status:'Pending'});save();toast('Sent to dispenser');go('airtimePending')}
function airtimePending(){app.innerHTML=`<main class="shell"><section class="screen">${header('Airtime Approval','Dispenser review')}<div class="state"><div class="stateico">⏳</div><h2>Awaiting dispenser approval</h2><p class="sub">Your airtime request is queued. A dispenser can review it from the staff console.</p><button class="primary full" onclick="go('dispenser')">Open Dispenser Console</button></div></section></main>`}
function airtimeSuccess(){app.innerHTML=`<main class="shell"><section class="screen"><div class="state"><div class="stateico">✓</div><h2>Airtime Credited</h2><p class="sub">Data Connect has recorded the approved airtime credit. The client notification is generated automatically.</p><div class="notice">📩 <b>Client message:</b> Data Connect has credited your airtime amount. Your dispenser has completed the transaction.</div><button class="primary full" onclick="go('notifications')">View Notification</button></div></section></main>`}
async function wallet(){await refreshBackendData();let tx=[];try{if(DC_API.base()&&DC_API.token)tx=(await DC_API.transactions()).transactions||[]}catch(e){}if(!tx.length)tx=state.walletTransactions||[];app.innerHTML=`<main class="shell"><section class="screen">${header('Wallet','Balance & transaction ledger')}<div class="hero"><div class="eyebrow">Available Balance</div><div class="balance">${money(state.balance)}</div><div class="hero-actions"><button class="whitebtn" onclick="fundWalletDemo()">＋ Add Money</button><button class="whitebtn" onclick="go('withdraw')">↗ Withdraw</button></div></div><div class="section"><div class="sectionhead"><h2>Recent wallet activity</h2><a onclick="go('transactions')">See all</a></div><div class="section list">${tx.length?tx.slice(0,6).map(txRow).join(''):`<div class="state"><div class="stateico">₦</div><h2>No transactions yet</h2><p class="sub">Your data, airtime, funding, earnings and withdrawal records will appear here.</p></div>`}</div></div></section>${bottom('wallet')}</main>`}
function fundWalletDemo(){const amount=5000;state.balance=Number(state.balance||0)+amount;state.walletTransactions=state.walletTransactions||[];state.walletTransactions.unshift({type:'Wallet Funding',description:'Demo wallet funding',amount:amount,status:'Successful',reference:'DC-FUND-'+Date.now().toString().slice(-8),created_at:new Date().toISOString()});state.notifications=(state.notifications||0)+1;save();toast('₦5,000 added in demo mode');wallet()}
function txRow(t){const positive=['credit','refund','share_return'].includes(t.type);return `<div class="row"><div class="ico">${positive?'＋':'−'}</div><div class="rowmain"><b>${esc(t.description||t.type)}</b><small>${esc(t.reference||'')} · ${esc(t.created_at||'')}</small></div><div class="amount ${positive?'positive':'negative'}">${positive?'+':'−'}${money(t.amount)}</div></div>`}
function esc(v){return String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]||c))}
async function transactions(){let tx=[];try{if(DC_API.base()&&DC_API.token)tx=(await DC_API.transactions()).transactions||[]}catch(e){}if(!tx.length)tx=state.walletTransactions||[];app.innerHTML=`<main class="shell"><section class="screen">${header('Transactions','Wallet activity')}<div class="pillbar"><span class="pill active">All</span><span class="pill">Credits</span><span class="pill">Debits</span><span class="pill">Withdrawals</span></div><div class="section list">${tx.length?tx.map(txRow).join(''):`<div class="state"><div class="stateico">📄</div><h2>No transaction history</h2><p class="sub">Completed wallet activity will appear here.</p></div>`}</div></section>${bottom('wallet')}</main>`}
async function shares(){
  let packages=[];
  try{
    if(DC_API.base()&&DC_API.token) packages=(await DC_API.shares()).packages||[];
  }catch(e){}
  if(!packages.length){
    packages=sharePackages.map((x,i)=>({
      id:i+1,
      investment_amount:Number(x[0].replace(/[^0-9]/g,'')),
      daily_amount:Number(x[1].replace(/[^0-9]/g,'')),
      duration_days:Number(x[2].split(' ')[0])
    }));
  }
  const active=!!state.shareholder;
  const total=p=>Number(p.investment_amount)+Number(p.daily_amount)*Number(p.duration_days);
  app.innerHTML=`<main class="shell"><section class="screen">
    ${header('Shares','Shareholder packages')}
    <div class="notice">Package figures shown below are configured company terms for the app interface. They are not a guarantee of returns. Live eligibility, funding, payouts and withdrawal approvals must be controlled by the production backend and applicable company requirements.</div>
    ${active?`<div class="shareholderCard"><div><small>Shareholder status</small><b>Active shareholder</b></div><span class="tag shareActive">Eligible</span></div>`:''}
    <div class="section"><div class="sectionhead"><h2>Available Packages</h2><span class="sub">${packages.length} packages</span></div>
      <div class="cards shareGrid">${packages.map((p,i)=>`<div class="package sharePackage">
        <div class="shareTop"><span class="shareBadge">PACKAGE ${i+1}</span><span class="tag">${p.duration_days} DAYS</span></div>
        <div class="amountbig">${money(p.investment_amount)}</div>
        <div class="daily">${money(p.daily_amount)} / day</div>
        <div class="shareRows">
          <div><span>Duration</span><b>${p.duration_days} days</b></div>
          <div><span>Daily amount</span><b>${money(p.daily_amount)}</b></div>
          <div><span>Scheduled total*</span><b>${money(total(p))}</b></div>
        </div>
        <button class="primary full" onclick="buyShareNow(${p.id})">Select Package</button>
      </div>`).join('')}</div>
    </div>
    <div class="summary">
      <div class="sumrow"><span>Withdrawal access</span><b>${active?'Available':'Shareholder required'}</b></div>
      <div class="sumrow"><span>Withdrawal options</span><b>₦500 · ₦1,000 · ₦2,000 · ₦5,000 · ₦10,000</b></div>
    </div>
    <p class="sub" style="margin-top:12px">*Scheduled total is an interface calculation from the configured package figures; actual settlement is determined by the company backend.</p>
    <button class="ghost full" onclick="go('withdraw')">View Withdrawal</button>
  </section></main>`;
}
async function buyShareNow(id){
  const selected=sharePackages[id-1];
  if(!selected)return toast('Package unavailable');
  if(!confirm('Continue with this shareholder package?'))return;
  try{
    if(typeof showDataConnectLoading==='function')showDataConnectLoading('Processing package...');
    if(DC_API.base()&&DC_API.token){
      const r=await DC_API.buyShare(id);
      state.shareholder=true;
      await refreshBackendData();
      save();
      if(typeof hideDataConnectLoading==='function')hideDataConnectLoading();
      toast(r.message||'Package request submitted');
      return go('wallet');
    }
    state.shareholder=true;
    state.selectedSharePackage=id;
    save();
    setTimeout(()=>{
      if(typeof hideDataConnectLoading==='function')hideDataConnectLoading();
      toast('Demo package selected');
      go('shares');
    },700);
  }catch(e){
    if(typeof hideDataConnectLoading==='function')hideDataConnectLoading();
    toast(e.message||'Unable to process package');
  }
}
function withdraw(){app.innerHTML=`<main class="shell"><section class="screen">${header('Withdrawal','Shareholders only')}<div class="notice">🔒 Only active shareholders can request withdrawal. The backend reserves the amount until staff approves or rejects it.</div>${state.shareholder?`<div class="tag successTag">Eligible shareholder</div>`:`<div class="tag">Not yet a shareholder</div>`}<div class="form"><label class="label">Withdrawal amount</label><select id="wdAmount" class="input"><option value="500">₦500</option><option value="1000">₦1,000</option><option value="2000">₦2,000</option><option value="5000">₦5,000</option><option value="10000">₦10,000</option></select><label class="label" style="margin-top:14px">Bank account number</label><input id="wdAccount" class="input" inputmode="numeric" placeholder="10-digit account number"><label class="label" style="margin-top:14px">Bank</label><input id="wdBank" class="input" placeholder="Bank name"><button class="primary full" onclick="submitWithdrawal()">Request Withdrawal</button></div></section></main>`}
async function submitWithdrawal(){if(!state.shareholder)return toast('Purchase a shareholder package first');const account=document.getElementById('wdAccount').value.trim(),bank=document.getElementById('wdBank').value.trim();if(!/^\d{10}$/.test(account)||!bank)return toast('Enter a valid 10-digit account and bank');try{if(DC_API.base()&&DC_API.token){const r=await DC_API.withdrawalRequest(Number(document.getElementById('wdAmount').value));await refreshBackendData();toast(r.message||'Withdrawal submitted');return go('transactions')}toast('Demo withdrawal submitted')}catch(e){toast(e.message||'Unable to submit withdrawal')}}
function marketer(){if(!canSeeMarketer()){toast('Marketer access is assigned by admin');return go('home')}app.innerHTML=`<main class="shell"><section class="screen">${header('Marketer','Section B profile')}<div class="profile"><div class="avatar">M</div><div><b>Marketer Profile</b><div class="tag">Pending approval</div></div></div><div class="notice">Eligibility rule: account must meet the company's required balance level of ₦3,250 or ₦7,000 and at least 12GB, subject to final company policy.</div><div class="form"><label class="label">Marketer ID</label><input class="input" placeholder="Assigned by admin"><label class="label" style="margin-top:14px">Name</label><input class="input" placeholder="Full name"><label class="label" style="margin-top:14px">Location</label><input class="input" placeholder="City / area"><label class="label" style="margin-top:14px">Monthly pay</label><input class="input" inputmode="numeric" placeholder="₦0.00"><label class="label" style="margin-top:14px">Upload picture</label><input class="input" type="file" accept="image/*"><label class="label" style="margin-top:14px">Guarantor name</label><input class="input" placeholder="Guarantor full name"><label class="label" style="margin-top:14px">Guarantor number</label><input class="input" inputmode="tel" placeholder="08012345678"><button class="primary full" onclick="state.marketerApproved=true;save();toast('Marketer application saved');go('marketerStatus')">Submit Marketer Profile</button></div></section></main>`}
function marketerStatus(){app.innerHTML=`<main class="shell"><section class="screen">${header('Marketer Status','Section B')}<div class="state"><div class="stateico">🪪</div><h2>Application received</h2><p class="sub">Your marketer profile is saved. Staff approval, account threshold and 12GB eligibility will be checked by the backend.</p><div class="summary"><div class="sumrow"><span>Profile</span><b>Submitted</b></div><div class="sumrow"><span>Approval</span><b>Pending</b></div><div class="sumrow"><span>Balance rule</span><b>₦3,250 / ₦7,000</b></div><div class="sumrow"><span>Data rule</span><b>Minimum 12GB</b></div></div></div></section></main>`}
function staff(){if(!canSeeStaff()){toast('Staff/SIC access is assigned by admin');return go('home')}app.innerHTML=`<main class="shell"><section class="screen">${header('SIC / Staff Chat','Work communication')}<div class="notice">🕘 Staff chat is intended for configured working hours only. Final company hours must be set by an administrator.</div><div class="profile"><div class="avatar">S</div><div><b>Staff support</b><div class="tag">${state.staffOnline?'Online during demo':'Outside work hours'}</div></div></div><div class="chat"><div class="bubble staff">Hello. Staff work chat is available during approved working hours.</div><div class="bubble me">I need help checking a customer transaction.</div></div><div class="form"><input class="input" placeholder="Type work message..."><button class="primary full" onclick="toast('Staff message sent')">Send</button></div></section></main>`}
function dispenser(){if(!canSeeDispenser()){toast('Dispenser access is assigned by admin');return go('home')}const pending=state.airtimeRequests.filter(x=>x.status==='Pending');app.innerHTML=`<main class="shell"><section class="screen">${header('Dispenser Console','Manual airtime approval')}<div class="notice">Only authorized dispensers should approve airtime sales. Production will enforce staff authentication and permissions on the backend.</div><div class="cards">${pending.length?pending.map((r,i)=>`<div class="option"><div class="bigico">📱</div><div style="flex:1"><strong>${r.network} · ${r.amount}</strong><small>${r.phone}</small></div><button class="primary" onclick="approveAirtime(${state.airtimeRequests.indexOf(r)})">Approve</button></div>`).join(''):`<div class="state"><div class="stateico">✓</div><h2>No pending sales</h2><p class="sub">All submitted airtime requests have been reviewed.</p></div>`}</div><button class="ghost full" onclick="go('staff')">SIC / Staff Chat</button></section></main>`}
function approveAirtime(i){state.airtimeRequests[i].status='Approved';state.notifications=(state.notifications||0)+1;save();toast('Airtime approved and client notification queued');go('airtimeSuccess')}
function notifications(){
const hasNotifications=Number(state.notifications||0)>0;
app.innerHTML=`<main class="shell"><section class="screen">${header('Notifications','Data Connect alerts')}${
hasNotifications
? `<div class="section list"><div class="row"><div class="ico">📩</div><div class="rowmain"><b>Data Connect credit notification</b><small>Your credited amount has been recorded.</small></div></div><div class="row"><div class="ico">✓</div><div class="rowmain"><b>Data purchase submitted</b><small>Transaction has been sent for processing.</small></div></div><div class="row"><div class="ico">🛡️</div><div class="rowmain"><b>Account security</b><small>Keep your password private.</small></div></div></div>`
: `<div class="state"><div class="stateico">🔔</div><h2>No Notifications</h2><p class="sub">You have no new Data Connect updates.</p><button class="primary full" onclick="go('home')">Back to Home</button></div>`
}</section></main>`}
function support(){app.innerHTML=`<main class="shell"><section class="screen">${header('Customer Care','Help and support')}<div class="notice">Customer Care is available for account, data, airtime and wallet support.</div><div class="chat"><div class="bubble staff">Welcome to Data Connect Customer Care. How can we help?</div><div class="bubble me">I need help with a transaction.</div><div class="bubble staff">Please send your transaction reference and we will check it.</div></div><div class="form"><input class="input" placeholder="Type your message..."><button class="primary full" onclick="toast('Message sent to Customer Care')">Send Message</button></div></section></main>`}
function account(){
  const biometricOn=biometricEnabled();
  const darkOn=localStorage.getItem('dc_dark_mode')==='1';
  const email=state.email||'Not added yet';
  const phone=state.phone||'Not added yet';
  const fullName=state.fullName||state.user||'Data Connect User';
  app.innerHTML=`<main class="shell"><section class="screen">${header('Account','Manage your profile, security and preferences')}
    <div class="account-profile-card">
      <div class="account-avatar">${state.profilePicture?`<img src="${esc(state.profilePicture)}" alt="Profile picture">`:`<span>${esc(profileInitials())}</span>`}</div>
      <div class="account-profile-info"><b>${esc(fullName)}</b><div class="tag">${esc(state.role||'Customer')}</div><small>${esc(phone)}</small></div>
      <button class="mini-action" onclick="toast('Profile editor ready')">Edit</button>
    </div>

    <div class="section account-section"><h3>👤 Profile</h3><div class="list settings">
      <div class="row" onclick="toast('Profile editor ready')"><div class="ico">👤</div><div class="rowmain"><b>Profile picture</b><small>Change your profile photo</small></div><b>›</b></div>
      <div class="row" onclick="toast('Profile editor ready')"><div class="ico">🪪</div><div class="rowmain"><b>Full name</b><small>${esc(fullName)}</small></div><b>›</b></div>
      <div class="row" onclick="toast('Profile editor ready')"><div class="ico">✉️</div><div class="rowmain"><b>Email</b><small>${esc(email)}</small></div><b>›</b></div>
      <div class="row" onclick="toast('Profile editor ready')"><div class="ico">📱</div><div class="rowmain"><b>Phone number</b><small>${esc(phone)}</small></div><b>›</b></div>
      <div class="row" onclick="toast('Profile editor ready')"><div class="ico">✏️</div><div class="rowmain"><b>Edit Profile</b><small>Update your personal details</small></div><b>›</b></div>
    </div></div>

    <div class="section account-section"><h3>🔐 Security</h3><div class="list settings">
      <div class="row" onclick="toast('Password change flow ready')"><div class="ico">🔑</div><div class="rowmain"><b>Change Password</b><small>Update your account password</small></div><b>›</b></div>
      <div class="row"><div class="ico">👆</div><div class="rowmain"><b>Fingerprint / Biometric Login</b><small>${biometricOn?'Enabled':'Enable secure fingerprint sign-in'}</small></div><button class="switch ${biometricOn?'on':''}" onclick="toggleBiometricSetting(event)"><span></span></button></div>
      <div class="row" onclick="toast('Security controls ready')"><div class="ico">🛡️</div><div class="rowmain"><b>Login & Security</b><small>Manage account security controls</small></div><b>›</b></div>
    </div></div>

    <div class="section account-section"><h3>🔔 Notifications</h3><div class="list settings">
      <div class="row" onclick="toast('Notification preferences ready')"><div class="ico">🔔</div><div class="rowmain"><b>Notification Preferences</b><small>Choose what you want to receive</small></div><b>›</b></div>
      <div class="row"><div class="ico">💳</div><div class="rowmain"><b>Transaction & Service Notifications</b><small>Purchases, wallet and service updates</small></div><button class="switch on" onclick="this.classList.toggle('on');toast('Notification setting updated')"><span></span></button></div>
    </div></div>

    <div class="section account-section"><h3>⚙️ App Settings</h3><div class="list settings">
      <div class="row"><div class="ico">🌙</div><div class="rowmain"><b>Dark Mode</b><small>${darkOn?'Enabled':'Use the darker app appearance'}</small></div><button class="switch ${darkOn?'on':''}" onclick="toggleDarkMode(event)"><span></span></button></div>
      <div class="row"><div class="ico">ℹ️</div><div class="rowmain"><b>App Version</b><small>V14.2.4</small></div><b>›</b></div>
      <div class="row" onclick="toast('App update check ready')"><div class="ico">⬆️</div><div class="rowmain"><b>App Update</b><small>Check for the latest version</small></div><b>›</b></div>
      <div class="row" onclick="toast('General preferences ready')"><div class="ico">⚙️</div><div class="rowmain"><b>General Preferences</b><small>Application preferences</small></div><b>›</b></div>
    </div></div>

    <div class="section account-section"><h3>🆘 Customer Care</h3><div class="list settings">
      <div class="row" onclick="go('support')"><div class="ico">💬</div><div class="rowmain"><b>Live Chat</b><small>Chat with customer care</small></div><b>›</b></div>
      <div class="row" onclick="toast('Customer care call action ready')"><div class="ico">📞</div><div class="rowmain"><b>Call Customer Care</b><small>Speak with our support team</small></div><b>›</b></div>
      <div class="row" onclick="toast('Support email action ready')"><div class="ico">📧</div><div class="rowmain"><b>Email Support</b><small>Send us an email</small></div><b>›</b></div>
      <div class="row" onclick="go('support')"><div class="ico">❓</div><div class="rowmain"><b>Help Center / FAQ</b><small>Find answers to common questions</small></div><b>›</b></div>
      <div class="row" onclick="toast('Support tickets ready')"><div class="ico">🎫</div><div class="rowmain"><b>My Support Tickets</b><small>Track your complaints and requests</small></div><b>›</b></div>
      <div class="row" onclick="toast('Problem report ready')"><div class="ico">📝</div><div class="rowmain"><b>Report a Problem</b><small>Tell us what went wrong</small></div><b>›</b></div>
    </div></div>

    <div class="section account-section"><h3>📄 Legal</h3><div class="list settings">
      <div class="row" onclick="toast('Terms and Conditions')"><div class="ico">📄</div><div class="rowmain"><b>Terms & Conditions</b><small>Read the terms of service</small></div><b>›</b></div>
      <div class="row" onclick="toast('Privacy Policy')"><div class="ico">🔒</div><div class="rowmain"><b>Privacy Policy</b><small>How we handle your information</small></div><b>›</b></div>
    </div></div>

    <div class="section account-section"><h3>🚪 Account</h3><div class="list settings">
      <div class="row" onclick="state.loggedIn=false;try{AndroidBiometric.disable()}catch(e){};DC_API.logout();save();go('login')"><div class="ico">↪</div><div class="rowmain"><b>Logout</b><small>Sign out of Data Connect</small></div><b>›</b></div>
    </div></div>
    <button class="ghost full" onclick="go('backend')">⚙ Backend Connection</button>
  </section>${bottom('account')}</main>`
}
function toggleBiometricSetting(event){
  if(event) event.stopPropagation();
  if(biometricEnabled()){try{AndroidBiometric.disable();toast('Fingerprint login disabled')}catch(e){toast('Unable to disable fingerprint login')}return go('account')}
  if(!biometricAvailable()) return toast('Biometric authentication is not available on this device');
  if(!DC_API.token && !localStorage.getItem('dc_auth_token') && !localStorage.getItem('data_connect_token')) return toast('Log in with your password first');
  try{
    window.onNativeBiometricEnabled=function(ok){toast(ok?'Fingerprint login enabled':'Fingerprint setup cancelled');go('account')};
    AndroidBiometric.enable(DC_API.token||localStorage.getItem('dc_auth_token')||localStorage.getItem('data_connect_token'));
  }catch(e){toast('Fingerprint login is not available on this device')}
}
function toggleDarkMode(event){
  if(event) event.stopPropagation();
  const on=localStorage.getItem('dc_dark_mode')!=='1';
  localStorage.setItem('dc_dark_mode',on?'1':'0');
  document.body.classList.toggle('dc-dark',on);
  toast(on?'Dark mode enabled':'Dark mode disabled');
  go('account');
}
document.body.classList.toggle('dc-dark',localStorage.getItem('dc_dark_mode')==='1');

function about(){app.innerHTML=`<main class="shell"><section class="screen">${header('Data Connect','Smart Way to Buy Data')}<div class="state"><div class="stateico">DC</div><h2>One app, connected services</h2><p class="sub">Data Center · Airtime · Wallet · Shares · Marketers · Withdrawal · SIC/Staff Chat · Customer Care</p><div class="notice">Installable mobile experience: the Android build packages the Data Connect web interface for phone use. V06 demo UI is designed around the company's current requirements. Real authentication, MySQL wallet, provider API, approvals, notifications and staff controls are backend work for the production stage.</div></div></section></main>`}
function errorState(message='We could not complete your request.'){
app.innerHTML=`<main class="shell"><section class="screen">${header('Something went wrong','Data Connect')}<div class="state"><div class="stateico">✕</div><h2>Transaction Failed</h2><p class="sub">${esc(message)}</p><button class="primary full" onclick="go('summary')">Try Again</button><button class="ghost full" onclick="go('home')">Back to Home</button></div></section></main>`}
function connectionError(){app.innerHTML=`<main class="shell"><section class="screen">${header('Connection Problem','Data Connect')}<div class="state"><div class="stateico">⚠</div><h2>Connection Problem</h2><p class="sub">Please check your internet connection and try again.</p><button class="primary full" onclick="location.reload()">Retry</button></div></section></main>`}
function render(){if(['marketer','staff','dispenser'].includes(state.page)){if(state.page==='marketer'&&!canSeeMarketer())return go('home');if(state.page==='staff'&&!canSeeStaff())return go('home');if(state.page==='dispenser'&&!canSeeDispenser())return go('home')}if(['login','signup'].includes(state.page))return auth();const map={home,dispenser,data,planSelection,recipient,summary,confirm,pending,success,airtime,airtimePending,airtimeSuccess,wallet,transactions,shares,withdraw,marketer,marketerStatus,staff,notifications,support,account,about,backend:backendSettings};(map[state.page]||home)()}
showScreenLoader('Starting Data Connect...');
setTimeout(()=>{render();setTimeout(hideScreenLoader,350)},450);

function backendSettings(){app.innerHTML=`<main class="shell"><section class="screen">${header('Backend Connection','V11 production API settings')}<div class="notice">Connect the Android app to the deployed PHP API over HTTPS. Keep MySQL and VTU credentials on the server.</div><div class="form"><label class="label">Backend URL</label><input id="apiUrl" class="input" placeholder="https://example.com" value="${DC_API.base()}"><button class="primary full" onclick="saveBackendConnection()">Save connection</button><button class="ghost full" onclick="testBackendConnection()">Test connection</button><button class="ghost full" onclick="DC_API.setBaseUrl('');toast('Demo mode enabled');go('account')">Use demo mode</button><div id="backendTest" class="notice" style="display:none"></div></div></section></main>`}
function saveBackendConnection(){try{DC_API.setBaseUrl(document.getElementById('apiUrl').value);toast('Backend URL saved');go('account')}catch(e){toast(e.message)}}
async function testBackendConnection(){const box=document.getElementById('backendTest');if(!box)return;if(!DC_API.base())return toast('Enter the backend URL first');box.style.display='block';box.textContent='Testing connection…';try{const r=await DC_API.health();box.textContent='✓ Server reachable: '+(r.message||'OK')}catch(e){box.textContent='✕ '+(e.message||'Connection failed')}}


/* V07.4 staff/dispenser + marketer workflow helpers */
window.DC_WORKFLOW = {
  airtimeStatus: "pending",
  approveAirtime() {
    this.airtimeStatus = "approved";
    return this.airtimeStatus;
  },
  rejectAirtime() {
    this.airtimeStatus = "rejected";
    return this.airtimeStatus;
  },
  marketerEligible(balance, gb) {
    return Number(balance) >= 3250 && Number(gb) >= 12;
  }
};

// V13.2 loading helpers
function showDataConnectLoading(message){let e=document.getElementById("dc-loader");if(!e){e=document.createElement("div");e.id="dc-loader";e.className="dc-loader";e.innerHTML="<div class=\"dc-spinner\"></div><div></div>";document.body.appendChild(e);}e.querySelector("div:last-child").textContent=message||"Loading...";}
function hideDataConnectLoading(){const e=document.getElementById("dc-loader");if(e)e.remove();}

/* ================= DATA CONNECT V13.6 FORGOT PASSWORD ================= */
const DC_V13_6_FORGOT_PASSWORD = true;

function dcV136ForgotPassword(){
  const email = prompt("Enter the email or phone number linked to your account:");
  if(!email || !String(email).trim()) return;
  const identifier = String(email).trim();

  // Production API path: use the app's configured API when available.
  if(typeof DC_API !== "undefined" && DC_API.base && DC_API.base()){
    if(typeof showDataConnectLoading === "function") showDataConnectLoading("Sending reset code...");
    fetch(DC_API.base().replace(/\/$/,"") + "/api/auth/forgot-password", {
      method:"POST",
      headers:{"Content-Type":"application/json"},
      body:JSON.stringify({identifier:identifier})
    })
    .then(async r => {
      const data=await r.json().catch(()=>({}));
      if(!r.ok) throw new Error(data.message || "Unable to send reset code.");
      return data;
    })
    .then(data=>{
      if(typeof hideDataConnectLoading==="function") hideDataConnectLoading();
      const code=data.reset_code || data.otp || "";
      const entered=prompt(data.message || "Enter the reset code sent to you:");
      if(!entered) return;
      if(code && entered!==String(code)) throw new Error("Invalid reset code.");
      const password=prompt("Create a new password (minimum 8 characters):");
      if(!password || password.length<8) throw new Error("Password must be at least 8 characters.");
      return fetch(DC_API.base().replace(/\/$/,"") + "/api/auth/reset-password",{
        method:"POST",
        headers:{"Content-Type":"application/json"},
        body:JSON.stringify({identifier:identifier,reset_code:entered,new_password:password})
      });
    })
    .then(async r=>{
      if(!r) return;
      const data=await r.json().catch(()=>({}));
      if(!r.ok) throw new Error(data.message || "Password reset failed.");
      if(typeof hideDataConnectLoading==="function") hideDataConnectLoading();
      if(typeof toast==="function") toast(data.message || "Password reset successful. You can now log in.");
    })
    .catch(e=>{
      if(typeof hideDataConnectLoading==="function") hideDataConnectLoading();
      if(typeof errorState==="function") errorState(e.message);
      else alert(e.message);
    });
    return;
  }

  // Demo fallback for offline APK/UI testing. Production must use the backend path above.
  const demoCode = String(Math.floor(100000 + Math.random()*900000));
  alert("Demo reset code: " + demoCode);
  const entered=prompt("Enter the reset code:");
  if(entered!==demoCode) return alert("Invalid reset code.");
  const password=prompt("Create a new password (minimum 8 characters):");
  if(!password || password.length<8) return alert("Password must be at least 8 characters.");
  if(typeof toast==="function") toast("Password reset successful. You can now log in.");
  else alert("Password reset successful. You can now log in.");
}

/* Data Connect V13.7 Roles */
const DC_V13_7_ROLES = {
 CUSTOMER: "customer",
 MARKETER: "marketer",
 STAFF_SIC: "staff_sic",
 DISPENSER: "dispenser",
 ADMIN: "admin"
};

/* ================= DATA CONNECT V13.7.1 ADMIN ================= */
const DC_V13_7_1_ADMIN = true;
const DC_V13_7_1_ROLES = [
  {key:"customer", label:"Customer"},
  {key:"marketer", label:"Marketer"},
  {key:"staff_sic", label:"Staff / SIC"},
  {key:"dispenser", label:"Dispenser"}
];

function dcV1371IsAdmin(user){
  return !!user && String(user.role||"").toLowerCase() === "admin";
}

function dcV1371AdminUsers(container, users, currentUser){
  if(!container) return;
  if(!dcV1371IsAdmin(currentUser)){
    container.innerHTML='<div class="dc-admin-denied">Admin access required.</div>';
    return;
  }
  users=Array.isArray(users)?users:[];
  container.innerHTML=`
    <section class="dc-admin">
      <div class="dc-admin-head"><h2>User Management</h2><p>Assign and manage privileged roles.</p></div>
      <input class="dc-admin-search" placeholder="Search users..." oninput="dcV1371FilterUsers(this.value)">
      <div id="dc-admin-users">
        ${users.map(u=>dcV1371UserCard(u)).join("") || '<div class="dc-admin-empty">No users found.</div>'}
      </div>
    </section>`;
  window.dcV1371Users=users;
}

function dcV1371UserCard(u){
  const role=String(u.role||"customer");
  return `<article class="dc-admin-user" data-search="${String((u.name||"")+" "+(u.email||"")+" "+role).toLowerCase()}">
    <div><b>${u.name||"User"}</b><small>${u.email||u.phone||""}</small><span>${role}</span></div>
    <button type="button" onclick="dcV1371ManageUser(${Number(u.id||0)})">Manage</button>
  </article>`;
}

function dcV1371FilterUsers(q){
  q=String(q||"").toLowerCase();
  document.querySelectorAll(".dc-admin-user").forEach(x=>{
    x.style.display=x.dataset.search.includes(q)?"flex":"none";
  });
}

function dcV1371ManageUser(id){
  const u=(window.dcV1371Users||[]).find(x=>Number(x.id)===Number(id));
  if(!u) return;
  const choices=DC_V13_7_1_ROLES.map(r=>`${r.key===u.role?"●":"○"} ${r.label}`).join("\n");
  const selected=prompt("Assign role:\n\n"+choices+"\n\nEnter: customer, marketer, staff_sic, or dispenser",u.role||"customer");
  if(!selected) return;
  const role=selected.trim().toLowerCase();
  if(!DC_V13_7_1_ROLES.some(r=>r.key===role)) return alert("Invalid role.");
  if(!confirm("Confirm role change to "+role+"?")) return;
  u.role=role;
  if(typeof toast==="function") toast("Role updated");
  if(window.dcV1371AdminRefresh) window.dcV1371AdminRefresh();
}

function dcV1371SuspendUser(id){
  if(!confirm("Suspend this account?")) return;
  if(typeof toast==="function") toast("Account suspension requested");
}

function dcV1371ActivateUser(id){
  if(!confirm("Activate this account?")) return;
  if(typeof toast==="function") toast("Account activation requested");
}

/* ================= DATA CONNECT V13.8 OPERATIONAL ROLES ================= */
const DC_V13_8_OPERATIONAL_ROLES = {
  marketer: {
    title:"Marketer Dashboard",
    subtitle:"Manage your assigned marketing activities",
    items:["Assigned Customers","Referral Activity","Marketing Status","Earnings"]
  },
  staff_sic: {
    title:"Staff / SIC Dashboard",
    subtitle:"Manage assigned operational requests",
    items:["Pending Requests","Airtime / Data Requests","Processing","Completed"]
  },
  dispenser: {
    title:"Dispenser Dashboard",
    subtitle:"Process assigned dispensing requests",
    items:["Assigned Requests","Process Request","Completed","History"]
  }
};

function dcV138RoleKey(user){
  return String(user && user.role || "customer").toLowerCase();
}

function dcV138RenderOperationalDashboard(container,user){
  if(!container) return false;
  const role=dcV138RoleKey(user);
  const cfg=DC_V13_8_OPERATIONAL_ROLES[role];
  if(!cfg) return false;
  container.innerHTML=`
    <section class="dc-v138-dashboard">
      <div class="dc-v138-hero">
        <small>DATACONNECT</small>
        <h2>${cfg.title}</h2>
        <p>${cfg.subtitle}</p>
      </div>
      <div class="dc-v138-grid">
        ${cfg.items.map((x,i)=>`
          <button type="button" class="dc-v138-card" onclick="dcV138OperationalAction('${role}',${i})">
            <span>${["✓","▣","↻","≡"][i]||"•"}</span>
            <b>${x}</b><small>Open</small>
          </button>`).join("")}
      </div>
    </section>`;
  return true;
}

function dcV138OperationalAction(role,index){
  if(typeof toast==="function"){
    const name=DC_V13_8_OPERATIONAL_ROLES[role]?.items[index]||"Request";
    toast(name+" opened");
  }
}

/* Data Connect V13.8.1 Backend API Map */
const DC_V13_8_1_API_MAP = {
 admin:{
  users:"/api/admin/users",
  assignRole:"/api/admin/assign-role",
  removeRole:"/api/admin/remove-role",
  suspend:"/api/admin/suspend-user"
 },
 marketer:{
  dashboard:"/api/marketer/dashboard",
  customers:"/api/marketer/customers",
  earnings:"/api/marketer/earnings"
 },
 staff:{
  dashboard:"/api/staff/dashboard",
  requests:"/api/staff/requests",
  update:"/api/staff/update-status"
 },
 dispenser:{
  tasks:"/api/dispenser/tasks",
  process:"/api/dispenser/process"
 }
};
