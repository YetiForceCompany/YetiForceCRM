/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import ColorPicker from './ColorPicker.vue'

Vue.config.productionTip = false

window.ColorPicker = {
	component: ColorPicker,
	mount(el) {
		return new Vue({
			render: h => h(ColorPicker)
		}).$mount(el)
	}
}
