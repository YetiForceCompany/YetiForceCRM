import Vuex from 'vuex'
import KnowledgeBase from './modules/KnowledgeBase.js'

window.Vue.use(Vuex)

const debug = process.env.NODE_ENV !== 'production'

const vuexStore = new Vuex.Store({
	modules: {
		KnowledgeBase
	},
	strict: debug
})

export default vuexStore
