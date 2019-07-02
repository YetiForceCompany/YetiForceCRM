<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div>
    <template v-for="(room, roomType) in messages">
      <div v-if="room.length" :key="roomType" class="text-uppercase text-primary full-width flex justify-center">
        {{ translate(`JS_CHAT_ROOM_${roomType.toUpperCase()}`) }}
      </div>
      <q-chat-message
        v-for="message in room"
        :key="message.id"
        :name="message.user_name"
        :stamp="message.created"
        :avatar="message.img"
        :text="[message.messages]"
        :bg-color="message.color"
        size="8"
        :sent="message.userid === userId"
      />
    </template>
    <no-results v-show="!areUnread" />
  </div>
</template>
<script>
import NoResults from 'components/NoResults.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions, mapMutations } = createNamespacedHelpers('Chat')

export default {
  name: 'Unread',
  components: { NoResults },
  data() {
    return {
      userId: CONFIG.userId
    }
  },
  props: {
    messages: {
      type: Object,
      required: true
    }
  },
  computed: {
    areUnread() {
      let areUnread = false
      Object.keys(this.messages).forEach(roomType => {
        if (this.messages[roomType].length) {
          areUnread = true
        }
      })
      return areUnread
    }
  },
  methods: {
    ...mapActions(['fetchEarlierEntries', 'fetchSearchData', 'fetchRoom', 'fetchUnread']),
    ...mapMutations(['setSearchInactive'])
  }
}
</script>
<style lang="sass">
</style>
