<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template v-slot:chatMessages>
  <q-page-container>
    <q-page>
      <q-tab-panels
        v-model="tab"
        @before-transition="getTabData"
        animated
        style="min-height: inherit;"
        class="chat-panels"
      >
        <q-tab-panel
          name="chat"
          style="min-height: inherit; display: flex;
    flex-direction: column;
    justify-content: space-between;"
        >
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
                <q-icon
                  v-show="inputSearch.length > 0"
                  name="mdi-close"
                  @click="inputSearch = ''"
                  class="cursor-pointer"
                />
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
              <div v-show="data.showMoreButton" class="text-center q-mt-md">
                <q-btn :loading="fetchingEarlier" @click="registerEarlierClick()" icon="mdi-chevron-double-up">
                  {{ translate('JS_CHAT_EARLIER') }}
                  <template v-slot:loading>
                    <q-spinner-facebook />
                  </template>
                </q-btn>
              </div>
              <div class="q-pa-md">
                <template v-for="row in data.chatEntries">
                  <!-- <q-chat-message :key="row.id" /> -->
                  <q-chat-message
                    :key="row.id"
                    :name="row.user_name"
                    :stamp="row.created"
                    :avatar="row.img"
                    :text="[row.messages]"
                    :bg-color="row.color"
                    size="8"
                    :sent="row.userid === userId"
                  />
                </template>
                <no-results v-show="!areEntries" />
              </div>
            </q-scroll-area>
            <q-resize-observer @resize="onResize" />
          </div>
          <message-input />
        </q-tab-panel>
        <q-tab-panel name="unread">
          <unread class="q-pa-md" :messages="unreadMessages" />
        </q-tab-panel>
        <q-tab-panel name="history"> </q-tab-panel>
      </q-tab-panels>
    </q-page>
  </q-page-container>
</template>
<script>
import MessageInput from './MessageInput.vue'
import Unread from './Unread.vue'
import History from './History.vue'
import NoResults from 'components/NoResults.vue'
import 'vue-multi-ref'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions, mapMutations } = createNamespacedHelpers('Chat')

export default {
  name: 'ChatMessages',
  components: { MessageInput, NoResults, History },
  data() {
    return {
      inputSearch: '',
      moduleName: 'Chat',
      userId: CONFIG.userId,
      fetchingEarlier: false,
      searching: false,
      unreadMessages: {
        crm: [],
        global: [],
        group: []
      }
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
    },
    areEntries() {
      return this.data.chatEntries === undefined ? true : this.data.chatEntries.length
    }
  },
  watch: {
    tab() {}
  },
  methods: {
    ...mapActions(['fetchEarlierEntries', 'fetchSearchData', 'fetchRoom', 'fetchUnread']),
    ...mapMutations(['setSearchInactive']),
    getTabData(newTab) {
      console.log(this.tab)
      switch (newTab) {
        case 'unread':
          this.fetchUnread().then(result => {
            this.unreadMessages = result
          })
      }
    },
    onResize({ height }) {
      this.$refs.scrollContainer.forEach(el => {
        console.log(el)
        Quasar.utils.dom.css(el.$el, {
          height: height + 'px'
        })
      })
    },
    registerEarlierClick() {
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
    }
  }
}
</script>
<style lang="sass">
.chat-panels.q-tab-panels.q-panel-parent
	.q-panel.scroll
		min-height: inherit
		overflow: hidden
</style>
