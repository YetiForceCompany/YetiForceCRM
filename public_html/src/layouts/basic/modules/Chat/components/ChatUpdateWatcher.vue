<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template></template>
<script>
import difference from 'lodash.difference'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions, mapMutations } = createNamespacedHelpers('Chat')
/**
 * @desc Updat watcher component for updating data in chat rooms
 * @vue-data {Mix} timerAmount - timer for fetchAmountOfNewMessages request
 * @vue-data {Mix} timerMessage - timer for fetchNewMessages request
 * @vue-data {Array} activeRooms - array of active room to update
 */
export default {
  name: 'ChatUpdateWatcher',
  data() {
    return {
      timerAmount: false,
      timerMessage: false,
      activeRooms: [],
      inactiveRooms: []
    }
  },
  computed: {
    ...mapGetters(['data', 'config', 'tab', 'allRooms', 'dialog']),
    timer() {
      return this.dialog
        ? this.config.refreshMessageTime
        : this.config.refreshTimeGlobal
    }
  },
  /**
   * Init component event listener and timeout
   */
  created() {
    this.startTimer()
  },
  methods: {
    ...mapActions(['notifyAboutNewMessages', 'fetchRoomList']),
    ...mapMutations(['updateActiveRooms', 'setNewRooms', 'setPinnedRooms']),
    /**
     * Init vuex event for adjusting request for updating chat rooms
     */
    adjustUpdateRequestToChatState() {
      this.$store.subscribe((mutation, state) => {
        if (mutation.type === 'Chat/setActiveRoom') {
          this.activeRooms = this.allRooms.filter(el => el.active)
          if (this.activeRooms.length && !this.timerMessage) {
            clearInterval(this.timerAmount)
            this.timerAmount = false
            this.initMessageTimer()
          }
        } else if (
          mutation.type === 'Chat/unsetActiveRoom' &&
          !this.allRooms.filter(el => el.active).length &&
          this.tab !== 'chat'
        ) {
          if (!this.timerAmount) {
            clearInterval(this.timerMessage)
            this.timerMessage = false
            this.initAmountTimer()
          }
        }
      })
    },
    /**
     * Start timer, when component is created.
     */
    startTimer() {
      this.fetchNewMessages({ firstFetch: true })
    },
    /**
     * Init amount timer
     */
    initMessageTimer() {
      this.timerMessage = setTimeout(this.fetchNewMessages, this.timer)
    },
    /**
     * Fetch new messages timeout function
     */
    fetchNewMessages({ firstFetch } = { firstFetch: false }) {
      this.activeRooms = this.allRooms.length
        ? this.allRooms.filter(el => el.active)
        : []
      AppConnector.request({
        module: 'Chat',
        action: 'ChatAjax',
        mode: 'getRoomsMessages',
        rooms: this.activeRooms
      }).done(({ result }) => {
        const newRooms = this.newRooms()
        if (result.areNewEntries) {
          this.updateActiveRooms({
            roomsToUpdate: [...this.activeRooms],
            newData: result
          })
        }
        if (newRooms.length) {
          this.setNewRooms({
            newRooms,
            newData: result.roomList
          })
        }
        this.notifyAboutNewMessages({
          ...result.amountOfNewMessages,
          firstFetch
        })
        this.updateRooms(roomList.private, 'private')
        if (this.timerMessage || firstFetch) {
          this.initMessageTimer()
        }
      })
    },
    newRooms(roomList) {
      let newRooms = []
      for (let roomType in roomList) {
        Object.keys(roomList[roomType]).forEach(room => {
          if (!this.data.roomList[roomType][room]) {
            newRooms.push({
              roomType,
              recordId: roomList[roomType][room].recordid
            })
          }
        })
      }
      return newRooms
    },
    updateRooms(rooms, roomType) {
      if (
        typeof rooms === 'object' &&
        difference(
          Object.keys(rooms),
          Object.keys(this.data.roomList[roomType])
        ).length
      ) {
        if (
          this.data.currentRoom.roomType === roomType &&
          !rooms[this.data.currentRoom.recordId]
        ) {
          this.fetchRoomList().then(_ => {
            this.setPinnedRooms({ rooms, roomType })
          })
        } else {
          this.setPinnedRooms({ rooms, roomType })
        }
      }
    }
  }
}
</script>
<style>
</style>
