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
//
//
//
//
//
//
//

import actions from '/src/store/actions.js';
/**
 * @vue-data     {String} reminderEmail
 * @vue-data     {String} reminderUsers
 */
var __script__ = {
  name: 'Reminder',
  data: function data() {
    return {
      reminderUsers: '',
      reminderEmail: ''
    };
  },

  methods: {
    onSubmit: function onSubmit() {
      this.$refs.reminderUsers.validate();
      this.$refs.reminderEmail.validate();
      if (this.$refs.reminderUsers.hasError || this.$refs.reminderEmail.hasError) {
        this.formHasError = true;
      } else {
        this.$store.dispatch(actions.Core.Users.remind, {
          reminderUsers: this.reminderUsers,
          reminderEmail: this.reminderEmail
        });
      }
    }
  },
  mounted: function mounted() {
    if (!this.$store.state.Core.Users.forgotPassword || this.$store.state.Core.Users.isBlockedIp) {
      this.$router.replace('/users/login/form');
    }
  }
};

var render = function render() {
  var _vm = this;var _h = _vm.$createElement;var _c = _vm._self._c || _h;
  return _c('form', {
    staticClass: "col q-gutter-md q-mx-lg",
    on: {
      "submit": function submit($event) {
        $event.preventDefault();
        $event.stopPropagation();
        return _vm.onSubmit($event);
      }
    }
  }, [_c('q-input', {
    ref: "reminderUsers",
    attrs: {
      "type": "text",
      "label": _vm.$t('LBL_USER'),
      "autocomplete": "off",
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
            "name": "mdi-account"
          }
        })];
      },
      proxy: true
    }]),
    model: {
      value: _vm.reminderUsers,
      callback: function callback($$v) {
        _vm.reminderUsers = $$v;
      },
      expression: "reminderUsers"
    }
  }), _vm._v(" "), _c('q-input', {
    ref: "reminderEmail",
    attrs: {
      "type": "text",
      "autocomplete": "off",
      "label": _vm.$t('LBL_EMAIL'),
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
            "name": "mail_outline"
          }
        })];
      },
      proxy: true
    }]),
    model: {
      value: _vm.reminderEmail,
      callback: function callback($$v) {
        _vm.reminderEmail = $$v;
      },
      expression: "reminderEmail"
    }
  }), _vm._v(" "), _c('q-btn', {
    staticClass: "full-width q-mt-lg",
    attrs: {
      "size": "lg",
      "label": _vm.$t('LBL_SEND'),
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
  }, [_vm._v(_vm._s(_vm.$t('LBL_TO_CRM')))])], 1);
};
var staticRenderFns = [];
var __template__ = { render: render, staticRenderFns: staticRenderFns };

export default Object.assign({}, __script__, __template__);