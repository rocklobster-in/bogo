import { useBlockProps } from '@wordpress/block-editor';

export default function LanguageSwitcher() {
	const blockProps = useBlockProps();

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
		<div { ...blockProps }>
			<ul className="bogo-language-switcher">
				{ listItems }
			</ul>
		</div>
	);
}
