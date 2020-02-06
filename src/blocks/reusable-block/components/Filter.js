import PropTypes from 'prop-types';

import {
	SelectControl,
	TextControl,
} from '@wordpress/components';
import { decodeEntities } from '@wordpress/html-entities';
import { __ } from '@wordpress/i18n';

const Filter = ( {
	categoriesList,
	searchCategory,
	searchID,
	searchKeyword,
	updateSearchCategory,
	updateSearchKeyword,
} ) => {
	const categoriesListOptions = categoriesList
		? categoriesList.map( ( { id, name } ) => ( {
			label: decodeEntities( name ),
			value: id,
		} ) )
		: [];

	if ( categoriesListOptions.length ) {
		categoriesListOptions.unshift( { label: __( '-- Select Category --', 'altis' ), value: '' } );
	}

	return (
		<div className="block-editor-reusable-blocks-inserter__filter">
			<TextControl
				className="block-editor-reusable-blocks-inserter__filter-search-keyword"
				label={ __( 'Search: ', 'enhanced-reusable-blocks' ) }
				onChange={ updateSearchKeyword }
				placeholder={ __( 'Search by keywords, post ID, or block ID...', 'enhanced-reusable-blocks' ) }
				value={ searchKeyword }
			/>
			<SelectControl
				className="block-editor-reusable-blocks-inserter__filter-search-category"
				disabled={ searchID }
				label={ __( 'Category: ', 'enhanced-reusable-blocks' ) }
				onChange={ updateSearchCategory }
				options={ categoriesListOptions }
				value={ searchCategory }
			/>
		</div>
	);
};

Filter.propTypes = {
	categoriesList: PropTypes.array.isRequired,
	searchCategory: PropTypes.number,
	searchID: PropTypes.number,
	searchKeyword: PropTypes.string,
	updateSearchCategory: PropTypes.func.isRequired,
	updateSearchKeyword: PropTypes.func.isRequired,
};

export default Filter;
