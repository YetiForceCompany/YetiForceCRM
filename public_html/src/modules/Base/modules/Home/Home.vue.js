/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import e from"/src/store/mutations.min.js";export function initialize({store:t,router:n}){t.commit(e.Core.Menu.addItem,{path:"/base/home",icon:"mdi-home",label:"Home",children:[]})}export default(function(e,t,n,o,r,i,s,c){const d=("function"==typeof n?n.options:n)||{};return d.__file="Home.vue",d.render||(d.render=e.render,d.staticRenderFns=e.staticRenderFns,d._compiled=!0,r&&(d.functional=!0)),d._scopeId=o,d}({render:function(){var e=this.$createElement;return(this._self._c||e)("div")},staticRenderFns:[]},0,{name:"Base.Home"},void 0,!1));