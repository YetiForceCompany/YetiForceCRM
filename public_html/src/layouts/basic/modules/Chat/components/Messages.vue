<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template v-slot:chatMessages>
  <q-page-container>
    <q-page>
      <div class="row col-12 q-px-sm">
        <div class="col-12">
          <q-input borderless v-model="inputSearch" :placeholder="placeholder">
            <template v-slot:prepend>
              <q-icon name="mdi-magnify" />
            </template>
            <template v-slot:append>
              <q-icon
                v-show="inputSearch.length > 0"
                name="mdi-close"
                @click="inputSearch = ''"
                class="cursor-pointer"
              />
            </template>
          </q-input>

          <div v-show="tabHistoryShow" class="row q-pb-sm">
            <div class="col-12">
              <q-tabs v-model="tabHistory" class="text-teal col-10">
                <q-tab name="ulubiony" label="Ulubiony" />
                <q-tab name="grupowy" label="Pokój grupy" />
                <q-tab name="globalny" label="Pokoje globalne" />
              </q-tabs>
            </div>
          </div>
        </div>
        <div class="col-12">
          <q-separator />
        </div>
      </div>
      <div class="col-12 q-px-sm">
        <q-scroll-area
          :thumb-style="thumbStyle"
          :content-style="contentStyle"
          :content-active-style="contentActiveStyle"
          style="height: 620px;"
        >
          <div class="text-center q-mt-xl">
            <q-btn class="">
              <i aria-hidden="true" class="q-icon mdi mdi-chevron-double-up"></i>
              Więcej
            </q-btn>
          </div>
          <div v-for="row in dataRow" :key="row.id">
            <div class="q-pa-md row justify-center">
              <q-chat-message
                :name="row.user_name"
                :avatar="row.img"
                :text="[row.messages]"
                :stamp="row.created"
                class="col-12 "
                size="8"
                :bg-color="row.color"
                v-if="row.user_name !== 'Administrator'"
                sent
              />
              <q-chat-message
                :name="row.user_name"
                :stamp="row.created"
                :avatar="row.img"
                :text="[row.messages]"
                class="col-12"
                :bg-color="row.color"
                size="8"
                text-color="white"
                v-if="row.user_name === 'Administrator'"
              />
            </div>
          </div>
        </q-scroll-area>
        <q-separator />
        <div class="col-12">
          <q-input borderless v-model="text" type="textarea" autogrow :placeholder="placeholderTexttera" :dense="dense">
            <template v-slot:append>
              <q-btn type="submit" :loading="submitting" round color="secondary" icon="mdi-send" />
            </template>
          </q-input>
        </div>
      </div>
    </q-page>
  </q-page-container>
</template>
<script>
export default {
  name: 'ChatMessages',
  computed: {
    contentStyle() {
      return {
        color: '#555'
      }
    },

    contentActiveStyle() {
      return {
        color: 'black'
      }
    },

    thumbStyle() {
      return {
        right: '2px',
        borderRadius: '5px',
        backgroundColor: '#027be3',
        width: '5px',
        opacity: 0.75
      }
    }
  },
  data() {
    return {
      iconSize: '.75rem',
      placeholder: 'Wyszukaj wiadomość',
      placeholderTexttera: 'Wpisz tutaj swoją wiadomość. Naciśnij SHIFT + ENTER, aby dodać nową linię.',
      text: '',
      inputSearch: '',
      tabHistory: 'ulubiony',
      tabHistoryShow: false,
      submitting: false,
      moduleName: 'Chat',
      dense: false,
      dataRow: {
        0: {
          id: 46,
          crmid: 167,
          userid: 1,
          created: '12:55',
          messages: 'test1',
          user_name: 'Administrator',
          last_name: 'Administrator',
          role_name: 'Board of Management',
          color: 'blue',
          img: 'https://cdn.quasar-framework.org/img/avatar3.jpg'
        },
        1: {
          id: 47,
          crmid: 167,
          userid: 1,
          created: '12:55',
          messages: 'test2',
          user_name: 'Test',
          last_name: 'Administrator',
          role_name: 'Board of Management',
          color: 'bg-grey-4',
          img: 'https://cdn.quasar-framework.org/img/avatar4.jpg'
        },
        2: {
          id: 48,
          crmid: 167,
          userid: 1,
          created: '12:55',
          messages: 'test3',
          user_name: 'Administrator',
          last_name: 'Administrator',
          role_name: 'Board of Management',
          color: 'blue',
          img: 'https://cdn.quasar-framework.org/img/avatar3.jpg'
        },
        3: {
          id: 49,
          crmid: 167,
          userid: 1,
          created: '12:55',
          messages: 'test3',
          user_name: 'Test',
          last_name: 'Administrator',
          role_name: 'Board of Management',
          color: 'bg-grey-4',
          img: 'https://cdn.quasar-framework.org/img/avatar4.jpg'
        },
        4: {
          id: 50,
          crmid: 167,
          userid: 1,
          created: '12:55',
          messages: 'tesdsdsdsdsdst3',
          user_name: 'Test',
          last_name: 'Administrator',
          role_name: 'Board of Management',
          color: 'bg-grey-4',
          img: 'https://cdn.quasar-framework.org/img/avatar4.jpg'
        },
        5: {
          id: 51,
          crmid: 167,
          userid: 1,
          created: '12:55',
          messages: 'dsdsdsd',
          user_name: 'Administrator',
          last_name: 'Administrator',
          role_name: 'Board of Management',
          color: 'blue',
          img: 'https://cdn.quasar-framework.org/img/avatar3.jpg'
        },
        6: {
          id: 52,
          crmid: 167,
          userid: 1,
          created: '12:55',
          messages: 'dssss',
          user_name: 'Administrator',
          last_name: 'Administrator',
          role_name: 'Board of Management',
          color: 'blue',
          img: 'https://cdn.quasar-framework.org/img/avatar3.jpg'
        }
      },
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
  methods: {
    simulateSubmit() {
      this.submitting = true

      // Simulating a delay here.
      // When we are done, we reset "submitting"
      // Boolean to false to restore the
      // initial state.
      setTimeout(() => {
        // delay simulated, we are done,
        // now restoring submit to its initial state
        this.submitting = false
      }, 3000)
    }
  }
}
</script>
<style module lang="stylus"></style>
