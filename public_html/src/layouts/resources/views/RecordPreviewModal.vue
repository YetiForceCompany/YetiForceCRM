/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

<template>
  <record-preview>
    <template slot="header-right">
      <q-btn dense flat icon="mdi-close" @click="hideModal()">
        <q-tooltip>{{ translate('JS_CLOSE') }}</q-tooltip>
      </q-btn>
    </template>
  </record-preview>
</template>
<script>
import RecordPreview from './RecordPreview.vue'
export default {
  name: 'RecordPreviewModal',
  components: { RecordPreview },
  methods: {
    hideModal() {
      app.hideModalWindow()
      this.$destroy()
    }
  },
  async created() {
    await this.getCategories()
    await this.getRecord(this.$options.options.recordId)
  }
}
</script>
<style>
</style>
