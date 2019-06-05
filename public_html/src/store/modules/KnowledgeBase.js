/**
 * Knowledge base module
 *
 * @description Knowledge base vuex module
 * @license YetiForce Public License 3.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */

import { Screen } from 'quasar'
// initial state
const state = {
	record: false,
	dialog: false,
	maximized: true,
	moduleName: '',
	iconSize: '18px',
	tree: {
		activeCategory: '',
		data: {
			records: [],
			featured: {}
		},
		topCategory: {
			icon: 'mdi-file-tree',
			label: 'JS_MAIN_CATEGORIES'
		},
		categories: {}
	},
	coordinates: {
		width: Screen.width - 100,
		height: Screen.height - 100,
		top: 0,
		left: Screen.width - (Screen.width - 100 / 2)
	}
}

// getters
const getters = {
	moduleName(state) {
		return state.moduleName
	},
	record(state) {
		return state.record
	},
	dialog(state) {
		return state.dialog
	},
	maximized(state) {
		return state.maximized
	},
	coordinates(state) {
		return state.coordinates
	},
	iconSize(state) {
		return state.iconSize
	},
	tree(state) {
		return state.tree
	}
}

// actions
const actions = {
	fetchRecord({ state, commit, getters }, id) {
		const aDeferred = $.Deferred()
		const progressIndicatorElement = $.progressIndicator({
			blockInfo: { enabled: true }
		})
		return AppConnector.request({
			module: getters.moduleName,
			action: 'KnowledgeBaseAjax',
			mode: 'detail',
			record: id
		}).done(data => {
			let recordData = data.result
			if (recordData.related.Articles) {
				recordData.related.Articles = Object.keys(recordData.related.Articles).map(function(key) {
					return { ...recordData.related.Articles[key], id: key }
				})
			}
			if (!getters.dialog) {
				commit('setDialog', true)
			}
			commit('setRecord', recordData)
			progressIndicatorElement.progressIndicator({ mode: 'hide' })
			aDeferred.resolve(recordData)
		})
	},
	fetchCategories({ state, commit, getters }) {
		const aDeferred = $.Deferred()
		return AppConnector.request({
			module: getters.moduleName,
			action: 'KnowledgeBaseAjax',
			mode: 'categories'
		}).done(data => {
			commit('setTreeCategories', data.result)
			aDeferred.resolve(data.result)
		})
	},
	fetchData({ state, commit, getters }, category = '') {
		const aDeferred = $.Deferred()
		commit('setActiveCategory', category)
		const progressIndicatorElement = $.progressIndicator({
			blockInfo: { enabled: true }
		})
		return AppConnector.request({
			module: getters.moduleName,
			action: 'KnowledgeBaseAjax',
			mode: 'list',
			category: category
		}).done(data => {
			let listData = data.result
			if (listData.records) {
				listData.records = Object.keys(listData.records).map(function(key) {
					return { ...listData.records[key], id: key }
				})
			}
			commit('setTreeData', listData)
			progressIndicatorElement.progressIndicator({ mode: 'hide' })
			aDeferred.resolve(listData)
		})
	},
	initState({ state, commit }, data) {
		commit('setState', data)
	}
}

// mutations
const mutations = {
	setState(state, payload) {
		state = Object.assign(state, payload)
	},
	setRecord(state, payload) {
		state.record = payload
	},
	setDialog(state, payload) {
		state.dialog = payload
	},
	setMaximized(state, payload) {
		state.maximized = payload
	},
	setCoordinates(state, payload) {
		state.coordinates = payload
	},
	setTreeData(state, payload) {
		state.tree.data = payload
	},
	setTreeCategories(state, payload) {
		state.tree.categories = payload
	},
	setActiveCategory(state, payload) {
		state.tree.activeCategory = payload
	}
}

export default {
	namespaced: true,
	state,
	getters,
	actions,
	mutations
}
