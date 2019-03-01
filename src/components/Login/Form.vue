<template>
  <div>
    <form
      @submit.prevent.stop="onSubmit"
      class="col q-gutter-md q-mx-lg"
      :autocomplete="CONFIG.LOGIN_PAGE_REMEMBER_CREDENTIALS ? 'on' : 'off'"
    >
      <q-input
        type="text"
        v-model="user"
        :label="$t('LBL_USER')"
        lazy-rules
        :rules="[val => (val && val.length > 0) || 'Please type something']"
        :autocomplete="CONFIG.LOGIN_PAGE_REMEMBER_CREDENTIALS ? 'on' : 'off'"
      />
      <q-input
        type="password"
        v-model="password"
        :label="$t('Password')"
        lazy-rules
        :rules="[val => (val && val.length > 0) || 'Please type something']"
        :autocomplete="CONFIG.LOGIN_PAGE_REMEMBER_CREDENTIALS ? 'on' : 'off'"
      />
      <q-select
        v-if="CONFIG.LANGUAGE_SELECTION"
        v-model="language"
        :options="CONFIG.LANGUAGES"
        :label="$t('LBL_CHOOSE_LANGUAGE')"
      >
        <template v-slot:prepend>
          <q-icon name="translate" />
        </template>
      </q-select>
      <q-select
        v-if="CONFIG.LAYOUT_SELECTION"
        v-model="layout"
        :options="CONFIG.LAYOUTS"
        :label="$t('LBL_SELECT_LAYOUT')"
      >
        <template v-slot:prepend>
          <q-icon name="looks" />
        </template>
      </q-select>
      <q-btn size="lg" :label="$t('LBL_SIGN_IN')" type="submit" color="secondary" class="full-width q-mt-lg" />
      <a
        v-if="CONFIG.FORGOT_PASSWORD"
        @click="toggleActiveComponent('reminder-form')"
        class="text-secondary float-right"
        href="#"
        >{{ $t('ForgotPassword') }}</a
      >
    </form>
  </div>
</template>

<script>
import actions from '../../store/actions.js'

export default {
  props: {
    CONFIG: {
      type: Object
    },
    toggleActiveComponent: {
      type: Function
    }
  },
  data() {
    return {
      user: '',
      password: '',
      language: this.CONFIG.DEFAULT_LANGUAGE, //AppConfig::main('default_language')
      layout: ''
    }
  },
  computed: {
    msgClass: function() {
      return {
        'bg-positive': this.CONFIG.MESSAGE_TYPE === 'success',
        'bg-negative': this.CONFIG.MESSAGE_TYPE === 'error',
        'bg-warning': this.CONFIG.MESSAGE_TYPE === ''
      }
    }
  },
  methods: {
    onSubmit() {
      this.$refs.user.validate()
      this.$refs.password.validate()
      if (this.$refs.user.hasError || this.$refs.password.hasError) {
        this.formHasError = true
      } else {
        this.$store.dispatch(actions.Login.login, { user: this.user, password: this.password, fingerPrint: '' })
      }
    }
  }
}
</script>

<style scoped></style>
