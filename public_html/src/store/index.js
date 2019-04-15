/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import Objects from '/utilities/Objects.js'

class Three extends VuexClass {
	constructor() {
		super()
		this.state = {}
		this.namespaced = true
		// ...
	}
}
class Two extends VuexClass {
	constructor() {
		super()
		this.state = {
			isBtn: false
		}
		this.modules = {
			three: new Three()
		}
		this.namespaced = true
	}
	set switchBtn(payload) {
		this.state.isBtn = !this.state.isBtn
	}
	get text() {
		return this.state.isBtn ? 'true' : 'false'
	}
}

class Four extends VuexClass {
	constructor() {
		super()
		this.state = {
			isBtn: true
		}
		this.modules = {
			three: new Three()
		}
		this.namespaced = false
	}
	set switchBtn(payload) {
		this.state.isBtn = !this.state.isBtn
	}
	get text() {
		return this.state.isBtn ? 'true' : 'false'
	}
}
class One extends VuexClass {
	constructor() {
		super()
		this.state = {
			count: 0
		}
		// Note: the sub module has no plugins option
		this.plugins = [VuexClass.init()]
		this.modules = {
			two: new Two(),
			four: new Four()
		}
	}
	// mutations
	set setCount(count) {
		this.state.count = count
	}
	// getters
	get countText() {
		return `text:${this.state.count}`
	}
	// actions
	clickCount() {
		setTimeout(() => {
			// Two methods to submit mutation
			// 1、Direct assignment
			// this.setCount = 1000
			// 2、Call method, note: if there is a get setCount attribute on class, this method does not exist.
			// this.setCount(1000)
		})
	}
}

const one = new One()
let store = null
console.log(VuexClass)

function createStore() {
	if (store === null) {
		Vue.use(VuexClass).use(Vuex)
		// store = new Vuex.Store({
		//   mutations: {
		//     ['Global/update'](state, payload) {
		//       state = Objects.mergeDeepReactive(state, payload)
		//     }
		//   },
		//   actions: {
		//     ['Global/update']({ commit }, payload) {
		//       commit('Global/update', payload)
		//     }
		//   }
		// })
		store = new Vuex.Store(one)
		console.log(store)
	}
	return store
}

export default createStore
export { store }
