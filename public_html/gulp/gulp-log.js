/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
const through = require('through2')

const defaultOptions = {
  prefix: 'Log',
  showContent: false
}

module.exports = function(options) {
  options = Object.assign({}, defaultOptions, options)
  return through.obj(function(file, encoding, callback) {
    if (file.isNull()) {
      return callback(null, file)
    }
    let contents
    if (file.isStream()) {
      this.emit('error', new PluginError(PLUGIN_NAME, 'Streams not supported!'))
    } else if (file.isBuffer()) {
      contents = file.contents.toString('utf8')
    }
    console.log(options.prefix, file.path)
    if (options.showContent) {
      console.log(contents)
    }
    return callback(null, file)
  })
}
