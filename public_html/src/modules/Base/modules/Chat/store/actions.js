/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import mutations from '/src/store/mutations.js'

export default {
  setDialog({ commit }, isOpen) {
    console.log('ser')
    commit(mutations.Base.Chat.dialog, isOpen)
  }
}
