<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div>
    <template v-for="(rooms, roomType) in unread">
      <div v-if="Object.entries(rooms).length" :key="roomType">
        <div class="text-uppercase text-primary full-width flex justify-center">
          {{ translate(`JS_CHAT_ROOM_${roomType.toUpperCase()}`) }}
        </div>
        <div v-for="(room, roomName) in rooms" :key="roomName">
          <div class="text-info full-width flex">
            {{ roomName }}
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
        </div>
      </div>
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
      lastRoomName: '',
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
    },
    unread() {
      let unread = {
        crm: {},
        global: {},
        group: {}
      }
      if (this.areUnread) {
        let tempRoomName = ''
        Object.keys(this.unreadMessages).forEach(roomType => {
          if (this.unreadMessages[roomType].length) {
            this.unreadMessages[roomType].forEach(el => {
              if (el.room_name !== tempRoomName) {
                tempRoomName = el.room_name
                unread[roomType][tempRoomName] = []
              }
              unread[roomType][tempRoomName].push(el)
            })
          }
        })
      }
      return unread
    }
  },
  methods: {
    ...mapActions(['fetchUnread']),
    checkLastRoom(roomName) {
      let ret = roomName !== this.lastRoomName
      this.lastRoomName = roomName
      return ret
    }
  },
  mounted() {
    this.fetchUnread().then(result => {
      this.unreadMessages = result
      this.$emit('onContentLoaded', true)
    })
  }
}
</script>
<style lang="sass">
</style>
