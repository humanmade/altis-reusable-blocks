/**
 * This file defines the development build configuration
 */
const { helpers, externals, presets } = require( '@humanmade/webpack-helpers' );
const { choosePort, filePath } = helpers;

module.exports = choosePort( 8080 ).then( port =>
	presets.development( {
		name: 'main',
		devServer: {
			port,
		},
		externals,
		entry: {
			index: filePath( 'src/index.js' ),
		},
	} ),
);
