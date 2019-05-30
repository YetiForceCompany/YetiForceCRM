// initial state
const state = {
	record: false,
	dialog: false,
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
		console.log(state)
		console.log(getters)
		return AppConnector.request({
			module: getters.moduleName,
			action: 'KnowledgeBaseAjax',
			mode: 'detail',
			record: id
		}).done(data => {
			commit('setRecord', data.result)
			commit('setDialog', true)
			progressIndicatorElement.progressIndicator({ mode: 'hide' })
			aDeferred.resolve(data.result)
		})
	},
	fetchCategories({ state, commit, getters }) {
		const aDeferred = $.Deferred()
		return AppConnector.request({
			module: getters.moduleName,
			action: 'KnowledgeBaseAjax',
			mode: 'categories'
		}).done(data => {
			console.log(state)

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
			commit('setTreeData', data.result)
			progressIndicatorElement.progressIndicator({ mode: 'hide' })
			aDeferred.resolve(data.result)
		})
	},
	initState({ state, commit }, data) {
		console.log(data)
		commit('setState', data)
	}
}

// mutations
const mutations = {
	setState(state, payload) {
		state = Object.assign(state, payload)
		console.log(state, payload)
	},
	setRecord(state, payload) {
		state.record = payload
	},
	setDialog(state, payload) {
		state.dialog = payload
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
