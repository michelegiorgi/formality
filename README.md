# [Formality](https://formality.dev)

Forms made simple. Wordpress all-in-one form plugin

## Requirements

* [WordPress](https://wordpress.org/) >= 5.4

## Plugin setup

* Create a `formality` folder in your `wp-content/plugins` dir
* Copy the entire repository into your `formality` folder
* Activate it from wp-admin (like any other plugin)

## Assets build process

Based on [Sage](https://roots.io/sage/) workflow/build process.
Make sure all dependencies have been installed before moving on:

* [Node.js](http://nodejs.org/) >= 8.0.0
* [Yarn](https://yarnpkg.com/en/docs/install)
* [WP-CLI](https://wp-cli.org)

### Setup

* Run `yarn` from the theme directory to install dependencies
* Update `assets/config.json` settings:
  * `devUrl` should reflect your local development hostname

### Build commands

* `yarn start` — Compile assets when file changes are made, start Browsersync session
* `yarn build` — Compile and optimize the files in your assets directory
* `yarn build:production` — Compile assets for production
* `yarn i18n-pot` — Scans PHP and JavaScript files for translatable strings and create POT language file.
* `yarn i18n-json` — Extract JavaScript strings from PO files and add them to individual JSON files.

## Documentation

Soon

## Contributing

Contributions are welcome from everyone.