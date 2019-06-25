<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-drawer :value="rightPanel" side="right" @hide="setRightPanel(false)" bordered>
    <div class="bg-grey-11 fit">
      <q-input v-model="inputSearchUsers.search" :placeholder="placeholderUsers" class="col-12 q-pb-sm q-px-sm">
        <template v-slot:prepend>
          <q-icon
            v-show="inputSearchUsers.visible"
            name="mdi-close"
            @click=";(inputSearchUsers.search = ''), (inputSearchUsers.visible = false)"
            class="cursor-pointer"
          />
        </template>
        <template v-slot:append>
          <i @click="inputSearchUsers.visible = true" class="q-icon mdi mdi-magnify cursor-pointer"></i>
        </template>
      </q-input>
      <div class="bg-grey-4 text-bold text-left q-pa-sm text-uppercase">
        Użytkownicy
      </div>
      <q-list>
        <template v-for="row in dataRowUsers">
          <q-item clickable v-ripple :key="row.id" v-if="row.user_name === row.user_name">
            <q-item-section avatar>
              <q-avatar>
                <img :src="row.img" />
              </q-avatar>
            </q-item-section>
            <q-item-section>
              <div class="row">
                <span class="col-12">{{ row.user_name }}</span>
                <span class="col-12 text-caption text-blue-6 text-weight-medium">{{ row.role_name }}</span>
                <span class="col-12 text-caption text-grey-5">{{ row.messages }}</span>
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
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations } = createNamespacedHelpers('Chat')
export default {
  name: 'ChatRightPanel',
  data() {
    return {
      iconSize: '.75rem',
      inputSearchUsers: { search: '', visible: true },
      placeholder: 'Wyszukaj wiadomość',
      placeholderUsers: 'Wyszukaj uczestników',
      dataRowUsers: {
        0: {
          id: 46,
          crmid: 167,
          userid: 1,
          messages: 'test1',
          user_name: 'Administrator',
          last_name: 'Administrator',
          role_name: 'Board of Management',
          img: 'https://cdn.quasar-framework.org/img/avatar3.jpg'
        },
        1: {
          id: 47,
          crmid: 167,
          userid: 1,
          messages: 'test2',
          user_name: 'Test',
          last_name: 'Administrator',
          role_name: 'Board of Management',
          img: 'https://cdn.quasar-framework.org/img/avatar4.jpg'
        }
      }
    }
  },
  computed: {
    ...mapGetters(['rightPanel'])
  },
  methods: {
    ...mapMutations(['setRightPanel'])
  }
}
</script>
<style module lang="stylus"></style>
