import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RadioControl } from '@wordpress/components';

export default function LanguageSwitcher( { attributes, setAttributes } ) {

	const DemoLanguageSwitcher = () => {
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
			<InspectorControls>
				<PanelBody title={ __( 'Styles', 'bogo' ) }>
					<RadioControl
						label={ __( 'Type', 'bogo' ) }
						help={ __( 'The type of language switcher', 'bogo' ) }
						selected={ attributes.type }
						options={ [
							{
								label: __( 'Default', 'bogo' ),
								value: 'language_switcher'
							},
							{
								label: __( 'Language suggestion', 'bogo' ),
								value: 'language_suggestion'
							},
						] }
						onChange={ ( value ) => setAttributes( {
							type: value
						} ) }
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps() }>
				<DemoLanguageSwitcher />
			</div>
		</>
	);
}
