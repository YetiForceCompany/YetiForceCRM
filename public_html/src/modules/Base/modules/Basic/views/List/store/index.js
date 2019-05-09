/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

export default class List extends VuexClass {
  constructor() {
    super()
    this.state = {
      listTest: 'test variable',
      moduleName: 'List'
    }
    this.namespaced = false
  }
  set updateTestVariable(value) {
    this.state.listTest = value
  }
  get getTestVariable() {
    return this.state.listTest
  }
  get getModuleName() {
    return this.state.moduleName
  }
  getData() {
    return 'test'
  }
}
