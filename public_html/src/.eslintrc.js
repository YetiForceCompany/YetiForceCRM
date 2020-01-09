module.exports = {
	root: true,
	env: {
		browser: true
	},
	extends: ['plugin:vue/strongly-recommended', 'plugin:prettier/recommended', '@vue/prettier'],
	rules: {
		'no-console': process.env.NODE_ENV === 'production' ? 'error' : 'off',
		'no-debugger': process.env.NODE_ENV === 'production' ? 'error' : 'off'
	},
	parserOptions: {
		parser: 'babel-eslint'
	}
}
