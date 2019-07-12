<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-btn
    @click="toggleDesktopNotification()"
    dense
    round
    flat
    :loading="isWaitingForPermission"
    :icon="storage.isDesktopNotification ? 'mdi-bell-outline' : 'mdi-bell-off-outline'"
    :color="storage.isDesktopNotification ? 'info' : ''"
  />
</template>
<script>
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations } = createNamespacedHelpers('Chat')

export default {
  name: 'NotifyBtn',
  data() {
    return {
      isWaitingForPermission: false
    }
  },
  computed: {
    ...mapGetters(['storage', 'config'])
  },
  methods: {
    ...mapMutations(['setDesktopNotification']),
    toggleDesktopNotification() {
      if (!this.storage.isDesktopNotification && !this.storage.isNotificationPermitted) {
        this.isWaitingForPermission = true
        PNotify.modules.Desktop.permission()
        setTimeout(() => {
          if (!this.storage.isNotificationPermitted) {
            Vtiger_Helper_Js.showPnotify({
              text: app.vtranslate('JS_NO_DESKTOP_PERMISSION'),
              type: 'info',
              animation: 'show'
            })
          } else {
            this.setDesktopNotification(!this.storage.isDesktopNotification)
            app.setCookie('chat-isDesktopNotification', true, 365)
          }
          this.isWaitingForPermission = false
        }, 3000)
      } else {
        this.setDesktopNotification(!this.storage.isDesktopNotification)
      }
    }
  },
  created() {
    if (this.storage.isDesktopNotification && !this.storage.isNotificationPermitted) {
      this.setDesktopNotification(false)
    }
  }
}
</script>
<style lang="sass">
</style>
