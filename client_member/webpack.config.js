
var webpack = require('webpack');
module.exports = {
  entry:  {page1:__dirname + "/app/index.js",
           page2:__dirname + "/app/index1.js"},//入口文件
  output: {
    path: __dirname + "/public/js",//打包后的文件存放的地方
    filename: "[name]bundle.js"//打包后输出文件的文件名
  },
  module: {
    loaders: [
      {
        test: /\.json$/,
        loader: "json"
      },
      {
        test: /\.js?$/,
        exclude: /jquery/,
        loader: 'babel',//在webpack的module部分的loaders里进行配置即可
        query: {
          presets: ['es2015']
        }
      },
      {test: /\.scss$/, loader:"style-loader!css-loader!sass-loader"},
      {test: /\.html$/, loader: "html"}
    ]
  },
  devServer: {
  contentBase: "./public",//本地服务器所加载的页面所在的目录
  colors: true,//终端中输出结果为彩色
  historyApiFallback: true,//不跳转
  inline: true//实时刷新
}
}
