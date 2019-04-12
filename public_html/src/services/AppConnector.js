/**
 * App connector
 *
 * @description application connector
 * @license YetiForce Public License 3.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */

'use strict'

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
  _handleSocketResponse(resolve, reject, requestId, data) {
    try {
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
