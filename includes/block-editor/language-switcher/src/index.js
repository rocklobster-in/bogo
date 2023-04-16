import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';

import edit from './edit';
import { createShortcode } from './helpers';

registerBlockType( 'bogo/language-switcher', {
	edit,

	save: ( { attributes } ) => {
		const shortcode = createShortcode( attributes );

		return(
			<div { ...useBlockProps.save() }>
				{ shortcode }
			</div>
		);
	},
} );
