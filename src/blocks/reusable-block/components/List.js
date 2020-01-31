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
	 * Get match counts for the keyword within the block title and content.
	 *
	 * @param {String} keyword - Search keyword.
	 * @param {Object} block - Block data object.
	 *
	 * @return {Array} Title match count, Content match count.
	 */
	getMatchCounts = ( keyword, block ) => {
		const regex = new RegExp( keyword, 'ig' );

		const titleMatches = block.title.match( regex ) || [];
		const contentMatches = block.content.match( regex ) || [];

		return [ titleMatches.length, contentMatches.length ];
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
			searchID,
			searchKeywords,
		} = this.props;

		if ( searchKeywords && ! searchID ) {
			filteredBlocksList.sort( ( blockX, blockY ) => {
				let blockXCount = 0;
				let blockYCount = 0;

				/**
				 * If keywords length is greater than 2, test for exact matches for the entire
				 * search term within the title and content and weigh those more heavily.
				 */
				if ( searchKeywords.length > 2 ) {
					const [
						titleXMatches,
						contentXMatches,
					] = this.getMatchCounts( searchKeywords.join( ' ' ), blockX );

					const [
						titleYMatches,
						contentYMatches,
					] = this.getMatchCounts( searchKeywords.join( ' ' ), blockY );

					const titleXScore = titleXMatches * TITLE_EXACT_MATCH_WEIGHT;
					const contentXScore = contentXMatches * CONTENT_EXACT_MATCH_WEIGHT;

					const titleYScore = titleYMatches * TITLE_EXACT_MATCH_WEIGHT;
					const contentYScore = contentYMatches * CONTENT_EXACT_MATCH_WEIGHT;

					blockXCount += titleXScore + contentXScore;
					blockYCount += titleYScore + contentYScore;
				}

				// Loop through each string in searchKeywords, test for matches, and weigh those normally.
				searchKeywords.forEach( ( keyword ) => {
					const [
						titleXMatches,
						contentXMatches,
					] = this.getMatchCounts( keyword, blockX );

					const [
						titleYMatches,
						contentYMatches,
					] = this.getMatchCounts( keyword, blockY );

					const titleXScore = titleXMatches * TITLE_WEIGHT;
					const contentXScore = contentXMatches * CONTENT_WEIGHT;

					const titleYScore = titleYMatches * TITLE_WEIGHT;
					const contentYScore = contentYMatches * CONTENT_WEIGHT;

					blockXCount += titleXScore + contentXScore;
					blockYCount += titleYScore + contentYScore;
				} );

				// Same weight, so sort by ID.
				if ( blockXCount === blockYCount ) {
					return blockX.id > blockY.id ? -1 : 1;
				}

				return blockXCount > blockYCount ? -1 : 1;
			} );
		}

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
					isFetching
						? ( <Placeholder><Spinner /></Placeholder> )
						: (
							<ul className="block-editor-block-types-list reusable-block-types-list">
								{
									sortedBlocks.map( ( block ) => (
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
	searchID: PropTypes.number,
	searchKeywords: PropTypes.array.isRequired,
};

export default List;
