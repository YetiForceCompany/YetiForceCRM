<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div>
    <q-tabs
      v-model="historyTab"
      @input="tabChange"
      align="left"
      dense
      shrink
      inline-label
      narrow-indicator
      class="text-teal"
    >
      <q-tab
        v-for="(room, roomType) of data.roomList"
        :key="roomType"
        :name="roomType"
        :label="translate(`JS_CHAT_ROOM_${roomType.toUpperCase()}`)"
        :icon="getGroupIcon(roomType)"
      />
    </q-tabs>
    <q-tab-panels v-model="historyTab" animated style="min-height: inherit;" class="chat-panels">
      <q-tab-panel v-for="(room, roomType) of data.roomList" :key="roomType" :name="roomType">
        <messages @earlierClick="earlierClick" :fetchingEarlier="fetchingEarlier" />
      </q-tab-panel>
    </q-tab-panels>
  </div>
</template>
<script>
import Messages from './Messages.vue'
import { getGroupIcon } from '../utils/utils.js'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions, mapMutations } = createNamespacedHelpers('Chat')

export default {
  name: 'History',
  components: { Messages },
  data() {
    return {
      userId: CONFIG.userId,
      fetchingEarlier: false
    }
  },
  computed: {
    ...mapGetters(['data', 'tab']),
    historyTab: {
      get() {
        return this.$store.getters['Chat/historyTab']
      },
      set(tab) {
        this.$store.commit('Chat/setHistoryTab', tab)
      }
    }
  },
  methods: {
    ...mapActions(['fetchHistory']),
    getGroupIcon,
    tabChange(val) {
      this.fetchHistory({ groupHistory: val, showMoreClicked: false })
    },
    earlierClick() {
      this.fetchingEarlier = true
      this.fetchHistory({ groupHistory: this.historyTab, showMoreClicked: true }).then(e => {
        this.fetchingEarlier = false
      })
    }
  },
  mounted() {
    this.fetchHistory({ groupHistory: this.historyTab, showMoreClicked: false }).then(() => {
      this.$emit('onContentLoaded', true)
    })
  }
}
</script>
<style lang="sass">
</style>
