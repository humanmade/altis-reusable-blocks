import _zipObject from 'lodash/zipObject';

import apiFetch from '@wordpress/api-fetch';

/**
 * Fetch JSON.
 *
 * Helper function to return parsed JSON and also the response headers.
 *
 * @param {object} args
 * @param {array} headerKeys Array of headers to include.
 */
export const fetchJson = ( args, headerKeys = [ 'x-wp-totalpages' ] ) => {
	return new Promise( resolve => {
		apiFetch( {
			...args,
			parse: false,
		} ).then( response => {
			return Promise.all( [
				response.json ? response.json() : [],
				_zipObject( headerKeys, headerKeys.map( key => response.headers.get( key ) ) ),
			] )
		} ).then( data => (
			resolve( data )
		) ).catch( ( e ) => {
			console.error( e );
		} );
	} )
}
