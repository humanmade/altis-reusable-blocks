import { name, settings } from '../';

jest.mock( '../containers/Relationships', () => () => null );

describe( 'name', () => {

	test( 'is as expected', () => {
		expect( name ).toBe( 'altis-reusable-block-relationships' );
	} );

} );

describe( 'settings', () => {

	test( 'are as expected', () => {
		expect( settings ).toMatchSnapshot();
	} );

} );
