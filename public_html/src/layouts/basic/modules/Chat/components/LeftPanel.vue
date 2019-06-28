<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-drawer :value="leftPanel" side="left" bordered @hide="setLeftPanel(false)">
    <div class="bg-grey-11 fit">
      <q-input dense v-model="inputRoom" :placeholder="translate('JS_CHAT_SEARCH_ROOMS')" class="q-px-sm">
        <template v-slot:prepend>
          <q-icon name="mdi-magnify" />
        </template>
        <template v-slot:append>
          <q-icon v-show="inputRoom.length > 0" name="mdi-close" @click="inputRoom = ''" class="cursor-pointer" />
        </template>
      </q-input>
      <div class="" v-for="(room, roomType) of data.roomList" :key="roomType" :style="{ fontSize: fontSize }">
        <q-list dense class="q-mb-none">
          <q-item-label header class="flex items-center">
            <q-item-section avatar>
              <q-icon :name="getGroupIcon(roomType)" :size="fontSize" />
            </q-item-section>
            {{ translate(`JS_CHAT_ROOM_${roomType.toUpperCase()}`) }}
            <q-icon :size="fontSize" name="mdi-information" class="q-ml-auto">
              <q-tooltip> {{ translate(`JS_CHAT_ROOM_DESCRIPTION_${roomType.toUpperCase()}`) }}</q-tooltip>
            </q-icon>
          </q-item-label>
          <template v-for="group of room">
            <q-item
              v-show="roomType === 'group' ? group.isPinned || showAllGroups : true"
              clickable
              v-ripple
              :key="group.name"
              class="q-pl-sm"
              :active="data.currentRoom.recordId === group.recordid"
              active-class="bg-teal-1 text-grey-8"
              @click="fetchRoom({ id: group.recordid, roomType: roomType })"
            >
              <div class="col-12 row items-center">
                <div class="col-7">
                  {{ group.name }}
                </div>
                <div class="col-5 row justify-end">
                  <div class="col-3 text-right" v-if="group.cnt_new_message !== undefined && group.cnt_new_message > 0">
                    <q-badge color="blue" :label="group.cnt_new_message" />
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
        <div class="full-width flex justify-end">
          <q-btn
            v-if="roomType === 'group'"
            dense
            flat
            no-caps
            color="info"
            :icon="showAllGroups ? 'mdi-chevron-up' : 'mdi-chevron-down'"
            :label="showAllGroups ? translate('JS_CHAT_HIDE') : translate('JS_CHAT_MORE')"
            @click="showAllGroups = !showAllGroups"
          />
        </div>
      </div>
    </div>
  </q-drawer>
</template>
<script>
import { getGroupIcon } from '../utils/utils.js'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations, mapActions } = createNamespacedHelpers('Chat')
export default {
  name: 'ChatLeftPanel',
  data() {
    return {
      inputRoom: '',
      fontSize: '0.88rem',
      showAllGroups: false
    }
  },
  computed: {
    ...mapGetters(['leftPanel', 'data'])
  },
  methods: {
    ...mapMutations(['setLeftPanel']),
    ...mapActions(['fetchRoom']),
    getGroupIcon
  }
}
</script>
<style module lang="stylus"></style>
