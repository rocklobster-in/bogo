import { useBlockProps } from '@wordpress/block-editor';

export default function LanguageSwitcher() {
	const blockProps = useBlockProps();

	const listItems = Object.entries(
		bogo?.availableLanguages ?? {}
	).map( ( [ locale, language ] ) => {
		return (
			<li key={ locale }>
				<span className="bogoflags"></span>
				<span className="bogo-language-name">
					{ language }
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
