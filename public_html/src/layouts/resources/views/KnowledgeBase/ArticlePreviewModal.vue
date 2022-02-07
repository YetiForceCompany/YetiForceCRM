<!--
/**
 * ArticlePreviewModal component
 *
 * @description Vue root component for article preview dialog
 * @license YetiForce Public License 5.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
-->
<template>
	<article-preview :isDragResize="false" maximizedOnly previewDialog>
		<template #headerRight>
			<q-btn dense flat icon="mdi-close" @click="hideModal">
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
	created() {
		this.initState(this.$options.state)
		this.fetchCategories()
		this.fetchRecord(this.$options.state.recordId)
		document.addEventListener('keyup', (evt) => {
			if (evt.keyCode === 27) {
				this.hideModal()
			}
		})
	},
	methods: {
		hideModal() {
			app.hideModalWindow()
			this.initState({
				record: false,
			})
			this.$destroy()
		},
		...mapActions(['fetchCategories', 'fetchRecord', 'initState']),
	},
}
</script>
<style></style>
