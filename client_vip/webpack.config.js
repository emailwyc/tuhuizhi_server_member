var conf = require('./package.json');
var webpack = require('webpack');
var ExtractTextPlugin = require("extract-text-webpack-plugin");
var fs = require("fs");
var path = require('path');
var env = process.env.NODE_ENV || 'dev';
var isPro = env === 'production';
var isDaily = env === 'daily';

var entry = conf.entry;
var output = {
  path: __dirname,
  publicPath: '//res.rtmap.com/',
  filename: (isPro || isDaily) ? "./static/dist/[hash]/js/[name].js" : "./static/dist/js/[name].js"
};

var plugins = [];

if (isPro) {
  plugins = [
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
        var conf = require("./app/package");
        conf.resource.static = stats.hash;
        conf.resource.mode = isDaily ? 'daily' : 'dist';
        fs.writeFileSync('./app/package.json',JSON.stringify(conf,null,2));
      });
  });
}

plugins.push(new ExtractTextPlugin((isPro || isDaily) ? "./static/dist/[hash]/css/[name].css" : "./static/dist/css/[name].css"));

module.exports = {
    entry: entry,
    output: output,
    module: {
        noParse: [/jquery/, /lodash/],
        // later
        preLoaders: [
            {
              test: /\.(js|jsx)$/,
              loader: 'eslint-loader',
              include: [path.resolve(__dirname, "static/js")],
              exclude: [/(node_modules|bower_components|jquery|lodash|querystring|galaxy|checkin|cookie|hogan|bootstrap)/] // galaxy|checkin 历史代码
            }
        ],
        loaders: [
          //{test: /\.css$/, loader: "style!css" },
          //{test: /\.scss$/,loaders: ["style", "css", "sass"]},
          {
              test: /\.js?$/,
              exclude: /(bower_components|jquery|lodash|bootstrap)/,
              loader: 'babel', // 'babel-loader' is also a legal name to reference
              query: {
                presets: ['react', 'es2015']
              }
          },
          {test:/\.css$/, loader: ExtractTextPlugin.extract("style-loader", "css-loader")},
          {test: /\.scss$/, loader:  ExtractTextPlugin.extract("style-loader", "css-loader!sass-loader")},
          {test: /\.(jpg|png|svg)$/, loader: "url?limit=8192&name=static/dist/img/[name].[hash:8].[ext]"},
          {test: /\.html$/, loader: "html"}
        ]
    },
    plugins: plugins
};
