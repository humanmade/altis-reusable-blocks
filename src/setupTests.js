import React from 'react';

global.wp = {
	// The plugin @babel/plugin-transform-react-jsx is configured to use wp.element.createElement as pragma.
	element: {
		createElement: React.createElement,
	},
};

/**
 * Create a mock component with the given display name.
 *
 * @param {string} displayName - Component display name.
 *
 * @return {function} Mock component with the given display name.
 */
global.mockComponent = ( displayName ) => {
	const Component = () => null;

	Component.displayName = displayName;

	return Component;
};

/**
 * Create mock components for all given display names.
 *
 * @param {Array} displayNames - List of component display names.
 *
 * @return {Object} Map with component display names as keys and mock components as values.
 */
global.mockComponents = ( displayNames ) => {
	const { mockComponent } = global;

	const components = {};

	for ( const displayName of displayNames ) {
		components[ displayName ] = mockComponent( displayName );
	}

	return components;
};
