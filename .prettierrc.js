module.exports = {
	eslintIntegration: true,
	singleQuote: true,
	semi: true,
	overrides: [
		{
			files: '*.scss',
			options: {
				singleQuote: false
			}
		}
	]
}
