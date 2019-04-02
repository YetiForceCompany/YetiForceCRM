/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
const through = require('through2')
const terser = require('terser')
const extend = require('extend')
const path = require('path')

function minify(file, contents, options) {
  let opts = extend(true, {}, options)
  let source = contents
  let generateSourceMap = true
  if (typeof options.sourceMap !== 'undefined' && options.sourceMap === false) {
    generateSourceMap = false
  }
  const dirname = path.dirname(file.path)
  let fileName = file.path
  if (dirname.length > 1) {
    fileName = file.path.substr(dirname.length + 1)
  }
  if (generateSourceMap && typeof file.sourceMap !== 'undefined') {
    if (file.sourceMap.mappings.length) {
      opts = extend(true, opts, { sourceMap: { content: JSON.stringify(file.sourceMap) } })
    } else {
      opts = extend(true, opts, { sourceMap: { filename: file.sourceMap.file } })
      source = { [fileName]: contents }
    }
  } else if (generateSourceMap) {
    opts = extend(true, opts, { sourceMap: { filename: fileName } })
    source = { [fileName]: contents }
  }
  const mini = terser.minify(source, opts)
  if ('error' in mini) {
    throw new Error(mini.error.message)
  }
  if (generateSourceMap && typeof mini.map === 'string') {
    const map = JSON.parse(mini.map)
    map.sources[0] = fileName
    map.file = file.path
    file.sourceMap = map
  }
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
    return callback(null, minify(file.clone(), contents, options))
  })
}
