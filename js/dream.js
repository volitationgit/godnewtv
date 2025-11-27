import { Crypto } from 'assets://js/lib/cat.js'

let api = 'http://api.2011.boxtv.win/api/wbtj5hmx'
let crapi = 'http://lc.aacalive.com:26789/list/ghjnvq5o/cr.json'
let device_id = "7c:95:1a:ba:ff:51"
let hardware = 'M30_Pro_ROW-MT6765V/WB-8-4.03 GB-47.52 GB-nw'
let version = 'DreamTV 20220608'
let from = '20267'
let key = '5sx72LcaqDFstYgIwpW9cTKHi9g3kLTW'

let siteKey = '';
let siteType = "3";

// Token信息和过期时间管理
let tokenInfo = {
    token: null,
    server: null,
    client_id: null,
    password: null,
    expireTime: 0  // token过期时间戳
};

// === 新增：TTL 與提前刷新秒數（與註解一致 300 秒，提前 30 秒刷新） ===
const TOKEN_TTL_SEC = 300;
const REFRESH_MARGIN_SEC = 30;

// ============== 新增：X-Api-key 產生器 ==============
function genXApiKey() {
    const rand = Math.random().toString();
    const keyMd5 = Crypto.MD5(rand).toString();
    const uuid = keyMd5.replace(/(.{8})(.{4})(.{4})(.{4})(.*)/, "$1-$2-$3-$4-$5");
    const t = Math.floor(Date.now() / 1000); // 當前秒級時間戳
    const apikey = uuid + 'rfsy&doqg@hdvpameh#ptrcg%jgerlcs' + t;
    const apikeySha = Crypto.SHA256(apikey).toString();
    return apikeySha + '..0..' + t + '..' + uuid;
}

// ============== 函数 ==============
function base64Encode(text) {
    return Crypto.enc.Base64.stringify(Crypto.enc.Utf8.parse(text));
}

function base64Decode(text) {
    return Crypto.enc.Utf8.stringify(Crypto.enc.Base64.parse(text));
}


