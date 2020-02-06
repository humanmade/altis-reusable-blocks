import { shallow } from 'enzyme';

import ListItem from '../ListItem';

jest.mock( '../Icon', () => mockComponent( 'Icon' ) );

describe( '<ListItem />', () => {

	test( 'renders as expected', () => {
		const result = shallow(
			<ListItem id={ 42 } onClick={ () => {} } onHover={ () => {} } title="Title" />
		);

		expect( result ).toMatchSnapshot();
	} );

} );
