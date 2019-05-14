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

  fetchData() {
    //TODO request based on module name
    return new Promise((resolve, reject) => {
      fetch('src/modules/Base/modules/Basic/views/List/store/testdata.json')
        .then(res => res.json())
        .then(data => {
          resolve(data)
        })
    })
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
