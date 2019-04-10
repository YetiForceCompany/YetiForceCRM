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
  http(params) {
    return ApiService(params)
  },
  /**
   * Websocket analogy to ajax request
   *
   * @param   {Object}  params  ajax params
   *
   * @return  {Promise}          axios promise
   */
  socket(params) {
    let requestId = Math.random() * 1
    return new Promise((resolve, reject) => {
      Socket.send(JSON.stringify({ id: requestId, params: params }))
      Socket.$on('message', this._handleSocketResponse.bind(this, resolve, reject, requestId))
    })
  },
  /**
   * Handle socket response
   *
   * @param   {Function}  resolve    promise native code
   * @param   {Function}  reject     promise native code
   * @param   {Number}    requestId  request id
   * @param   {Object}    message    socket message
   *
   * @return  {[type]}             [return description]
   */
  _handleSocketResponse(resolve, reject, requestId, message) {
    try {
      const data = JSON.parse(message.data)
      if (data.id === requestId) {
        resolve(data)
        Socket.$off('message', this._handleSocket)
      } else {
        reject(data)
      }
    } catch (e) {
      reject(e)
      return
    }
  }
}
export default AppConnector
