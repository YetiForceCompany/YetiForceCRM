/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import axios from 'axios'

const LoginService = axios.create({
  baseURL: 'http://yeti2/', //dev path
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
    'access-control-allow-origin': '*',
    'Access-Control-Allow-Headers': '*'
  }
})

export default LoginService
