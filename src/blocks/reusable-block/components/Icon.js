import { BlockIcon } from '@wordpress/block-editor';

const icon = (
	<svg
		aria-hidden="true"
		height="24"
		viewBox="0 0 24 24"
		width="24"
		xmlns="http://www.w3.org/2000/svg"
	>
		<path fill="none" d="M0 0H24V24H0z" />
		<path d="M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2zM6
		6h5v5H6V6zm4.5 13a2.5 2.5 0 010-5 2.5 2.5 0 010 5zm3-6l3-5 3 5h-6z" />
	</svg>
);

const Icon = () => (
	<BlockIcon icon={ icon } showColors />
);

export default Icon;
