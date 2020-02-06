import PropTypes from 'prop-types';

import { PanelRow } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';

const Pagination = ( {
	currentPage,
	goToNextPage,
	goToPrevPage,
	totalItems,
	totalPages,
} ) => {
	if ( totalPages < 2 ) {
		return null;
	}

	const pagingText = sprintf( __( '%d of %d', 'altis-reusable-blocks' ), currentPage, totalPages );

	return (
		<PanelRow className="tablenav relationship-pagination">
			<div className="tablenav-pages">
				<span className="displaying-num">
					{ sprintf( __( '%d items', 'altis-reusable-blocks' ), totalItems ) }
				</span>
				<span className="pagination-links">
					{ currentPage === 1 ? (
						<span className="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>
					) : (
						<button className="prev-page button" onClick={ goToPrevPage }>
							<span className="screen-reader-text">
								{ __( 'Previous page', 'altis-reusable-blocks' ) }
							</span>
							<span aria-hidden="true">‹</span>
						</button>
					) }
					<span className="screen-reader-text">{ __( 'Current Page', 'altis-reusable-blocks' ) }</span>
					<span id="table-paging" className="paging-input">
						<span className="tablenav-paging-text">{ pagingText }</span>
					</span>
					{ currentPage === totalPages ? (
						<span className="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>
					) : (
						<button className="next-page button" onClick={ goToNextPage }>
							<span className="screen-reader-text">
								{ __( 'Next page', 'altis-reusable-blocks' ) }
							</span>
							<span aria-hidden="true">›</span>
						</button>
					) }
				</span>
			</div>
		</PanelRow>
	);
};

Pagination.propTypes = {
	currentPage: PropTypes.number.isRequired,
	goToNextPage: PropTypes.func.isRequired,
	goToPrevPage: PropTypes.func.isRequired,
	totalItems: PropTypes.number.isRequired,
	totalPages: PropTypes.number.isRequired,
};

export default Pagination;
