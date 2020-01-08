import PropTypes from 'prop-types';

import {
	Placeholder,
	Spinner,
} from '@wordpress/components';
import { Component } from '@wordpress/element';

import ListItem from './ListItem';

// Weighting variables to control sort results for matches on each individual search term.
const TITLE_WEIGHT = 2;
const CONTENT_WEIGHT = 0.5;

// Weighting variables to control sort results for exact matches on the entire search term.
const TITLE_EXACT_MATCH_WEIGHT = 5;
const CONTENT_EXACT_MATCH_WEIGHT = 2.5;

class List extends Component {
	state = {
		sortedBlocks: [],
	};

	componentDidUpdate( prevProps ) {
		if ( this.props.filteredBlocksList !== prevProps.filteredBlocksList ) {
			this.sortBlocks();
		}
	}

	/**
	 * Get match results of the keyword within the block title and content.
	 */
	getMatches = ( keyword, block ) => {
		const regex = new RegExp( keyword, 'ig' );

		const titleMatches = block.title.match( regex ) || [];
		const contentMatches = block.content.match( regex ) || [];

		return [ titleMatches, contentMatches ];
	};

	/**
	 * Sort the blocks with a custom weighting.
	 *
	 * Find the number of occurrences for each search term in both
	 * the title and content, and use a custom weight to sort them.
	 */
	sortBlocks = () => {
		const {
			filteredBlocksList,
			searchKeywords,
		} = this.props;

		if ( ! searchKeywords ) {
			return;
		}

		filteredBlocksList.sort( ( blockX, blockY ) => {

			let blockXCount = 0;
			let blockYCount = 0;

			/**
			 * If keywords length is greater than 2, test for exact matches for the entire
			 * search term within the title and content and weigh those more heavily.
			 */
			if ( searchKeywords.length > 2 ) {
				const [ titleXMatches, contentXMatches ] = this.getMatches( searchKeywords.join(' '), blockX );
				const [ titleYMatches, contentYMatches ] = this.getMatches( searchKeywords.join(' '), blockY );

				blockXCount += titleXMatches.length * TITLE_EXACT_MATCH_WEIGHT + contentXMatches.length * CONTENT_EXACT_MATCH_WEIGHT;
				blockYCount += titleYMatches.length * TITLE_EXACT_MATCH_WEIGHT + contentYMatches.length * CONTENT_EXACT_MATCH_WEIGHT;
			}

			// Loop through each string in searchKeywords, test for matches, and weigh those normally.
			searchKeywords.forEach( keyword => {
				const [ titleXMatches, contentXMatches ] = this.getMatches( keyword, blockX );
				const [ titleYMatches, contentYMatches ] = this.getMatches( keyword, blockY );

				blockXCount += titleXMatches.length * TITLE_WEIGHT + contentXMatches.length * CONTENT_WEIGHT;
				blockYCount += titleYMatches.length * TITLE_WEIGHT + contentYMatches.length * CONTENT_WEIGHT;
			} );

			// Same weight, so sort by ID.
			if ( blockXCount === blockYCount ) {
				return blockX.id > blockY.id ? -1 : 1;
			}

			return blockXCount > blockYCount ? -1 : 1;
		} );

		this.setState( { sortedBlocks: filteredBlocksList } );
	};

	render() {
		const {
			isFetching,
			onHover,
			onItemSelect,
		} = this.props;

		const {
			sortedBlocks,
		} = this.state;

		return (
			<div className="block-editor-reusable-blocks-inserter__list">
				{
					( ! sortedBlocks.length && isFetching) || isFetching ?
					(
						<Placeholder><Spinner /></Placeholder>
					) :
					(
						<ul role="list" className="block-editor-block-types-list reusable-block-types-list">
							{
								sortedBlocks.map(block => (
									<ListItem
										key={ block.id }
										onClick={ () => onItemSelect( block.id ) }
										onHover={ onHover }
										{ ...block }
									/>
								) )
							}
						</ul>
					)
				}
			</div>
		);
	}
}

List.propTypes = {
	filteredBlocksList: PropTypes.array.isRequired,
	isFetching: PropTypes.bool.isRequired,
	onItemSelect: PropTypes.func.isRequired,
	onHover: PropTypes.func.isRequired,
	searchKeywords: PropTypes.array.isRequired,
};

export default List;
