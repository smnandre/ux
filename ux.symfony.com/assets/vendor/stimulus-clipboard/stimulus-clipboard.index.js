/**
 * Bundled by jsDelivr using Rollup v2.79.1 and Terser v5.19.2.
 * Original file: /npm/stimulus-clipboard@3.3.0/dist/stimulus-clipboard.mjs
 *
 * Do NOT use SRI with dynamically generated files! More information: https://www.jsdelivr.com/using-sri-with-dynamic-files
 */
import{Controller as t}from"@hotwired/stimulus";class e extends t{connect(){this.hasButtonTarget&&(this.originalContent=this.buttonTarget.innerHTML)}copy(t){t.preventDefault();const e=this.sourceTarget.innerHTML||this.sourceTarget.value;navigator.clipboard.writeText(e).then((()=>this.copied()))}copied(){this.hasButtonTarget&&(this.timeout&&clearTimeout(this.timeout),this.buttonTarget.innerText=this.data.get("successContent"),this.timeout=setTimeout((()=>{this.buttonTarget.innerHTML=this.originalContent}),this.successDurationValue))}}e.targets=["button","source"],e.values={successDuration:{type:Number,default:2e3}};export{e as default};
