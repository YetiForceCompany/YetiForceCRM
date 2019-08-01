<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div v-if="config.isChatAllowed">
    <transition :enter-active-class="buttonAnimationClasses" mode="out-in">
      <q-btn
        round
        color="primary"
        class="glossy"
        @click="dialog = !dialog"
        ref="chatBtn"
        :key="data.amountOfNewMessages"
      >
        <icon icon="yfi-branding-chat" />
        <q-badge v-if="config.showNumberOfNewMessages" v-show="data.amountOfNewMessages > 0" color="danger" floating>
          <div>
            {{ data.amountOfNewMessages }}
          </div>
        </q-badge>
      </q-btn>
    </transition>

    <q-dialog
      v-model="dialog"
      seamless
      :maximized="!computedMiniMode"
      transition-show="slide-up"
      transition-hide="slide-down"
      content-class="quasar-reset"
    >
      <drag-resize :coordinates.sync="coordinates" :maximized="!computedMiniMode">
        <chat container :parentRefs="$refs" />
      </drag-resize>
    </q-dialog>
  </div>
</template>
<script>
import Chat from './Chat.vue'
import DragResize from 'components/DragResize.vue'

import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations, mapActions } = createNamespacedHelpers('Chat')
export default {
  name: 'Dialog',
  components: { Chat, DragResize },
  data() {
    return {
      timerGlobal: null
    }
  },
  computed: {
    ...mapGetters(['miniMode', 'data', 'config']),
    dialog: {
      get() {
        return this.$store.getters['Chat/dialog']
      },
      set(isOpen) {
        this.setDialog(isOpen)
      }
    },
    coordinates: {
      get() {
        return this.$store.getters['Chat/coordinates']
      },
      set(coords) {
        this.setCoordinates(coords)
      }
    },
    computedMiniMode() {
      return this.$q.platform.is.desktop ? this.miniMode : false
    },
    buttonAnimationClasses() {
      return this.data.amountOfNewMessages ? 'animated flash' : ''
    }
  },
  watch: {
    dialog() {
      if (this.dialog) {
        clearInterval(this.timerGlobal)
      } else {
        this.trackNewMessages()
      }
    }
  },
  methods: {
    ...mapActions(['fetchChatConfig', 'updateAmountOfNewMessages']),
    ...mapMutations(['setDialog', 'setCoordinates']),
    initTimer() {
      this.timerGlobal = setTimeout(this.trackNewMessages, this.config.refreshTimeGlobal)
    },
    trackNewMessages() {
      AppConnector.request({
        module: 'Chat',
        action: 'ChatAjax',
        mode: 'trackNewMessages'
      }).done(({ result }) => {
        this.updateAmountOfNewMessages(result)
        this.initTimer()
      })
    }
  },
  created() {
    this.fetchChatConfig().then(result => {
			console.log(result)
      if (result.config.isChatAllowed) this.trackNewMessages()
    })
  }
}
</script>
<style module lang="stylus"></style>
