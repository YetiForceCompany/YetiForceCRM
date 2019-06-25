<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div class="inline-block">
    <q-btn round color="white" text-color="black" icon="mdi-message-text-outline" class="text-muted" @click="dialog = true" />
    <q-dialog
      v-model="dialog"
			seamless
      :maximized="maximizedDialog"
      transition-show="slide-up"
      transition-hide="slide-down"
      content-class="quasar-reset"
    >
      <chat container />
    </q-dialog>
  </div>
</template>
<script>
import Chat from './Chat.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions } = createNamespacedHelpers('Chat')
export default {
  name: 'Dialog',
  components: { Chat },
  data() {
    return {
      iconSize: '.75rem',
      placeholder: 'Wyszukaj wiadomość',
      visible: false,
      groupFooter: 'Grupa',
      roomFooter: 'Pokój',
      left: true,
      right: true,
      tabHistory: 'ulubiony',
      tabHistoryShow: false,
      submitting: false,
      moduleName: 'Chat',
      dense: false
    }
  },
  computed: {
    dialog: {
      get() {
        return this.$store.getters['Chat/dialog']
      },
      set(isOpen) {
        this.setDialog(isOpen)
      }
    },
    ...mapGetters(['maximizedDialog'])
  },
  methods: {
    ...mapActions(['setDialog'])
  }
}
</script>
<style module lang="stylus"></style>
