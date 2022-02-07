<!--
/**
 * KnowledgeBaseModal component
 *
 * @description KnowledgeBaseModal parent component
 * @license YetiForce Public License 5.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
-->
<template>
	<q-dialog v-model="dialog" :maximized="maximized" transition-show="slide-up" transition-hide="slide-down" content-class="quasar-reset">
		<drag-resize :coordinates.sync="coordinates" :maximized="maximized">
			<q-card class="KnowledgeBaseModal full-height">
				<q-bar dark class="bg-yeti text-white dialog-header">
					<div class="flex items-center no-wrap full-width js-drag">
						<div class="flex items-center">
							<div class="flex items-center no-wrap ellipsis q-mr-sm-sm">
								<span :class="[`yfm-${moduleName}`, 'q-mr-sm']"></span>
								{{ translate(`JS_${moduleName.toUpperCase()}`) }}
							</div>
						</div>
						<q-space />
						<template v-if="$q.platform.is.desktop">
							<ButtonGrab v-show="!maximized" class="flex text-white" grabClass="js-drag" size="19px" />
							<q-btn dense flat :icon="maximized ? 'mdi-window-restore' : 'mdi-window-maximize'" @click="maximized = !maximized">
								<q-tooltip>{{ maximized ? translate('JS_MINIMIZE') : translate('JS_MAXIMIZE') }}</q-tooltip>
							</q-btn>
						</template>
						<q-btn dense flat icon="mdi-close" v-close-popup>
							<q-tooltip>{{ translate('JS_CLOSE') }}</q-tooltip>
						</q-btn>
					</div>
				</q-bar>
				<div>
					<knowledge-base :coordinates="coordinates" />
				</div>
			</q-card>
		</drag-resize>
	</q-dialog>
</template>
<script>
import DragResize from 'components/DragResize.vue'
import ButtonGrab from 'components/ButtonGrab.vue'
import KnowledgeBase from './KnowledgeBase.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions } = createNamespacedHelpers('KnowledgeBase')
export default {
	name: 'KnowledgeBaseModal',
	components: { KnowledgeBase, DragResize, ButtonGrab },
	data() {
		return {
			coordinates: {
				width: Quasar.plugins.Screen.width - 100,
				height: Quasar.plugins.Screen.height - 100,
				top: 0,
				left: Quasar.plugins.Screen.width - (Quasar.plugins.Screen.width - 100 / 2),
			},
		}
	},
	computed: {
		...mapGetters(['maximized', 'moduleName']),
		dialog: {
			set(val) {
				this.$store.commit('KnowledgeBase/setDialog', val)
			},
			get() {
				return this.$store.getters['KnowledgeBase/dialog']
			},
		},
		maximized: {
			set(val) {
				this.$store.commit('KnowledgeBase/setMaximized', val)
			},
			get() {
				return this.$store.getters['KnowledgeBase/maximized']
			},
		},
	},
	methods: {
		...mapActions(['fetchCategories', 'initState']),
	},
	created() {
		this.initState(this.$options.state)
	},
}
</script>
<style>
.dialog-header {
	padding-top: 3px !important;
	padding-bottom: 3px !important;
	height: unset !important;
}
.modal-full-height {
	max-height: calc(100vh - 31.14px) !important;
}
.contrast-50 {
	filter: contrast(50%);
}
</style>
