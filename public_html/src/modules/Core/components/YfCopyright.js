/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

/* script */
var __vue_script__ = {
  name: 'YfCopyright'
  /* template */

};

var __vue_render__ = function __vue_render__() {
  var _vm = this;

  var _h = _vm.$createElement;

  var _c = _vm._self._c || _h;

  return _c('hook-wrapper', {
    staticClass: "Core-Component-YfCopyright"
  }, [_c('div', {
    staticClass: "flex"
  }, [_c('div', [_c('div', [_vm._v("© YetiForce.com All rights reserved.")]), _vm._v(" "), _c('q-separator', {
    attrs: {
      "dark": ""
    }
  }), _vm._v(" "), _c('div', {
    staticClass: "flex no-wrap justify-between q-py-xs"
  }, [_c('a', {
    staticClass: "q-link text-blue-grey-11",
    attrs: {
      "href": "https://www.linkedin.com/groups/8177576",
      "rel": "noreferrer noopener"
    }
  }, [_c('q-icon', {
    attrs: {
      "size": "16px",
      "name": "mdi-linkedin",
      "title": "Linkedin"
    }
  })], 1), _vm._v(" "), _c('q-separator', {
    staticClass: "q-mx-sm",
    attrs: {
      "dark": "",
      "vertical": ""
    }
  }), _vm._v(" "), _c('a', {
    staticClass: "q-link text-blue-grey-11",
    attrs: {
      "href": "https://twitter.com/YetiForceEN",
      "rel": "noreferrer noopener"
    }
  }, [_c('q-icon', {
    attrs: {
      "size": "16px",
      "name": "mdi-twitter",
      "title": "Twitter"
    }
  })], 1), _vm._v(" "), _c('q-separator', {
    staticClass: "q-mx-sm",
    attrs: {
      "dark": "",
      "vertical": ""
    }
  }), _vm._v(" "), _c('a', {
    staticClass: "q-link text-blue-grey-11",
    attrs: {
      "href": "https://www.facebook.com/YetiForce-CRM-158646854306054/",
      "rel": "noreferrer noopener"
    }
  }, [_c('q-icon', {
    attrs: {
      "size": "16px",
      "name": "mdi-facebook",
      "title": "Facebook"
    }
  })], 1), _vm._v(" "), _c('q-separator', {
    staticClass: "q-mx-sm",
    attrs: {
      "dark": "",
      "vertical": ""
    }
  }), _vm._v(" "), _c('a', {
    staticClass: "q-link text-blue-grey-11",
    attrs: {
      "href": "https://github.com/YetiForceCompany/YetiForceCRM",
      "rel": "noreferrer noopener"
    }
  }, [_c('q-icon', {
    attrs: {
      "size": "16px",
      "name": "mdi-github-circle",
      "title": "Github"
    }
  })], 1), _vm._v(" "), _c('q-separator', {
    staticClass: "q-mx-sm",
    attrs: {
      "dark": "",
      "vertical": ""
    }
  }), _vm._v(" "), _c('a', {
    staticClass: "q-link text-blue-grey-11",
    attrs: {
      "href": "https://yetiforce.shop",
      "rel": "noreferrer noopener"
    }
  }, [_c('q-icon', {
    attrs: {
      "size": "18px",
      "name": "mdi-cart-outline",
      "title": "yetiforce.shop"
    }
  })], 1), _vm._v(" "), _c('q-separator', {
    staticClass: "q-mx-sm",
    attrs: {
      "dark": "",
      "vertical": ""
    }
  }), _vm._v(" "), _c('a', {
    staticClass: "q-link text-blue-grey-11",
    attrs: {
      "href": "#",
      "role": "button"
    }
  }, [_c('q-icon', {
    attrs: {
      "size": "18px",
      "name": "mdi-information-outline",
      "title": "YetiForceCRM"
    }
  })], 1)], 1)], 1), _vm._v(" "), _c('div', {
    staticClass: "flex flex-center gt-xs q-ml-sm"
  }, [_c('q-avatar', {
    staticClass: "q-mx-auto",
    attrs: {
      "size": "32px"
    }
  }, [_c('img', {
    attrs: {
      "src": "/src/statics/Logo/white_logo_yetiforce.png",
      "alt": "yetiforce logo"
    }
  })])], 1)])]);
};

var __vue_staticRenderFns__ = [];
/* style */

var __vue_inject_styles__ = function __vue_inject_styles__(inject) {
  if (!inject) return;
  inject("data-v-18b6b512_0", {
    source: "",
    map: undefined,
    media: undefined
  });
  Object.defineProperty(this, "$style", {
    value: {}
  });
};
/* scoped */


