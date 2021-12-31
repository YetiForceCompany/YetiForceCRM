const rollup = require('rollup'),
	{ babel } = require('@rollup/plugin-babel'),
	finder = require('findit')('../'),
	path = require('path'),
	sourcemaps = require('rollup-plugin-sourcemaps'),
	{ done } = require('@vue/cli-shared-utils')

const dirModules = __dirname + '/node_modules/'
let filesToMin = []
async function build(fileName) {
	const inputOptions = {
			input: fileName,
			treeshake: false,
			plugins: [
				babel({
					babelrc: false,
					babelHelpers: 'inline',
					presets: [
						[
							`${dirModules}@babel/preset-env`,
							{
								modules: false
							}
						],
						[
							`${dirModules}babel-preset-minify`,
							{
								typeConstructors: false,
								mangle: false,
								builtIns: false
							}
						]
					],
					plugins: [
						`${dirModules}@babel/plugin-proposal-class-properties`,
						`${dirModules}@babel/plugin-proposal-object-rest-spread`,
						`${dirModules}@babel/plugin-transform-classes`
					]
				}),
				sourcemaps()
			]
		},
		outputOptions = {
			sourcemap: true,
			file: fileName.replace('.js', '.min.js'),
			format: 'cjs'
		}
	// create a bundle
	const bundle = await rollup.rollup(inputOptions)
	// generate code and a sourcemap
	await bundle.generate(outputOptions)
	// or write the bundle to disk
	await bundle.write(outputOptions)
}

finder.on('directory', (dir, stat, stop) => {
	const base = path.basename(dir)
	if (base === 'node_modules' || base === 'libraries' || base === 'vendor' || base === '_private' || base === 'src') stop()
})

finder.on('file', (file, stat) => {
	const re = new RegExp('(?<!\\.min)\\.js$')
	if (file.includes('roundcube') && !(!file.includes('skins') && file.includes('yetiforce'))) return
	if (file.endsWith('vue.js')) return
	if (file.match(re)) filesToMin.push(file)
})

finder.on('end', () => {
	filesToMin.forEach(file => {
		console.log('Building... ' + file)
		build(file)
			.then(_ => {
				done(file)
			})
			.catch(err => {
				console.log(err)
			})
	})
})
