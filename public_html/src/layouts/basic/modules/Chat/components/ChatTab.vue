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
        <messages @earlierClick="earlierClick()" :fetchingEarlier="fetchingEarlier" ref="messagesContainer" />
      </q-scroll-area>
      <q-resize-observer @resize="onResize" />
    </div>
    <message-input @onSended="scrollDown()" />
  </div>
</template>
<script>
import MessageInput from './MessageInput.vue'
import Messages from './Messages.vue'
import { createNamespacedHelpers } from 'vuex'
import isEqual from 'lodash.isequal'
const { mapGetters, mapActions, mapMutations } = createNamespacedHelpers('Chat')

export default {
  name: 'MainPanel',
  components: { MessageInput, Messages },
  data() {
    return {
      inputSearch: '',
      fetchingEarlier: false,
      searching: false,
      timerMessage: null,
      scrollbarHidden: false
    }
  },
  computed: {
    ...mapGetters(['miniMode', 'data', 'config', 'isSearchActive', 'tab'])
  },
  watch: {
    data() {
			this.$nextTick(function () {
      	this.scrollDown()
			})
    }
  },
  methods: {
    ...mapActions(['fetchEarlierEntries', 'fetchSearchData', 'fetchRoom', 'fetchUnread', 'updateAmountOfNewMessages']),
    ...mapMutations(['setSearchInactive', 'updateChat']),
    onResize({ height }) {
      Quasar.utils.dom.css(this.$refs.scrollContainer.$el, {
        height: height + 'px'
      })
    },
    earlierClick() {
      this.fetchingEarlier = true
      if (!this.isSearchActive) {
        this.fetchEarlierEntries().then(e => {
          this.fetchingEarlier = false
        })
      } else {
        this.fetchSearchData(this.inputSearch).then(e => {
          this.fetchingEarlier = false
        })
      }
    },
    clearSearch() {
      this.inputSearch = ''
      this.fetchRoom()
      this.setSearchInactive()
      this.fetchNewMessages()
    },
    search() {
      clearTimeout(this.timerMessage)
      this.searching = true
      this.fetchSearchData(this.inputSearch).then(e => {
        this.searching = false
      })
    },
    fetchNewMessages() {
      this.timerMessage = setTimeout(() => {
        AppConnector.request({
          module: 'Chat',
          action: 'ChatAjax',
          mode: 'getMessages',
          lastId:
            this.data.chatEntries.slice(-1)[0] !== undefined ? this.data.chatEntries.slice(-1)[0]['id'] : undefined,
          recordId: this.data.currentRoom.recordId,
          roomType: this.data.currentRoom.roomType,
          miniMode: this.miniMode ? true : undefined
        }).done(({ result }) => {
          this.updateAmountOfNewMessages(result.amountOfNewMessages)
          if (result.chatEntries.length || !isEqual(this.data.roomList, result.roomList)) {
            this.updateChat(result)
          }
          if (result.chatEntries.length) {
            this.scrollDown()
          }
          this.fetchNewMessages()
        })
      }, this.config.refreshMessageTime)
    },
    scrollDown() {
      this.scrollbarHidden = true
			this.$refs.scrollContainer.setScrollPosition(this.$refs.messagesContainer.$el.clientHeight)
			this.scrollbarHidden = false
    }
  },
  mounted() {
    this.fetchRoom({ id: undefined, roomType: undefined }).then(e => {
			this.scrollDown()
      this.$emit('onContentLoaded', true)
      this.fetchNewMessages()
		})
  },
  beforeDestroy() {
    clearTimeout(this.timerMessage)
  }
}
</script>
<style lang="sass">
.scrollbarHidden
	.q-scrollarea__thumb
		visibility: hidden
</style>
