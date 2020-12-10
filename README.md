[![Formality](https://formality.dev/download/markrepo.svg)](https://formality.dev)  
#### [New plugin website](https://formality.dev) &nbsp;|&nbsp; [Wordpress.org page](https://wordpress.org/plugins/formality)  
Designless, multistep, conversational, secure, all-in-one WordPress forms plugin.  
<br/>
  
## Welcome

Welcome to the official Formality repository on GitHub. Here you can browse the source, look at open issues and keep track of development.
If you are not a developer, please download the latest release of the Formality plugin from the [WordPress.org plugins repository](https://wordpress.org/plugins/formality) or directly install it from the plugins page in your WordPress admin panel.

## Requirements

* [WordPress](https://wordpress.org/) >= 5.5

## Plugin setup

* Create a `formality` folder in your `wp-content/plugins` dir
* Copy the entire repository into your `formality` folder
* Activate it from wp-admin (like any other plugin)

## Assets build process

Based on [Sage](https://roots.io/sage/) workflow/build process.  
Make sure all dependencies have been installed before moving on:

* [Node.js](http://nodejs.org/) >= 12.0.0
* [Yarn](https://yarnpkg.com/en/docs/install)
* [WP-CLI](https://wp-cli.org)

### Setup

* Run `yarn` from the plugin directory to install dependencies
* Update `webpack.mix.js` settings:
  * `devUrl` should reflect your local development hostname

### Build commands

* `yarn start` — Compile assets when file changes are made, start Browsersync session
* `yarn build` — Compile and optimize the files in your assets directory
* `yarn build:production` — Compile assets for production
* `yarn download` — Create a lightweight plugin copy that includes only executable files and production assets
* `yarn release` — Create a lightweight plugin copy ready for WordPress.org Plugin SVN Repository
* `yarn i18n:pot` — Scans PHP and JavaScript files for translatable strings and create POT language file.
* `yarn i18n:json` — Extract JavaScript strings from PO files and add them to individual JSON files.

## Documentation

Soon

## Contributing

Contributions are welcome from everyone.
