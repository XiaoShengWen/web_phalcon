var webpack = require('webpack')

module.exports = {
    entry: './dev/js/entry.js',
    output: {
       path: __dirname,
       filename: './public/js/bundle.js'
    },
    module: {
        loaders: [
           {
               test: /\.css$/, loader: 'style!css'
           },
           {
               test: /\.js$/,
               loaders: ['babel?presets[]=es2015'],
               exclude: /node_modules/
           }
       ]
    },
    plugins: [
         new webpack.BannerPlugin('This file is created by wuzhongyang')
    ]
}

