Mix.manifest.refresh = _ => void 0
const mix = require('laravel-mix');
require('laravel-mix-copy-watched');

mix
  .setPublicPath('./dist')
  .browserSync('formality.local');

mix
  .sass('assets/styles/public.scss', 'styles/formality-public.css')
  .sass('assets/styles/admin.scss', 'styles/formality-admin.css')

mix
  .js('assets/scripts/public.js', 'scripts/formality-public.js')
  .js('assets/scripts/editor.js', 'scripts/formality-editor.js')
  .js('assets/scripts/admin.js', 'scripts/formality-admin.js')

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
    terser: { extractComments: false }
  })
  .sourceMaps(false, 'source-map')
  .version();
