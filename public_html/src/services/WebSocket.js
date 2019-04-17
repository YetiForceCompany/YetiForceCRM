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
let numberOfRetries = 3
const timeout = 1500

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
  const socketUrl = store.getters[getters.Core.Env.all]['webSocketUrl']

  if (connection === null || connection.readyState !== 1) {
    let hasReturned = false
    return new Promise(function(resolve, reject) {
      if (!socketUrl) {
        store.dispatch(actions.Core.Notification.show, { message: 'Socket is inactive', color: 'negative' })
        reject()
        return
      }
      setTimeout(function() {
        if (!hasReturned) {
          rejectInternal('Opening websocket timed out: ' + socketUrl)
        }
      }, timeout)
      connection = new WebSocket(socketUrl)
      connection.onmessage = message => {
        const data = JSON.parse(message.data)
        Socket.$emit('message', data)
        if (!data.id) {
          triggerAction(data)
        }
      }
      connection.onerror = err => {
        Socket.$emit('error', err)
        console.info('websocket error! url: ' + socketUrl)
        rejectInternal(err)
      }
      connection.onclose = err => {
        console.info('websocket closed! url: ' + socketUrl)
        rejectInternal(err)
      }
      connection.onopen = () => {
        hasReturned = true
        resolve(Socket)
      }
      function rejectInternal(err) {
        if (numberOfRetries <= 0) {
          console.info(err)
          store.dispatch(actions.Core.Notification.show, { message: 'Socket is inactive', color: 'negative' })
          reject(err)
        } else if (!hasReturned) {
          hasReturned = true
          console.info(
            err,
            'Retrying connection to websocket! url: ' + socketUrl + ', remaining retries: ' + --numberOfRetries
          )
          initSocket().then(resolve, reject)
        }
      }
    })
  } else {
    return connection
  }
}

function triggerAction(params) {
  try {
    const vuexAction = `${params.module}.${params.action}`
    let actionName = Objects.get(actions.Base, vuexAction)
    if (!actionName) {
      actionName = Objects.get(actions.Core, vuexAction)
    }
    store.dispatch(actionName, params.data)
  } catch (err) {
    console.error('socket action doesnt exist', err)
    return
  }
}

export default Socket
export { initSocket }
