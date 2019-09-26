<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div v-if="config.isChatAllowed">
    <div class="btn-absolute hover-container">
      <YfDrag :coordinates.sync="buttonCoordinates" dragHandleClass=".js-chat-grab">
        <transition :enter-active-class="buttonAnimationClasses" mode="out-in">
          <q-btn
            round
            color="primary"
            class="glossy count-2"
            @click="showDialog"
            :loading="dialogLoading"
            ref="chatBtn"
            :key="parseInt(data.amountOfNewMessages)"
            style="z-index: 99999999999;"
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
              @mouseup="addRecordRoomToChat()"
              @touchend="addRecordRoomToChat()"
              class="shadow-3 text-primary btn-badge btn-badge--left-top"
              color="white"
              floating
            >
              <q-icon name="mdi-plus" size="1rem" />
              <q-tooltip>{{ translate('JS_CHAT_ROOM_ADD_CURRENT') }}</q-tooltip>
            </q-badge>
          </q-btn>
        </transition>
        <q-badge
          class="shadow-3 text-primary justify-center btn-badge btn-badge--right-bottom hover-height hover-grow"
          color="white"
          floating
          @click.stop
        >
          <ButtonGrab class="flex flex-center" grabClass="js-chat-grab" linkClass="q-px-none" size="18px" />
        </q-badge>
      </YfDrag>
    </div>
    <q-dialog
      v-model="dialog"
      seamless
      :maximized="!computedMiniMode"
      transition-show="slide-up"
      transition-hide="slide-down"
      content-class="quasar-reset all-pointer-events"
      @show="dialogLoading = false"
      @hide="dialogLoading = false"
    >
      <DragResize :coordinates.sync="coordinates" :maximized="!computedMiniMode">
        <ChatContainer container :parentRefs="$refs" />
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
import ButtonGrab from 'components/ButtonGrab.vue'
import isEqual from 'lodash.isequal'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations } = createNamespacedHelpers('Chat')
export default {
  name: 'Dialog',
  components: { ChatUpdateWatcher, ChatContainer, DragResize, ButtonGrab, YfDrag },
  data() {
    return {
      timerGlobal: null,
      dragging: false,
      windowConfig: CONFIG,
      addingRoom: false,
      dialogLoading: false
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
    }
  },
  methods: {
    ...mapMutations(['setDialog', 'setCoordinates', 'setButtonCoordinates', 'updateRooms']),
    showDialog() {
      if (!this.addingRoom) {
        this.dialogLoading = true
        this.dialog = !this.dialog
      }
    },
    addRecordRoomToChat() {
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
  z-index: 2147483647;

  &:hover {
    transform: scale(1.75);
  }
  &--left-top {
    top: -8px;
    left: -7px;
  }
  &--right-bottom {
    top: 28px;
    right: -6px;
  }
}

.hover-container {
  .hover-height {
    visibility: hidden;
    height: 0;
  }
  &:hover .hover-height {
    visibility: visible;
    height: $btn-badge-size;
  }
}
</style>
