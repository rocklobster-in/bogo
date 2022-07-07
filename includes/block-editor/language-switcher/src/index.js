import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';

import edit from './edit';

registerBlockType( 'bogo/language-switcher', {
	edit,

	save: () => {
		const blockProps = useBlockProps.save();

		return <div { ...blockProps }>[bogo]</div>;
	},
} );
