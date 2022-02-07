<!--
/**
 * KnowledgeBase component
 *
 * @description Knowledge base view root component
 * @license YetiForce Public License 5.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
-->
<template>
	<div class="KnowledgeBase h-100">
		<q-layout class="absolute" :style="coordinates.height && !maximized ? { 'max-height': `${coordinates.height - 31.14}px` } : {}" view="hHh Lpr fFf" container>
			<q-header class="bg-white text-primary" elevated>
				<q-toolbar class="flex-md-nowrap flex-wrap items-center q-gutter-x-md q-gutter-y-sm q-pl-md q-pr-none q-py-xs">
					<div :class="['flex items-center no-wrap flex-md-grow-1 q-mr-sm-sm', searchData ? 'invisible' : '']">
						<q-btn dense round push icon="mdi-menu" @click="toggleDrawer()">
							<q-tooltip>{{ translate('JS_KB_TOGGLE_CATEGORY_MENU') }}</q-tooltip>
						</q-btn>
						<q-breadcrumbs class="ml-2" v-show="tab === 'categories'">
							<template #separator>
								<q-icon size="1.5em" name="mdi-chevron-right" />
							</template>
							<q-breadcrumbs-el
								:class="[activeCategory === '' ? 'text-black' : 'cursor-pointer']"
								:icon="tree.topCategory.icon"
								:label="translate(tree.topCategory.label)"
								@click="activeCategory === '' ? '' : fetchData()"
							/>
							<template v-if="activeCategory !== ''">
								<q-breadcrumbs-el
									v-for="(category, index) in tree.categories[activeCategory].parentTree"
									:key="index"
									:class="[index === tree.categories[activeCategory].parentTree.length - 1 ? 'text-black' : 'cursor-pointer']"
									@click="index === tree.categories[activeCategory].parentTree.length - 1 ? '' : fetchData(category)"
								>
									<YfIcon v-if="tree.categories[category].icon" class="q-mr-sm" :size="iconSize" :icon="tree.categories[category].icon"></YfIcon>
									{{ tree.categories[category].label }}
								</q-breadcrumbs-el>
							</template>
						</q-breadcrumbs>
						<q-breadcrumbs class="ml-2" v-show="tab === 'accounts'">
							<q-breadcrumbs-el v-if="activeAccount !== ''" class="text-black">
								<YfIcon :size="iconSize" :icon="'yfm-Accounts'" class="q-mr-sm"></YfIcon>
								{{ activeAccount }}
							</q-breadcrumbs-el>
						</q-breadcrumbs>
					</div>
					<div class="tree-search flex flex-grow-1 no-wrap order-sm-none order-xs-last">
						<q-input
							v-model="filter"
							class="full-width"
							:placeholder="translate('JS_KB_SEARCH_PLACEHOLDER')"
							rounded
							outlined
							type="search"
							autofocus
							@input="search"
						>
							<template #prepend>
								<q-icon name="mdi-magnify" />
								<q-tooltip v-model="inputFocus" anchor="top middle" self="center middle">{{
									translate('JS_INPUT_TOO_SHORT').replace('_LENGTH_', '3')
								}}</q-tooltip>
							</template>
							<template #append>
								<q-icon v-if="filter !== ''" name="mdi-close" class="cursor-pointer" @click.stop="clearSearch()" />
								<div class="flex items-center q-ml-sm">
									<icon-info :customOptions="{ iconSize: '21px' }">
										<div style="white-space: pre-line" v-html="translate('JS_FULL_TEXT_SEARCH_INFO')"></div>
									</icon-info>
								</div>
								<div v-show="activeCategory !== ''" class="flex">
									<q-toggle v-model="categorySearch" icon="mdi-file-tree" />
									<q-tooltip>{{ translate('JS_KB_SEARCH_CURRENT_CATEGORY') }}</q-tooltip>
								</div>
							</template>
						</q-input>
					</div>
					<div class="flex-md-grow-1 flex justify-end q-ml-sm-sm">
						<q-btn round dense color="white" text-color="primary" icon="mdi-plus" @click="openQuickCreateModal()">
							<q-tooltip>{{ translate('JS_KB_QUICK_CREATE') }}</q-tooltip>
						</q-btn>
					</div>
				</q-toolbar>
			</q-header>
			<q-drawer
				v-show="!searchData"
				ref="drawer"
				v-model="left"
				:mini="miniState"
				:width="searchData ? 0 : 250"
				:breakpoint="700"
				side="left"
				elevated
				content-class="bg-white text-black"
				content-style="overflow: hidden !important"
			>
				<template v-if="showAccounts">
					<q-tabs v-model="tab" class="text-grey" dense active-color="primary" indicator-color="primary" align="justify" narrow-indicator @input="onTabChange">
						<q-tab name="categories" :label="translate('JS_KB_CATEGORIES')" />
						<q-tab name="accounts" :label="translate('JS_KB_ACCOUNTS')" />
					</q-tabs>
					<q-tab-panels v-model="tab" style="height: calc(100% - 36px)" animated>
						<q-tab-panel name="categories">
							<q-scroll-area class="fit">
								<categories-list :data="data" :activeCategory="activeCategory" @fetchData="fetchData" />
							</q-scroll-area>
						</q-tab-panel>
						<q-tab-panel name="accounts">
							<div class="q-px-sm">
								<q-input v-model="accountSearch" :placeholder="translate('JS_KB_SEARCH_PLACEHOLDER')" dense>
									<template #prepend>
										<q-icon name="mdi-magnify" size="16px" />
									</template>
									<template #append>
										<q-icon v-show="accountSearch !== ''" class="cursor-pointer" name="mdi-close" size="16px" @click="accountSearch = ''" />
									</template>
								</q-input>
							</div>
							<q-scroll-area style="height: calc(100% - 56px)">
								<q-list>
									<q-item
										v-for="account in accountsList"
										:key="account.id"
										:active="activeAccount === account.name"
										clickable
										@click="
											fetchData(null, account.id)
											activeAccount = account.name
										"
									>
										<q-item-section>{{ account.name }}</q-item-section>
										<q-item-section avatar>
											<a
												class="js-popover-tooltip--record ellipsis"
												:href="`index.php?module=Accounts&view=Detail&record=${account.id}`"
												@click.prevent=""
											>
												<q-icon name="mdi-link" />
											</a>
										</q-item-section>
									</q-item>
								</q-list>
							</q-scroll-area>
						</q-tab-panel>
					</q-tab-panels>
				</template>
				<q-scroll-area v-else class="fit">
					<categories-list :data="data" :activeCategory="activeCategory" @fetchData="fetchData" />
				</q-scroll-area>
			</q-drawer>
			<q-page-container>
				<q-page class="q-pa-sm">
					<div v-show="!searchData">
						<columns-grid v-show="featuredCategories.length" :columnBlocks="featuredCategories" class="q-pa-sm">
							<template #default="slotProps">
								<q-list bordered padding dense>
									<q-item header clickable class="text-black flex" @click="fetchData(slotProps.relatedBlock)">
										<YfIcon :icon="tree.categories[slotProps.relatedBlock].icon" :size="iconSize" class="mr-2"></YfIcon>
										{{ tree.categories[slotProps.relatedBlock].label }}
									</q-item>
									<q-item
										v-for="featuredValue in selectedTabData.featured[slotProps.relatedBlock]"
										:key="featuredValue.id"
										class="text-subtitle2"
										clickable
										v-ripple
										@click.prevent="showArticlePreview(featuredValue.id)"
									>
										<q-item-section class="align-items-center flex-row no-wrap justify-content-start">
											<q-icon class="mr-2" name="mdi-star" :size="iconSize"></q-icon>
											<a
												class="js-popover-tooltip--record ellipsis"
												:href="`index.php?module=${moduleName}&view=Detail&record=${featuredValue.id}`"
												>{{ featuredValue.subject }}</a
											>
										</q-item-section>
									</q-item>
								</q-list>
							</template>
						</columns-grid>
						<div v-show="activeCategory !== '' || tab === 'accounts'">
							<q-separator v-show="featuredCategories.length" />
							<articles-list :data="selectedTabData.records" :title="translate('JS_KB_ARTICLES')" @onClickRecord="previewDialog = true" />
						</div>
					</div>
					<articles-list v-show="searchData" :data="searchDataArray" :title="translate('JS_KB_ARTICLES')" @onClickRecord="previewDialog = true" />
				</q-page>
			</q-page-container>
		</q-layout>
		<article-preview isDragResize :previewDialog.sync="previewDialog" @onDialogToggle="onDialogToggle" />
	</div>
</template>
<script>
import YfIcon from '~/components/YfIcon.vue'
import IconInfo from '~/components/IconInfo.vue'
import ColumnsGrid from '~/components/ColumnsGrid.vue'
import Carousel from './components/Carousel.vue'
import ArticlesList from './components/ArticlesList.vue'
import ArticlePreview from './components/ArticlePreview.vue'
import CategoriesList from './components/CategoriesList.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions } = createNamespacedHelpers('KnowledgeBase')
export default {
	name: 'KnowledgeBase',
	components: { YfIcon, IconInfo, Carousel, ArticlesList, ArticlePreview, ColumnsGrid, CategoriesList },
	props: {
		coordinates: {
			type: Object,
			default: () => {
				return {
					width: 0,
					height: 0,
					top: 0,
					left: 0,
				}
			},
		},
	},
	data() {
		return {
			drawerBehaviour: 'desktop',
			miniState: false,
			left: true,
			filter: '',
			accountSearch: '',
			categorySearch: false,
			searchData: false,
			activeCategory: '',
			activeAccount: '',
			previewDialog: false,
			tab: 'categories',
			showAccounts: false,
			data: {
				categories: [],
				records: [],
				featured: {},
			},
			accountsData: {
				categories: [],
				records: [],
				featured: {},
			},
			accounts: [],
		}
	},
	computed: {
		...mapGetters(['tree', 'record', 'iconSize', 'moduleName', 'maximized', 'defaultTreeIcon']),
		accountsList() {
			if (this.accountSearch === '') {
				return this.accounts
			} else {
				return this.accounts.filter((account) => {
					return account.name.toLowerCase().includes(this.accountSearch.toLowerCase())
				})
			}
		},
		selectedTabData() {
			return this.tab === 'categories' ? this.data : this.accountsData
		},
		searchDataArray() {
			return this.searchData ? this.searchData : []
		},
		featuredCategories() {
			if (typeof this.selectedTabData.featured.length === 'undefined' && this.selectedTabData.categories) {
				let arr = this.selectedTabData.categories.map((e) => {
					return this.selectedTabData.featured[e] ? e : false
				})
				return arr.filter(function (item) {
					return typeof item === 'string'
				})
			} else {
				return []
			}
		},
		inputFocus: {
			set(val) {
				return false
			},
			get() {
				return this.filter.length > 0
			},
		},
	},
	methods: {
		...mapActions(['fetchRecord', 'fetchCategories']),
		onTabChange(tabName) {
			if (this.accounts.length === 0 && tabName === 'accounts') {
				this.fetchAccounts()
			}
		},
		fetchAccounts() {
			const aDeferred = $.Deferred()
			const progressIndicatorElement = $.progressIndicator({
				blockInfo: { enabled: true },
			})
			return AppConnector.request({
				module: this.moduleName,
				action: 'KnowledgeBaseAjax',
				mode: 'getAccounts',
			}).done((data) => {
				let listData = data.result
				if (listData) {
					listData = Object.keys(listData).map(function (key) {
						return { name: listData[key], id: key }
					})
				}
				this.accounts = listData
				progressIndicatorElement.progressIndicator({ mode: 'hide' })
				aDeferred.resolve(listData)
			})
		},
		search() {
			if (this.filter.length >= 3) {
				this.debouncedSearch()
			} else {
				this.searchData = false
			}
		},
		clearSearch() {
			this.filter = ''
			this.searchData = false
		},
		fetchData(category = '', accountId = '') {
			const aDeferred = $.Deferred()
			if (category !== null) {
				this.activeCategory = category
			}
			const progressIndicatorElement = $.progressIndicator({
				blockInfo: { enabled: true },
			})
			return AppConnector.request({
				module: this.moduleName,
				action: 'KnowledgeBaseAjax',
				mode: 'list',
				category: category,
				accountid: accountId,
			}).done((data) => {
				let listData = data.result
				if (listData.showAccounts) {
					this.showAccounts = true
				}
				if (listData.records) {
					listData.records = Object.keys(listData.records).map(function (key) {
						return { ...listData.records[key], id: key }
					})
				}
				if (accountId !== '') {
					this.accountsData = listData
				} else {
					this.data = listData
				}
				progressIndicatorElement.progressIndicator({ mode: 'hide' })
				aDeferred.resolve(listData)
			})
		},
		openQuickCreateModal() {
			App.Components.QuickCreate.createRecord(this.moduleName)
		},
		showArticlePreview(id) {
			this.fetchRecord(id).then(() => {
				this.previewDialog = true
			})
		},
		toggleDrawer() {
			if (!this.$refs.drawer.belowBreakpoint) {
				this.miniState = !this.miniState
			} else {
				this.left = !this.left
			}
		},
		onDialogToggle(val) {
			this.previewDialog = val
		},
	},
	created() {
		this.fetchCategories().then((_) => {
			this.fetchData()
		})
	},
	mounted() {
		const debounceDelay = 1000
		this.debouncedSearch = Quasar.utils.debounce(() => {
			if (this.filter.length < 3) {
				return
			}
			const aDeferred = $.Deferred()
			const progressIndicatorElement = $.progressIndicator({
				blockInfo: { enabled: true },
			})
			AppConnector.request({
				module: this.moduleName,
				action: 'KnowledgeBaseAjax',
				mode: 'search',
				value: this.filter,
				category: this.categorySearch ? this.activeCategory : '',
			}).done((data) => {
				let listData = data.result
				if (listData) {
					listData = Object.keys(listData).map(function (key) {
						return { ...listData[key], id: key }
					})
				}
				this.searchData = listData
				aDeferred.resolve(listData)
				progressIndicatorElement.progressIndicator({ mode: 'hide' })
				return listData
			})
		}, debounceDelay)
	},
}
</script>
<style>
.tree-search {
	min-width: 320px;
	width: 50%;
}
.tree-search .q-field__control,
.tree-search .q-field__marginal {
	height: 40px;
}
</style>
