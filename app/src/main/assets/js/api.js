/* Data Connect V11 API service
   The Android app talks to the deployed PHP API over HTTPS.
   Never put database or VTU provider secrets in the Android assets.
*/
window.DC_API = {
  BASE_URL: localStorage.getItem("dc_api_base_url") || "",
  token: localStorage.getItem("dc_auth_token") || localStorage.getItem("data_connect_token") || "",

  setBaseUrl(url) {
    let value = (url || "").trim().replace(/\/+$/, "");
    // Users may paste either https://host or https://host/api.
    value = value.replace(/\/api$/i, "");
    if (value && !/^https:\/\//i.test(value)) {
      throw new Error("Backend URL must use HTTPS.");
    }
    this.BASE_URL = value;
    localStorage.setItem("dc_api_base_url", this.BASE_URL);
  },

  base() { return this.BASE_URL; },
  isLive() { return !!this.BASE_URL; },

  async request(path, options={}) {
    if (!this.BASE_URL) throw new Error("Backend URL is not configured");
    const headers = Object.assign({"Content-Type":"application/json"}, options.headers || {});
    if (this.token) headers.Authorization = "Bearer " + this.token;
    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), 15000);
    let res;
    try {
      res = await fetch(this.BASE_URL + path, Object.assign({}, options, {headers, signal: controller.signal}));
    } catch (err) {
      clearTimeout(timer);
      if (err.name === "AbortError") throw new Error("Request timed out. Please try again.");
      throw new Error("Unable to reach the Data Connect server.");
    }
    clearTimeout(timer);
    const data = await res.json().catch(()=>({}));
    if (res.status === 401) {
      this.token = "";
      localStorage.removeItem("dc_auth_token");
      localStorage.removeItem("data_connect_token");
    }
    if (!res.ok || data.success === false) throw new Error(data.message || ("HTTP " + res.status));
    return data;
  },

  async register(phone, username, password) {
    const created = await this.request("/api/register.php", {method:"POST", body:JSON.stringify({phone,username,password})});
    // Register endpoint intentionally returns no reusable credential; authenticate immediately.
    return this.login(phone, password).then(login => Object.assign({}, created, login, {message: created.message || login.message}));
  },

  async login(phone, password) {
    const data = await this.request("/api/login.php", {method:"POST", body:JSON.stringify({phone,password})});
    if (data.token) {
      this.token = data.token;
      localStorage.setItem("dc_auth_token", data.token);
    }
    return data;
  },

  async me() { return this.request("/api/me.php"); },
  async wallet() { return this.request("/api/wallet.php"); },
  async transactions() { return this.request("/api/transactions.php"); },
  async notifications() { return this.request("/api/notifications.php"); },
  async health() { return this.request("/api/health.php"); },
  async dataPlans() { return this.request("/api/data-plans.php"); },
  async purchaseData(network, planName, amount, recipientPhone) { return this.request("/api/purchase-data.php", {method:"POST", body:JSON.stringify({network, plan_name:planName, amount:Number(amount), recipient_phone:recipientPhone})}); },
  async airtimeRequest(network, amount, recipientPhone) { return this.request("/api/request-airtime.php", {method:"POST", body:JSON.stringify({network, amount, recipient_phone:recipientPhone})}); },
  async withdrawals() { return this.request("/api/withdrawals.php"); },
  async withdrawalRequest(amount) { return this.request("/api/withdrawal-request.php", {method:"POST", body:JSON.stringify({amount})}); },
  async shares() { return this.request("/api/share-packages.php"); },
  async holdings() { return this.request("/api/share-holdings.php"); },
  async shareReturns() { return this.request("/api/share-returns.php"); },
  async buyShare(packageId) { return this.request("/api/buy-share.php", {method:"POST", body:JSON.stringify({package_id:packageId})}); },


  logout() {
    this.token = "";
    localStorage.removeItem("dc_auth_token");
    localStorage.removeItem("data_connect_token");
  }
};
