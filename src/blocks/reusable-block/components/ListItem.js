import PropTypes from 'prop-types';

import { Button } from '@wordpress/components';
import Icon from './Icon';

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
				<Icon />
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
