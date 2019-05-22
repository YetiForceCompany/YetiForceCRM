/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

const rollup = require('rollup'),
	babel = require('rollup-plugin-babel'),
	finder = require('findit')('modules'),
	path = require('path'),
	sourcemaps = require('rollup-plugin-sourcemaps'),
	vue = require('rollup-plugin-vue'),
	commonjs = require('rollup-plugin-commonjs'),
	resolve = require('rollup-plugin-node-resolve'),
	globals = require('rollup-plugin-node-globals'),
	alias = require('rollup-plugin-alias'),
	json = require('rollup-plugin-json')

let filesToMin = []
async function build(filePath) {
	let directiories = filePath.split('\\')
	const fileName = directiories.pop().replace('.js', '.vue.js')
	const moduleName = directiories.pop()
	const outputFile = `../layouts/basic/modules/${moduleName}/${fileName}`
	const inputOptions = {
		input: filePath,
		plugins: [
			json(),
			alias({
				vue: path.resolve('./node_modules/vue/dist/vue.js'),
				axios: path.resolve('./node_modules/axios/dist/axios.js')
			}),
			resolve(),
			commonjs(),
			vue({ compileTemplate: true }),
			babel({
				babelrc: false,
				presets: [
					[
						`babel-preset-minify`,
						{
							typeConstructors: false,
							mangle: false
						}
					]
				],
				plugins: [
					`babel-plugin-external-helpers`,
					`babel-plugin-transform-object-rest-spread`,
					`babel-plugin-transform-es2015-classes`
				]
			}),
			sourcemaps(),
			globals()
		]
	}
	const outputOptions = {
		sourcemap: true,
		file: outputFile,
		format: 'cjs'
	}
	const bundle = await rollup.rollup(inputOptions)
	const { code, map } = await bundle.generate(outputOptions)
	await bundle.write(outputOptions)
}

finder.on('directory', (dir, stat, stop) => {
	const base = path.basename(dir)
	if (base === 'node_modules' || base === 'libraries' || base === 'vendor' || base === '_private') stop()
})

finder.on('file', (file, stat) => {
	const re = new RegExp('(?<!\\.min)\\.js$')
	if (file.includes('roundcube') && !(!file.includes('skins') && file.includes('yetiforce'))) return
	if (file.match(re)) filesToMin.push(file)
})

finder.on('end', () => {
	filesToMin.forEach(file => {
		console.log(file)
		build(file)
	})
})
