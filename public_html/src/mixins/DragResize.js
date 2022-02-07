/**
 * DragResize mixins
 *
 * @description Mixins for drag-resize components
 * @license YetiForce Public License 5.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
export const keepElementInWindow = {
	methods: {
		keepElementInWindow() {
			this.correctCoordinates(this.coordinates)
		},
	},
	mounted() {
		this.keepElementInWindow(this.coordinates)
		window.addEventListener('resize', this.keepElementInWindow)
	},
}
