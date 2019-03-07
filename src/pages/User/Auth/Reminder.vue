<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template v-if="$store.state.User.forgotPassword && showReminderForm">
  <form class="col q-gutter-md q-mx-lg" action="index.php?module=Users&action=ForgotPassword" method="POST">
    <q-input type="text" v-model="reminderUser" :title="$t('LBL_USER')" :label="$t('LBL_USER')" autocomplete="off">
      <template v-slot:prepend>
        <q-icon name="person" />
      </template>
    </q-input>
    <q-input type="text" v-model="reminderEmail" autocomplete="off" :title="$t('LBL_EMAIL')" :label="$t('LBL_EMAIL')">
      <template v-slot:prepend>
        <q-icon name="mail_outline" />
      </template>
    </q-input>
    <q-btn size="lg" :label="$t('LBL_SEND')" type="submit" color="secondary" class="full-width q-mt-lg" />
    <router-link :to="{ name: 'Login' }" class="text-secondary float-right">{{ $t('LBL_TO_CRM') }}</router-link>
  </form>
</template>
<script>
/**
 * @vue-data     {String} reminderEmail
 * @vue-data     {String} reminderUser
 */
export default {
  name: 'Reminder',
  data() {
    return {
      reminderEmail: '',
      reminderUser: ''
    }
  },
  mounted() {
    if (!this.$store.state.User.forgotPassword || this.$store.state.User.isBlockedIp) {
      this.$router.replace('/User/login')
    }
  }
}
</script>

<style scoped></style>
