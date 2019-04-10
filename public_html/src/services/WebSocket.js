/**
 * Websocket connection
 *
 * @description initialization and socket vue emitter
 * @license YetiForce Public License 3.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */

'use strict'

import { store } from '/src/store/index.js'
import getters from '/src/store/getters.js'
import actions from '/src/store/actions.js'
import Objects from '/utilities/Objects.js'

let connection = null
let Socket = new Vue({
  methods: {
    send(message) {
      if (connection && 1 === connection.readyState) {
        connection.send(message)
      } else {
        console.error('websocket disconnected, connection:', connection)
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
        const data = JSON.parse(message.data)
        Socket.$emit('message', data)
        if (!data.id) {
          triggerAction(data)
        }
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
    const vuexAction = `${params.parentModule}.${params.module}.${params.action}`
    const actionName = Objects.get(actions, vuexAction)
    store.dispatch(actionName, params.data)
  } catch (err) {
    console.error('socket action doesnt exist', err)
    return
  }
}

export default Socket
export { initSocket }
