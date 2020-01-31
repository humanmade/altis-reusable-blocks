import PropTypes from 'prop-types';

import { BlockIcon } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';

const icon = (
	<svg
		xmlns="http://www.w3.org/2000/svg"
		width="24"
		height="24"
		aria-hidden="true"
		viewBox="0 0 24 24"
	>
		<path fill="none" d="M0 0H24V24H0z"></path>
		<path d="M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2zM6
		6h5v5H6V6zm4.5 13a2.5 2.5 0 010-5 2.5 2.5 0 010 5zm3-6l3-5 3 5h-6z"></path>
	</svg>
);

const ListItem = ( { id, onClick, onHover, title, ...props } ) => (
	<li className="block-editor-block-types-list__list-item">
		<Button
			className="block-editor-block-types-list__item"
			onClick={ ( e ) => {
				e.preventDefault();
				onClick();
			} }
			onFocus={ () => onHover( id ) }
			onMouseEnter={ () => onHover( id ) }
			onMouseLeave={ () => onHover( null ) }
			onBlur={ () => onHover( null ) }
			{ ...props }
		>
			<span className="block-editor-block-types-list__item-icon">
				<BlockIcon icon={ icon } showColors />
			</span>
			<span className="block-editor-block-types-list__item-title">
				{ title }
			</span>
		</Button>
	</li>
);

ListItem.propTypes = {
	id: PropTypes.number.isRequired,
	onClick: PropTypes.func.isRequired,
	onHover: PropTypes.func.isRequired,
	title: PropTypes.string.isRequired,
};

export default ListItem;
