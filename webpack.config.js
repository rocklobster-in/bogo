const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	output: {
		...defaultConfig.output,
		clean: false,
	},
	plugins: [
		...defaultConfig.plugins.filter(
			plugin => plugin.constructor.name !== 'CleanWebpackPlugin',
		),
	],
};
