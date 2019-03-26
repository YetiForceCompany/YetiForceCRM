const express = require('express')
const exec = require('child_process').exec
const axios = require('axios')
const path = require('path')
const app = express()
const port = 8080

let baseURL = ''
process.chdir('../')
console.log(`CWD ${process.cwd()}`)

var staticOptions = {
  dotfiles: 'ignore',
  etag: false,
  extensions: ['htm', 'html'],
  index: false,
  maxAge: '1d',
  redirect: false,
  setHeaders: function(res, path, stat) {
    res.set('x-timestamp', Date.now())
  }
}

app.use(express.static('public_html', staticOptions))

// get configuration by running index.php from command line
// which will in return get dev server template with configuration from php
app.all('/', (req, res) => {
  exec('php ' + path.join(__dirname, '..', 'dev.php'), function(error, stdout, stderr) {
    res.append('access-control-allow-origin', '*')
    res.append('access-control-allow-headers', '*')
    res.append('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, DELETE, OPTIONS')
    if (stderr) {
      console.error('PHP Server error', stderr)
      return res.end(stderr)
    }
    const matches = /data\-env\-url\=\"([^\"]+)\"/gi.exec(stdout)
    if (matches && matches.length > 1) {
      baseURL = matches[1]
    } else {
      console.error('No baseURL inside template', stdout)
    }
    res.end(stdout)
  })
})

// catch login and api.php request and forward it to php server
app.all('/login.php', (req, res) => {
  axios
    .post(baseURL + '/login.php', req.body)
    .then(function(response) {
      for (let headerName in response.headers) {
        res.append(headerName, response.headers[headerName])
      }
      if (response.status === 200) {
        res.json(response.data)
      } else {
        res.status(response.status).end(response.statusText)
      }
    })
    .catch(function(error) {
      res.end(error)
    })
})

app.all('/api.php', (req, res) => {
  axios
    .post(baseURL + '/api.php', req.body)
    .then(function(response) {
      for (let headerName in response.headers) {
        res.append(headerName, response.headers[headerName])
      }
      if (response.status === 200) {
        res.json(response.data)
      } else {
        res.status(response.status).end(response.statusText)
      }
    })
    .catch(function(error) {
      res.end(error)
    })
})

app.listen(port, () => console.log(`Dev server listening on port ${port}!`))
