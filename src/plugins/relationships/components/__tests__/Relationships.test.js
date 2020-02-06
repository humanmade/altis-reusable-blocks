import { shallow } from 'enzyme';

import { addQueryArgs } from '@wordpress/url';

import Relationships from '../Relationships';

import { fetchJson } from '../../../../utils/fetch';

jest.mock( '@wordpress/url', () => ( { addQueryArgs: jest.fn() } ) );

jest.mock( '../Pagination', () => mockComponent( 'Pagination' ) );

jest.mock( '../RelationshipItem', () => mockComponent( 'RelationshipItem' ) );

jest.mock( '../../../../settings', () => ( {
	relationshipsPerPage: 10,
} ) );

jest.mock( '../../../../utils/fetch', () => ( {
	fetchJson: jest.fn( () => Promise.resolve( [] ) ),
} ) );

afterEach( () => {
	jest.clearAllMocks();
} );

const createRelationshipsList = ( length, start = 1 ) => Array.from( { length }, ( _, i ) => ( { id: start + i } ) );

describe( '<Relationships />', () => {

	test( 'initializes state as expected', () => {
		const result = shallow(
			<Relationships currentPostId={ 42 } />,
			{
				disableLifecycleMethods: true,
			}
		);

		expect( result.state() ).toMatchSnapshot();
	} );

	test( 'fetches relationships on mount', () => {
		const result = shallow(
			<Relationships currentPostId={ 42 } />,
			{
				disableLifecycleMethods: true,
			}
		);

		const instance = result.instance();

		instance.fetchRelationships = jest.fn();

		instance.componentDidMount();

		expect( instance.fetchRelationships ).toHaveBeenCalled();
	} );

	test( 'fetches relationships', async () => {
		const data = [ createRelationshipsList( 3 ), [] ];

		fetchJson.mockImplementationOnce( () => Promise.resolve( data ) );

		const currentPostId = 42;

		const result = shallow(
			<Relationships currentPostId={ currentPostId } />,
			{
				disableLifecycleMethods: true,
			}
		);

		const instance = result.instance();

		instance.updateRelationshipsList = jest.fn();

		await instance.fetchRelationships();

		expect( addQueryArgs ).toHaveBeenCalledWith( '/altis-reusable-blocks/v1/relationships', {
			block_id: currentPostId,
			page: 1,
		} );
		expect( instance.updateRelationshipsList ).toHaveBeenCalledWith( data );
		expect( result.state( 'isFetching' ) ).toBe( false );
	} );

	test( 'handles rejection while fetching relationships', async () => {
		fetchJson.mockImplementationOnce( () => Promise.reject( new Error() ) );

		const result = shallow(
			<Relationships currentPostId={ 42 } />,
			{
				disableLifecycleMethods: true,
			}
		);

		const instance = result.instance();

		instance.updateRelationshipsList = jest.fn();

		await instance.fetchRelationships();

		expect( instance.updateRelationshipsList ).not.toHaveBeenCalled();
		expect( result.state( 'isFetching' ) ).toBe( false );
	} );

	test( 'handles error while fetching relationships', async () => {
		fetchJson.mockImplementationOnce( () => {
			throw new Error();
		} );

		const result = shallow(
			<Relationships currentPostId={ 42 } />,
			{
				disableLifecycleMethods: true,
			}
		);

		const instance = result.instance();

		instance.updateRelationshipsList = jest.fn();

		await instance.fetchRelationships();

		expect( instance.updateRelationshipsList ).not.toHaveBeenCalled();
		expect( result.state( 'isFetching' ) ).toBe( false );
	} );

	test( 'updating relationships list with empty data does not overwrite existing data', () => {
		const result = shallow(
			<Relationships currentPostId={ 42 } />,
			{
				disableLifecycleMethods: true,
			}
		);

		const state = {
			relationshipsList: createRelationshipsList( 3 ),
			totalItems: 3,
			totalPages: 1,
		};

		result.setState( state );

		result.instance().updateRelationshipsList( [ [] ] );

		expect( result.state() ).toMatchObject( state );
	} );

	test( 'updates relationships list', () => {
		const result = shallow(
			<Relationships currentPostId={ 42 } />,
			{
				disableLifecycleMethods: true,
			}
		);

		const state = {
			relationshipsList: createRelationshipsList( 2 ),
			totalItems: 2,
			totalPages: 1,
		};

		result.setState( state );

		result.instance().updateRelationshipsList( [
			createRelationshipsList( 2, 3 ),
			{
				'x-wp-total': 4,
				'x-wp-totalpages': 2,
			},
		] );

		expect( result.state() ).toMatchObject( {
			relationshipsList: createRelationshipsList( 4 ),
			totalItems: 4,
			totalPages: 2,
		} );
	} );

	test( 'updating relationships list does not add duplicates', () => {
		const result = shallow(
			<Relationships currentPostId={ 42 } />,
			{
				disableLifecycleMethods: true,
			}
		);

		const state = {
			relationshipsList: createRelationshipsList( 3 ),
			totalItems: 2,
			totalPages: 1,
		};

		result.setState( state );

		result.instance().updateRelationshipsList( [
			createRelationshipsList( 3, 2 ),
			{
				'x-wp-total': 4,
				'x-wp-totalpages': 2,
			},
		] );

		expect( result.state() ).toMatchObject( {
			relationshipsList: createRelationshipsList( 4 ),
			totalItems: 4,
			totalPages: 2,
		} );
	} );

	test( 'navigates to previous page', () => {
		const result = shallow(
			<Relationships currentPostId={ 42 } />,
			{
				disableLifecycleMethods: true,
			}
		);

		result.setState( { currentPage: 2 } );

		result.instance().goToPrevPage();

		expect( result.state( 'currentPage' ) ).toBe( 1 );
	} );

	test( 'does not navigate back from first page', () => {
		const result = shallow(
			<Relationships currentPostId={ 42 } />,
			{
				disableLifecycleMethods: true,
			}
		);

		result.setState( { currentPage: 1 } );

		result.instance().goToPrevPage();

		expect( result.state( 'currentPage' ) ).toBe( 1 );
	} );

	test( 'navigates to next page', () => {
		const result = shallow(
			<Relationships currentPostId={ 42 } />,
			{
				disableLifecycleMethods: true,
			}
		);

		result.setState( {
			currentPage: 1,
			relationshipsList: createRelationshipsList( 20 ),
			totalPages: 2,
		} );

		const instance = result.instance();

		instance.fetchRelationships = jest.fn();

		instance.goToNextPage();

		expect( result.state( 'currentPage' ) ).toBe( 2 );
		expect( instance.fetchRelationships ).not.toHaveBeenCalled();
	} );

	test( 'navigates to new page and fetches items', () => {
		const result = shallow(
			<Relationships currentPostId={ 42 } />,
			{
				disableLifecycleMethods: true,
			}
		);

		result.setState( {
			currentPage: 1,
			relationshipsList: createRelationshipsList( 10 ),
			totalPages: 2,
		} );

		const instance = result.instance();

		instance.fetchRelationships = jest.fn();

		instance.goToNextPage();

		expect( result.state( 'currentPage' ) ).toBe( 2 );
		expect( instance.fetchRelationships ).toHaveBeenCalled();
	} );

	test( 'does not navigate beyond last page', () => {
		const result = shallow(
			<Relationships currentPostId={ 42 } />,
			{
				disableLifecycleMethods: true,
			}
		);

		result.setState( {
			currentPage: 1,
			totalPages: 1,
		} );

		result.instance().goToNextPage();

		expect( result.state( 'currentPage' ) ).toBe( 1 );
	} );

	test( 'renders as expected if fetching', () => {
		const result = shallow(
			<Relationships currentPostId={ 42 } />,
			{
				disableLifecycleMethods: true,
			}
		);

		result.setState( { isFetching: true } );

		expect( result ).toMatchSnapshot();
	} );

	test( 'renders as expected with no items', () => {
		const result = shallow(
			<Relationships currentPostId={ 42 } />,
			{
				disableLifecycleMethods: true,
			}
		);

		expect( result ).toMatchSnapshot();
	} );

	test( 'renders as expected with items', () => {
		const result = shallow(
			<Relationships currentPostId={ 42 } />,
			{
				disableLifecycleMethods: true,
			}
		);

		result.setState( {
			relationshipsList: createRelationshipsList( 3 ),
			totalItems: 3,
			totalPages: 1,
		} );

		expect( result ).toMatchSnapshot();
	} );

} );
