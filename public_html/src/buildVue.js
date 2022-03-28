/**
 * build vue files in dev mode
 *
 * @license YetiForce Public License 5.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

const rollup = require('rollup'),
	finder = require('findit')('layouts'),
	alias = require('@rollup/plugin-alias'),
	path = require('path'),
	vue = require('rollup-plugin-vue'),
	sass = require('rollup-plugin-sass'),
	commonjs = require('@rollup/plugin-commonjs'),
	resolve = require('@rollup/plugin-node-resolve').nodeResolve,
	globals = require('rollup-plugin-node-globals'),
	json = require('@rollup/plugin-json'),
	buble = require('@rollup/plugin-buble'),
	{ terser } = require('rollup-plugin-terser'),
	{ done } = require('@vue/cli-shared-utils')

let filesToMin = []
const plugins = [
	alias({
		resolve: ['.vue', '.js', '.json'],
		entries: [
			{ find: '~', replacement: __dirname },
			{ find: 'store', replacement: `${__dirname}/store/index` },
			{ find: 'components', replacement: `${__dirname}/components` }
		]
	}),
	json(),
	sass(),
	vue({
		needMap: false,
		scss: {
			indentedSyntax: true
		}
	}),
	buble({
		transforms: {
			arrow: true,
			modules: false,
			dangerousForOf: true,
			spreadRest: false
		},
		objectAssign: 'Object.assign'
	}),
	resolve(),
	commonjs(),
	globals()
]

if (process.env.NODE_ENV === 'production') {
	plugins.push(terser())
}

async function build(filePath, isWatched = false) {
	const outputFile = `../${filePath.replace('.js', '.vue.js')}`
	const inputOptions = {
		input: filePath,
		external: 'vue',
		plugins
	}

	const outputOptions = {
		name: outputFile,
		file: outputFile,
		format: 'iife',
		globals: {
			vue: 'Vue'
		},
		sourcemap: true
	}

	if (process.env.NODE_ENV === 'development' && !isWatched) {
		const watcher = rollup.watch({
			...inputOptions,
			output: [outputOptions],
			watch: {
				exclude: 'node_modules/**'
			}
		})

		watcher.on('event', event => {
			if (event.code === 'START') {
				runBuild(filePath, true)
			}
		})
	} else {
		const bundle = await rollup.rollup(inputOptions)
		await bundle.generate(outputOptions)
		await bundle.write(outputOptions)
	}
}

function runBuild(file, isWatched = false) {
	console.log('Building... ' + file)
	build(file, isWatched)
		.then(e => {
			done(file)
		})
		.catch(err => {
			console.log(err)
		})
}

finder.on('directory', (dir, stat, stop) => {
	const base = path.basename(dir)
	if (base === 'node_modules' || base === 'store' || base === 'utils') stop()
})

finder.on('file', (file, stat) => {
	const re = new RegExp('(?<!\\.min)\\.js$')
	if (file.includes('roundcube') && !(!file.includes('skins') && file.includes('yetiforce'))) return
	if (file.match(re)) filesToMin.push(file)
})

finder.on('end', () => {
	filesToMin.forEach(file => {
		if (process.env.NODE_ENV === 'development') {
			build(file)
		} else {
			runBuild(file)
		}
	})
})
