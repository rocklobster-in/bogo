import { __ } from '@wordpress/i18n';
import { useBlockProps, BlockControls } from '@wordpress/block-editor';
import { ToolbarDropdownMenu } from '@wordpress/components';

import { formatListBullets, tip } from '@wordpress/icons';

export default function LanguageSwitcher( { attributes, setAttributes } ) {

	const ListPreview = () => {
		const listItems = Object.entries(
			bogo?.availableLanguages ?? {}
		).map( ( [ locale, language ] ) => {
			const flag = ( flag => {
				const found = flag.match( /\/(?<name>[a-z]+)\.png$/ );

				const classes = [
					'bogoflags',
					`bogoflags-${ found?.groups.name ?? 'zz' }`,
				];

				return (
					<span className={ classes.join( ' ' ) }></span>
				);
			} )( language.flag );

			return (
				<li key={ locale } className={ language.tags.join( ' ' ) }>
					{ bogo.showFlags && flag }
					<span className="bogo-language-name">
						{ language.nativename ?? locale }
					</span>
				</li>
			);
		} );

		return (
			<ul className="bogo-language-switcher">
				{ listItems }
			</ul>
		);
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
							onClick: () => setAttributes( {
								view: 'list',
							} ),
						},
						{
							title: __( 'Suggestion view', 'bogo' ),
							icon: tip,
							onClick: () => setAttributes( {
								view: 'suggestion',
							} ),
						},
					] }
				/>
			</BlockControls>
			<div { ...useBlockProps() }>
				<ListPreview />
			</div>
		</>
	);
}
