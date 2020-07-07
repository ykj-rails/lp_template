/* eslint-disable no-unused-vars */
// プラグインを利用するためにwebpackを読み込んでおく
const webpack = require("webpack");
// output.pathに絶対パスを指定する必要があるため、pathモジュールを読み込んでおく
const path = require("path");
const globule = require("globule");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const CopyWebpackPlugin = require("copy-webpack-plugin");
const HtmlWebpackPlugin = require("html-webpack-plugin");
const HtmlBeautifyPlugin = require("html-beautify-webpack-plugin");
const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");

const getEntriesList = targetTypes => {
  const entriesList = {};
  for (const [srcType, targetType] of Object.entries(targetTypes)) {
    let filesMatched = null;

    if (srcType === "pug") {
      filesMatched = globule.find([`**/*.${srcType}`, `!_pug/**`], {
        cwd: `${__dirname}/src`
      });
    } else if (srcType === "styl") {
      filesMatched = globule.find([`**/*.${srcType}`, `!**/_*.${srcType}`], {
        cwd: `${__dirname}/src`
      });
    } else if (srcType === "js") {
      filesMatched = globule.find(
        [`**/*.${srcType}`, `!**/_*.${srcType}`, `!**/libs/**`],
        { cwd: `${__dirname}/src` }
      );
    } else {
      return;
    }

    for (const srcName of filesMatched) {
      let targetName = null;
      if (srcType === "js") {
        targetName = srcName.replace(new RegExp(`.${srcType}$`, "i"), "");
      } else if (srcType === "styl") {
        targetName = srcName
          .replace(/_stylus/, "css")
          .replace(new RegExp(`.${srcType}$`, "i"), "");
      } else {
        targetName = srcName.replace(
          new RegExp(`.${srcType}$`, "i"),
          `.${targetType}`
        );
      }
      entriesList[targetName] = `${__dirname}/src/${srcName}`;
    }
  }
  return entriesList;
};

// console.log(getEntriesList({ pug : 'html' }));
// console.log(getEntriesList({ stylus : 'css' }));
// console.log(getEntriesList({ js : 'js' }));

const app = (env, argv) => {
  let sourceMap = "source-map";
  if (argv.mode === "production") {
    sourceMap = "";
  }

  const settings = [
    {
      //CSS
      context: `${__dirname}`,
      entry: getEntriesList({ styl: 'css' }),
      output: {
        path: `${__dirname}/public/`,
        filename: './../cssCompile/[name].js',
      },
      module: {
        rules: [
          {
            test: /\.styl$/,
            use: [
              {
                loader: MiniCssExtractPlugin.loader,
              },
              // CSSをバンドルするための機能
              {
                loader: 'css-loader',
                options: {
                  // CSS内のurl()メソッドの取り込みを禁止する
                  url: false,
                  // ソースマップの利用有無
                  sourceMap: false,
                  // 事前に適応するloaderの数
                  // stylus+PostCSSだから2を指定
                  importLoaders: 2,
                },
              },
              // PostCSSのための設定
              {
                loader: 'postcss-loader',
                options: {
                  // PostCSS側のソースマップの利用有無
                  sourceMap: true,
                  ident: 'postcss',
                  plugins: [
                    // require('autoprefixer')({
                    //   grid: true,
                    // }),
                    require('postcss-cssnext'),
                  ],
                },
              },
              // stylusをバンドルするための機能
              {
                loader: 'stylus-loader',
                options: {
                  sourceMap: true,
                },
              },
            ],
          },
        ],
      },
      devtool: sourceMap,
      plugins: [
        new MiniCssExtractPlugin({
          filename: './[name].css',
        }),
      ],
      optimization: {
        minimizer: [
          new OptimizeCSSAssetsPlugin(), // CSS の minify を行う
        ],
      },
    },
    {
      //JS
      context: `${__dirname}`,
      entry: getEntriesList({ js: 'js' }),
      output: {
        // 出力先のパス（絶対パスを指定する必要がある）
        path: `${__dirname}/public/`,
        // 出力するファイル名
        filename: '[name].js',
      },
      externals: [
        {
          jquery: 'jQuery',
        },
      ],
      module: {
        rules: [
          // {
          //   enforce: "pre", // babel-loaderによる変更が行われる前にESLintによる構文解析を行うように指定
          //   test: /\.js$/, // ローダーの処理対象ファイル
          //   exclude: /node_modules/, // ローダーの処理対象から外すディレクトリ
          //   loader: "eslint-loader", // 利用するローダー
          // },
          {
            test: /\.js?$/,
            exclude: /node_modules/, // ローダーの処理対象から外すディレクトリ
            use: [
              {
                loader: 'babel-loader', // 利用するローダー
                options: {
                  presets: ['@babel/preset-env'],
                  plugins: [['@babel/plugin-transform-runtime', { corejs: 3 }]]
                }
              }
            ]
          }
        ]
      },
      devtool: sourceMap,
      plugins: [
        new webpack.DefinePlugin({
          NODE_ENV: JSON.stringify(process.env.NODE_ENV),
        })
      ],
    },
    {
      //pug
      context: `${__dirname}`,
      entry: {
        index: './src/index.pug',
      },
      output: {
        path: `${__dirname}/public`,
        filename: '[name].html',
      },
      module: {
        rules: [
          {
            test: /\.pug$/,
            use: 'pug-loader',
          },
        ],
      },
      plugins: [
        new HtmlWebpackPlugin({
          template: './src/index.pug',
        }),
        new CopyWebpackPlugin(
          //コンパイルしないものをコピー移動
          [
            {
              from: './',
              to: '',
              ignore: [
                '_**',
                '_**/**',
                '.**/**',
                '*.pug',
                '*.js',
                '*.styl',
                'js/modules/',
              ],
            },
          ],
          { context: 'src' }
        ),
        new CopyWebpackPlugin(
          //jsはプラグインのみコンパイルせずに移動
          [
            {
              from: './',
              to: 'js/libs',
            },
          ],
          { context: 'src/js/libs' }
        ),
      ],
    },
  ];

  //pugファイルセット
  for (const [targetName, srcName] of Object.entries(
    getEntriesList({ pug: "html" })
  )) {
    settings[2].plugins.push(
      new HtmlWebpackPlugin({
        filename: targetName,
        template: srcName,
        minify: {
          collapseWhitespace: false,
          removeComments: true,
          removeRedundantAttributes: true,
          removeScriptTypeAttributes: true,
          removeStyleLinkTypeAttributes: true,
          useShortDoctype: true,
        },
      })
    );
  }
  settings[2].plugins.push(
    new HtmlBeautifyPlugin({
      config: {
        html: {
          end_with_newline: true,
          indent_size: 2,
          indent_with_tabs: true,
          indent_inner_html: true,
          preserve_newlines: true,
          unformatted: ["p", "i", "b", "span"]
        }
      }
    })
  );

  return settings;
};

module.exports = app;
