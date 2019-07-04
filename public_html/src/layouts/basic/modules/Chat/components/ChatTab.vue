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
      <q-scroll-area :thumb-style="thumbStyle" v-multi-ref:scrollContainer>
        <messages @earlierClick="earlierClick()" :fetchingEarlier="fetchingEarlier" />
      </q-scroll-area>
      <q-resize-observer @resize="onResize" />
    </div>
    <message-input />
  </div>
</template>
<script>
import MessageInput from './MessageInput.vue'
import Messages from './Messages.vue'
import 'vue-multi-ref'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions, mapMutations } = createNamespacedHelpers('Chat')

export default {
  name: 'MainPanel',
  components: { MessageInput, Messages },
  data() {
    return {
      inputSearch: '',
      fetchingEarlier: false,
      searching: false,
      timerMessage: null
    }
  },
  computed: {
    ...mapGetters(['maximizedDialog', 'data', 'isSearchActive', 'tab']),
    thumbStyle() {
      return {
        right: '2px',
        borderRadius: '5px',
        backgroundColor: '#027be3',
        width: '5px',
        opacity: 0.75
      }
    }
  },
  methods: {
    ...mapActions(['fetchEarlierEntries', 'fetchSearchData', 'fetchRoom', 'fetchUnread']),
    ...mapMutations(['setSearchInactive', 'updateChat']),
    onResize({ height }) {
      this.$refs.scrollContainer.forEach(el => {
        Quasar.utils.dom.css(el.$el, {
          height: height + 'px'
        })
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
    },
    search() {
      this.searching = true
      this.fetchSearchData(this.inputSearch).then(e => {
        this.searching = false
      })
    },
    fetchNewMessages() {
      this.timerMessage = setTimeout(() => {
        console.log({
          module: 'Chat',
          action: 'ChatAjax',
          mode: 'getEntries',
          lastId: this.data.chatEntries.slice(-1)[0]['id'],
          recordId: this.data.currentRoom.recordId,
          roomType: this.data.currentRoom.roomType
        })
        AppConnector.request({
          module: 'Chat',
          action: 'ChatAjax',
          mode: 'getEntries',
          lastId: this.data.chatEntries.slice(-1)[0]['id'],
          recordId: this.data.currentRoom.recordId,
          roomType: this.data.currentRoom.roomType
        }).done(({ result }) => {
          console.log(result)
          this.updateChat(result)
          // let tempData = Object.assign({}, getters['data'])
          // commit('setData', Object.assign(tempData, result))
          this.fetchNewMessages()
        })
      }, this.data.refreshMessageTime)
    }
  },
  mounted() {
    if (this.data.currentRoom) {
      this.fetchRoom()
    }
    this.fetchNewMessages()
  },
  beforeDestroy() {
    clearInterval(this.timerMessage)
  }
}
</script>
<style lang="sass">
</style>
