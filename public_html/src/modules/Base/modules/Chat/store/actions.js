/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import mutations from '/src/store/mutations.js'
import getters from '/src/store/getters.js'

export default {
  setDialog({ commit }, isOpen) {
    commit(mutations.Base.Chat.dialog, isOpen)
  },
  maximizedDialog({ commit }, isMax) {
    commit(mutations.Base.Chat.maximizedDialog, isMax)
  },

  toggleLeftPanel({ commit, rootGetters }) {
    commit(mutations.Base.Chat.leftPanel, !rootGetters[getters.Base.Chat.leftPanel])
  },
  toggleRightPanel({ commit, rootGetters }) {
    commit(mutations.Base.Chat.rightPanel, !rootGetters[getters.Base.Chat.rightPanel])
  }
}
