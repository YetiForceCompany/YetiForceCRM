// initial state
const state = {
	record: false,
	dialog: false,
	moduleName: ''
}

// getters
const getters = {
	moduleName(state, getters) {
		return state.moduleName
	},
	getRecord(state, getters) {
		return state.record
	}
}

// actions
const actions = {
	getRecord({ state, commit, getters }, id) {
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
	setRecord(state, { record }) {
		state.record = record
	},
	setDialog(state, { dialog }) {
		state.dialog = dialog
	}
}

export default {
	namespaced: true,
	state,
	getters,
	actions,
	mutations
}
