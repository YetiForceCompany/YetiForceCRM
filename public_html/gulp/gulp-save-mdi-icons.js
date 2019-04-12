/**
 * Gulp save mdi icons
 *
 * @description get icon names from @mdi scss file
 * @license YetiForce Public License 3.0
 * @author Rafal Pospiech <r.pospiech@yetiforce.com>
 */

const through = require('through2')

function getVariables(contents, file) {
  const variables = eval('module.exports=' + contents)
  const json = {}
  for (let name in variables['mdi-icons']) {
    name = name.replace(/"/g, '')
    json[name] = { name, className: 'mdi-' + name, keywords: name.split('-') }
  }
  file.contents = new Buffer.from('export default ' + JSON.stringify(json))
  return file
}

module.exports = function() {
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

    return callback(null, getVariables(contents, file.clone()))
  })
}
