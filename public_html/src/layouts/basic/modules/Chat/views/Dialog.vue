<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div v-if="config.isChatAllowed">
    <div class="btn-absolute">
      <YfDrag
        :coordinates.sync="buttonCoordinates"
        @dragstop="onDragstop"
      >
        <transition
          :enter-active-class="buttonAnimationClasses"
          mode="out-in"
        >
          <q-btn
            ref="chatBtn"
            :key="parseInt(data.amountOfNewMessages)"
            :loading="dialogLoading"
            round
            color="primary"
            class="glossy animation-duration"
            style="z-index: 99999999999;"
            @mouseup="showDialog"
            @touchend="showDialog"
          >
            <YfIcon icon="yfi-branding-chat" />
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
              class="shadow-3 text-primary btn-badge btn-badge--left-top"
              color="white"
              floating
              @mouseup="addRecordRoomToChat()"
              @touchend="addRecordRoomToChat()"
            >
              <q-icon
                name="mdi-plus"
                size="1rem"
              />
              <q-tooltip>{{ translate('JS_CHAT_ROOM_ADD_CURRENT') }}</q-tooltip>
            </q-badge>
          </q-btn>
        </transition>
      </YfDrag>
    </div>
    <q-dialog
      v-model="dialogModel"
      :maximized="!computedMiniMode"
      :content-class="dialogClasses"
      transition-show="slide-up"
      transition-hide="slide-down"
      seamless
      @show="dialogLoading = false"
      @hide="dialogLoading = false"
    >
      <DragResize
        :coordinates.sync="coordinates"
        :maximized="!computedMiniMode"
        @dragstop="onDragstop"
      >
        <ChatContainer
          :parentRefs="$refs"
          container
        />
      </DragResize>
    </q-dialog>
    <ChatUpdateWatcher />
  </div>
</template>
<script>
import ChatUpdateWatcher from '../components/ChatUpdateWatcher.vue'
import ChatContainer from '../components/ChatContainer.vue'
import YfDrag from 'components/YfDrag.vue'
import DragResize from 'components/DragResize.vue'
import isEqual from 'lodash.isequal'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations } = createNamespacedHelpers('Chat')
export default {
  name: 'Dialog',
  components: { ChatUpdateWatcher, ChatContainer, DragResize, YfDrag },
  data() {
    return {
      timerGlobal: null,
      dragging: false,
      dragStopped: true,
      windowConfig: CONFIG,
      addingRoom: false,
      dialogLoading: false,
      dialogModel: false,
      dragTimeout: 300
    }
  },
  computed: {
    ...mapGetters(['miniMode', 'data', 'config', 'getDetailPreview']),
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
        this.setDragState()
        this.setCoordinates(coords)
      }
    },
    buttonCoordinates: {
      get() {
        return this.$store.getters['Chat/buttonCoordinates']
      },
      set(coords) {
        if (!isEqual({ left: coords.left, top: coords.top }, { ...this.$store.getters['Chat/buttonCoordinates'] })) {
          this.setDragState()
          this.setButtonCoordinates(coords)
        }
      }
    },
    computedMiniMode() {
      return this.$q.platform.is.desktop ? this.miniMode : false
    },
    buttonAnimationClasses() {
      return this.data.amountOfNewMessages ? 'animated shake' : ''
    },
    hasCurrentRecordChat() {
      let id = false
      if (this.isDetail) {
        id = app.getRecordId()
      }
      if (this.getDetailPreview && this.config.chatModules.some(el => el.id === this.getDetailPreview.module)) {
        id = this.getDetailPreview.id
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
    },
    dialogClasses() {
      return {
        'quasar-reset': true,
        animated: true,
        slideOutDown: !this.dialog,
        slideInUp: this.dialog,
        'all-pointer-events': !this.dragStopped
      }
    }
  },
  updated() {
    this.initDialogModel()
  },
  methods: {
    ...mapMutations(['setDialog', 'setCoordinates', 'setButtonCoordinates', 'updateRooms']),
    initDialogModel() {
      if (!this.dialogModel && this.dialog) {
        this.dialogModel = true
      }
    },
    showDialog() {
      this.dragging = false
      setTimeout(_ => {
        if (!this.dragging && !this.addingRoom) {
          this.dialog = !this.dialog
        }
      }, this.dragTimeout)
    },
    addRecordRoomToChat() {
      setTimeout(_ => {
        this.addingRoom = true
        AppConnector.request({
          module: 'Chat',
          action: 'Room',
          mode: 'addToFavorites',
          roomType: 'crm',
          recordId: this.isDetail ? app.getRecordId() : this.getDetailPreview.id
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
      }, this.dragTimeout)
    },
    setDragState() {
      this.dragging = true
      this.dragStopped = false
    },
    onDragstop(e) {
      this.dragStopped = true
    }
  }
}
</script>
<style scoped lang="scss">
$btn-badge-size: 23px;

.btn-absolute {
  width: 100%;
  height: 100%;
  position: absolute;
  top: 0;
  left: 0;
}

.btn-badge {
  justify-content: center;
  align-items: center;
  width: $btn-badge-size;
  height: $btn-badge-size;
  border-radius: 100%;
  transition: all 0.2s ease-in-out;

  &:hover {
    transform: scale(1.5);
  }

  &--left-top {
    top: -8px;
    left: -7px;
  }
}

.animation-duration {
  animation-duration: 0.8s;
}
</style>
