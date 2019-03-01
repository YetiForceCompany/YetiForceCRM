<template v-if="CONFIG.FORGOT_PASSWORD && showReminderForm">
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
    <a @click="toggleActiveComponent('login-form')" href="#" class="text-secondary float-right">{{
      $t('LBL_TO_CRM')
    }}</a>
  </form>
</template>
<script>
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
      reminderEmail: '',
      reminderUser: ''
    }
  }
}
</script>

<style scoped></style>
