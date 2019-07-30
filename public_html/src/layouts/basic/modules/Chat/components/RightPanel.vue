<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-drawer :value="rightPanel" side="right" @hide="setRightPanel(false)" bordered>
    <backdrop v-show="tab !== 'chat'" />
    <div class="bg-grey-11 fit">
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
      <q-list style="font-size: 0.88rem;">
        <q-item-label class="flex items-center text-bold text-muted q-py-sm q-px-md">
          <q-item-section avatar>
            <icon icon="yfi-entrant-chat" size="0.88rem" />
          </q-item-section>
          {{ translate('JS_CHAT_PARTICIPANTS') }}
        </q-item-label>
        <template v-for="participant in participantsList">
          <q-item :key="participant.user_id" v-if="participant.user_name === participant.user_name">
            <q-item-section avatar>
              <q-avatar>
                <img v-if="participant.img" :src="participant.img" />
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
            <q-separator />
          </q-item>
        </template>
      </q-list>
    </div>
  </q-drawer>
</template>
<script>
import Backdrop from 'components/Backdrop.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations } = createNamespacedHelpers('Chat')
export default {
  name: 'ChatRightPanel',
  components: { Backdrop },
  data() {
    return {
      filterParticipants: ''
    }
  },
  computed: {
    ...mapGetters(['rightPanel', 'data', 'tab']),
    participantsList() {
      if (this.filterParticipants === '') {
        return this.data.participants
      } else {
        return this.data.participants.filter(participant => {
          return (
            participant.user_name.toLowerCase().includes(this.filterParticipants.toLowerCase()) ||
            participant.role_name.toLowerCase().includes(this.filterParticipants.toLowerCase())
          )
        })
      }
    }
  },
  methods: {
    ...mapMutations(['setRightPanel'])
  }
}
</script>
<style module lang="stylus"></style>
