import { mapSelectToProps } from '../Relationships';

jest.mock( '../../components/Relationships', () => () => null );

describe( 'mapSelectToProps', () => {

	test( 'passes current post ID', () => {
		const postId = 42;

		const select = () => ( {
			getCurrentPostId: () => postId,
		} );

		const { currentPostId } = mapSelectToProps( select );

		expect( currentPostId ).toBe( postId );
	} );

} );
