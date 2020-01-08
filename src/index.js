import { registerBlockType } from '@wordpress/blocks';
import { registerPlugin } from '@wordpress/plugins';

import * as RelationshipsPlugin from './plugins/relationships';

import * as ReusableBlock from './blocks/reusable-block';

import pluginSettings from './settings';

import './styles.scss';

const { context } = pluginSettings;

const { postType } = context;

if ( postType === 'wp_block' ) {
	registerPlugin( RelationshipsPlugin.name, RelationshipsPlugin.settings );
}

registerBlockType( ReusableBlock.name, ReusableBlock.options );
