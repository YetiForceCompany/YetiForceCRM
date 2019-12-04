<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div style="min-height: inherit;">
    <div
      v-if="isVisible"
      :key="isVisible"
      class="flex column justify-between"
      style="min-height: inherit;"
    >
      <div class="flex no-wrap items-center q-px-sm">
        <slot name="searchPrepend" />
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
        <slot name="searchAppend" />
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
      :key="isVisible"
    >
      <slot name="noRoom">
      </slot>
    </div>
  </div>
</template>
<script>
import TabChatInput from './Tabs/TabChatInput.vue'
import TabMessages from './Tabs/TabMessages.vue'

import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions } = createNamespacedHelpers('Chat')
export default {
  name: 'ChatContent',
  components: { TabChatInput, TabMessages },
  props: {
    roomData: {
      type: Object,
      required: true
    },
    isVisible: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      inputSearch: '',
      isSearchActive: false,
      searching: false,
      fetchingEarlier: false,
      scrollbarHidden: false
    }
  },
  computed: {
    ...mapGetters(['miniMode', 'config', 'dialog']),
    roomMessages() {
      return this.roomData.chatEntries
    }
  },
  watch: {
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
  mounted() {
    this.registerMountedEvents()
  },
  methods: {
    ...mapActions(['fetchEarlierEntries', 'fetchSearchData']),
    onResize({ height }) {
      if (!this.isVisible) return
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
      if (!this.isVisible) return
      this.scrollbarHidden = true
      this.$refs.scrollContainer.setScrollPosition(
        this.$refs.messagesContainer.$el.clientHeight
      )
      setTimeout(() => {
        this.scrollbarHidden = false
      }, 1800)
    },
    registerMountedEvents() {
      this.scrollDown()
      this.$emit('onContentLoaded', true)
    }
  }
}
</script>
<style lang="sass">
.scrollbarHidden
	.q-scrollarea__thumb
		visibility: hidden
</style>
