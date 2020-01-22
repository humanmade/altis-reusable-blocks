import PropTypes from 'prop-types';

import { PanelRow } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';

import settings from '../../../settings';

const { editPostUrl } = settings;

const RelationshipItem = ( { id, status, title } ) => {
	const itemTitle = title.rendered || __( '(No Title)', 'enhanced-reusable-blocks' );

	return (
		<PanelRow key={ id }>
			<a target="_blank" rel="noopener noreferrer" href={ sprintf( editPostUrl, id ) }>
				{ `#${ id } - ${ itemTitle }` }
			</a>
			{ status === 'draft' && __( '(Draft)', 'enhanced-reusable-blocks' ) }
			{ status === 'pending' && __( '(Pending)', 'enhanced-reusable-blocks' ) }
		</PanelRow>
	);
};

RelationshipItem.propTypes = {
	id: PropTypes.number.isRequired,
	status: PropTypes.string.isRequired,
	title: PropTypes.shape( {
		rendered: PropTypes.string.isRequired,
	} ),
};

export default RelationshipItem;
