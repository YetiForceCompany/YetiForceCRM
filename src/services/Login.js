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
