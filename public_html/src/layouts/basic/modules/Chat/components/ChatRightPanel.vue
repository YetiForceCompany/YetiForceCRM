<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div class="fit bg-grey-11">
    <slot name="top"></slot>
    <div class="bg-grey-11">
      <q-input
        dense
        v-model="filterParticipants"
        :placeholder="translate('JS_CHAT_FILTER_PARTICIPANTS')"
        class="q-px-sm"
      >
        <template v-slot:prepend>
          <q-icon name="mdi-magnify" />
        </template>
        <template v-slot:append>
          <q-icon
            v-show="filterParticipants.length > 0"
            name="mdi-close"
            @click="filterParticipants = ''"
            class="cursor-pointer"
          />
        </template>
      </q-input>
      <q-list :style="{ 'font-size': layout.drawer.fs }">
        <q-item-label class="flex items-center text-bold text-muted q-py-sm q-px-md">
          <q-item-section avatar>
            <YfIcon icon="yfi-entrant-chat" :size="layout.drawer.fs" />
          </q-item-section>
          {{ translate('JS_CHAT_PARTICIPANTS') }}
          <div class="q-ml-auto">
            <q-btn
              v-if="isUserModerator"
              dense
              flat
              round
              size="sm"
              color="primary"
              icon="mdi-plus"
              @click="showAddPanel = !showAddPanel"
            >
              <q-tooltip>{{ translate('JS_CHAT_ADD_PARTICIPANT') }}</q-tooltip>
            </q-btn>
            <q-icon name="mdi-information" class="q-pr-xs">
              <q-tooltip>{{ translate(`JS_CHAT_PARTICIPANTS_DESCRIPTION`) }}</q-tooltip>
            </q-icon>
          </div>
        </q-item-label>
        <q-item v-if="isUserModerator" v-show="showAddPanel">
          <RoomUserSelect :isVisible.sync="showAddPanel" class="q-pb-xs" />
        </q-item>
        <template v-for="participant in participantsList">
          <q-item
            :active="!!participant.message"
            active-class="opacity-1"
            :key="participant.user_id"
            v-if="participant.user_name === participant.user_name"
            class="q-py-xs opacity-5"
          >
            <q-item-section avatar>
              <q-avatar style="height: unset;">
                <img v-if="participant.image" :src="participant.image" />
                <q-icon v-else name="mdi-account" size="40px" />
              </q-avatar>
            </q-item-section>
            <q-item-section>
              <div class="row">
                <span class="col-12">{{ participant.user_name }}</span>
                <span class="col-12 text-caption text-blue-6 text-weight-medium" v-html="participant.role_name"></span>
                <span class="col-12 text-caption text-grey-5" v-html="participant.message"></span>
              </div>
            </q-item-section>
            <q-item-section avatar>
              <q-icon v-if="participant.isAdmin" name="mdi-crown" />
              <q-btn
                v-if="isUserModerator && participant.user_id !== userId"
                @click.stop="
                  unpinUser({
                    roomType: currentRoomData.roomType,
                    recordId: currentRoomData.recordid,
                    userId: participant.user_id,
                    active: !!participant.message
                  })
                "
                dense
                round
                flat
                size="xs"
                :icon="'mdi-pin'"
              />
            </q-item-section>
          </q-item>
        </template>
      </q-list>
    </div>
  </div>
</template>
<script>
import RoomUserSelect from './Rooms/RoomUserSelect.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations, mapActions } = createNamespacedHelpers('Chat')
export default {
  name: 'ChatRightPanel',
  components: { RoomUserSelect },
  props: {
    participants: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
      filterParticipants: '',
      userId: CONFIG.userId,
      showAddPanel: false
    }
  },
  computed: {
    ...mapGetters(['currentRoomData', 'config', 'layout']),
    isUserModerator() {
      return (
        this.currentRoomData.roomType === 'private' &&
        (this.currentRoomData.creatorid === this.userId || this.config.isAdmin)
      )
    },
    participantsList() {
      if (this.filterParticipants === '') {
        return this.participants.length ? this.participants.concat().sort(this.sortByCurrentUserMessagesName) : []
      } else {
        return this.participants.filter(participant => {
          return (
            participant.user_name.toLowerCase().includes(this.filterParticipants.toLowerCase()) ||
            participant.role_name.toLowerCase().includes(this.filterParticipants.toLowerCase())
          )
        })
      }
    }
  },
  methods: {
    ...mapActions(['removeUserFromRoom']),
    ...mapMutations(['unsetParticipant']),
    unpinUser({ roomType, recordId, userId, active }) {
      this.removeUserFromRoom({ roomType, recordId, userId }).then(({ result }) => {
        this.unsetParticipant({ roomId: recordId, participantId: userId })
        this.$q.notify({
          position: 'top',
          color: 'success',
          message: this.translate('JS_CHAT_PARTICIPANT_REMOVED'),
          icon: 'mdi-check'
        })
      })
    },
    sortByCurrentUserMessagesName(a, b) {
      if (a.user_id === this.userId) {
        return -1
      }
      if (!a.message && b.message) {
        return 1
      } else if (a.message && !b.message) {
        return -1
      }
      if (a.user_name > b.user_name) {
        return 1
      } else {
        return -1
      }
    }
  }
}
</script>
<style scoped>
.opacity-5 {
  opacity: 0.5;
}
.opacity-1 {
  opacity: 1;
}
</style>
