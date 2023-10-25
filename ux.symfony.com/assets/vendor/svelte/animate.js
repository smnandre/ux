/**
 * Bundled by jsDelivr using Rollup v2.79.1 and Terser v5.17.1.
 * Original file: /npm/svelte@3.59.1/animate/index.mjs
 *
 * Do NOT use SRI with dynamically generated files! More information: https://www.jsdelivr.com/using-sri-with-dynamic-files
 */
function t(t){const n=t-1;return n*n*n+1}function n(n,{from:r,to:e},o={}){const a=getComputedStyle(n),i="none"===a.transform?"":a.transform,[s,h]=a.transformOrigin.split(" ").map(parseFloat),f=r.left+r.width*s/e.width-(e.left+s),l=r.top+r.height*h/e.height-(e.top+h),{delay:p=0,duration:u=(t=>120*Math.sqrt(t)),easing:d=t}=o;return{delay:p,duration:(c=u,"function"==typeof c?u(Math.sqrt(f*f+l*l)):u),easing:d,css:(t,n)=>{const o=n*f,a=n*l,s=t+n*r.width/e.width,h=t+n*r.height/e.height;return`transform: ${i} translate(${o}px, ${a}px) scale(${s}, ${h});`}};var c}export{n as flip};export default null;
