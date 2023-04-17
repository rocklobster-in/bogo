export const createShortcode = attributes => {
	let shortcode = `[bogo]`;

	if ( 'suggestion' === attributes.view ) {
		shortcode = shortcode.replace( /\]$/,
			` view="${ attributes.view }"]`
		);
	}

	return shortcode;
};
