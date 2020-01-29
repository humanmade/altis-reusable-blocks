/**
 * This file defines the production build configuration
*/
const { helpers, externals, presets, plugins } = require( '@humanmade/webpack-helpers' );
const { filePath } = helpers;

/**
 * Define a shared constant seed value to allow the manifest plugin to append instead of overwrite.
 *
 * This is defined as an object here because a manifest seed has to be a
 * shared object rather than another copy of the same object.
 *
 * See: https://github.com/danethurber/webpack-manifest-plugin#optionsseed
 */
let seed = {};

/**
 * Build the manifest file.
 *
 * This is flipped from the default manifest, in that it should include each of
 * the entry points in the prod manifest, mapped to the appropriate bundle on
 * the dev server.
 *
 * @param {} seed `entry` from above, used as a seed to start the manifest.
 * @param FileDescriptor[] Files processed in the dev entry configuration.
 * @return {} Map of entries to the URL for the output bundle they belong to.
 */
const generate = ( seed, files ) => {
	return files.reduce( ( manifest, file ) => {
		const { name, path, isAsset } = file;

		// Skip imported asset files, like fonts and images. This manifest is
		// only for scripts and stylesheet files that will be enqueued through
		// the Asset Loader.
		if ( ! isAsset ) {
			manifest[ name.replace( '/dist/', '/src/' ) ] = path;
		}

		return manifest;
	}, seed );
}

module.exports = presets.production( {
	externals,
	entry: {
		index: filePath( 'src/index.js' ),
	},
	plugins: [
		plugins.manifest( {
			seed,
			generate,
			path: filePath( 'build' ),
			fileName: 'asset-manifest.json',
			writeToFileEmit: true,
		} ),
	]
} );
