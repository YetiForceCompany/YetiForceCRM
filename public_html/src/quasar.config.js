/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

import VuePlugin from 'quasar/src/vue-plugin.js'

import {
	QLayout,
	QPageContainer,
	QPage,
	QHeader,
	QFooter,
	QDrawer,
	QPageSticky,
	QPageScroller,
	QToolbar,
	QToolbarTitle,
	QBtn,
	QBreadcrumbs,
	QBreadcrumbsEl,
	QIcon,
	QInput,
	QToggle,
	QTooltip,
	QScrollArea,
	QList,
	QItem,
	QItemLabel,
	QItemSection,
	QTable,
	QDialog,
	QCard,
	QBar,
	QSpace,
	QCardSection,
	QCarousel,
	QCarouselSlide,
	QCarouselControl,
	QImg,
	QAvatar,
	QSeparator
} from 'quasar/src/components.js'
import * as directives from 'quasar/src/directives.js'
import { AppFullscreen } from 'quasar/src/plugins.js'
import lang from './quasar.lang.js'
import mdi from 'quasar/icon-set/mdi-v3.js'

function setLang() {
	if (lang[CONFIG.langKey] !== undefined) {
		return lang[CONFIG.langKey]
	} else {
		let langPref = CONFIG.langPrefix.replace('-', '')
		return lang[langPref]
	}
}

const Quasar = {
	...VuePlugin,
	install(Vue, opts) {
		VuePlugin.install(Vue, {
			components: {
				QLayout,
				QPageContainer,
				QPage,
				QHeader,
				QFooter,
				QDrawer,
				QPageSticky,
				QPageScroller,
				QToolbar,
				QToolbarTitle,
				QBtn,
				QBreadcrumbs,
				QBreadcrumbsEl,
				QIcon,
				QInput,
				QToggle,
				QTooltip,
				QScrollArea,
				QList,
				QItem,
				QItemLabel,
				QItemSection,
				QTable,
				QDialog,
				QCard,
				QBar,
				QSpace,
				QCardSection,
				QCarousel,
				QCarouselSlide,
				QCarouselControl,
				QImg,
				QAvatar,
				QSeparator
			},
			directives,
			plugins: { AppFullscreen },
			lang: setLang(),
			...opts
		})
	}
}
window.Vue.use(Quasar)
Quasar.iconSet.set(mdi)

export default Quasar
