/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
export default {
  dialog(state, idOpen) {
    state.dialog = idOpen
  },
  maximizedDialog(state, isMax) {
    state.maximizedDialog = isMax
  },
  leftPanel(state, isOpen) {
    state.leftPanel = isOpen
  },
  rightPanel(state, isOpen) {
    state.rightPanel = isOpen
  }
}
