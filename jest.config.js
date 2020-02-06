const preset = require( '@wordpress/jest-preset-default/jest-preset' );

module.exports = {
	...preset,
	setupFilesAfterEnv: [
		...preset.setupFilesAfterEnv,
		'<rootDir>/src/setupTests.js',
	],
};
