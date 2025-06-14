const path = require("path");
const webpack = require("webpack");
const { VueLoaderPlugin } = require("vue-loader");
const crypto = require("crypto");
const crypto_orig_createHash = crypto.createHash;
crypto.createHash = (algorithm) =>
    crypto_orig_createHash(algorithm == "md4" ? "sha256" : algorithm);

module.exports = {
    entry: {
        "yaroslavche-sylius-translation-plugin":
            "./assets/js/YaroslavcheSyliusTranslationPlugin.js",
    },
    output: {
        filename: "[name].js",
        path: __dirname + "/src/Resources/public/js",
    },
    module: {
        rules: [
            {
                test: /\.css$/,
                use: ["vue-style-loader", "css-loader"],
            },
            {
                test: /\.vue$/,
                loader: "vue-loader",
                options: {
                    loaders: {},
                    // other vue-loader options go here
                },
            },
            {
                test: /\.js$/,
                loader: "babel-loader",
                exclude: /node_modules/,
            },
            {
                test: /\.(png|jpg|gif|svg)$/,
                loader: "file-loader",
                options: {
                    name: "[name].[ext]?[hash]",
                },
            },
        ],
    },
    resolve: {
        alias: {
            vue$: "vue/dist/vue.esm.js",
        },
        extensions: ["*", ".js", ".vue", ".json"],
    },
    devServer: {
        historyApiFallback: true,
        noInfo: true,
        overlay: true,
    },
    performance: {
        hints: false,
    },
    devtool: "eval-source-map",
    plugins: [new VueLoaderPlugin()],
};

if (process.env.NODE_ENV === "production") {
    module.exports.devtool = "source-map";
    // http://vue-loader.vuejs.org/en/workflow/production.html
    module.exports.plugins = (module.exports.plugins || []).concat([
        new webpack.DefinePlugin({
            "process.env": {
                NODE_ENV: '"production"',
            },
        }),
        new webpack.LoaderOptionsPlugin({
            minimize: true,
        }),
    ]);
}