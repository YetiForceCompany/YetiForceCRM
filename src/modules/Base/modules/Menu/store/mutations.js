/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
export default {
  /**
   * Update menu positions
   *
   * @param {object} state
   * @param {array} positions
   */
  updateItems(state, items) {
    state.items = items
  },

  addItem(state, menuItem) {
    state.items.push(menuItem)
  }
}
