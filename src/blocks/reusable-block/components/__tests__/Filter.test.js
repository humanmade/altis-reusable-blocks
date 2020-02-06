import { shallow } from 'enzyme';

import Filter from '../Filter';

describe( '<Filter />', () => {

	test( 'renders as expected if no categories', () => {
		const result = shallow(
			<Filter
				categoriesList={ [] }
				searchID={ 42 }
				updateSearchCategory={ () => {} }
				updateSearchKeyword={ () => {} }
			/>
		);

		expect( result ).toMatchSnapshot();
	} );

	test( 'renders as expected with categories', () => {
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
			<Filter
				categoriesList={ categoriesList }
				updateSearchCategory={ () => {} }
				updateSearchKeyword={ () => {} }
			/>
		);

		expect( result ).toMatchSnapshot();
	} );

} );
