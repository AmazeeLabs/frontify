var FrontifyFinder=function(y){"use strict";function c(t,e){switch(t){case"warning":break;case"error":console.error(`${e.code}: ${e.message}`,e.error||"");break}}let r=class extends Error{constructor(e,n){super(`${e}: ${n}`),this.code=e,c("error",{code:e,message:n})}};function $(t){const e="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";let n=new Uint8Array(t);return window.crypto.getRandomValues(n),n=n.map(o=>e.charCodeAt(o%e.length)),String.fromCharCode.apply(null,Array.from(n))}function P(t){return btoa(t).replace(/\+/g,"-").replace(/\//g,"_").replace(/=+$/,"")}function F(t){return Object.keys(t).filter(e=>t[e]).map(e=>`${e}=${encodeURIComponent(t[e])}`).join("&")}function m(t){const e=t.replace(/^(http(?:s)?:\/\/)/,"");return e.endsWith("/")?e.replace(/\/+$/,""):e}async function f(t,e){return fetch(t,e).then(async n=>{if(n.status>=200&&n.status<=299)return await n.json();throw new r("ERR_AUTH_HTTP_REQUEST",n.statusText)}).then(n=>n).catch(n=>{throw n instanceof r?n:new r("ERR_AUTH_HTTP_REQUEST",n)})}function M(t,e){return window.addEventListener(t,e),()=>{window.removeEventListener(t,e)}}const L="Authorize Frontify",H={title:L,width:800,height:600,top:50,left:50},a=class{constructor(e){this.listeners={},this.domain="",this.attachEventListeners=()=>o=>{switch(o.data){case a.EVENT_NAME_CANCELLED:this.call(a.EVENT_METHOD_CANCELLED);break;case a.EVENT_NAME_SUCCESS:this.call(a.EVENT_METHOD_SUCCESS);break;default:o.data.domain?(this.setDomain(o.data.domain),this.call(a.EVENT_METHOD_DOMAIN)):o.data.aborted&&this.call(a.EVENT_METHOD_ABORTED);break}};const n={...H,...e};this.popUp=a.openPopUp(n),this.unregisterEventListener=M("message",this.attachEventListeners()),this.interval=setInterval(()=>{this.popUp&&this.popUp.closed&&(clearInterval(this.interval),this.call(a.EVENT_METHOD_CANCELLED),this.call(a.EVENT_METHOD_ABORTED))},100)}static openPopUp(e){const n=window.open("about:blank",e.title,`width=${e.width}, 
            height=${e.height}, 
            left=${e.left}, 
            top=${e.top}, 
            toolbar=no, menubar=no, 
            location=no, status=no, 
            directories=no, titlebar=no`);if(!n)throw new r("ERR_AUTH_POPUP_BLOCKED","Popup is blocked. Make sure to enable popups.");return n}call(e){this.listeners[e]&&this.listeners[e]()}setDomain(e){this.domain=e}getDomain(){return this.domain}onDomain(e){this.listeners.domain=e}onAborted(e){this.listeners.aborted=e}onSuccess(e){this.listeners.success=e}onCancelled(e){this.listeners.canceled=e}close(){this.listeners={},clearInterval(this.interval),this.unregisterEventListener(),this.popUp&&!this.popUp.closed&&this.popUp.close()}navigateToUrl(e){if(this.popUp&&!this.popUp.closed){this.popUp.location.replace(e);return}throw new r("ERR_AUTH_POPUP_CLOSED","Popup is closed.")}};let l=a;l.EVENT_NAME_CANCELLED="frontify-oauth-authorize-cancelled",l.EVENT_NAME_SUCCESS="frontify-oauth-authorize-success",l.EVENT_METHOD_CANCELLED="cancelled",l.EVENT_METHOD_SUCCESS="success",l.EVENT_METHOD_DOMAIN="domain",l.EVENT_METHOD_ABORTED="aborted";const V=64,x="code",z="S256",v="/connection/authenticator",j="authorization_code",W="SHA-256",B="Bearer";async function q(t){const e=new TextEncoder().encode(t),n=await window.crypto.subtle.digest(W,e),o=String.fromCharCode.apply(null,Array.from(new Uint8Array(n)));return P(o)}async function K(t){try{const e=$(V),n=await q(e),o=await Q(t.domain)||"";return{authorizationUrl:`https://${m(t.domain)}/api/oauth/authorize?${F({response_type:x,client_id:t.clientId,scope:t.scopes.join("+"),code_challenge:n,code_challenge_method:z,redirect_uri:v,session_id:o})}`,codeVerifier:e,sessionId:o}}catch{throw new r("ERR_AUTH_COMPUTE_URL","Error computing authorization url.")}}async function Q(t){try{return(await f(`https://${m(t)}/api/oauth/create/session`,{method:"POST"}))?.data.key}catch{throw new r("ERR_AUTH_SESSION","Error generating session.")}}async function J(t,e){try{return(await f(`https://${m(t.domain)}/api/oauth/poll`,{method:"POST",headers:{"content-type":"application/json"},body:JSON.stringify({session_id:e})}).catch(()=>{throw new r("ERR_AUTH_POLL_REQUEST","Error requesting oauth session poll.")})).data.payload.code}catch{throw new r("ERR_AUTH_POLL","Error polling oauth session.")}}async function Y(t,e,n){try{const o=m(t?.domain),s=await f(`https://${o}/api/oauth/accesstoken`,{method:"POST",headers:{"content-type":"application/json"},body:JSON.stringify({grant_type:j,code:e,code_verifier:n,client_id:t.clientId,redirect_uri:v})});return{bearerToken:{tokenType:B,expiresIn:s.expires_in,accessToken:s.access_token,refreshToken:s.refresh_token,domain:o},clientId:t.clientId,scopes:t.scopes}}catch{throw new r("ERR_AUTH_ACCESS_TOKEN","Error retrieving token.")}}async function G(t,e){try{await f(`https://${m(t)}/api/oauth/revoke`,{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({token:e})})}catch{throw new r("ERR_AUTH_TOKEN_REVOKE","Error revoking token.")}}const X="https://app.frontify.com/finder",Z="Authorize Frontify",i={open:!1};let h,w,U,_;async function ee(t,e){if(i.open&&h.close(),h=re(e??{title:Z,width:800,height:600,top:50,left:50}),i.open=!0,t.domain?await I(t,h).then(n=>{i.open=!1,n&&(w=n)}).catch(n=>{throw n===!1?new r("ERR_AUTH_POPUP_CLOSED","Auth aborted by client."):new r("ERR_AUTH_FAILED","Auth failed.")}):await ne(t,h).then(n=>{i.open=!1,w=n}).catch(n=>{throw n===!1?new r("ERR_AUTH_DOMAIN_POPUP_CLOSED","Domain cancelled by client."):new r("ERR_AUTH_FAILED","Auth failed.")}),!w)throw new r("ERR_AUTH_NO_TOKEN","No token returned.");return w}async function te(t){return await G(t.bearerToken.domain,t.bearerToken.accessToken),t}async function I(t,e){try{const n=await K(t);return await oe(n.authorizationUrl,e).then(async()=>{const o=await J(t,n.sessionId);return Y(t,o,n.codeVerifier)})}catch(n){throw e.popUp?.postMessage({domainError:"Error generating session. Make sure that the inserted domain is a valid and secure Frontify instance."},"*"),n instanceof r&&n.code==="ERR_AUTH_COMPUTE_URL"?new r("ERR_AUTH_SESSION","Failed generating session."):(i.open=!1,new r("ERR_AUTH","Failed retrieving access token."))}}function ne(t,e){return e.navigateToUrl(X),c("warning",{code:"WARN_DOMAIN_POPUP_OPEN",message:"Domain popup opened."}),new Promise((n,o)=>{U=setTimeout(()=>{i.open=!1,e.close(),c("warning",{code:"WARN_DOMAIN_TIMEOUT",message:"Domain popup timed out."})},3e5),e.onDomain(()=>{clearTimeout(U),t.domain=h.getDomain(),I(t,h).then(s=>{s&&n(s)}).catch(s=>{s instanceof r&&s.code!=="ERR_AUTH_SESSION"?o():delete t.domain}),c("warning",{code:"WARN_DOMAIN_SELECT",message:"Domain input submitted."})}),e.onAborted(()=>{i.open=!1,clearTimeout(U),e.close(),o(!1)})})}function oe(t,e){return e.navigateToUrl(t),c("warning",{code:"WARN_AUTH_POPUP_OPEN",message:"Auth popup opened."}),new Promise((n,o)=>{_=setTimeout(()=>{i.open=!1,e.close(),c("warning",{code:"WARN_AUTH_TIMEOUT",message:"Auth popup timed out."})},3e5),e.onAborted(()=>{i.open=!1,clearTimeout(_),e.close(),o(!1)}),e.onSuccess(()=>{i.open=!1,clearTimeout(_),e.close(),c("warning",{code:"WARN_AUTH_SUCCESS",message:"Auth success."}),n()}),e.onCancelled(()=>{i.open=!1,clearTimeout(_),e.close(),o(!1)})})}function re(t){return new l(t??{})}var se=Object.defineProperty,ie=(t,e,n)=>e in t?se(t,e,{enumerable:!0,configurable:!0,writable:!0,value:n}):t[e]=n,ae=(t,e,n)=>ie(t,e+"",n);class ce{constructor(){ae(this,"length",0)}key(){return null}clear(){for(const e of Object.keys(this))this.removeItem(e)}getItem(e){return typeof this[e]!="string"?null:this[e]}removeItem(e){typeof this[e]=="string"&&delete this[e]}setItem(e,n){this[e]=n}}function d(t,e){switch(t){case"warning":break;case"error":console.error(`${e.code}: ${e.message}`,e.error||"");break}}class u extends Error{constructor(e,n){super(`${e}: ${n}`),this.code=e,this.name="FinderError",d("error",{code:e,message:n})}}function O(){return"FRONTIFY_FINDER"}function T(t){return`${O()}-${t}`}async function le(t,e){try{const n=await fetch(t,e);if(n.status<200||n.status>=300)throw new u("ERR_FINDER_HTTP_REQUEST",n.statusText);return await n.json()}catch(n){if(n instanceof u)throw n;const o=n instanceof Error?n.message:"";throw new u("ERR_FINDER_HTTP_REQUEST",o)}}const E={key:`${O()}_test`,value:"yes"};let R;function A(){return R===void 0&&(R=pe()),R}function C(){return Math.floor(Date.now()/1e3)}function de(t){return t<C()}function N(t){A().removeItem(t)}function ue(t,e,n){const o={expiresAt:n?C()+n:void 0,data:e};A().setItem(t,JSON.stringify(o))}function he(t){const e=b(t);return e&&N(t),e}function b(t){const e=A().getItem(t);if(!e)return;const n=JSON.parse(e);if(!n){N(t);return}if(n.expiresAt&&de(n.expiresAt)){N(t);return}return n.data}function pe(){return D("localStorage")?window.localStorage:D("sessionStorage")?window.sessionStorage:new ce}function D(t){try{if(typeof window[t]>"u")return!1;const e=window[t];return e.setItem(E.key,E.value),e.getItem(E.key)===E.value?(e.removeItem(E.key),!0):!1}catch{return!1}}var me="3.0.1";const Ee=`
query AssetByIds($ids: [ID!]!, $permanent: Boolean!) {
  assets(ids: $ids) {
    id
    externalId
    title
    description
    type: __typename
    creator {
      name
    }
    createdAt
    expiresAt
    alternativeText
    ...withMetadata
    ...onImage
    ...onDocument
    ...onFile
    ...onAudio
    ...onVideo
  }
}

fragment withMetadata on Asset {
  tags {
    value
    source
  }
  customMetadata {
    property {
      id
      name
      type {
        name
      }
    }
    ... on CustomMetadataValue {
      value
    }
    ... on CustomMetadataValues {
      values
    }
  }
  copyright {
    status
    notice
  }
  licenses {
    title
    text: license
  }
}

fragment onImage on Image {
  author
  filename
  extension
  size
  downloadUrl(permanent: $permanent)
  previewUrl
  dynamicPreviewUrl
  thumbnailUrl
  width
  height
  focalPoint
}

fragment onFile on File {
  author
  filename
  extension
  size
  downloadUrl(permanent: $permanent)
  icon: previewUrl
  dynamicPreviewUrl
  thumbnailUrl
}

fragment onDocument on Document {
  author
  filename
  extension
  size
  pageCount
  downloadUrl(permanent: $permanent)
  previewUrl
  dynamicPreviewUrl
  thumbnailUrl
  focalPoint
}

fragment onAudio on Audio {
  author
  filename
  extension
  size
  downloadUrl(permanent: $permanent)
  previewUrl
  dynamicPreviewUrl
  thumbnailUrl
}

fragment onVideo on Video {
  author
  filename
  extension
  size
  downloadUrl(permanent: $permanent)
  previewUrl
  dynamicPreviewUrl
  thumbnailUrl
  width
  height
  duration
  bitrate
}
`;async function fe({domain:t,bearerToken:e,permanentDownloadUrls:n},o){const s=await le(`https://${t}/graphql`,{method:"POST",headers:{"content-type":"application/json",authorization:`Bearer ${e}`,"x-frontify-finder-version":`v${me}`,"x-frontify-beta":"enabled","x-frontify-development-flags":"PUBLIC_API_DYNAMIC_CDN"},body:JSON.stringify({query:Ee,variables:{ids:o,permanent:n}})});if(s.errors&&d("error",{code:"ERR_FINDER_ASSETS_REQUEST",message:"Assets data request failed.",error:s.errors[0]}),!s?.data?.assets||s.data.assets.length===0)throw new u("ERR_FINDER_ASSETS_REQUEST_EMPTY","Assets data request returned no valid values.");return s.data.assets.map(p=>(p.previewUrl&&(p.previewUrl=p.previewUrl.split("?")[0]),p))}var we=Object.defineProperty,_e=(t,e,n)=>e in t?we(t,e,{enumerable:!0,configurable:!0,writable:!0,value:n}):t[e]=n,g=(t,e,n)=>_e(t,typeof e!="symbol"?e+"":e,n);class S{constructor(e,n,o){this.token=e,this.options=n,this.onLogoutRequested=o,g(this,"parentNode"),g(this,"iFrame"),g(this,"listeners",{}),g(this,"unsubscribe"),this.iFrame=Te(e.bearerToken.domain)}static get VERSION(){return 2}subscribeToFinderEvents(){const e=this.parentNode?.ownerDocument?.defaultView||window;this.unsubscribe=ge(e,"message",n=>{if(!(this.iFrame.contentWindow!==n.source||n.origin!==this.origin)){if(n.data.configurationRequested){this.initialize();return}if(n.data.assetsChosen){try{this.handleAssetsChosen(n.data.assetsChosen.map(o=>o.id))}catch(o){throw o}return}if(n.data.aborted){this.handleFinderCancel();return}if(n.data.logout){this.onLogoutRequested(),this.handleFinderCancel();return}d("warning",{code:"WARN_FINDER_UNKNOWN_EVENT",message:"Unknown event from Frontify Finder."})}})}get origin(){return`https://${this.token.bearerToken.domain}`}get domain(){return this.token.bearerToken.domain}get accessToken(){return this.token.bearerToken.accessToken}initialize(){this.iFrame?.contentWindow?.postMessage({version:S.VERSION,token:this.accessToken,supports:{cancel:!0,logout:!0},multiSelectionAllowed:this.options?.allowMultiSelect??!1,filters:this.options?.filters},this.origin)}handleFinderCancel(){this.options.autoClose&&this.close(),this.listeners.cancel&&this.listeners.cancel()}async handleAssetsChosen(e){try{const n=await fe({domain:this.domain,bearerToken:this.accessToken,permanentDownloadUrls:this.options?.permanentDownloadUrls??!1},e);this.options?.autoClose&&this.close(),this.listeners.assetsChosen&&this.listeners.assetsChosen(n)}catch(n){n instanceof u||d("error",{code:"ERR_FINDER_ASSETS_SELECTION",message:"Failed retrieving assets data."})}}onAssetsChosen(e){return this.listeners.assetsChosen=e,this}onCancel(e){return this.listeners.cancel=e,this}mount(e){if(this.parentNode)throw new u("ERR_FINDER_ALREADY_MOUNTED","Frontify Finder already mounted on a parent node.");this.parentNode=e,this.subscribeToFinderEvents(),this.parentNode.appendChild(this.iFrame)}close(){try{this.unsubscribe&&this.unsubscribe(),this.parentNode&&this.parentNode.removeChild(this.iFrame)}catch{d("error",{code:"ERR_FINDER_CLOSE",message:"Error closing Frontify Finder."})}finally{delete this.parentNode,delete this.unsubscribe}}}function Te(t){const e=document.createElement("iframe");return e.style.border="none",e.style.outline="none",e.style.width="100%",e.style.height="100%",e.style.display="block",e.className="frontify-finder-iframe",e.src=`https://${t}/external-asset-chooser`,e.name="Frontify Finder",e.sandbox.add("allow-same-origin"),e.sandbox.add("allow-scripts"),e.sandbox.add("allow-forms"),e}function ge(t,e,n){const o=s=>{n(s)};return t.addEventListener(e,o),()=>{t.removeEventListener(e,o)}}const ye=["basic:read","finder:read"],Ue=300,Re={autoClose:!1,allowMultiSelect:!1,filters:[]};async function Ae({clientId:t,domain:e,options:n},o){if(!Ne({clientId:t})){const p=await ee({domain:e,clientId:t,scopes:ye},o).then(Se=>Se).catch(()=>{d("error",{code:"ERR_FINDER_AUTH_FAILED",message:"Authentication Failed!"})});be(p,{clientId:t})}const s=b(T(t));if(!s)throw new u("ERR_FINDER_ACCESS_STORED_TOKEN","Error accessing stored token.");return new S(s,n??Re,async()=>{await k({clientId:t}),d("warning",{code:"WARN_USER_LOGOUT",message:"User successfully logged out"})})}async function k({clientId:t}){const e=T(t),n=he(e);n&&await te(n)}function Ne({clientId:t}){const e=T(t);return!!b(e)}function be(t,{clientId:e}){const n=t.bearerToken.expiresIn-Ue,o=T(e);ue(o,t,n)}return y.create=Ae,y.logout=k,y}({});
//# sourceMappingURL=index.js.map
