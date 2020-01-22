import _zipObject from 'lodash/zipObject';

import apiFetch from '@wordpress/api-fetch';

/**
 * Fetch JSON.
 *
 * Helper function to return parsed JSON and also the response headers.
 *
 * @param {Object} args - Array of arguments to pass to apiFetch.
 * @param {Object[]} headerKeys - Array of headers to include.
 */
export const fetchJson = ( args, headerKeys = [ 'x-wp-totalpages' ] ) => {
	return apiFetch(
		{
			...args,
			parse: false,
		}
	).then( ( response ) => {
		return Promise.all( [
			response.json ? response.json() : [],
			_zipObject( headerKeys, headerKeys.map( key => response.headers.get( key ) ) ),
		] );
	} );
};
