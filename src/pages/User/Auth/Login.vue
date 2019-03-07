<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div>
    <form
      @submit.prevent.stop="onSubmit"
      class="col q-gutter-md q-mx-lg"
      :autocomplete="CONFIG.LOGIN_PAGE_REMEMBER_CREDENTIALS ? 'on' : 'off'"
    >
      <q-input
        type="text"
        ref="user"
        v-model="user"
        :label="$t('LBL_USER')"
        lazy-rules
        :rules="[val => (val && val.length > 0) || 'Please type something']"
        :autocomplete="CONFIG.LOGIN_PAGE_REMEMBER_CREDENTIALS ? 'on' : 'off'"
      />
      <q-input
        type="password"
        ref="password"
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
      <router-link v-if="CONFIG.FORGOT_PASSWORD" class="text-secondary float-right" :to="{ name: 'Reminder' }">{{
        $t('ForgotPassword')
      }}</router-link>
    </form>
  </div>
</template>
<script>
import actions from 'src/store/actions.js'
/**
 * @vue-prop     {Object} CONFIG - view config
 * @vue-data     {String} user - form data
 * @vue-data     {String} password - form data
 * @vue-data     {String} language - form data
 * @vue-data     {String} layout - form data
 * @vue-computed {String} msgClass - additional message class
 * @vue-event    {Object} onSubmit - submit form event
 */
export default {
  name: 'Login',
  props: {
    CONFIG: {}
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
        this.$store.dispatch(actions.User.login, {
          username: this.user,
          password: this.password,
          fingerPrint: ''
        })
      }
    }
  }
}
</script>

<style scoped></style>
