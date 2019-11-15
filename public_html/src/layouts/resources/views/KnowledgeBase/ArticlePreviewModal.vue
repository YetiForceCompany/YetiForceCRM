<!--
/**
 * ArticlePreviewModal component
 *
 * @description Vue root component for article preview dialog
 * @license YetiForce Public License 3.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
-->
<template>
  <article-preview
    isDragResize
    maximizedOnly
    previewDialog
  >
    <template slot="header-right">
      <q-btn
        dense
        flat
        icon="mdi-close"
        @click="hideModal()"
      >
        <q-tooltip>{{ translate('JS_CLOSE') }}</q-tooltip>
      </q-btn>
    </template>
  </article-preview>
</template>
<script>
import ArticlePreview from './components/ArticlePreview.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions } = createNamespacedHelpers('KnowledgeBase')
export default {
  name: 'ArticlePreviewModal',
  components: { ArticlePreview },
  methods: {
    hideModal() {
      app.hideModalWindow()
      this.initState({
        record: false
      })
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
<style></style>
