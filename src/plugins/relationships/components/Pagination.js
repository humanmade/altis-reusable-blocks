import PropTypes from 'prop-types';

import { PanelRow } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const Pagination = props => {
	const {
		currentPage,
		goToPrevPage,
		goToNextPage,
		totalPages,
		totalItems,
	} = props;

	if ( totalPages < 2 ) {
		return null;
	}

	return (
		<PanelRow className="tablenav relationship-pagination">
			<div className="tablenav-pages">
				<span className="displaying-num">{ sprintf( __( '%d items', 'enhanced-reusable-blocks' ), totalItems ) }</span>
				<span className="pagination-links">
					{ currentPage === 1 ?
						<span className="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>
						: 	<a className="prev-page button" onClick={ goToPrevPage }>
								<span className="screen-reader-text">{ __( 'Previous page', 'enhanced-reusable-blocks' ) }</span>
								<span aria-hidden="true">‹</span>
							</a>
					}
					<span className="screen-reader-text">{ __( 'Current Page', 'enhanced-reusable-blocks' ) }</span>
					<span id="table-paging" className="paging-input">
						<span className="tablenav-paging-text">{ sprintf( __( '%d of %d', 'enhanced-reusable-blocks' ), currentPage, totalPages ) } </span>
					</span>
					{ currentPage === totalPages ?
						<span className="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>
						: 	<a className="next-page button" onClick={ goToNextPage }>
								<span className="screen-reader-text">{ __( 'Next page', 'enhanced-reusable-blocks' ) }</span>
								<span aria-hidden="true">›</span>
							</a>
					}
				</span>
			</div>
		</PanelRow>
	);
};

Pagination.propTypes = {
	currentPage: PropTypes.number.isRequired,
	goToPrevPage: PropTypes.func.isRequired,
	goToNextPage: PropTypes.func.isRequired,
	totalPages: PropTypes.number.isRequired,
	totalItems: PropTypes.number.isRequired,
};

export default Pagination;
