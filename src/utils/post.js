import { sprintf } from '@wordpress/i18n';

import settings from '../settings';

const { editPostUrl } = settings;

/**
 * Return the Edit URL for the post with the given ID.
 *
 * @param {number} postId - Post ID.
 *
 * @return {string} URL.
 */
export function getEditPostUrl( postId ) {
	return sprintf( editPostUrl, postId );
}
