import { shallow } from 'enzyme';

import Edit from '../Edit';

jest.mock( '@wordpress/block-editor', () => mockComponents( [
	'BlockPreview',
] ) );

jest.mock( '@wordpress/blocks', () => ( {
	createBlock: () => ( {} ),
} ) );

describe( '<Edit />', () => {

	test( 'renders as expected with empty categories list', () => {
		const result = shallow(
			<Edit />,
			{
				disableLifecycleMethods: true,
			}
		);

		expect( result ).toMatchSnapshot();
	} );

	test( 'renders as expected when hovering', () => {
		const categoriesList = [
			{
				id: 1,
				name: 'First',
			},
			{
				id: 2,
				name: 'Second',
			},
			{
				id: 3,
				name: 'Third',
			},
		];

		const result = shallow(
			<Edit categoriesList={ categoriesList } />,
			{
				disableLifecycleMethods: true,
			}
		);

		result.setState( { hoveredId: 2 } );

		expect( result ).toMatchSnapshot();
	} );

} );
