var webpack = require('webpack');
var ExtractTextPlugin = require("extract-text-webpack-plugin");
var fs = require("fs");
var path = require('path');
var conf = require('./app.json');
var env = process.env.NODE_ENV || 'development';
var isPro = env === 'production';
var isDaily = env === 'daily';
var entry = {
  // "ping": "./static/js/ping.js",
  // "lib": "./static/js/lib.js",
};

conf.pages.forEach(page => {
  entry[page] = `./pages/${page}.js`;
});

var output = {
  path: __dirname,
  publicPath: isPro ? 'https://res.rtmap.com/' : 'http://res.rtmap.com/',
  filename: (isPro || isDaily) ? "./static/dist/[hash]/[name].js" : "./static/dist/[name].js"
};

var plugins = [];
if (isPro) {
  plugins = [
    new webpack.DefinePlugin({
      'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV || 'development')
    }),
    new webpack.optimize.UglifyJsPlugin({
      sourceMap: false,
      compress: { warnings: false }
    })
  ];
}
if (isPro || isDaily) {
  // 生成版本
  plugins.push(function() {
      this.plugin("done", function(stats) {
        var conf = require("./app.json");
        conf.version = stats.hash;
        // conf.resource.mode = isDaily ? 'daily' : 'dist';
        fs.writeFileSync('./app.json', JSON.stringify(conf, null, 2));
      });
  });
}

plugins.push(new ExtractTextPlugin((isPro || isDaily) ? "./static/dist/[hash]/[name].css" : "./static/dist/[name].css"));

module.exports = {
  entry: entry,
  output: output,
  plugins: plugins,
  module: {
    noParse: [/jquery/],
    preLoaders: [
      {
        test: /\.(js|jsx)$/,
        loader: 'eslint-loader',
        // include: [path.resolve(__dirname, "static/js")],
        exclude: [/(node_modules|bower_components|jquery|lodash|querystring|galaxy|checkin|cookie|hogan|qrcode|barcode)/] // galaxy|checkin 历史代码
      }
    ],
    loaders: [
      {
        test: /\.js?$/,
        exclude: null,
        loader: 'babel', // 'babel-loader' is also a legal name to reference
        query: {
          presets: ['react', 'es2015'],
          plugins: ['transform-class-properties', 'transform-object-assign'],
        }
      },
      {test:/\.css$/, loader: ExtractTextPlugin.extract("style-loader", "css-loader")},
      {test: /\.scss$/, loader:  ExtractTextPlugin.extract("style-loader", "css-loader!sass-loader?modules")},
      {test: /\.(jpg|png|svg|ttf)$/, loader: "url?limit=8192&name=static/dist/img/[name].[hash:8].[ext]"},
      {test: /\.html$/, loader: "html"}
    ]
  },
};
