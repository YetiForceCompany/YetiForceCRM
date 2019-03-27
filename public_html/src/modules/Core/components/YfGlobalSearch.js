/* script */
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
var __vue_script__ = {
  name: 'YfGlobalSearch',
  data: function data() {
    return {
      searchModule: 'All records',
      searchModules: ['All records', 'acc', 'con'],
      searchText: '',
      iconSize: '.75rem'
    };
  }
};
/* template */

var __vue_render__ = function __vue_render__() {
  var _vm = this;

  var _h = _vm.$createElement;

  var _c = _vm._self._c || _h;

  return _c("hook-wrapper", {
    staticClass: "Core-Component-YfGlobalSearch"
  }, [_c("div", [_c("q-btn", {
    staticClass: "lt-md",
    attrs: {
      round: "",
      size: _vm.iconSize,
      flat: "",
      icon: "mdi-magnify",
      dense: ""
    }
  }), _vm._v(" "), _c("div", {
    staticClass: "flex gt-sm"
  }, [_c("div", {
    staticClass: "q-pl-sm headerField",
    staticStyle: {
      "min-width": "200px"
    }
  }, [_c("q-select", {
    attrs: {
      options: _vm.searchModules,
      placeholder: "Placeholder",
      dense: ""
    },
    model: {
      value: _vm.searchModule,
      callback: function callback($$v) {
        _vm.searchModule = $$v;
      },
      expression: "searchModule"
    }
  })], 1), _vm._v(" "), _c("q-input", {
    staticClass: "q-pl-lg headerField",
    staticStyle: {
      "min-width": "200px"
    },
    attrs: {
      placeholder: "Placeholder",
      dense: ""
    },
    scopedSlots: _vm._u([{
      key: "after",
      fn: function fn() {
        return [_c("q-btn", {
          attrs: {
            round: "",
            size: _vm.iconSize,
            flat: "",
            icon: "mdi-magnify",
            dense: ""
          }
        }), _vm._v(" "), _c("q-btn", {
          attrs: {
            round: "",
            size: _vm.iconSize,
            flat: "",
            icon: "mdi-format-text",
            dense: ""
          }
        }), _vm._v(" "), _c("q-btn", {
          attrs: {
            round: "",
            size: _vm.iconSize,
            flat: "",
            icon: "mdi-feature-search-outline",
            dense: ""
          }
        })];
      },
      proxy: true
    }]),
    model: {
      value: _vm.searchText,
      callback: function callback($$v) {
        _vm.searchText = $$v;
      },
      expression: "searchText"
    }
  })], 1)], 1)]);
};

var __vue_staticRenderFns__ = [];
__vue_render__._withStripped = true;
/* style */

var __vue_inject_styles__ = function __vue_inject_styles__(inject) {
  if (!inject) return;
  inject("data-v-0a32a088_0", {
    source: ".headerField[data-v-0a32a088],\n.headerField input[data-v-0a32a088] {\n  min-width: 200px;\n}\n",
    map: {
      "version": 3,
      "sources": ["C:\\www\\YetiForceCRM\\public_html\\src\\modules\\Core\\components\\YfGlobalSearch.vue", "YfGlobalSearch.vue"],
      "names": [],
      "mappings": "AAyCA;;EACA,gBAAA;ACvCA",
      "file": "YfGlobalSearch.vue",
      "sourcesContent": ["<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->\r\n<template>\r\n  <hook-wrapper class=\"Core-Component-YfGlobalSearch\">\r\n    <div>\r\n      <q-btn class=\"lt-md\" round :size=\"iconSize\" flat icon=\"mdi-magnify\" dense />\r\n      <div class=\"flex gt-sm\">\r\n        <div class=\"q-pl-sm headerField\" style=\"min-width: 200px\">\r\n          <q-select v-model=\"searchModule\" :options=\"searchModules\" placeholder=\"Placeholder\" dense />\r\n        </div>\r\n        <q-input\r\n          class=\"q-pl-lg headerField\"\r\n          style=\"min-width: 200px\"\r\n          v-model=\"searchText\"\r\n          placeholder=\"Placeholder\"\r\n          dense\r\n        >\r\n          <template v-slot:after>\r\n            <q-btn round :size=\"iconSize\" flat icon=\"mdi-magnify\" dense />\r\n            <q-btn round :size=\"iconSize\" flat icon=\"mdi-format-text\" dense />\r\n            <q-btn round :size=\"iconSize\" flat icon=\"mdi-feature-search-outline\" dense />\r\n          </template>\r\n        </q-input>\r\n      </div>\r\n    </div>\r\n  </hook-wrapper>\r\n</template>\r\n<script>\r\nexport default {\r\n  name: 'YfGlobalSearch',\r\n  data() {\r\n    return {\r\n      searchModule: 'All records',\r\n      searchModules: ['All records', 'acc', 'con'],\r\n      searchText: '',\r\n      iconSize: '.75rem'\r\n    }\r\n  }\r\n}\r\n</script>\r\n\r\n<style scoped lang=\"stylus\">\r\n.headerField, .headerField input {\r\n  min-width: 200px;\r\n}\r\n</style>\r\n", ".headerField,\n.headerField input {\n  min-width: 200px;\n}\n"]
    },
    media: undefined
  });
};
/* scoped */


var __vue_scope_id__ = "data-v-0a32a088";
/* module identifier */

var __vue_module_identifier__ = undefined;
/* functional template */

var __vue_is_functional_template__ = false;
/* component normalizer */

function __vue_normalize__(template, style, script, scope, functional, moduleIdentifier, createInjector, createInjectorSSR) {
  var component = (typeof script === 'function' ? script.options : script) || {}; // For security concerns, we use only base name in production mode.

  component.__file = "C:\\www\\YetiForceCRM\\public_html\\src\\modules\\Core\\components\\YfGlobalSearch.vue";

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

      if (false && css.map) {
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