/**
 * Bundled by jsDelivr using Rollup v2.79.1 and Terser v5.19.2.
 * Original file: /npm/vue@3.3.0/dist/vue.esm-bundler.js
 *
 * Do NOT use SRI with dynamically generated files! More information: https://www.jsdelivr.com/using-sri-with-dynamic-files
 */
import*as e from"@vue/runtime-dom";import{registerRuntimeCompiler as o}from"@vue/runtime-dom";export*from"@vue/runtime-dom";import{compile as n}from"@vue/compiler-dom";import{isString as t,NOOP as m,extend as r}from"@vue/shared";const u=Object.create(null);function i(o,i){if(!t(o)){if(!o.nodeType)return m;o=o.innerHTML}const s=o,c=u[s];if(c)return c;if("#"===o[0]){const e=document.querySelector(o);o=e?e.innerHTML:""}const p=r({hoistStatic:!0,onError:void 0,onWarn:m},i);p.isCustomElement||"undefined"==typeof customElements||(p.isCustomElement=e=>!!customElements.get(e));const{code:f}=n(o,p),d=new Function("Vue",f)(e);return d._rc=!0,u[s]=d}o(i);export{i as compile};export default null;
