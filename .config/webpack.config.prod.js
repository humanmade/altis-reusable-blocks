/**
 * This file defines the production build configuration
*/
const { helpers, externals, presets } = require( '@humanmade/webpack-helpers' );
const { filePath } = helpers;

module.exports = presets.production( {
	externals,
	entry: {
		index: filePath( 'src/index.js' ),
	},
} );
