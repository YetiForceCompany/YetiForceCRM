<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <q-footer class="bg-blue-grey-10 text-white">
    <q-bar>
      <template>
        <div class="q-pa-md q-gutter-sm">
          <q-breadcrumbs>
            <q-breadcrumbs-el class="text-white" :label="groupFooter.toUpperCase()" />
            <q-breadcrumbs-el class="text-white text-cyan-9 text-bold" :label="roomFooter.toUpperCase()" />
          </q-breadcrumbs>
        </div>
      </template>
    </q-bar>
  </q-footer>
</template>
<script>
export default {
  name: 'ChatFooter',
  props: {
    groupFooter: { type: String, required: true },
    roomFooter: { type: String, required: true }
  },

  data() {
    return {
      iconSize: '.75rem',
      maximizedToggle: true,
      inputSearchSearch: '',
      inputSearchVisible: true,
      placeholder: 'Wyszukaj wiadomość',
      visible: false,
      left: true,
      right: true,
      tabHistory: 'ulubiony',
      tabHistoryShow: false,
      submitting: false,
      moduleName: 'Chat',
      dense: false
    }
  }
}
</script>
<style module lang="stylus"></style>
