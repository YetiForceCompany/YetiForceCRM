<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div>
    <div v-show="data.showMoreButton" class="text-center q-mt-md">
      <q-btn :loading="fetchingEarlier" @click="$emit('earlierClick')" icon="mdi-chevron-double-up">
        {{ translate('JS_CHAT_EARLIER') }}
        <template v-slot:loading>
          <q-spinner-facebook />
        </template>
      </q-btn>
    </div>
    <div class="q-pa-md">
      <template v-for="row in data.chatEntries">
        <!-- <q-chat-message :key="row.id" /> -->
        <transition :key="row.id" appear enter-active-class="animated fadeIn" leave-active-class="animated fadeOut">
          <q-chat-message
            :key="row.id"
            :name="header ? header(row) : row.user_name"
            :stamp="row.created"
            :avatar="row.img"
            :text="[row.messages]"
            :bg-color="row.color"
            size="8"
            :sent="row.userid === userId"
          />
        </transition>
      </template>
      <no-results v-show="!areEntries" />
    </div>
  </div>
</template>
<script>
import NoResults from 'components/NoResults.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions, mapMutations } = createNamespacedHelpers('Chat')

export default {
  name: 'Messages',
  components: { NoResults },
  props: {
    fetchingEarlier: {
      type: Boolean,
      default: false
    },
    header: {
      type: Function
    }
  },
  data() {
    return {
      userId: CONFIG.userId
    }
  },
  computed: {
    ...mapGetters(['data', 'historyTab']),
    areEntries() {
      return this.data.chatEntries === undefined ? true : this.data.chatEntries.length
    }
	}
}
</script>
<style lang="sass">
</style>
