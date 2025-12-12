import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

import edit from './edit';

registerBlockType( 'bogo/language-switcher', {
	edit,

	save: () => {
		return (
			<div { ...useBlockProps.save() }>
				{ __(
					'Language Switcher Block – dynamic content rendered on the server.',
					'bogo'
				) }
			</div>
		);
	},
} );
