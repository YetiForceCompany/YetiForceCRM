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
	],
	overrides: [
		{
			files: '*.json',
			options: {
				useTabs: false,
			}
		}
	],
	printWidth: 100,
	tabWidth: 2,
	useTabs: true,
	trailingComma: "none",
	bracketSpacing: true,
	jsxBracketSameLine: false,
	arrowParens: "always",
	proseWrap: "preserve"
}
