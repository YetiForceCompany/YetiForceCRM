/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import e from"/src/ModuleLoader.min.js";import r from"./store/index.min.js";const t="Base";export function initialize({store:n,router:i}){n.registerModule(t.split("."),e.prepareStoreNames(t,r))}export default(function(e,r,t,n,i,o,s,d){const c=("function"==typeof t?t.options:t)||{};return c.__file="Base.vue",c.render||(c.render=e.render,c.staticRenderFns=e.staticRenderFns,c._compiled=!0,i&&(c.functional=!0)),c._scopeId=n,c}({render:function(){var e=this.$createElement;return(this._self._c||e)("div")},staticRenderFns:[]},0,{name:t},void 0,!1));