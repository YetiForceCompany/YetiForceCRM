/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import ApiService from '/services/Api.js'
import getters from '/store/getters.js'
import mutations from '/store/mutations.js'
export default {
  /**
   * Fetch data
   */
  fetchData({ commit, rootGetters }) {
    commit(mutations.Core.Menu.updateItems, [
      {
        name: 'Base.Home',
        path: '/home',
        icon: 'mdi-home',
        label: 'Home',
        children: []
      },
      {
        name: 'Base.ModuleExample',
        path: '/module-example',
        icon: 'mdi-cube',
        label: 'Base Example',
        children: []
      },
      {
        name: 'Base.Basic',
        path: '/basic',
        icon: 'mdi-cube',
        label: 'Basic',
        children: []
      },
      {
        name: 'Testing',
        path: '',
        icon: 'mdi-settings',
        label: 'Testing',
        children: [
          {
            name: 'Settings.ModuleExample',
            path: '/settings/module-example',
            icon: 'mdi-cube',
            label: 'Example',
            children: []
          },
          {
            name: 'Settings.Menu',
            path: '/settings/menu',
            icon: 'mdi-cube',
            label: 'Menu Settings',
            children: []
          }
        ]
      }
    ])
  }
}
