import { compose } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';

import Relationships from '../components/Relationships';

export const mapSelectToProps = ( select ) => {
	const { getCurrentPostId } = select( 'core/editor' );

	return {
		currentPostId: getCurrentPostId(),
	};
};

export default compose( [
	withSelect( mapSelectToProps ),
] )( Relationships );
