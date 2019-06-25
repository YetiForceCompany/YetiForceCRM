/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

export default {
	setDialog({ commit }, isOpen) {
		commit('dialog', isOpen)
	},
	maximizedDialog({ commit }, isMax) {
		commit('maximizedDialog', isMax)
	},
	toggleLeftPanel({ commit, rootGetters }) {
		commit('leftPanel', !rootGetters['leftPanel'])
	},
	toggleRightPanel({ commit, rootGetters }) {
		commit('rightPanel', !rootGetters['rightPanel'])
	}
}
