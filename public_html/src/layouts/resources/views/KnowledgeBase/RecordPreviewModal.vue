<!--
/**
 * RecordPreviewModal component
 *
 * @description Vue root component for record preview dialog
 * @license YetiForce Public License 3.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
-->
<template>
  <record-preview :isDragResize="false">
    <template slot="header-right">
      <q-btn dense flat icon="mdi-close" @click="hideModal()">
        <q-tooltip>{{ translate('JS_CLOSE') }}</q-tooltip>
      </q-btn>
    </template>
  </record-preview>
</template>
<script>
import RecordPreview from './components/RecordPreview.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions } = createNamespacedHelpers('KnowledgeBase')
export default {
  name: 'RecordPreviewModal',
  components: { RecordPreview },
  methods: {
    hideModal() {
      app.hideModalWindow()
      this.$destroy()
    },
    ...mapActions(['fetchCategories', 'fetchRecord', 'initState'])
  },
  async created() {
    await this.initState(this.$options.state)
    await this.fetchCategories()
    await this.fetchRecord(this.$options.state.recordId)
    document.addEventListener('keyup', evt => {
      if (evt.keyCode === 27) {
        this.hideModal()
      }
    })
  }
}
</script>
<style>
</style>
