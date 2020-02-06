import { shallow } from 'enzyme';

import RelationshipItem from '../RelationshipItem';

jest.mock( '../../../../utils/post', () => ( {
	getEditPostUrl: ( postId ) => `https://example.com/edit/${ postId }`,
} ) );

jest.mock( '@wordpress/components', () => mockComponents( [
	'PanelRow',
] ) );

describe( '<RelationshipItem/>', () => {

	test( 'renders published post as expected', () => {
		const title = {
			rendered: 'Published Post',
		};

		const result = shallow(
			<RelationshipItem id={ 42 } status="publish" title={ title } />
		);

		expect( result ).toMatchSnapshot();
	} );

	test( 'renders pending post as expected', () => {
		const title = {
			rendered: 'Pending Post',
		};

		const result = shallow(
			<RelationshipItem id={ 42 } status="pending" title={ title } />
		);

		expect( result ).toMatchSnapshot();
	} );

	test( 'renders draft post with empty title as expected', () => {
		const title = {
			rendered: '',
		};

		const result = shallow(
			<RelationshipItem id={ 42 } status="draft" title={ title } />
		);

		expect( result ).toMatchSnapshot();
	} );

	test( 'renders post with custom post status and missing title as expected', () => {
		const result = shallow(
			<RelationshipItem id={ 42 } status="other" />
		);

		expect( result ).toMatchSnapshot();
	} );

} );
