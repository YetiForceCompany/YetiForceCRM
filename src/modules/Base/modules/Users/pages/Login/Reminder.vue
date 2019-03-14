<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template v-if="$store.state.Base.Users.forgotPassword && showReminderForm">
  <form class="col q-gutter-md q-mx-lg" @submit.prevent.stop="onSubmit">
    <q-input
      type="text"
      ref="reminderUsers"
      v-model="reminderUsers"
      :label="$t('LBL_USER')"
      autocomplete="off"
      lazy-rules
      :rules="[val => (val && val.length > 0) || 'Please type something']"
    >
      <template v-slot:prepend>
        <q-icon name="person"/>
      </template>
    </q-input>
    <q-input
      type="text"
      ref="reminderEmail"
      v-model="reminderEmail"
      autocomplete="off"
      :label="$t('LBL_EMAIL')"
      lazy-rules
      :rules="[val => (val && val.length > 0) || 'Please type something']"
    >
      <template v-slot:prepend>
        <q-icon name="mail_outline"/>
      </template>
    </q-input>
    <q-btn
      size="lg"
      :label="$t('LBL_SEND')"
      type="submit"
      color="secondary"
      class="full-width q-mt-lg"
    />
    <router-link
      :to="{ name: 'LoginForm' }"
      class="text-secondary float-right"
    >{{ $t('LBL_TO_CRM') }}</router-link>
  </form>
</template>
<script>
import actions from 'src/store/actions.js'
/**
 * @vue-data     {String} reminderEmail
 * @vue-data     {String} reminderUsers
 */
export default {
  name: 'Reminder',
  data() {
    return {
      reminderUsers: '',
      reminderEmail: ''
    }
  },
  methods: {
    onSubmit() {
      this.$refs.reminderUsers.validate()
      this.$refs.reminderEmail.validate()
      if (this.$refs.reminderUsers.hasError || this.$refs.reminderEmail.hasError) {
        this.formHasError = true
      } else {
        this.$store.dispatch(actions.Base.Users.remind, {
          reminderUsers: this.reminderUsers,
          reminderEmail: this.reminderEmail
        })
      }
    }
  },
  mounted() {
    if (!this.$store.state.Base.Users.forgotPassword || this.$store.state.Base.Users.isBlockedIp) {
      this.$router.replace('/base/users/login/form')
    }
  }
}
</script>

<style scoped></style>
