/**
 * Quasar config file
 *
 * @description Including selected components and other options
 * @license YetiForce Public License 3.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
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
	QSeparator,
	QResizeObserver
} from 'quasar/src/components.js'
import * as directives from 'quasar/src/directives.js'
import { AppFullscreen } from 'quasar/src/plugins.js'
import mdi from 'quasar/icon-set/mdi-v3.js'

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
				QSeparator,
				QResizeObserver
			},
			directives,
			plugins: { AppFullscreen },
			...opts
		})
	}
}

window.Vue.use(Quasar)
Quasar.iconSet.set(mdi)

export default Quasar
