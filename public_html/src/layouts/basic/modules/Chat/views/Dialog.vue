<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div v-if="config.isChatAllowed">
    <div class="drag-area">
      <drag :coordinates.sync="buttonCoordinates">
        <transition :enter-active-class="buttonAnimationClasses" mode="out-in">
          <q-btn
            round
            color="primary"
            class="glossy"
            @mouseup="showDialog"
            @touchend="showDialog"
            ref="chatBtn"
            :key="parseInt(data.amountOfNewMessages)"
            style="z-index: 99999999999;"
          >
            <icon icon="yfi-branding-chat" />
            <q-badge
              v-if="config.showNumberOfNewMessages"
              v-show="data.amountOfNewMessages > 0"
              color="danger"
              floating
            >
              <div>
                {{ data.amountOfNewMessages }}
              </div>
            </q-badge>
          </q-btn>
        </transition>
      </drag>
    </div>
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
		<update-watcher />
  </div>
</template>
<script>
import UpdateWatcher from '../components/UpdateWatcher.vue'
import Chat from '../components/Chat.vue'
import Drag from 'components/Drag.vue'
import DragResize from 'components/DragResize.vue'
import isEqual from 'lodash.isequal'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations, mapActions } = createNamespacedHelpers('Chat')
export default {
  name: 'Dialog',
  components: { UpdateWatcher, Chat, DragResize, Drag },
  data() {
    return {
      timerGlobal: null,
      dragging: false
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
    buttonCoordinates: {
      get() {
        return this.$store.getters['Chat/buttonCoordinates']
      },
      set(coords) {
        if (!isEqual(coords, { ...this.$store.getters['Chat/buttonCoordinates'] })) {
          this.dragging = true
          this.setButtonCoordinates(coords)
        }
      }
    },
    computedMiniMode() {
      return this.$q.platform.is.desktop ? this.miniMode : false
    },
    buttonAnimationClasses() {
      return this.data.amountOfNewMessages ? 'animated flash' : ''
    }
  },
  methods: {
    ...mapActions(['fetchChatConfig']),
    ...mapMutations(['setDialog', 'setCoordinates', 'setButtonCoordinates']),

    showDialog() {
      setTimeout(_ => {
        if (!this.dragging) {
          this.dialog = !this.dialog
        }
        this.dragging = false
      }, 300)
    }
  }
}
</script>
<style scoped>
.drag-area {
  width: 100%;
  height: 100%;
  position: absolute;
  top: 0;
  left: 0;
}
</style>
