/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict'
import { store } from '/src/store/index.js'
import getters from '/src/store/getters.js'

let connection = null
function connect() {
  if (connection === null) {
    return new Promise(function(resolve, reject) {
      connection = new WebSocket(store.getters[getters.Core.Env.all]['webSocket'])
      connection.onopen = () => {
        resolve(connection)
      }
      connection.onerror = err => {
        reject(err)
      }
      connection.onclose = err => {
        reject(err)
      }
    })
  } else {
    return connection
  }
}
export default connect()
