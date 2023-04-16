export const createShortcode = attributes => {
	let shortcode = `[bogo]`;

	if ( 'language_suggestion' === attributes.type ) {
		shortcode = shortcode.replace( /\]$/,
			` type="${ attributes.type }"]`
		);
	}

	return shortcode;
};
