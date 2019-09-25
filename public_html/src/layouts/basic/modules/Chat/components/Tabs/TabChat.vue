<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div class="flex column justify-between" style="min-height: inherit;">
    <div class="q-px-sm">
      <q-input
        @keydown.enter="search()"
        dense
        :loading="searching"
        v-model="inputSearch"
        :placeholder="translate('JS_CHAT_SEARCH_MESSAGES')"
      >
        <template #prepend>
          <q-icon @click="search()" name="mdi-magnify" class="cursor-pointer" />
        </template>
        <template #append>
          <q-icon v-show="inputSearch.length > 0" name="mdi-close" @click="inputSearch = ''" class="cursor-pointer" />
          <q-btn
            v-show="isSearchActive"
            color="danger"
            flat
            dense
            :label="translate('JS_LBL_CANCEL')"
            @click="clearSearch()"
          />
        </template>
      </q-input>
    </div>
    <div class="flex-grow-1" style="min-height: 100%; height: 0; overflow: hidden">
      <q-scroll-area ref="scrollContainer" :class="[scrollbarHidden ? 'scrollbarHidden' : '']">
        <TabMessages
          :roomData="isSearchActive ? roomData.searchData : roomData"
          @earlierClick="earlierClick()"
          :fetchingEarlier="fetchingEarlier"
          ref="messagesContainer"
        />
      </q-scroll-area>
      <q-resize-observer @resize="onResize" />
    </div>
    <TabChatInput @onSended="scrollDown()" :roomData="roomData" />
  </div>
</template>
<script>
import TabChatInput from './TabChatInput.vue'
import TabMessages from './TabMessages.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions, mapMutations } = createNamespacedHelpers('Chat')

export default {
  name: 'ChatTab',
  components: { TabChatInput, TabMessages },
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
    ...mapGetters(['miniMode', 'data', 'config', 'currentRoomData']),
    roomMessages() {
      return this.roomData.chatEntries
    }
  },
  watch: {
    roomData() {
      if ((this.roomData.recordid !== this.roomId || this.roomData.roomType !== this.roomType) && this.dataReady) {
        this.disableNewMessagesListener()
        this.updateComponentsRoom()
        this.enableNewMessagesListener()
      }
    },
    roomMessages() {
      if (!this.fetchingEarlier) {
        this.$nextTick(function() {
          this.scrollDown()
        })
      } else {
        this.fetchingEarlier = false
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
      'removeActiveRoom'
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
      this.$refs.scrollContainer.setScrollPosition(this.$refs.messagesContainer.$el.clientHeight)
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
      if (this.data.roomList[this.roomType] && this.data.roomList[this.roomType][this.roomId]) {
        this.removeActiveRoom({ recordId: this.roomId, roomType: this.roomType })
      }
    },
    registerPostLoadEvents() {
      this.scrollDown()
      this.$emit('onContentLoaded', true)
      this.updateComponentsRoom()
      if (!this.recordRoom) {
        this.enableNewMessagesListener()
      }
      this.dataReady = true
    }
  },
  mounted() {
    if (!this.recordRoom && !this.currentRoomData.recordid) {
      this.fetchRoom({
        id: this.roomData.recordid,
        roomType: this.roomData.roomType
      }).then(e => {
        this.registerPostLoadEvents()
      })
    } else {
      this.registerPostLoadEvents()
    }
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
