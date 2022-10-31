const mix = require('laravel-mix');
Mix.manifest.refresh = _ => void 0
const devUrl = 'formality.local';
require('laravel-mix-copy-watched');
require('laravel-mix-banner');

mix
  .setPublicPath('./dist')
  .browserSync(devUrl);

mix
  .sass('assets/styles/public/index.scss', 'styles/formality-public.css')
  .sass('assets/styles/admin/index.scss', 'styles/formality-admin.css');

mix
  .js('assets/scripts/public/index.js', 'scripts/formality-public.js')
  .js('assets/scripts/editor/index.js', 'scripts/formality-editor.js')
  .js('assets/scripts/admin/index.js', 'scripts/formality-admin.js')
  .banner({ banner: 'Formality v1.5.6' });

mix
  .copyWatched('assets/images/admin/**', 'dist/images/admin')
  .copyWatched('assets/images/templates.json', 'dist/images')
  .copyWatched('assets/images/logo.svg', 'dist/images')
  .copyWatched([
      'assets/fonts/*.woff',
      'assets/fonts/*.woff2'
    ], 'dist/fonts');

mix
  .webpackConfig({
    externals: {
      wp: 'wp',
      react: 'React',
      'react-dom': 'ReactDOM',
      lodash: 'lodash',
    },
  })
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
