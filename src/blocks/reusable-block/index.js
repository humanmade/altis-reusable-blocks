import { __ } from '@wordpress/i18n';

import edit from './containers/Edit';

export const name = 'erb/reusable-block';

export const options = {
	category: 'common',
	description: __( 'Create content, and save it for you and other contributors to reuse across your site.', 'enhanced-reusable-blocks' ),
	edit,
	icon: 'controls-repeat',
	save: () => null,
	title: __( 'Reusable Block', 'enhanced-reusable-blocks' ),
	supports: {
		customClassName: false,
		html: false,
		reusable: false,
	},
};
