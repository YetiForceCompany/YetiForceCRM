/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

export default class Detail extends VuexClass {
  constructor() {
    super()
    this.state = {
      detailTest: 'test variable',
      moduleName: 'Detail'
    }
    this.namespaced = false
  }
  set updateTestVariable(value) {
    this.state.detailTest = value
  }
  get getTestVariable() {
    return this.state.detailTest
  }
  get getModuleName() {
    return this.state.moduleName
  }
  getData() {
    return 'test'
  }
}
