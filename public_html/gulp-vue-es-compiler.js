const through = require('through2')
const vueCompiler = require('@vue/component-compiler')

let compiler = vueCompiler.createDefaultCompiler()

const defaultOptions = {
  extension: '.js'
}

function compile(contents, file, options) {
  const opts = { ...defaultOptions, ...options }
  const descriptor = compiler.compileToDescriptor(file.path, contents)
  const assemble = vueCompiler.assemble(compiler, file.path, descriptor)
  file.contents = new Buffer.from(assemble.code.replace(/^\/\/\n/gim, ''))
  file.path = file.path.substr(0, file.path.lastIndexOf('.')) + opts.extension
  return file
}

module.exports = function(options) {
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
    return callback(null, compile(contents, file.clone(), options))
  })
}
