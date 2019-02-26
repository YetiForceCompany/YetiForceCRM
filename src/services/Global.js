import axios from 'axios'

const apiClient = axios.create({
  baseURL: 'http://yeti2/', //dev path,
  withCredentials: false,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json'
  },
  timeout: 10000
})
