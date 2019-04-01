/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
const through = require('through2')
const terser = require('terser')
const extend = require('extend')

function minify(file, contents, options) {
  let opts = options
  const sourceMapFile = file.sourceMap.file
  opts = extend(true, opts, { sourceMap: { content: file.sourceMap } })
  const mini = terser.minify(contents, opts)
  file.sourceMap = JSON.parse(mini.map)
  file.sourceMap.file = sourceMapFile
  file.contents = new Buffer.from(mini.code)
  return file
}

module.exports = function(options = {}) {
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
    return callback(null, minify(file, contents, options))
  })
}
