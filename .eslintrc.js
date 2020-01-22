/**
 * Even though this is a JavaScript file, the config data itself is formatted as JSON to allow for easier comparison,
 * as well as copy-and-paste from and to other .eslintrc JSON files.
 */

const isProduction = process.env.NODE_ENV === 'production';

module.exports = {
	"root": true,
	"extends": [
		"plugin:@wordpress/eslint-plugin/recommended"
	],
	"env": {
		"browser": true,
		"jquery": true
	},
	"plugins": [
		"jsdoc"
	],
	"rules": {
		"@wordpress/dependency-group": "off",
		"@wordpress/react-no-unsafe-timeout": "error",
		"arrow-parens": [ 2, "as-needed", { "requireForBlockBody": true } ],
		"jsdoc/check-param-names": "warn",
		"jsdoc/check-tag-names": "warn",
		"jsdoc/check-types": [
			"warn",
			{
				"noDefaults": true
			}
		],
		"jsdoc/newline-after-description": "warn",
		"jsdoc/no-undefined-types": "warn",
		"jsdoc/require-description-complete-sentence": "warn",
		"jsdoc/require-hyphen-before-param-description": "warn",
		"jsdoc/require-param": "warn",
		"jsdoc/require-param-description": "warn",
		"jsdoc/require-param-name": "warn",
		"jsdoc/require-param-type": "warn",
		"jsdoc/require-returns-type": "warn",
		"jsdoc/valid-types": "warn",
		"max-len": [
			"warn",
			120,
			{
				"ignoreTemplateLiterals": true,
				"ignoreStrings": true
			}
		],
		"no-console": isProduction ? "error" : "warn",
		"no-debugger": isProduction ? "error" : "warn",
		"no-plusplus": "off",
		"no-shadow": "off",
		"no-unused-vars": [
			"warn",
			{
				"vars": "all",
				"varsIgnorePattern": "_",
				"args": "after-used",
				"argsIgnorePattern": "_",
				"ignoreRestSiblings": true
			}
		],
		"operator-linebreak": [
			"error",
			"before",
			{
				"overrides": {
					"=": "none"
				}
			}
		],
		"react/prop-types": "warn",
		"valid-jsdoc": [
			"off",
			{}
		],

		// There is an issue with references not being detected when used as JSX component name (e.g., App in <App />).
		"@wordpress/no-unused-vars-before-return": "off"
	}
};
