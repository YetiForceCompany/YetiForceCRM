<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template v-slot:chatMessages>
  <q-page-container>
    <q-page
      style="display: flex;
    flex-direction: column;
    justify-content: space-between;"
    >
      <div class="q-px-sm">
        <q-input v-show="!historyTab" dense v-model="inputSearch" :placeholder="translate('JS_CHAT_SEARCH_MESSAGES')">
          <template v-slot:prepend>
            <q-icon name="mdi-magnify" />
          </template>
          <template v-slot:append>
            <q-icon v-show="inputSearch.length > 0" name="mdi-close" @click="inputSearch = ''" class="cursor-pointer" />
          </template>
        </q-input>
        <q-tabs v-model="tabHistory" v-show="historyTab" align="justify" class="text-teal">
          <q-tab name="ulubiony" label="Ulubiony" />
          <q-tab name="grupowy" label="Pokój grupy" />
          <q-tab name="globalny" label="Pokoje globalne" />
        </q-tabs>
      </div>
      <div class="flex-grow-1" style="height: 0; overflow: hidden">
        <q-scroll-area
          :thumb-style="thumbStyle"
          :content-style="contentStyle"
          :content-active-style="contentActiveStyle"
          ref="scrollContainer"
        >
          <div v-show="data.showMoreButton" class="text-center q-mt-md">
            <q-btn icon="mdi-chevron-double-up">
              {{ translate('JS_CHAT_EARLIER') }}
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
      <div class="q-px-sm" ref="textContainer">
        <q-separator />
        <q-input borderless v-model="text" type="textarea" autogrow :placeholder="translate('JS_CHAT_MESSAGE')" class="overflow-hidden">
          <template v-slot:append>
            <q-btn :loading="sending" round color="secondary" icon="mdi-send" @click="simulateSubmit" />
          </template>
        </q-input>
      </div>
    </q-page>
  </q-page-container>
</template>
<script>
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions } = createNamespacedHelpers('Chat')
export default {
  name: 'ChatMessages',
  data() {
    return {
      text: '',
      inputSearch: '',
      tabHistory: 'ulubiony',
      sending: false,
      moduleName: 'Chat',
      userId: CONFIG.userId
    }
  },
  computed: {
    contentStyle() {
      return {
        color: '#555'
      }
    },
    contentActiveStyle() {
      return {
        color: 'black'
      }
    },
    thumbStyle() {
      return {
        right: '2px',
        borderRadius: '5px',
        backgroundColor: '#027be3',
        width: '5px',
        opacity: 0.75
      }
    },
    ...mapGetters(['maximizedDialog', 'historyTab', 'data'])
  },
  methods: {
    ...mapActions(['sendMessage']),
    simulateSubmit() {
      if (this.text.length < this.data.maxLengthMessage) {
        // clearTimeout(this.timerMessage)
        this.sendMessage(this.text)
        this.text = ''
      } else {
        Vtiger_Helper_Js.showPnotify({
          text: app.vtranslate('JS_MESSAGE_TOO_LONG'),
          type: 'error',
          animation: 'show'
        })
      }
    },
    onResize({ height }) {
      Quasar.utils.dom.css(this.$refs.scrollContainer.$el, {
        height: height + 'px'
      })
    }
  }
}
</script>
<style module lang="stylus"></style>
