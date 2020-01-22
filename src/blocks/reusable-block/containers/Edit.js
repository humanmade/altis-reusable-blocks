import { compose } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';

import ReusableBlockEdit from '../components/Edit';

export const mapSelectToProps = ( select ) => {
	const {
		getEntityRecords,
		getTaxonomy,
	} = select( 'core' );
	const { getEditedPostAttribute } = select( 'core/editor' );

	const taxonomy = getTaxonomy( 'category' );

	const categories = taxonomy ? getEditedPostAttribute( taxonomy.rest_base ) : [];
	const [ postCategory ] = categories || [];

	const categoriesList = getEntityRecords( 'taxonomy', 'category', { per_page: 100 } );

	return {
		postCategory,
		categoriesList,
	};
};

export default compose( [
	withSelect( mapSelectToProps ),
] )( ReusableBlockEdit );
