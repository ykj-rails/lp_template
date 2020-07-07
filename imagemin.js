'use strict';
const imagemin = require('imagemin-keep-folder');
const imageminMozjpeg = require('imagemin-mozjpeg');
const imageminPngquant = require('imagemin-pngquant');
const imageminGifsicle = require('imagemin-gifsicle');
const imageminSvgo = require('imagemin-svgo');

imagemin(['src/img/**/*.{jpg,png,gif,svg}'], {
  plugins: [
    imageminMozjpeg({ quality: 80 }),
    imageminPngquant({
      quality: [0.65, 0.8],
    }),
    imageminGifsicle(),
    imageminSvgo(),
  ],
  replaceOutputDir: output => {
    return output.replace(/img\//, '../public/img/');
  },
}).then(() => {
  // eslint-disable-next-line no-console
  console.log('画像が最適化されました');
});
