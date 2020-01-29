import { compose } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';

import ReusableBlockEdit from '../components/Edit';

export const mapSelectToProps = ( select ) => {
	const { getEntityRecords } = select( 'core' );

	const categoriesList = getEntityRecords( 'taxonomy', 'wp_block_category', { per_page: 100 } );

	return {
		categoriesList,
	};
};

export default compose( [
	withSelect( mapSelectToProps ),
] )( ReusableBlockEdit );
