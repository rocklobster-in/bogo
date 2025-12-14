import { __ } from '@wordpress/i18n';
import { useBlockProps, BlockControls } from '@wordpress/block-editor';
import { ToolbarDropdownMenu } from '@wordpress/components';

import { formatListBullets, tip } from '@wordpress/icons';

import './editor.scss';

export default function LanguageSwitcher( { attributes, setAttributes } ) {
	const ListPreview = () => {
		/* eslint-disable no-undef */
		const listItems = Object.entries( bogo?.availableLanguages ?? {} ).map(
			( [ locale, language ] ) => {
				const flag = ( ( country ) => {
					if ( country ) {
						country = country.toLowerCase();
					} else {
						country = 'zz';
					}

					const classes = [ 'bogoflags', `bogoflags-${ country }` ];

					return <span className={ classes.join( ' ' ) }></span>;
				} )( language.country );

				return (
					<li key={ locale } className={ language.tags.join( ' ' ) }>
						{ bogo.showFlags && flag }
						<span className="bogo-language-name">
							{ language.nativename ?? locale }
						</span>
					</li>
				);
			}
		);

		return <ul className="bogo-language-switcher">{ listItems }</ul>;
		/* eslint-enable no-undef */
	};

	const SuggestionPreview = () => {
		return __( 'This page is also available in XXX.', 'bogo' );
	};

	return (
		<>
			<BlockControls group="block">
				<ToolbarDropdownMenu
					label={ __( 'Switch view', 'bogo' ) }
					icon={
						attributes.view === 'suggestion'
							? tip
							: formatListBullets
					}
					controls={ [
						{
							title: __( 'List view', 'bogo' ),
							icon: formatListBullets,
							onClick: () =>
								setAttributes( {
									view: 'list',
								} ),
						},
						{
							title: __( 'Suggestion view', 'bogo' ),
							icon: tip,
							onClick: () =>
								setAttributes( {
									view: 'suggestion',
								} ),
						},
					] }
				/>
			</BlockControls>
			<div { ...useBlockProps() }>
				{ attributes.view === 'suggestion' ? (
					<SuggestionPreview />
				) : (
					<ListPreview />
				) }
			</div>
		</>
	);
}
