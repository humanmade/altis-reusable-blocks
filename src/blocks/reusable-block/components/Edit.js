import _debounce from 'lodash/debounce';
import _deburr from 'lodash/deburr';
import _isEqual from 'lodash/isEqual';
import _uniqBy from 'lodash/uniqBy';
import PropTypes from 'prop-types';

import { BlockPreview } from '@wordpress/block-editor';
import { createBlock } from '@wordpress/blocks';
import { dispatch } from '@wordpress/data';
import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';

import List from './List';
import Filter from './Filter';

import { fetchJson } from '../../../utils/fetch';

class Edit extends Component {
	state = {
		blocksList: [],
		filteredBlocksList: [],
		hoveredId: null,
		inputText: '',
		isFetching: false,
		searchCategory: null,
		searchID: 0,
		searchKeyword: '',
	};

	constructor( props ) {
		super( props );

		// Debounce the fetchBlocks calls.
		this.fetchBlocks = _debounce( this.fetchBlocks, 1000 );
		this.abortController = new AbortController();
	}

	componentDidMount() {
		const {
			blocksList,
			isFetching,
		} = this.state;

		if ( ! blocksList.length && ! isFetching ) {
			this.fetchBlocks();
		}
	}

	componentDidUpdate( prevProps, prevState ) {
		if (
			this.state.searchCategory !== prevState.searchCategory
			|| this.state.searchKeyword !== prevState.searchKeyword
		) {
			this.fetchBlocks();
		}
	}

	componentWillUnmount() {
		this.abortController.abort();
	}

	fetchBlocks = () => {
		const {
			blocksList,
			searchID,
		} = this.state;

		if ( ! searchID ) {
			return this.fetchQueriedBlocks();
		}

		const filteredBlock = blocksList.find( ( block ) => block.id === searchID );

		// If block already exists in blocksList, just filter the list.
		if ( filteredBlock ) {
			this.setState( { filteredBlocksList: [ filteredBlock ] } );
		} else {
			this.fetchQueriedBlocksByID();
		}
	}

	/**
	 * Fetches either the reusable blocks within a Post by post ID or fetch a single block by block ID.
	 */
	fetchQueriedBlocksByID = async () => {
		const { searchID } = this.state;

		this.setState( { isFetching: true } );

		try {
			const [ data ] = await fetchJson(
				{
					path: addQueryArgs( '/erb/v1/search', { searchID } ),
					signal: this.abortController.signal,
				}
			);

			this.updateBlocksList( data );
		} catch ( e ) {
			/* eslint-disable no-console */
			console.error( __( 'Error retrieving blocks by post or block ID.', 'enhanced-reusable-blocks' ) );
			console.error( e );
			/* eslint-enable no-console */

			// Filter the block list with no blocks to match query.
			this.setState( { filteredBlocksList: [] } );
		}

		this.setState( { isFetching: false } );
	}

	/**
	 * Fetch the most recently created blocks within the currently selected category for the post that is being edited.
	 */
	fetchQueriedBlocks = async () => {
		const {
			searchCategory,
			searchKeyword,
		} = this.state;

		this.setState( { isFetching: true } );

		try {
			const queryArgs = { per_page: 100 };

			if ( searchKeyword ) {
				queryArgs.search = searchKeyword;
			}

			if ( searchCategory ) {
				queryArgs.wp_block_category = searchCategory;
			}

			const [ data ] = await fetchJson(
				{
					path: addQueryArgs( '/wp/v2/blocks', queryArgs ),
					signal: this.abortController.signal,
				}
			);

			this.updateBlocksList( data );
		} catch ( e ) {
			/* eslint-disable no-console */
			console.error( __( 'Error retrieving blocks.', 'enhanced-reusable-blocks' ) );
			console.error( e );
			/* eslint-enable no-console */

			// Filter the block list with no blocks to match query.
			this.setState( { filteredBlocksList: [] } );
		}

		this.setState( { isFetching: false } );
	};

	/**
	 * Normalize an array of blocks into the format we want them in.
	 *
	 * @param {Object[]} blocks - Array of blocks.
	 *
	 * @return {Object[]} Normalized blocks.
	 */
	normalizeBlocks = ( blocks ) => {
		return blocks.map( ( block ) => ( {
			id: block.id,
			title: block.title.raw,
			content: block.content.raw,
			categories: block.categories,
		} ) );
	};

