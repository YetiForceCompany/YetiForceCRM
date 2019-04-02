/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
export default {
  /**
   * Push log message
   *
   * @param   {object}  state
   * @param   {payload}  payload {moduleName:String, type:('log'|'info'|'notice'|'warning'|'error'), message:String, data:any}
   */
  push(state, payload) {
    payload.date = new Date()
    state.all.push(payload)
    state[payload.data.type].push(payload)
  }
}
