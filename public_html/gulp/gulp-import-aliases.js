/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
const through = require('through2')

const defaultOptions = {
  map: null
}

function replaceStatic(contents, options) {
  const opts = { ...defaultOptions, ...options }
  let result = contents
  for (let alias in opts.map) {
    const regString = `(import\\s?[^'"\`]+\\s?from\\s?)(['"\`]{1})(\\/?${alias})([^'"\`]+)(['"\`]{1})`
    const reg = new RegExp(regString, 'gim')
    result = result.replace(reg, `$1$2${opts.map[alias]}$4$5`)
  }
  return result
}

function replace(contents, file, options) {
  let result = contents
  result = replaceStatic(result, options)
  file.contents = new Buffer.from(result)
  return file
}

module.exports = function(options) {
  return through.obj(function(file, encoding, callback) {
    if (options.map === null) {
      return callback(null, file)
    }
    if (file.isNull()) {
      return callback(null, file)
    }
    let contents
    if (file.isStream()) {
      this.emit('error', new PluginError(PLUGIN_NAME, 'Streams not supported!'))
    } else if (file.isBuffer()) {
      contents = file.contents.toString('utf8')
    }
    return callback(null, replace(contents, file.clone(), options))
  })
}
