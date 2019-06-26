<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-drawer :value="leftPanel" side="left" bordered @hide="setLeftPanel(false)">
    <div class="bg-grey-11 fit">
      <div class="col-12 ">
        <q-input dense v-model="inputRoom" :placeholder="translate('JS_CHAT_SEARCH_ROOMS')" class="q-px-sm">
          <template v-slot:prepend>
            <q-icon name="mdi-magnify" />
          </template>
          <template v-slot:append>
            <q-icon v-show="inputRoom.length > 0" name="mdi-close" @click="inputRoom = ''" class="cursor-pointer" />
          </template>
        </q-input>
      </div>
      <div class="col-12" v-for="(room, roomType) of data.roomList" :key="roomType" :style="{ fontSize: fontSize }">
        <q-list dense class="q-mb-none">
          <q-item-label header class="flex items-center">
            <q-item-section avatar>
              <q-icon :name="setGroupIcon(roomType)" :size="fontSize" />
            </q-item-section>
            {{ translate(`JS_CHAT_ROOM_${roomType.toUpperCase()}`) }}
            <q-icon :size="fontSize" name="mdi-information" class="q-ml-auto">
              <q-tooltip> {{ translate(`JS_CHAT_ROOM_DESCRIPTION_${roomType.toUpperCase()}`) }}</q-tooltip>
            </q-icon>
          </q-item-label>
          <template v-for="rows of room">
            <q-item
              clickable
              v-ripple
              :key="rows.name"
              class="q-pl-sm"
              :active="data.currentRoom.recordId === rows.recordid"
              @click="fetchRoom({ id: rows.recordid, roomType: roomType })"
            >
              <div class="col-12 row items-center">
                <div class="col-7">
                  {{ rows.name }}
                </div>
                <div class="col-5 row justify-end">
                  <div class="col-3 text-right" v-if="rows.cnt_new_message > 0">
                    <q-badge color="blue" :label="rows.cnt_new_message" />
                  </div>
                  <!-- <div class="col-3  text-right" v-if="roomType === 'crm'">
                  <i aria-hidden="true" class= q-icon mdi mdi-link-variant" />
                </div>
                <div class="col-3  text-right" v-if="pinShow" @click=";(pinOffShow = true), (pinShow = false)">
                  <i aria-hidden="true" class="text-red q-icon mdi mdi-pin" />
                </div>
                <div class="col-3  text-right" v-if="pinOffShow" @click=";(pinShow = true), (pinOffShow = false)">
                  <i aria-hidden="true" class= q-icon mdi mdi-pin-off" />
                </div> -->
                </div>
              </div>
            </q-item>
          </template>
        </q-list>
      </div>
    </div>
  </q-drawer>
</template>
<script>
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations, mapActions } = createNamespacedHelpers('Chat')
export default {
  name: 'ChatLeftPanel',
  data() {
    return {
      inputRoom: '',
      fontSize: '0.88rem'
    }
  },
  computed: {
    ...mapGetters(['leftPanel', 'data'])
  },
  methods: {
    ...mapMutations(['setLeftPanel']),
    ...mapActions(['fetchRoom']),
    setGroupIcon(roomType) {
      switch (roomType) {
        case 'crm':
          return 'mdi-star'
        case 'group':
          return 'mdi-account-multiple'
        case 'global':
          return 'mdi-account-group'
      }
    }
  }
}
</script>
<style module lang="stylus"></style>