	/**
	 * Update the Blocks List state object with a new list and normalize them.
	 *
	 * @param {Object[]} newBlocks - Array of new blocks fetched.
	 */
	updateBlocksList = ( newBlocks ) => {
		const { blocksList, searchID } = this.state;

		const normalizedNewBlocks = this.normalizeBlocks( newBlocks );

		const newBlocksList = _uniqBy( [ ...blocksList, ...normalizedNewBlocks ], 'id' );

		if ( ! _isEqual( newBlocksList, blocksList ) ) {
			this.setState( { blocksList: newBlocksList } );
		}

		if ( searchID ) {
			return this.setState( { filteredBlocksList: normalizedNewBlocks } );
		}

		this.filterBlocksList();
	};

	/**
	 * Replace the current block with the `core/block` once we get the ID of that block.
	 *
	 * @param {Number} ref - Reference ID for the reusable block.
	 */
	replaceWithCoreBlock = ( ref ) => {
		const { clientId } = this.props;
		const { replaceBlock } = dispatch( 'core/block-editor' );

		replaceBlock( clientId, createBlock( 'core/block', { ref } ) );
	};

	/**
	 * Converts the search keyword into a normalized keyword.
	 *
	 * @param {string} keyword - The search keyword to normalize.
	 *
	 * @return {Array} The normalized search keywords with each keyword as an item in the array.
	 */
	normalizeSearchKeywords = ( keyword ) => {
		// Disregard diacritics.
		//  Input: "média"
		keyword = _deburr( keyword );

		// Accommodate leading slash, matching autocomplete expectations.
		//  Input: "/media"
		keyword = keyword.replace( /^\//, '' );

		// Strip leading and trailing whitespace.
		//  Input: " media "
		keyword = keyword.trim();

		return keyword.split( ' ' );
	};

	/**
	 * Filter blocks list based on the selected category and search keyword.
	 */
	filterBlocksList = () => {
		const {
			blocksList,
			searchCategory,
			searchKeyword,
		} = this.state;

		if ( ! searchKeyword && ! searchCategory ) {
			return this.setState( { filteredBlocksList: blocksList } );
		}

		const filteredBlocksList = blocksList.filter( ( block ) => {
			if ( searchCategory && ! block.categories.includes( searchCategory ) ) {
				return false;
			}

			if ( searchKeyword ) {
				// Split the keywords by spaces and then check each word.
				const searchKeywords = this.normalizeSearchKeywords( searchKeyword );

				return searchKeywords.every( ( keyword ) => {
					// Check if keyword is excluded.
					const isExcludedKeyword = keyword.charAt( 0 ) === '-';

					// If it is excluded, remove the dash prefix.
					const regex = new RegExp( isExcludedKeyword ? keyword.slice( 1 ) : keyword, 'ig' );

					// Check that the post does not include the excluded keyword.
					if ( isExcludedKeyword ) {
						return ! regex.test( block.title ) && ! regex.test( block.content );
					}

					return regex.test( block.title ) || regex.test( block.content );
				} );
			}

			return true;
		} );

		this.setState( { filteredBlocksList } );
	};

	render() {
		const {
			filteredBlocksList,
			hoveredId,
			isFetching,
			searchCategory,
			searchKeyword,
			searchID,
		} = this.state;

		const { categoriesList } = this.props;

		return (
			<div className="block-editor-blocks-list">
				<div className="block-editor-reusable-blocks-inserter">
					<Filter
						categoriesList={ categoriesList || [] }
						searchCategory={ searchCategory }
						searchID={ searchID }
						searchKeyword={ searchKeyword }
						updateSearchCategory={ ( searchCategory ) => {
							searchCategory = searchCategory ? parseInt( searchCategory, 10 ) : null;
							this.setState( { searchCategory } );
						} }
						updateSearchKeyword={ ( searchKeyword ) => {
							const searchID = /^[0-9]+$/.test( searchKeyword ) ? parseInt( searchKeyword, 10 ) : 0;

							this.setState( {
								searchKeyword,
								searchID,
							} );
						} }
					/>
					<List
						filteredBlocksList={ filteredBlocksList }
						isFetching={ isFetching }
						onItemSelect={ this.replaceWithCoreBlock }
						onHover={ ( hoveredId ) => this.setState( { hoveredId } ) }
						searchID={ searchID }
						searchKeywords={ this.normalizeSearchKeywords( searchKeyword ) }
					/>
					<div className="block-editor-reusable-blocks-inserter__preview">
						{ hoveredId && (
							<div className="block-editor-reusable-blocks-inserter__preview-content">
								<BlockPreview
									blocks={ createBlock( 'core/block', { ref: hoveredId } ) }
									padding={ 10 }
									viewportWidth={ 500 }
								/>
							</div>
						) }
					</div>
				</div>
			</div>
		);
	}
}

Edit.propTypes = {
	clientId: PropTypes.string,
	categoriesList: PropTypes.array,
};

export default Edit;
