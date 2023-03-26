const webpack = require('webpack');
const CopyWebpackPlugin = require('copy-webpack-plugin');

const info = require('./package.json');

module.exports = {
  entry: `${__dirname}/src/main.js`,
  output: {
    path: `${__dirname}/dist`,
    filename: 'geolayer-map.js',
  },
  performance: {
    hints: false,
  },
  module: {
    rules: [
      {
        test: /\.css$/,
        use: [
          { loader: 'style-loader' },
          { loader: 'css-loader' },
        ],
      },
    ],
  },
  plugins: [
    new webpack.BannerPlugin(`geolayer-map ${info.version}`),
    new CopyWebpackPlugin([
      { from: 'static' },
    ]),
  ],
};
