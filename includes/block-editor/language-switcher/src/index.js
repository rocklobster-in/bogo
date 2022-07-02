import { registerBlockType } from '@wordpress/blocks';

registerBlockType( 'bogo/language-switcher', {
	edit: () => {
		return <p> Hello world (from the editor)</p>;
	},
	save: () => {
		return <p> Hola mundo (from the frontend) </p>;
	},
} );
