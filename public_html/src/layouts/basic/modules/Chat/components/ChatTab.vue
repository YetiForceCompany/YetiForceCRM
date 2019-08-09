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
        <template v-slot:prepend>
          <q-icon @click="search()" name="mdi-magnify" class="cursor-pointer" />
        </template>
        <template v-slot:append>
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
    <div class="flex-grow-1" style="height: 0; overflow: hidden">
      <q-scroll-area ref="scrollContainer" :class="[scrollbarHidden ? 'scrollbarHidden' : '']">
        <messages
          :roomData="roomData"
          @earlierClick="earlierClick()"
          :fetchingEarlier="fetchingEarlier"
          ref="messagesContainer"
        />
      </q-scroll-area>
      <q-resize-observer @resize="onResize" />
    </div>
    <message-input @onSended="scrollDown()" :roomData="roomData" />
  </div>
</template>
<script>
import MessageInput from './MessageInput.vue'
import Messages from './Messages.vue'
import { createNamespacedHelpers } from 'vuex'
import isEqual from 'lodash.isequal'
const { mapGetters, mapActions, mapMutations } = createNamespacedHelpers('Chat')

export default {
  name: 'ChatTab',
  components: { MessageInput, Messages },
  props: {
    roomData: {
      type: Object,
      required: true
    },
    recordView: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      inputSearch: '',
      fetchingEarlier: false,
      searching: false,
      scrollbarHidden: false,
      dataReady: false,
      roomId: null,
      roomType: null
    }
  },
  computed: {
		...mapGetters(['miniMode', 'data', 'config', 'isSearchActive']),
		roomMessages() {
			return this.roomData.chatEntries
		}
  },
  watch: {
    roomData() {
      if (this.roomData.recordid !== this.roomId && this.dataReady) {
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
    ...mapMutations(['setSearchInactive']),
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
        this.fetchSearchData(this.inputSearch)
      }
    },
    clearSearch() {
      this.inputSearch = ''
      this.fetchRoom()
      this.setSearchInactive()
      this.enableNewMessagesListener()
    },
    search() {
      this.disableNewMessagesListener()
      this.searching = true
      this.fetchSearchData({
        value: this.inputSearch,
        roomData: this.roomData
      }).then(e => {
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
      console.log(this.roomData)
      this.roomId = this.roomData.recordid
      this.roomType = this.roomData.roomType
    },
    enableNewMessagesListener() {
      this.addActiveRoom({ recordId: this.roomId, roomType: this.roomType })
    },
    disableNewMessagesListener() {
      this.removeActiveRoom({ recordId: this.roomId, roomType: this.roomType })
    }
  },
  mounted() {
    this.fetchRoom({ id: this.roomData.recordid, roomType: this.roomData.roomType, recordView: this.recordView }).then(e => {
      this.scrollDown()
      this.$emit('onContentLoaded', true)
      this.updateComponentsRoom()
      this.enableNewMessagesListener()
      this.dataReady = true
    })
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
