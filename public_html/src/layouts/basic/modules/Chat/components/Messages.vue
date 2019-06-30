<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template v-slot:chatMessages>
  <q-page-container>
    <q-page
      style="display: flex;
    flex-direction: column;
    justify-content: space-between;"
    >
      <div class="q-px-sm">
        <q-input
          v-show="!historyTab"
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
            <q-icon v-show="inputSearch.length > 0" name="mdi-close" @click="clearSearch()" class="cursor-pointer" />
          </template>
        </q-input>
        <q-tabs v-model="tabHistory" v-show="historyTab" align="justify" class="text-teal">
          <q-tab name="ulubiony" label="Ulubiony" />
          <q-tab name="grupowy" label="Pokój grupy" />
          <q-tab name="globalny" label="Pokoje globalne" />
        </q-tabs>
      </div>
      <div class="flex-grow-1" style="height: 0; overflow: hidden">
        <q-scroll-area :thumb-style="thumbStyle" ref="scrollContainer">
          <div v-show="data.showMoreButton" class="text-center q-mt-md">
            <q-btn :loading="fetchingEarlier" @click="registerEarlierClick()" icon="mdi-chevron-double-up">
              {{ translate('JS_CHAT_EARLIER') }}
              <template v-slot:loading>
                <q-spinner-facebook />
              </template>
            </q-btn>
          </div>
          <div class="q-pa-md">
            <q-chat-message
              v-for="row in data.chatEntries"
              :key="row.id"
              :name="row.user_name"
              :stamp="row.created"
              :avatar="row.img"
              :text="[row.messages]"
              :bg-color="row.color"
              size="8"
              :sent="row.userid === userId"
            />
          </div>
        </q-scroll-area>
        <q-resize-observer @resize="onResize" />
      </div>
      <message-input />
    </q-page>
  </q-page-container>
</template>
<script>
import MessageInput from './MessageInput.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions, mapMutations } = createNamespacedHelpers('Chat')

export default {
  name: 'ChatMessages',
  components: { MessageInput },
  data() {
    return {
      inputSearch: '',
      tabHistory: 'ulubiony',
      moduleName: 'Chat',
      userId: CONFIG.userId,
      fetchingEarlier: false,
      searching: false
    }
  },
  computed: {
    thumbStyle() {
      return {
        right: '2px',
        borderRadius: '5px',
        backgroundColor: '#027be3',
        width: '5px',
        opacity: 0.75
      }
    },
    ...mapGetters(['maximizedDialog', 'historyTab', 'data', 'isSearchActive'])
  },
  methods: {
    ...mapActions(['fetchEarlierEntries', 'fetchSearchData']),
    ...mapMutations(['setSearchInactive']),
    onResize({ height }) {
      Quasar.utils.dom.css(this.$refs.scrollContainer.$el, {
        height: height + 'px'
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
<style module lang="stylus"></style>
