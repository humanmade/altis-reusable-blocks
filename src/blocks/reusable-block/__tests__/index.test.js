import { name, options } from '../';

describe( 'name', () => {

	test( 'is as expected', () => {
		expect( name ).toBe( 'altis/reusable-block' );
	} );

} );

describe( 'options', () => {

	test( 'are as expected', () => {
		expect( options ).toMatchSnapshot( {
			description: expect.any( String ),
			title: expect.any( String ),
		} );
	} );

} );
