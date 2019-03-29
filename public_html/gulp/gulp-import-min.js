/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
const through = require('through2')

const defaultConfig = {
  additionalRegs: [],
  extension: 'min.js'
}

function replace(contents, file, config) {
  let result = contents
    .replace(/(import\s.+\s(['"`]){1}(?!\/?node_modules)\.?\.?[^\.]+\.)js['"`]/gim, `$1${config.extension}$2`)
    .replace(/import\(['"`]?(?!.*\/?node_modules)(.+)(?<!\.vue)\.js(['"`]?)\)/gim, `import($2$1.${config.extension}$2)`)
  if (config.additionalRegs.length) {
    config.additionalRegs.forEach(reg => {
      result = result.replace(reg.regexp, reg.replace)
    })
  }
  file.contents = new Buffer.from(result)
  return file
}

module.exports = function(config) {
  config = { ...config, ...defaultConfig }
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
    return callback(null, replace(contents, file.clone(), config))
  })
}
