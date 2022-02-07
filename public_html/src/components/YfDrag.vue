<!--
/**
 * YfDrag component
 *
 * @description Use of vue-drag-resize
 * @license YetiForce Public License 5.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
-->
<template>
	<vue-drag-resize
		v-if="active"
		:key="active"
		ref="drag"
		:isResizable="false"
		isDraggable
		:dragHandle="dragHandleClass"
		:x="coordinates.left"
		:y="coordinates.top"
		:w="width"
		:h="height"
		@dragging="drag"
		@dragstop="correctCoordinates"
	>
		<slot></slot>
	</vue-drag-resize>
	<div v-else :key="active">
		<slot></slot>
	</div>
</template>
<script>
import VueDragResize from '~/node_modules/vue-drag-resize/src/components/vue-drag-resize.vue'
import { keepElementInWindow } from '~/mixins/DragResize'
export default {
	name: 'YfDrag',
	mixins: [keepElementInWindow],
	components: { VueDragResize },
	props: {
		active: {
			type: Boolean,
			required: false,
			default: true,
		},
		coordinates: {
			type: Object,
			required: true,
		},
		width: {
			type: Number,
			default: 45,
		},
		height: {
			type: Number,
			default: 42,
		},
		dragHandleClass: {
			type: String,
			required: false,
		},
	},
	methods: {
		drag(newRect, e) {
			this.$emit('update:coordinates', {
				top: newRect.top,
				left: newRect.left,
			})
		},
		correctCoordinates(rect) {
			let computedRect = Object.assign({}, rect)
			if (rect.left + this.width - this.width < 0) {
				computedRect.left = this.width - this.width
			} else if (this.width + rect.left > window.innerWidth) {
				computedRect.left = window.innerWidth - this.width
			}
			if (rect.top < 0) {
				computedRect.top = 0
			} else if (rect.top > window.innerHeight - this.height) {
				computedRect.top = window.innerHeight - this.height
			}
			this.$emit('update:coordinates', computedRect)
			this.$emit('dragstop', true)
		},
	},
}
</script>

<style></style>
