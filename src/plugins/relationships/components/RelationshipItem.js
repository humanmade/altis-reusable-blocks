import PropTypes from 'prop-types';

import { PanelRow } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import { getEditPostUrl } from '../../../utils/post';

const statusMap = {
	draft: __( '(Draft)', 'newspress' ),
	pending: __( '(Pending)', 'newspress' ),
};

const RelationshipItem = ( { id, status, title } ) => (
	<PanelRow>
		<a target="_blank" rel="noopener noreferrer" href={ getEditPostUrl( id ) }>
			{ `#${ id } - ${ ( title.rendered || __( '(No Title)', 'altis-reusable-blocks' ) ) }` }
		</a>
		{ statusMap[ status ] || null }
	</PanelRow>
);

RelationshipItem.propTypes = {
	id: PropTypes.number.isRequired,
	status: PropTypes.string.isRequired,
	title: PropTypes.shape( {
		rendered: PropTypes.string,
	} ),
};

RelationshipItem.defaultProps = {
	title: {},
};

export default RelationshipItem;
