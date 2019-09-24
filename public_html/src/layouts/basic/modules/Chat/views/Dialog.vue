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
            <q-badge
              v-if="hasCurrentRecordChat"
              @mouseup="addRecordRoomToChat()"
              @touchend="addRecordRoomToChat()"
              class="shadow-3 text-primary badge-button"
              color="white"
              floating
            >
              <q-icon name="mdi-plus" size="1rem" />
              <q-tooltip>{{ translate('JS_CHAT_ROOM_ADD_CURRENT') }}</q-tooltip>
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
const { mapGetters, mapMutations } = createNamespacedHelpers('Chat')
export default {
  name: 'Dialog',
  components: { UpdateWatcher, Chat, DragResize, Drag },
  data() {
    return {
      timerGlobal: null,
      dragging: false,
      windowConfig: CONFIG,
      addingRoom: false
    }
  },
  computed: {
    ...mapGetters(['miniMode', 'data', 'config', 'getRelatedRecord']),
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
        if (!isEqual({ left: coords.left, top: coords.top }, { ...this.$store.getters['Chat/buttonCoordinates'] })) {
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
    },
    hasCurrentRecordChat() {
      let id = false
      if (this.isDetail) {
        id = app.getRecordId()
        console.log(id)
      }
      if (this.getRelatedRecord && this.config.chatModules.some(el => el.id === this.getRelatedRecord.module)) {
        id = this.getRelatedRecord.id
      }
      if (id && !this.data.roomList.crm[id]) {
        return true
      } else {
        return false
      }
    },
    isDetail() {
      return (
        this.windowConfig.view === 'Detail' && this.config.chatModules.some(el => el.id === this.windowConfig.module)
      )
    }
  },
  methods: {
    ...mapMutations(['setDialog', 'setCoordinates', 'setButtonCoordinates', 'updateRooms']),
    showDialog() {
      setTimeout(_ => {
        if (!this.dragging && !this.addingRoom) {
          this.dialog = !this.dialog
        }
        this.dragging = false
      }, 300)
    },
    addRecordRoomToChat() {
      this.addingRoom = true
      setTimeout(_ => {
        if (!this.dragging) {
          AppConnector.request({
            module: 'Chat',
            action: 'Room',
            mode: 'addToFavorites',
            roomType: 'crm',
            recordId: this.isDetail ? app.getRecordId() : this.getRelatedRecord.id
          }).done(({ result }) => {
            this.addingRoom = false
            this.updateRooms(result)
            this.$q.notify({
              position: 'top',
              color: 'success',
              message: this.translate('JS_CHAT_ROOM_ADDED'),
              icon: 'mdi-check'
            })
          })
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
.badge-button {
  left: -3px;
  width: fit-content;
  padding: 0 1px;
}
</style>
