Mix.manifest.refresh = _ => void 0
const mix = require('laravel-mix');
const devUrl = 'formality.local';
require('laravel-mix-copy-watched');
require('laravel-mix-banner');

mix
  .setPublicPath('./dist')
  .browserSync(devUrl);

mix
  .sass('assets/styles/public.scss', 'styles/formality-public.css')
  .sass('assets/styles/admin.scss', 'styles/formality-admin.css');

mix
  .js('assets/scripts/public.js', 'scripts/formality-public.js')
  .js('assets/scripts/editor.js', 'scripts/formality-editor.js')
  .js('assets/scripts/admin.js', 'scripts/formality-admin.js')
  .banner({ banner: 'Formality v1.3.6' });

mix
  .copyWatched('assets/images/admin/**', 'dist/images/admin')
  .copyWatched('assets/images/templates.json', 'dist/images')
  .copyWatched([
      'assets/fonts/*.otf',
      'assets/fonts/*.ttf',
      'assets/fonts/*.woff',
      'assets/fonts/*.woff2'
    ], 'dist/fonts');

mix
  .webpackConfig({
    externals: {
      wp: 'wp',
      react: 'React',
      jquery: 'jQuery',
      'react-dom': 'ReactDOM',
      lodash: 'lodash',
    },
  })
  .autoload({ jquery: ['$', 'window.jQuery'] })
  .options({
    processCssUrls: false,
    terser: {
      terserOptions: {
        mangle: { reserved: ['__'] },
        output: {
          comments ({}, { value }) { return value.startsWith("! Formality v") }
        }
      },
      extractComments: false
    }
  })
  .sourceMaps(false, 'source-map')
  .version();
