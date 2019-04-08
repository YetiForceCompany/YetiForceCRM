/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict'
import { store } from '/src/store/index.js'
import getters from '/src/store/getters.js'
import actions from '/src/store/actions.js'
import Objects from '/utilities/Objects.js'

let connection = null
let Socket = new Vue({
  methods: {
    send(message) {
      if (1 === connection.readyState) {
        connection.send(message)
      } else {
        console.error('websocket disconnected, status:' + connection.readyState)
      }
    }
  }
})
/**
 * connect with websocket
 */
function initSocket() {
  if (connection === null || connection.readyState !== 1) {
    return new Promise(function(resolve, reject) {
      connection = new WebSocket(store.getters[getters.Core.Env.all]['webSocket'])
      connection.onmessage = message => {
        Socket.$emit('message', message)
        triggerAction(JSON.parse(message.data))
      }
      connection.onerror = err => {
        Socket.$emit('error', err)
        reject(err)
      }
      connection.onclose = err => {
        reject(err)
      }
      connection.onopen = () => {
        resolve(Socket)
      }
    })
  } else {
    return connection
  }
}

function triggerAction(params) {
  try {
    const actionName = Objects.get(actions, params.action)
    store.dispatch(actionName, params.data)
  } catch (err) {
    console.error('socket action doesnt exist', err)
    return
  }
}

export default Socket
export { initSocket }
