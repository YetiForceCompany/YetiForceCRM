/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import ApiService from './Api.js'
import WebSocket from './WebSocket.js'
// import { v1 } from '/node_modules/uuid/index.js'

const AppConnector = {
  request(params) {
    return ApiService(params)
  },
  webSocket(params, cb) {
    WebSocket.then(connection => {
      connection.send(JSON.stringify(params))
      connection.onmessage = function(message) {
        const json = JSON.parse(message.data)
        if (json.action === params.action) {
          cb(json)
        }
      }
    })
  },
  webSocketPromise(params) {
    let messageID = Math.random() * 1
    return new Promise(function(resolve, reject) {
      WebSocket.then(function(connection) {
        connection.send(JSON.stringify({ id: messageID, params: params }))
        connection.onmessage = function(message) {
          try {
            const json = JSON.parse(message.data)
            if (json.id === messageID) {
              resolve(json)
            } else {
              reject(json)
            }
          } catch (e) {
            reject(e)
            return
          }
        }
      })
    })
  }
}
export default AppConnector