function getUrlDir(link) {
    // 去掉末尾的 / 或 文件名部分
    return link.replace(/\/[^\/?#]+(\?.*)?(#.*)?$/, '');
}
// ★ 新增在這裡（helper 區）★
function guessContentType(u){
  if (/\.m3u8($|\?)/i.test(u)) return 'application/vnd.apple.mpegurl';
  if (/\.vtt($|\?)/i.test(u))  return 'text/vtt';
  if (/\.ts($|\?)/i.test(u))   return 'video/MP2T';
  if (/\.(mp4|m4s|cmfa|cmfv)($|\?)/i.test(u)) return 'video/mp4';
  return 'application/octet-stream'; // key.bin 等其他二進位
}
// 判斷字串看起來是不是 m3u8
function looksLikeM3U8(txt){
  return typeof txt === 'string' && /#EXTM3U/.test(txt);
}
function joinUrl(base, rel) {
    if (/^https?:\/\//i.test(rel)) return rel;         // 已是絕對網址
    if (rel.startsWith('/')) {                          // 以 / 開頭 → 補 domain
        const m = base.match(/^(https?:\/\/[^\/]+)/i);
        return (m ? m[1] : '') + rel;
    }
    // 其他相對路徑 → 用 base 的目錄補上
    return getUrlDir(base).replace(/\/+$/, '') + '/' + rel.replace(/^\.\/+/, '');
}
async function curl_post(url, postdata, headerArr) {
    const headers = headerArr.reduce((acc, h) => {
        let [k, v] = h.split(/:\s*/)
        acc[k] = v
        return acc
    }, {})
    headers["Content-Type"] = "application/json"
    
    try {
        let res = await req(url, {
            method: 'post',
            headers,
            data: postdata
        })
        return res.content
    } catch (error) {
        console.log('curl_post error:', error);
        throw error;
    }
}

async function curl_get(url) {
    const headers = {
        "User-Agent": "Lavf/58.12.100",
        "userid": tokenInfo.client_id || "",
        "usertoken": tokenInfo.token || "",
		"X-Api-key": genXApiKey()  // 🔥 新增
    }
    
    try {
        let res = await req(url, { headers })
        return res.content
    } catch (error) {
        console.log('curl_get error:', error);
        throw error;
    }
}

async function curl_get2(url) {
    const headers = {
        "User-Agent": "Lavf/58.12.100",
        "userid": tokenInfo.client_id || "",
        "usertoken": tokenInfo.token || "",
		"X-Api-key": genXApiKey()  // 🔥 新增
    }
    
    try {
        let res = await req(url, { buffer: 2, headers })
        return res.content
    } catch (error) {
        console.log('curl_get2 error:', error);
        throw error;
    }
}

function getCode(Method) {
    // 以 PHP 相同順序組字串: appid + secret + time + method + sn
    const now = Math.floor(Date.now() / 1000);
    const sign = Crypto.MD5(
        String(from) + String(key) + String(now) + String(Method) + String(device_id)
    ).toString();

    const vparams = {};
    if (tokenInfo.token) {
        vparams["client_id"] = tokenInfo.client_id;
        vparams["password"] = tokenInfo.password;
        vparams["token"] = tokenInfo.token;
    }
    vparams["device_id"] = device_id;
    vparams["hardware"] = hardware;
    vparams["sn"] = device_id;
    vparams["version"] = version;

    const vSys = {
        from,
        sign,
        time: now,
        version: "V1"
    };
    const vArr = {
        method: Method,
        system: vSys,
        params: vparams
    };
    return vArr;
}

// 检查 token 是否即将过期（到期前 30 秒就判定要刷新）
function isTokenExpiring() {
  if (!tokenInfo.expireTime) return true;
  const now = Date.now();
  const margin = REFRESH_MARGIN_SEC * 1000;
  const willExpire = now >= (tokenInfo.expireTime - margin);
  if (willExpire) {
    const left = Math.max(0, Math.floor((tokenInfo.expireTime - now) / 1000));
    console.log('[token] expiring soon. seconds_left=', left);
  }
  return willExpire;
}


// 刷新token信息（打 1-1-2）
async function refreshToken() {
  console.log('Refreshing token...');
  try {
    const header = ["User-Agent: okhttp/3.12.5"];
    const resp1 = await curl_post(api, getCode("1-1-2"), header);
    const obj1 = JSON.parse(resp1).data;

    if (!obj1?.server?.hosts?.length) throw new Error('No server hosts available');

    tokenInfo.token     = obj1.client.token;
    tokenInfo.server    = obj1.server.hosts[0].url;
    tokenInfo.client_id = obj1.client.client_id;
    tokenInfo.password  = obj1.client.password;
    tokenInfo.expireTime = Date.now() + (TOKEN_TTL_SEC * 1000); // 與上方常數一致

    if (!tokenInfo.token || !tokenInfo.server) {
      throw new Error('Invalid token or server');
    }

    console.log(
      '[token] refreshed. ttl_sec=',
      TOKEN_TTL_SEC,
      'expires_at=',
      new Date(tokenInfo.expireTime).toLocaleString()
    );
    return tokenInfo;
  } catch (error) {
    console.log('Refresh token error:', error);
    throw error;
  }
}

// 获取有效的token信息
async function getValidTokenInfo() {
    // 如果没有token或者即将过期，则刷新
    if (!tokenInfo.token || isTokenExpiring()) {
        await refreshToken();
    }
    
    return tokenInfo;
}

// ============== init函数 ==============
async function init(cfg) {
    if (cfg && typeof cfg === 'object') {
        siteKey = cfg.skey || cfg.siteKey || '';
        siteType = cfg.stype || cfg.siteType || "3";
    } else {
        console.warn('Invalid config passed to init:', cfg);
        siteKey = '';
        siteType = "3";
    }
    
    console.log('Config initialized:', { siteKey, siteType });
}

// 讀取 JSON（自動 GET）——只加 BOM 清除，其他不動
function _stripBOM(s){ return typeof s === 'string' ? s.replace(/^\uFEFF/, '') : s; }
async function getJson(url) {
  const txt = await curl_get(url);
  try { return JSON.parse(_stripBOM(txt)); } catch (e) {
    console.log('[getJson] parse fail head=', (''+txt).slice(0,120));
    return null;
  }
}


async function live() {
    console.log('Live function started. Config:', { siteKey, siteType });

    try {
        // 1) 確保 token 可用
        await getValidTokenInfo();
        const header = ["User-Agent: okhttp/3.12.5"];

        // 2) 驗證帳號
        await curl_post(api, getCode("1-1-3"), header);

        // 3) 拉第一份列表
        const resp3 = await curl_post(api, getCode("1-1-4"), header);
        const rCode = JSON.parse(resp3);
        if (rCode.code == 1) {
            console.log('API (list1) returned error code 1');
            return "";
        }

        // 4) js2Proxy 只建一次
        const js2Base = await js2Proxy(true, siteType, siteKey, 'smart/', {});
        // 用 Map 合併分類
        const groups = new Map();

        // helper: 放一筆
        const put = (cate, name, url) => {
            if (!groups.has(cate)) groups.set(cate, []);
            groups.get(cate).push({ name, url });
        };

        // 5) 處理第一份
        for (const v of rCode.data || []) {
            const ct = v.category || '未分組';
            if (v.url && v.url.startsWith("http")) {
                put(ct, v.name, v.url);
            } else if (v.url) {
                put(ct, v.name, js2Base + base64Encode(tokenInfo.server + v.url));
            }
        }
        console.log('List1 done. Categories:', new Set((rCode.data||[]).map(x=>x.category)).size);

// 6) 拉第二份列表（GET）— 強制統一走 smart 代理
const j2raw = await getJson(crapi);
console.log('[List2] typeof=', typeof j2raw, 'keys=', j2raw && Object.keys(j2raw));

let list2 = [];
// cr.json 目前為 {"code":0,"data":[ ... ]}，先吃 data；其餘結構做兼容但不報錯
if (j2raw && Array.isArray(j2raw.data)) {
  list2 = j2raw.data;
} else if (Array.isArray(j2raw)) {
  list2 = j2raw;
} else if (j2raw && Array.isArray(j2raw.list)) {
  list2 = j2raw.list;
} else if (j2raw && j2raw.items && Array.isArray(j2raw.items)) {
  list2 = j2raw.items;
}

if (!list2.length) {
  console.log('[List2] empty or unrecognized. head=', j2raw ? JSON.stringify(j2raw).slice(0,200) : 'null');
} else {
  const js2Base = await js2Proxy(true, siteType, siteKey, 'smart/', {}); // 只建一次
  let added = 0;
  for (const v of list2) {
    const ct   = v.category || v.group || v.type || v.cate || '未分組';
    const name = v.name || v.title || v.channel || '未命名';
    const url  = v.url  || v.link  || v.play   || '';
    if (!url) continue;

    // ★ 重點：第二份一律包 smart 代理（不判斷 http/相對路徑）
    put(ct, name, js2Base + base64Encode(url));
    added++;
  }
  console.log(`[List2] added=${added}, cate≈${new Set(list2.map(x => (x.category || x.group || x.type || x.cate || '未分組'))).size}`);
}


        // 7) 輸出合併結果
        let out = "";
        for (const [cate, items] of groups.entries()) {
            out += `${cate},#genre#\n`;
            for (const it of items) {
                out += `${it.name},${it.url}\n`;
            }
        }
        console.log('Merged live list generated. Groups:', groups.size);

        return out;

    } catch (error) {
        console.log('Live function error:', error);
        return "";
    }
}

async function proxy(segments, headers) {
    if (!segments || segments.length < 2) {
        return JSON.stringify({
            code: 400,
            content: 'Invalid segments',
        })
    }
    
    let what = segments[0]
    let id = base64Decode(segments[1]);
    
    console.log('Proxy called with:', { what, idLength: id.length });

    if (what === 'smart') {
        try {
            await getValidTokenInfo();
            
console.log('Fetching M3U8 from:', id);
let data = await curl_get(id);

// 第一次不對 → 強制刷新 token 後重抓一次
if (!looksLikeM3U8(data)) {
  console.log('[smart] manifest invalid, force refresh + retry once');
  await refreshToken();              // 不看期限，直接刷新
  data = await curl_get(id);
}

if (looksLikeM3U8(data)) {
  // === 你的改寫流程（已處理 KEY/MAP/MEDIA/I-FRAME 等 URI）===
  const js2Sts = await js2Proxy(false, siteType, siteKey, 'sts/', {});
  const js2Smt = await js2Proxy(false, siteType, siteKey, 'smart/', {});

  let result = '';
  let tsCount = 0;

  data.split(/\r?\n/).forEach(raw => {
    const line = (raw || '').trim();
    if (line === '') { result += '\n'; return; }

    if (line.startsWith('#EXT-X-KEY') || line.startsWith('#EXT-X-SESSION-KEY')) {
      const m = line.match(/URI="([^"]+)"/i);
      if (m) {
        const u = joinUrl(id, m[1]);
        const prox = js2Sts + base64Encode(u);
        result += line.replace(/URI="([^"]+)"/i, 'URI="' + prox + '"') + '\n';
      } else {
        result += line + '\n';
      }
      return;
    }

    if (line.startsWith('#EXT-X-MAP')) {
      const m = line.match(/URI="([^"]+)"/i);
      if (m) {
        const u = joinUrl(id, m[1]);
        const prox = js2Sts + base64Encode(u);
        result += line.replace(/URI="([^"]+)"/i, 'URI="' + prox + '"') + '\n';
      } else {
        result += line + '\n';
      }
      return;
    }

    if (line.startsWith('#EXT-X-MEDIA') || line.startsWith('#EXT-X-I-FRAME-STREAM-INF')) {
      const m = line.match(/URI="([^"]+)"/i);
      if (m) {
        const u = joinUrl(id, m[1]);
        const prox = js2Smt + base64Encode(u);
        result += line.replace(/URI="([^"]+)"/i, 'URI="' + prox + '"') + '\n';
      } else {
        result += line + '\n';
      }
      return;
    }

    if (line.startsWith('#')) { result += line + '\n'; return; }

    const full = joinUrl(id, line);
    if (/\.m3u8($|\?)/i.test(full)) {
      result += js2Smt + base64Encode(full) + '\n';
    } else {
      result += js2Sts + base64Encode(full) + '\n';
      tsCount++;
    }
  });

  console.log('M3U8 processed successfully. TS segments:', tsCount);
  return JSON.stringify({
    code: 200,
    content: result,
    headers: { 'Content-Type': 'application/vnd.apple.mpegurl' }
  });
} else {
  console.log('[smart] manifest still invalid after refresh. giving up');
  return JSON.stringify({ code: 502, content: 'Bad manifest' });
}

        } catch (error) {
            console.log('Proxy smart error:', error);
        }
} else if (what === "sts") {
  try {
    await getValidTokenInfo();

    const turl = id;
    console.log('Fetching binary segment:', {
      url: turl.substring(0, 100) + '...',
      fullLength: turl.length
    });

let resp = await curl_get2(turl);

// 檢測是否被擋：很小包 / 可解析為文字 / 含 606/403/HTML
let needRetry = false;
if (typeof resp === 'string') {
  const s = resp.slice(0, 200);
  if (/606|403|<html|<!doctype/i.test(s)) needRetry = true;
} else if (!resp || (resp.length && resp.length < 200)) {
  needRetry = true;
}

if (needRetry) {
  console.log('[sts] suspect blocked, force refresh + retry once');
  await refreshToken();
  resp = await curl_get2(turl);
}

console.log('Binary segment fetched. Size:', resp ? resp.length : 0);

return JSON.stringify({
  code: 200,
  buffer: 2,
  content: resp,
  headers: { 'Content-Type': guessContentType(turl) },
});

  } catch (error) {
    console.log('Proxy sts error:', error);
    return JSON.stringify({
      code: 500,
      content: 'TS segment fetch failed',
    });
  }
}

    console.log('Unknown proxy type or error occurred');
    return JSON.stringify({
        code: 500,
        content: 'Unknown error',
    })
}

export function __jsEvalReturn() {
    return {
        init,
        live,
        proxy
    }
}