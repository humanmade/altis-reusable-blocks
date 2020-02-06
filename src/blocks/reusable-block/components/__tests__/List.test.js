import { shallow } from 'enzyme';

import List from '../List';

jest.mock( '../ListItem', () => mockComponent( 'ListItem' ) );

describe( '<List />', () => {

	test( 'renders as expected if fetching', () => {
		const result = shallow(
			<List
				filteredBlocksList={ [] }
				isFetching
				onHover={ () => {} }
				onItemSelect={ () => {} }
				searchKeywords={ [] }
			/>,
			{
				disableLifecycleMethods: true,
			}
		);

		expect( result ).toMatchSnapshot();
	} );

	test( 'renders as expected if no items', () => {
		const result = shallow(
			<List
				filteredBlocksList={ [] }
				onHover={ () => {} }
				onItemSelect={ () => {} }
				searchKeywords={ [] }
			/>,
			{
				disableLifecycleMethods: true,
			}
		);

		expect( result ).toMatchSnapshot();
	} );

	test( 'renders as expected', () => {
		const result = shallow(
			<List
				filteredBlocksList={ [] }
				onHover={ () => {} }
				onItemSelect={ () => {} }
				searchKeywords={ [] }
			/>,
			{
				disableLifecycleMethods: true,
			}
		);

		result.setState( { sortedBlocks: [ { id: 23 }, { id: 42 } ] } );

		expect( result ).toMatchSnapshot();
	} );

} );
