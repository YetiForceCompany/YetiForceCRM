/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import ApiService from './Api.js'
import WebSocket from './WebSocket.js'
import { SocketEmitter } from './WebSocket.js'

// import { v1 } from '/node_modules/uuid/index.js'

const AppConnector = {
  /**
   * Ajax request with axios
   *
   * @param   {Object}  params  ajax params
   *
   * @return  {Promise}          axios promise
   */
  request(params) {
    return ApiService(params)
  },

  /**
   * Websocket analogy to ajax request
   *
   * @param   {Object}  params  ajax params
   *
   * @return  {Promise}          axios promise
   */
  webSocketPromise(params) {
    let messageID = Math.random() * 1
    console.log(messageID)

    return new Promise(function(resolve, reject) {
      WebSocket.then(function(connection) {
        SocketEmitter.send(JSON.stringify({ id: messageID, params: params }))
        SocketEmitter.$on('message', message => {
          try {
            const data = JSON.parse(message.data)
            console.log(data)
            if (data.id === messageID) {
              resolve(data)
            } else {
              reject(data)
            }
          } catch (e) {
            reject(e)
            return
          }
        })
      })
    })
  },

  /**
   * Create websocket event for specified action callback
   *
   * @param   {Object}  params  [params description]
   * @param   {Function}  cb      [cb description]
   */
  webSocket(params, cb) {
    WebSocket.then(connection => {
      SocketEmitter.send(JSON.stringify(params))
      console.log(SocketEmitter)
      SocketEmitter.$on('message', message => {
        const data = JSON.parse(message.data)
        console.log(data)
        if (data.action === params.action) {
          cb(data)
        }
      })
    })
  }
}
export default AppConnector
