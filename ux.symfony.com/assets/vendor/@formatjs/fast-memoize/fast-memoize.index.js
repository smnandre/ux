/**
 * Bundled by jsDelivr using Rollup v2.79.1 and Terser v5.19.2.
 * Original file: /npm/@formatjs/fast-memoize@2.2.0/lib/index.js
 *
 * Do NOT use SRI with dynamically generated files! More information: https://www.jsdelivr.com/using-sri-with-dynamic-files
 */
function e(e,t){var r=t&&t.cache?t.cache:o,n=t&&t.serializer?t.serializer:i;return(t&&t.strategy?t.strategy:c)(e,{cache:r,serializer:n})}function t(e,t,r,n){var c,i=null==(c=n)||"number"==typeof c||"boolean"==typeof c?n:r(n),a=t.get(i);return void 0===a&&(a=e.call(this,n),t.set(i,a)),a}function r(e,t,r){var n=Array.prototype.slice.call(arguments,3),c=r(n),i=t.get(c);return void 0===i&&(i=e.apply(this,n),t.set(c,i)),i}function n(e,t,r,n,c){return r.bind(t,e,n,c)}function c(e,c){return n(e,this,1===e.length?t:r,c.cache.create(),c.serializer)}var i=function(){return JSON.stringify(arguments)};function a(){this.cache=Object.create(null)}a.prototype.get=function(e){return this.cache[e]},a.prototype.set=function(e,t){this.cache[e]=t};var o={create:function(){return new a}},u={variadic:function(e,t){return n(e,this,r,t.cache.create(),t.serializer)},monadic:function(e,r){return n(e,this,t,r.cache.create(),r.serializer)}};export{e as memoize,u as strategies};export default null;
