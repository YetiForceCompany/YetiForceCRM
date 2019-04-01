/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
const through = require('through2')
const vueCompiler = require('@vue/component-compiler')
const extend = require('extend')

const defaultOptions = {
  extension: '.js',
  compiler: {
    style: { trim: true, postcssModulesOptions: { generateScopedName: '[path][name]__[local]' } },
    template: {
      isProduction: true,
      compilerOptions: {
        //outputSourceRange: true
      }
    }
  }
}

function compile(contents, file, options) {
  const opts = extend(true, JSON.parse(JSON.stringify(defaultOptions)), options)
  const compiler = vueCompiler.createDefaultCompiler(opts.compiler)
  const descriptor = compiler.compileToDescriptor(file.path, contents)
  const assemble = vueCompiler.assemble(compiler, file.path, descriptor)
  file.contents = new Buffer.from(assemble.code)
  file.path = file.path.substr(0, file.path.lastIndexOf('.')) + opts.extension
  file.sourceMap = { ...file.sourceMap, ...assemble.map }
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
