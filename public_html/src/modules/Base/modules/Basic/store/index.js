/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

export default class Basic extends VuexClass {
  constructor() {
    super()
    this.state = {
      testVariable: 'test variable',
      menu: true,
      moduleName: 'Basic'
    }
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
