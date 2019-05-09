/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

import List from '../views/List/store/index.js'
import Detail from '../views/Detail/store/index.js'
export default class Basic extends VuexClass {
  constructor(
    state = {
      testVariable: 'test variable',
      menu: true,
      moduleName: 'Basic'
    },
    modules = {
      List: new List(),
      Detail: new Detail()
    }
  ) {
    super()
    this.state = state
    this.modules = modules
    this.namespaced = false
  }

  set updateTestVariable(value) {
    this.state.testVariable = value
  }
  get getTestVariable() {
    return this.state.testVariable
  }
  get getModuleName() {
    return this.state.moduleName
  }
  getData() {
    return 'test'
  }
}
