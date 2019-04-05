/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import ApiService from './Api.js'
import WebSocket from './WebSocket.js'
export default class AppConnector {
  constructor(type = 'request', params = { module: '', action: '', title: '', body: '' }) {
    this.type = type
    this.params = params
    this.module = params.module
    this.action = params.action
    this.title = params.title
    this.body = params.body
    if (type === 'request') {
      this._request(params)
    } else if (type === 'webSocket') {
      this._webSocket(params)
    }
  }
  _request() {
    return ApiService(this.params)
  }
  _webSocket() {
    let messageID = 1
    WebSocket.then(function(connection) {
      console.log(connection)
      connection.send('asdfasdf')

      connection.onerror = function(error) {
        console.log('error???')

        // an error occurred when sending/receiving data
      }
      connection.onclose = function(error) {
        console.log('close???')

        // an error occurred when sending/receiving data
      }

      connection.onmessage = function(message) {
        // try to decode json (I assume that each message
        // from server is json)
        try {
          const json = JSON.parse(message.data)
          console.log('Valid???')
          console.log(message)
        } catch (e) {
          console.log("This doesn't look like a valid JSON: ", message.data)
          return
        }
        // handle incoming message
      }
    })
  }
}
