/* DataConnect Frontend V14.0 API layer — Backend V2.0.5 */
window.DC_API = {
  BASE_URL: localStorage.getItem("dc_api_base_url") || "https://api.example.com",
  token: localStorage.getItem("dc_auth_token") || "",

  setBaseUrl(url) {
    let value = (url || "").trim().replace(/\/+$/, "");
    value = value.replace(/\/api$/i, "");
    if (value && !/^https:\/\//i.test(value)) throw new Error("Backend URL must use HTTPS.");
    this.BASE_URL = value;
    localStorage.setItem("dc_api_base_url", value);
  },
  base(){ return this.BASE_URL; },
  isLive(){ return !!this.BASE_URL && this.BASE_URL !== "https://api.example.com"; },

  async request(path, options={}) {
    if (!this.BASE_URL) throw new Error("Backend URL is not configured.");
    const headers = Object.assign({"Accept":"application/json","Content-Type":"application/json"}, options.headers || {});
    if (this.token) headers.Authorization = "Bearer " + this.token;
    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), 15000);
    let res;
    try { res = await fetch(this.BASE_URL + path, Object.assign({}, options, {headers, signal: controller.signal})); }
    catch (err) { clearTimeout(timer); if (err.name === "AbortError") throw new Error("Request timed out. Please try again."); throw new Error("Unable to reach the DataConnect server."); }
    clearTimeout(timer);
    const data = await res.json().catch(()=>({}));
    if (res.status === 401) { this.logout(); }
    if (!res.ok || data.success === false) throw new Error(data.message || ("HTTP " + res.status));
    return data;
  },

  async health(){ return this.request("/api/health"); },
  async version(){ return this.request("/api/v2/app/version"); },
  async register(name,email,phone,password,password_confirmation=password){
    return this.request("/api/v2/auth/register", {method:"POST", body:JSON.stringify({name,email,phone,password,password_confirmation})});
  },
  async login(email,password){
    const data = await this.request("/api/v2/auth/login", {method:"POST", body:JSON.stringify({email,password})});
    if(data.data && data.data.token){ this.token=data.data.token; localStorage.setItem("dc_auth_token",this.token); }
    return data;
  },
  async logoutRemote(){ try { await this.request("/api/v2/auth/logout",{method:"POST"}); } finally { this.logout(); } },
  async me(){ return this.request("/api/v2/account/profile"); },
  async settings(){ return this.request("/api/v2/account/settings"); },
  async wallet(){ return this.request("/api/v2/wallet"); },
  async transactions(){ return this.request("/api/v2/wallet/transactions"); },
  async notifications(){ return this.request("/api/v2/notifications"); },
  async dataPlans(){ return this.request("/api/v2/data/plans"); },
  async purchaseData(data_plan_id,phone_number){ return this.request("/api/v2/data/purchase",{method:"POST",body:JSON.stringify({data_plan_id,phone_number})}); },
  async airtimeRequest(network,amount,phone_number){ return this.request("/api/v2/airtime/purchase",{method:"POST",body:JSON.stringify({network,amount:Number(amount),phone_number})}); },
  async orders(){ return this.request("/api/v2/orders"); },
  async withdrawals(){ return this.request("/api/v2/withdrawals"); },
  async withdrawalRequest(amount,bank_name,account_number,account_name){ return this.request("/api/v2/withdrawals",{method:"POST",body:JSON.stringify({amount:Number(amount),bank_name,account_number,account_name})}); },
  async shares(){ return this.request("/api/v2/shares"); },
  async holdings(){ return this.request("/api/v2/shares/portfolio"); },
  async buyShare(share_id,units){ return this.request("/api/v2/shares/purchase",{method:"POST",body:JSON.stringify({share_id,units:Number(units)})}); },
  logout(){ this.token=""; localStorage.removeItem("dc_auth_token"); localStorage.removeItem("data_connect_token"); }
};
