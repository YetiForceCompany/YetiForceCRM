/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

const rollup = require('rollup'),
	babel = require('rollup-plugin-babel'),
	finder = require('findit')('C:/www/YetiForceCRM/public_html/src/modules'),
	path = require('path'),
	sourcemaps = require('rollup-plugin-sourcemaps'),
	vue = require('rollup-plugin-vue'),
	commonjs = require('rollup-plugin-commonjs'),
	resolve = require('rollup-plugin-node-resolve'),
	globals = require('rollup-plugin-node-globals'),
	alias = require('rollup-plugin-alias');

let filesToMin = [];
// const absoluteNodeModulesPath = 'C:/www/YetiForceCRM/dev_tools/node_modules/';

async function build(filePath) {
	let directiories = filePath.split('\\');
	console.log(directiories);
	const fileName = directiories.pop();
	const moduleName = directiories.pop();

	const outputFile = `../layouts/basic/modules/${moduleName}/${fileName}`;
	//rollup input and output options
	const inputOptions = {
			input: filePath,
			plugins: [
				alias({
					vue: path.resolve('./node_modules/vue/dist/vue.js'),
					quasar: path.resolve('./node_modules/quasar/dist/quasar.esm.js')
				}),
				resolve(),
				commonjs(),
				vue({ compileTemplate: true }),
				babel({
					babelrc: false,
					presets: [
						[`babel-preset-env`, { modules: false }],
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
		},
		outputOptions = {
			sourcemap: true,
			file: outputFile,
			format: 'cjs'
		};
	// create a bundle
	const bundle = await rollup.rollup(inputOptions);
	// generate code and a sourcemap
	const { code, map } = await bundle.generate(outputOptions);
	// or write the bundle to disk
	await bundle.write(outputOptions);
}
// build();
finder.on('directory', (dir, stat, stop) => {
	const base = path.basename(dir);
	if (base === 'node_modules' || base === 'libraries' || base === 'vendor' || base === '_private') stop();
});

finder.on('file', (file, stat) => {
	const re = new RegExp('(?<!\\.min)\\.js$');
	if (file.includes('roundcube') && !(!file.includes('skins') && file.includes('yetiforce'))) return;
	if (file.match(re)) filesToMin.push(file);
});

finder.on('end', () => {
	filesToMin.forEach(file => {
		//log files to minify
		console.log(file);
		build(file);
	});
});
