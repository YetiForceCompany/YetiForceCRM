/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import ApiService from './Api.js'
import Socket from './WebSocket.js'

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
    return new Promise(function(resolve, reject) {
      Socket.send(JSON.stringify({ id: messageID, params: params }))
      Socket.$on('message', message => {
        try {
          const data = JSON.parse(message.data)
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
  },

  /**
   * Create websocket event for specified action callback
   *
   * @param   {Object}  params  [params description]
   * @param   {Function}  cb      [cb description]
   */
  webSocket(params, cb) {
    Socket.send(JSON.stringify(params))
    Socket.$on('message', message => {
      const data = JSON.parse(message.data)
      if (data.action === params.action) {
        cb(data)
      }
    })
  }
}
export default AppConnector
