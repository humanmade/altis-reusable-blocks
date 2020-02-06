import { shallow } from 'enzyme';

import Pagination from '../Pagination';

jest.mock( '@wordpress/components', () => mockComponents( [
	'PanelRow',
] ) );

describe( '<Pagination />', () => {

	test.each( [
		[ 0 ],
		[ 1 ],
	] )( 'renders nothing if total number of pages is %d', ( totalPages ) => {
		const result = shallow(
			<Pagination
				currentPage={ 1 }
				goToNextPage={ () => {} }
				goToPrevPage={ () => {} }
				totalItems={ totalPages }
				totalPages={ totalPages }
			/>
		);

		expect( result ).toMatchSnapshot();
	} );

	test( 'renders as expected if current page is first page', () => {
		const result = shallow(
			<Pagination
				currentPage={ 1 }
				goToNextPage={ () => {} }
				goToPrevPage={ () => {} }
				totalItems={ 42 }
				totalPages={ 5 }
			/>
		);

		expect( result ).toMatchSnapshot();
	} );

	test( 'renders as expected if current page is last page', () => {
		const result = shallow(
			<Pagination
				currentPage={ 5 }
				goToNextPage={ () => {} }
				goToPrevPage={ () => {} }
				totalItems={ 42 }
				totalPages={ 5 }
			/>
		);

		expect( result ).toMatchSnapshot();
	} );

	test( 'renders as expected if current page is neither first nor last page', () => {
		const result = shallow(
			<Pagination
				currentPage={ 3 }
				goToNextPage={ () => {} }
				goToPrevPage={ () => {} }
				totalItems={ 42 }
				totalPages={ 5 }
			/>
		);

		expect( result ).toMatchSnapshot();
	} );

} );
