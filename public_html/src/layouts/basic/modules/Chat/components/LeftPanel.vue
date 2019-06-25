<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-drawer :value="leftPanel" side="left" bordered @hide="setLeftPanel(false)">
    <div class="bg-grey-9 fit">
      <div class="col-12 ">
        <q-input
          dense
          v-model="inputRoom"
          dark
          color="white"
          :placeholder="placeholderRoom"
          class="col-12 q-pb-sm q-px-sm"
        >
          <template v-slot:prepend>
            <q-icon name="mdi-magnify" />
          </template>
          <template v-slot:append>
            <q-icon
              v-show="inputRoom.length > 0"
              name="mdi-close"
              @click="inputRoom = ''"
              class="cursor-pointer  text-white"
            />
          </template>
        </q-input>
      </div>

      <div class="col-12 text-white bg-grey-9" v-for="(row, index) of roomsByUser" :key="index">
        <div class="bg-black text-bold q-pa-sm">
          <i aria-hidden="true" class="q-icon mdi mdi-star q-mr-sm" v-if="index === 'crm'" />
          <i aria-hidden="true" class="q-icon mdi mdi-account-group-outline q-mr-sm" v-if="index === 'group'" />
          <i aria-hidden="true" class="q-icon mdi mdi-earth q-mr-sm" v-if="index === 'global'" />
          {{ translate(`LBL_ROOM_${index.toUpperCase()}`, moduleName) }}
          <q-btn round flat dense class="gt-xs float-right" size="9px" icon="mdi-information">
            <q-tooltip> {{ translate(`LBL_ROOM_DESCRIPTION_${index.toUpperCase()}`, moduleName) }}</q-tooltip>
          </q-btn>
        </div>
        <q-list class="q-mb-none" v-for="rows of row" :key="rows.name">
          <q-item clickable v-ripple class="q-pl-sm">
            <div class="col-12 row items-center">
              <div class="col-7" @click="footerGroup(index), footerRoom(rows.name)">
                {{ rows.name }}
              </div>
              <div class="col-5 row justify-end">
                <div class="col-3 text-right" v-if="rows.cnt_new_message > 0">
                  <q-badge color="blue" :label="rows.cnt_new_message" />
                </div>
                <div class="col-3  text-right" v-if="index === 'crm'">
                  <i aria-hidden="true" class="text-white q-icon mdi mdi-link-variant" />
                </div>
                <div class="col-3  text-right" v-show="pinShow" @click=";(pinOffShow = true), (pinShow = false)">
                  <i aria-hidden="true" class="text-red q-icon mdi mdi-pin" />
                </div>
                <div class="col-3  text-right" v-show="pinOffShow" @click=";(pinShow = true), (pinOffShow = false)">
                  <i aria-hidden="true" class="text-white q-icon mdi mdi-pin-off" />
                </div>
              </div>
            </div>
          </q-item>
        </q-list>
      </div>
    </div>
  </q-drawer>
</template>
<script>
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations } = createNamespacedHelpers('Chat')
export default {
  name: 'ChatLeftPanel',
  props: {
    groupFooter: { type: String, required: false },
    roomFooter: { type: String, required: false }
  },
  data() {
    return {
      iconSize: '.75rem',
      maximizedToggle: true,
      left: false,
      roomsByUser: {
        crm: {
          0: { roomid: 1, userid: 1, recordid: 167, name: 'Promax', cnt_new_message: 1, moduleName: 'Accounts' }
        },
        group: {
          0: {
            roomid: 4,
            userid: 3,
            recordid: 167,
            name: 'Marketing Group 1',
            cnt_new_message: 0
          },
          1: {
            roomid: 5,
            userid: 3,
            recordid: 168,
            name: 'Marketing Group 2',
            cnt_new_message: 0
          }
        },
        global: {
          0: { name: 'General', recordid: 1, cnt_new_message: 0 }
        }
      },
      pinShow: true,
      inputSearchRoom: false,
      placeholderRoom: 'Wyszukaj pokój',
      inputRoom: '',
      pinOffShow: false,
      submitting: false,
      moduleName: 'Chat',
      dense: false
    }
  },
  computed: {
    ...mapGetters(['leftPanel'])
  },
  methods: {
    footerGroup: function(groupName) {
      this.$emit('footerGroup', groupName)
    },
    footerRoom: function(roomName) {
      this.$emit('footerRoom', roomName)
    },
    ...mapMutations(['setLeftPanel'])
  }
}
</script>
<style module lang="stylus"></style>
