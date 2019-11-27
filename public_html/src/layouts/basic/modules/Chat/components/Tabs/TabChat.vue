<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div style="min-height: inherit;">
    <div
      v-if="isRoom"
      :key="isRoom"
      class="flex column justify-between"
      style="min-height: inherit;"
    >
      <div class="flex no-wrap items-center q-px-sm">
        <q-btn
          dense
          flat
          round
          :color="leftPanel ? 'info' : 'grey'"
          @click="toggleLeftPanel()"
        >
          <YfIcon icon="yfi-menu-group-room" />
          <q-tooltip>{{ translate('JS_CHAT_ROOMS_MENU') }}</q-tooltip>
        </q-btn>
        <q-input
          v-model="inputSearch"
          class="full-width q-px-sm"
          dense
          :loading="searching"
          :placeholder="translate('JS_CHAT_SEARCH_MESSAGES')"
          @keydown.enter="search()"
        >
          <template #prepend>
            <q-icon
              @click="search()"
              class="cursor-pointer"
              name="mdi-magnify"
            />
          </template>
          <template #append>
            <q-icon
              v-show="inputSearch.length > 0"
              class="cursor-pointer"
              name="mdi-close"
              @click="inputSearch = ''"
            />
            <q-btn
              v-show="isSearchActive"
              :label="translate('JS_LBL_CANCEL')"
              color="danger"
              flat
              dense
              @click="clearSearch()"
            />
          </template>
        </q-input>
        <q-btn
          :color="rightPanel ? 'info' : 'grey'"
          dense
          flat
          round
          @click="toggleRightPanel()"
        >
          <YfIcon icon="yfi-menu-entrant" />
          <q-tooltip>{{ translate('JS_CHAT_PARTICIPANTS_MENU') }}</q-tooltip>
        </q-btn>
      </div>
      <div
        class="flex-grow-1"
        style="min-height: 100%; height: 0; overflow: hidden"
      >
        <q-scroll-area
          ref="scrollContainer"
          :class="[scrollbarHidden ? 'scrollbarHidden' : '']"
        >
          <TabMessages
            ref="messagesContainer"
            :roomData="isSearchActive ? roomData.searchData : roomData"
            :fetchingEarlier="fetchingEarlier"
            @earlierClick="earlierClick()"
          />
        </q-scroll-area>
        <q-resize-observer @resize="onResize" />
      </div>
      <TabChatInput
        :roomData="roomData"
        @onSended="scrollDown()"
      />
    </div>
    <div
      v-else
      :key="isRoom"
    >
      <slot name="noRoom">
        <TabChatNoRoom />
      </slot>
    </div>
  </div>
</template>
<script>
import TabChatInput from './TabChatInput.vue'
import TabMessages from './TabMessages.vue'
import TabChatNoRoom from './TabChatNoRoom.vue'

import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions, mapMutations } = createNamespacedHelpers('Chat')
export default {
  name: 'TabChat',
  components: { TabChatInput, TabMessages, TabChatNoRoom },
  props: {
    roomData: {
      type: Object,
      required: true
    },
    recordRoom: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      inputSearch: '',
      isSearchActive: false,
      searching: false,
      fetchingEarlier: false,
      scrollbarHidden: false,
      dataReady: false,
      roomId: null,
      roomType: null
    }
  },
  computed: {
    ...mapGetters([
      'miniMode',
      'data',
      'config',
      'currentRoomData',
      'dialog',
      'leftPanel',
      'rightPanel'
    ]),
    roomMessages() {
      return this.roomData.chatEntries
    },
    isRoom() {
      return Object.keys(this.currentRoomData).length
    }
  },
  watch: {
    roomData() {
      if (
        this.isRoom &&
        this.dataReady &&
        (this.roomData.recordid !== this.roomId ||
          this.roomData.roomType !== this.roomType)
      ) {
        this.disableNewMessagesListener()
        this.updateComponentsRoom()
        this.enableNewMessagesListener()
      }
    },
    roomMessages() {
      if (!this.fetchingEarlier && this.isRoom) {
        this.$nextTick(function() {
          this.scrollDown()
        })
      } else {
        this.fetchingEarlier = false
      }
    },
    dialog() {
      if (this.dialog) {
        this.onShowTabChatEvent()
      } else {
        this.disableNewMessagesListener()
      }
    }
  },
  methods: {
    ...mapActions([
      'fetchEarlierEntries',
      'fetchSearchData',
      'fetchRoom',
      'fetchUnread',
      'addActiveRoom',
      'removeActiveRoom',
      'toggleLeftPanel',
      'toggleRightPanel'
    ]),
    onResize({ height }) {
      Quasar.utils.dom.css(this.$refs.scrollContainer.$el, {
        height: height + 'px'
      })
    },
    earlierClick() {
      this.fetchingEarlier = true
      if (!this.isSearchActive) {
        this.fetchEarlierEntries({
          chatEntries: this.roomData.chatEntries,
          roomType: this.roomData.roomType,
          recordId: this.roomData.recordid
        })
      } else {
        this.fetchSearchData({
          value: this.inputSearch,
          roomData: this.roomData,
          showMore: true
        })
      }
    },
    clearSearch() {
      this.isSearchActive = false
      this.inputSearch = ''
      this.$nextTick(function() {
        this.scrollDown()
      })
    },
    search() {
      this.searching = true
      this.fetchSearchData({
        value: this.inputSearch,
        roomData: this.roomData,
        showMore: false
      }).then(e => {
        this.isSearchActive = true
        this.searching = false
      })
    },
    scrollDown() {
      this.scrollbarHidden = true
      this.$refs.scrollContainer.setScrollPosition(
        this.$refs.messagesContainer.$el.clientHeight
      )
      setTimeout(() => {
        this.scrollbarHidden = false
      }, 1800)
    },
    updateComponentsRoom() {
      this.roomId = this.roomData.recordid
      this.roomType = this.roomData.roomType
    },
    enableNewMessagesListener() {
      this.addActiveRoom({ recordId: this.roomId, roomType: this.roomType })
    },
    disableNewMessagesListener() {
      if (
        this.data.roomList[this.roomType] &&
        this.data.roomList[this.roomType][this.roomId]
      ) {
        this.removeActiveRoom({
          recordId: this.roomId,
          roomType: this.roomType
        })
      }
    },
    registerPostLoadEvents() {
      this.scrollDown()
      this.$emit('onContentLoaded', true)
      this.updateComponentsRoom()
      if (!this.recordRoom) {
        this.enableNewMessagesListener()
      }
    },
    onShowTabChatEvent() {
      if (!this.recordRoom && !this.currentRoomData.recordid) {
        this.fetchRoom({
          id: this.roomData.recordid,
          roomType: this.roomData.roomType
        }).then(result => {
          if (result) {
            this.registerPostLoadEvents()
          } else {
            this.$emit('onContentLoaded', true)
          }
        })
      } else {
        this.registerPostLoadEvents()
      }
    }
  },
  mounted() {
    if (this.dialog) {
      this.onShowTabChatEvent()
    } else if (this.recordRoom) {
      this.registerPostLoadEvents()
    }
    this.dataReady = true
  },
  beforeDestroy() {
    this.disableNewMessagesListener()
  }
}
</script>
<style lang="sass">
.scrollbarHidden
	.q-scrollarea__thumb
		visibility: hidden
</style>
