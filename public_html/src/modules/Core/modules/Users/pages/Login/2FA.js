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

import actions from '/src/store/actions.js';
/**
 * @vue-prop     {Object} CONFIG - view config
 * @vue-data     {String} user - form data
 * @vue-data     {String} password - form data
 * @vue-data     {String} language - form data
 * @vue-data     {String} layout - form data
 * @vue-computed {String} msgClass - additional message class
 * @vue-event    {Object} onSubmit - submit form event
 */
var __script__ = {
  name: 'Login',
  data: function data() {
    return {
      user_code: ''
    };
  },

  methods: {
    onSubmit: function onSubmit() {
      this.$refs.user_code.validate();
      if (this.$refs.user_code.hasError) {
        this.formHasError = true;
      } else {
        this.$store.dispatch(actions.Core.Users.login, {
          user_code: this.user_code
        });
      }
    }
  }
};

var render = function render() {
  var _vm = this;var _h = _vm.$createElement;var _c = _vm._self._c || _h;
  return _c('div', [_c('form', {
    staticClass: "col q-gutter-md q-mx-lg",
    on: {
      "submit": function submit($event) {
        $event.preventDefault();
        $event.stopPropagation();
        return _vm.onSubmit($event);
      }
    }
  }, [_c('q-input', {
    ref: "user_code",
    attrs: {
      "type": "text",
      "autocomplete": "off",
      "autofocus": "",
      "label": _vm.$t('PLL_AUTHY_TOTP'),
      "lazy-rules": "",
      "rules": [function (val) {
        return val && val.length > 0 || 'Please type something';
      }]
    },
    scopedSlots: _vm._u([{
      key: "prepend",
      fn: function fn() {
        return [_c('q-icon', {
          attrs: {
            "name": "mdi-key"
          }
        })];
      },
      proxy: true
    }]),
    model: {
      value: _vm.user_code,
      callback: function callback($$v) {
        _vm.user_code = $$v;
      },
      expression: "user_code"
    }
  }), _vm._v(" "), _c('q-btn', {
    staticClass: "full-width q-mt-lg",
    attrs: {
      "size": "lg",
      "label": _vm.$t('LBL_SIGN_IN'),
      "type": "submit",
      "color": "secondary"
    }
  }), _vm._v(" "), _c('router-link', {
    staticClass: "text-secondary float-right",
    attrs: {
      "to": {
        name: 'LoginForm'
      }
    }
  }, [_vm._v(_vm._s(_vm.$t('LBL_TO_CRM')))])], 1)]);
};
var staticRenderFns = [];
var __template__ = { render: render, staticRenderFns: staticRenderFns };

export default Object.assign({}, __script__, __template__);