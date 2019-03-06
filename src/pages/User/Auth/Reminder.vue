<template v-if="CONFIG.FORGOT_PASSWORD && showReminderForm">
  <form
    class="col q-gutter-md q-mx-lg"
    action="index.php?module=Users&action=ForgotPassword"
    method="POST"
  >
    <q-input
      type="text"
      v-model="reminderUser"
      :title="$t('LBL_USER')"
      :label="$t('LBL_USER')"
      autocomplete="off"
    >
      <template v-slot:prepend>
        <q-icon name="person"/>
      </template>
    </q-input>
    <q-input
      type="text"
      v-model="reminderEmail"
      autocomplete="off"
      :title="$t('LBL_EMAIL')"
      :label="$t('LBL_EMAIL')"
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
    <router-link :to="{ name: 'Login' }" class="text-secondary float-right">{{ $t('LBL_TO_CRM') }}</router-link>
  </form>
</template>
<script>
/**
 * @vue-prop     {Object} CONFIG - view config
 * @vue-data     {String} reminderEmail
 * @vue-data     {String} reminderUser
 */
export default {
  name: 'Reminder',
  props: {
    CONFIG: {
      type: Object
    }
  },
  data() {
    return {
      reminderEmail: '',
      reminderUser: ''
    }
  },
  mounted() {
    if (!this.CONFIG.FORGOT_PASSWORD || this.CONFIG.IS_BLOCKED_IP) {
      this.$router.replace('/User/login')
    }
  }
}
</script>

<style scoped></style>
