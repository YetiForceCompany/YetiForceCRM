/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import ApiService from './Api.js'
import WebSocket from './WebSocket.js'
const AppConnector = {
  request(params) {
    return ApiService(params)
  },
  webSocket(params) {
    WebSocket.then(connection => {
      connection.send(JSON.stringify(params))
      return connection
    })
  },
  webSocketPromise(params) {
    let messageID = Math.random() * 1
    return new Promise(function(resolve, reject) {
      WebSocket.then(function(connection) {
        console.log('send')
        connection.send(JSON.stringify({ id: messageID, text: 'asdfasdf0' + messageID }))
        connection.onmessage = function(message) {
          try {
            const json = JSON.parse(message.data)
            console.log(json)
            console.log(messageID)
            if (json.id === messageID) {
              console.log('success')
              resolve('success')
              //TODO return promise with resolve here
            } else {
              reject('wrong id')
            }
          } catch (e) {
            console.log("This doesn't look like a valid JSON: ", message.data)
            reject(e)
            return
          }
        }
      })
    })
  }
}
export default AppConnector
