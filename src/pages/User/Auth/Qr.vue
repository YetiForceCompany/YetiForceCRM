<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div>
    <form @submit.prevent.stop="onSubmit" class="col q-gutter-md q-mx-lg">
      <q-input
        type="text"
        v-model="user_code"
        autocomplete="off"
        autofocus
        ref="user_code"
        :label="$t('PLL_AUTHY_TOTP')"
        lazy-rules
        :rules="[val => (val && val.length > 0) || 'Please type something']"
      >
        <template v-slot:prepend>
          <q-icon name="mdi-key" />
        </template>
      </q-input>
      <q-btn size="lg" :label="$t('LBL_SIGN_IN')" type="submit" color="secondary" class="full-width q-mt-lg" />
      <router-link :to="{ name: 'Login' }" class="text-secondary float-right">{{ $t('LBL_TO_CRM') }}</router-link>
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
  data() {
    return {
      user_code: ''
    }
  },
  methods: {
    onSubmit() {
      this.$refs.user_code.validate()
      if (this.$refs.user_code.hasError) {
        this.formHasError = true
      } else {
        this.$store.dispatch(actions.User.login, {
          user_code: this.user_code
        })
      }
    }
  }
}
</script>

<style scoped></style>
