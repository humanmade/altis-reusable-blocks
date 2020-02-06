import { mapSelectToProps } from '../Edit';

jest.mock( '../../components/Edit', () => () => null );

describe( 'mapSelectToProps', () => {

	test( 'passes expected props', () => {
		const categoriesList = [ 47, 11 ];

		const postCategory = 23;

		const select = () => ( {
			getEditedPostAttribute: () => [ postCategory, 42 ],
			getEntityRecords: () => categoriesList,
			getTaxonomy: () => ( {
				rest_base: 'rest-base',
			} ),
		} );

		const props = mapSelectToProps( select );

		expect( props.categoriesList ).toStrictEqual( categoriesList );
	} );

} );
