import _uniqBy from 'lodash/uniqBy';
import PropTypes from 'prop-types';

import {
	PanelBody,
	PanelRow,
	Placeholder,
	Spinner,
} from '@wordpress/components';
import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/edit-post';
import { Component, Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';

import settings from '../../../settings';
import { fetchJson } from '../../../utils/fetch';

import Pagination from './Pagination';
import RelationshipItem from './RelationshipItem';

const { relationshipsPerPage } = settings;

const baseClassName = 'altis-reusable-block-relationships';
const sidebarName = 'altis-reusable-block-relationships';

class Relationships extends Component {
	state = {
		relationshipsList: [],
		currentPage: 1,
		isFetching: false,
		totalPages: 0,
		totalItems: 0,
	};

	/**
	 * When component mounts, fetch relationships for the block.
	 */
	componentDidMount() {
		this.fetchRelationships();
	}

	/**
	 * Fetch all the posts that use the current block.
	 *
	 * @param {Number} page - Page number for the request.
	 */
	fetchRelationships = async ( page = 1 ) => {
		const { currentPostId } = this.props;

		this.setState( { isFetching: true } );

		try {
			const data = await fetchJson(
				{
					path: addQueryArgs(
						`/altis-reusable-blocks/v1/relationships`, {
							block_id: currentPostId,
							page,
						}
					),
				},
				[ 'x-wp-totalpages', 'x-wp-total' ]
			);

			this.updateRelationshipsList( data );
		} catch ( e ) {
			/* eslint-disable no-console */
			console.error( 'Error retrieving relationships for block.' );
			console.error( e );
			/* eslint-enable no-console */
		}

		this.setState( { isFetching: false } );
	};

	/**
	 * Update the Relationships List state object with a new list and normalize them.
	 *
	 * @param {Object[]} data - Array of new relationships fetched and response headers.
	 * @param {Object[]} data.newRelationshipsList - Array of new relationships fetched.
	 * @param {Object[]} data.headers - Array of response headers.
	 */
	updateRelationshipsList = ( [ newRelationshipsList, headers ] ) => {
		const { relationshipsList } = this.state;

		if ( ! newRelationshipsList.every( ( item ) => relationshipsList.includes( item ) ) ) {
			const totalPages = parseInt( headers[ 'x-wp-totalpages' ], 10 );
			const totalItems = parseInt( headers[ 'x-wp-total' ], 10 );

			this.setState( {
				relationshipsList: _uniqBy( [ ...relationshipsList, ...newRelationshipsList ], 'id' ),
				totalPages,
				totalItems,
			} );
		}
	};

	/**
	 * Changes crurrentPage state to previous page.
	 */
	goToPrevPage = () => {
		const { currentPage } = this.state;

		if ( currentPage > 1 ) {
			this.setState( {
				currentPage: currentPage - 1,
			} );
		}
	};

	/**
	 * Changes crurrentPage state to next page.
	 */
	goToNextPage = () => {
		const {
			currentPage,
			relationshipsList,
			totalPages,
		} = this.state;

		if ( currentPage < totalPages ) {
			this.setState( {
				currentPage: currentPage + 1,
			} );

			if ( relationshipsList.length <= currentPage * relationshipsPerPage ) {
				this.fetchRelationships( currentPage + 1 );
			}
		}
	};

	render() {
		const {
			currentPage,
			isFetching,
			relationshipsList,
			totalPages,
			totalItems,
		} = this.state;

		const title = __( 'Relationships', 'altis-reusable-blocks' );

		const startIndex = currentPage === 1 ? 0 : ( currentPage - 1 ) * relationshipsPerPage;

		const items = relationshipsList.slice( startIndex, relationshipsPerPage * currentPage );
		const relationshipItems = items.length
			? items.map( ( relationshipItem ) => {
				return ( <RelationshipItem { ...relationshipItem } key={ relationshipItem.id } /> );
			} )
			: <PanelRow>{ __( 'No Relationships to Display', 'altis-reusable-blocks' ) }</PanelRow>;

		return (
			<Fragment>
				<PluginSidebarMoreMenuItem target={ sidebarName }>
					{ title }
				</PluginSidebarMoreMenuItem>
				<PluginSidebar
					name={ sidebarName }
					title={ title }
				>
					<PanelBody className={ `${ baseClassName }__relationships_list` }>
						{
							isFetching
								? <Placeholder><Spinner /></Placeholder>
								: relationshipItems
						}
						<Pagination
							currentPage={ currentPage }
							goToPrevPage={ this.goToPrevPage }
							goToNextPage={ this.goToNextPage }
							totalPages={ totalPages }
							totalItems={ totalItems }
						/>
					</PanelBody>
				</PluginSidebar>
			</Fragment>
		);
	}
}

Relationships.propTypes = {
	currentPostId: PropTypes.number.isRequired,
};

export default Relationships;
