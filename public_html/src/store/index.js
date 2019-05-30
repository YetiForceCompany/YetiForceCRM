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
console.log(window.Vue)
console.log(vuexStore)
export default vuexStore
export { Vuex }
