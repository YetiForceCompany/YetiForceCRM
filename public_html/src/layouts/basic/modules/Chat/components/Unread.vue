<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div>
    <template v-for="(room, roomType) in unreadMessages">
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
const { mapActions } = createNamespacedHelpers('Chat')

export default {
  name: 'Unread',
  components: { NoResults },
  data() {
    return {
      userId: CONFIG.userId,
      unreadMessages: {
        crm: [],
        global: [],
        group: []
      }
    }
  },
  computed: {
    areUnread() {
      let areUnread = false
      Object.keys(this.unreadMessages).forEach(roomType => {
        if (this.unreadMessages[roomType].length) {
          areUnread = true
        }
      })
      return areUnread
    }
  },
  methods: {
    ...mapActions(['fetchUnread'])
  },
  mounted() {
    this.fetchUnread().then(result => {
      this.unreadMessages = result
    })
  }
}
</script>
<style lang="sass">
</style>