var __vue_scope_id__ = undefined;
/* module identifier */

var __vue_module_identifier__ = undefined;
/* functional template */

var __vue_is_functional_template__ = false;
/* component normalizer */

function __vue_normalize__(template, style, script, scope, functional, moduleIdentifier, createInjector, createInjectorSSR) {
  var component = (typeof script === 'function' ? script.options : script) || {}; // For security concerns, we use only base name in production mode.

  component.__file = "YfCopyright.vue";

  if (!component.render) {
    component.render = template.render;
    component.staticRenderFns = template.staticRenderFns;
    component._compiled = true;
    if (functional) component.functional = true;
  }

  component._scopeId = scope;

  if (true) {
    var hook;

    if (false) {
      // In SSR.
      hook = function hook(context) {
        // 2.3 injection
        context = context || // cached call
        this.$vnode && this.$vnode.ssrContext || // stateful
        this.parent && this.parent.$vnode && this.parent.$vnode.ssrContext; // functional
        // 2.2 with runInNewContext: true

        if (!context && typeof __VUE_SSR_CONTEXT__ !== 'undefined') {
          context = __VUE_SSR_CONTEXT__;
        } // inject component styles


        if (style) {
          style.call(this, createInjectorSSR(context));
        } // register component module identifier for async chunk inference


        if (context && context._registeredComponents) {
          context._registeredComponents.add(moduleIdentifier);
        }
      }; // used by ssr in case component is cached and beforeCreate
      // never gets called


      component._ssrRegister = hook;
    } else if (style) {
      hook = function hook(context) {
        style.call(this, createInjector(context));
      };
    }

    if (hook !== undefined) {
      if (component.functional) {
        // register for functional component in vue file
        var originalRender = component.render;

        component.render = function renderWithStyleInjection(h, context) {
          hook.call(context);
          return originalRender(h, context);
        };
      } else {
        // inject component registration as beforeCreate hook
        var existing = component.beforeCreate;
        component.beforeCreate = existing ? [].concat(existing, hook) : [hook];
      }
    }
  }

  return component;
}
/* style inject */


function __vue_create_injector__() {
  var head = document.head || document.getElementsByTagName('head')[0];
  var styles = __vue_create_injector__.styles || (__vue_create_injector__.styles = {});
  var isOldIE = typeof navigator !== 'undefined' && /msie [6-9]\\b/.test(navigator.userAgent.toLowerCase());
  return function addStyle(id, css) {
    if (document.querySelector('style[data-vue-ssr-id~="' + id + '"]')) return; // SSR styles are present.

    var group = isOldIE ? css.media || 'default' : id;
    var style = styles[group] || (styles[group] = {
      ids: [],
      parts: [],
      element: undefined
    });

    if (!style.ids.includes(id)) {
      var code = css.source;
      var index = style.ids.length;
      style.ids.push(id);

      if (true && css.map) {
        // https://developer.chrome.com/devtools/docs/javascript-debugging
        // this makes source maps inside style tags work properly in Chrome
        code += '\n/*# sourceURL=' + css.map.sources[0] + ' */'; // http://stackoverflow.com/a/26603875

        code += '\n/*# sourceMappingURL=data:application/json;base64,' + btoa(unescape(encodeURIComponent(JSON.stringify(css.map)))) + ' */';
      }

      if (isOldIE) {
        style.element = style.element || document.querySelector('style[data-group=' + group + ']');
      }

      if (!style.element) {
        var el = style.element = document.createElement('style');
        el.type = 'text/css';
        if (css.media) el.setAttribute('media', css.media);

        if (isOldIE) {
          el.setAttribute('data-group', group);
          el.setAttribute('data-next-index', '0');
        }

        head.appendChild(el);
      }

      if (isOldIE) {
        index = parseInt(style.element.getAttribute('data-next-index'));
        style.element.setAttribute('data-next-index', index + 1);
      }

      if (style.element.styleSheet) {
        style.parts.push(code);
        style.element.styleSheet.cssText = style.parts.filter(Boolean).join('\n');
      } else {
        var textNode = document.createTextNode(code);
        var nodes = style.element.childNodes;
        if (nodes[index]) style.element.removeChild(nodes[index]);
        if (nodes.length) style.element.insertBefore(textNode, nodes[index]);else style.element.appendChild(textNode);
      }
    }
  };
}
/* style inject SSR */


export default __vue_normalize__({
  render: __vue_render__,
  staticRenderFns: __vue_staticRenderFns__
}, __vue_inject_styles__, __vue_script__, __vue_scope_id__, __vue_is_functional_template__, __vue_module_identifier__, __vue_create_injector__, undefined);