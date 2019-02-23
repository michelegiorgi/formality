# Ultimate Fields

Ultimate Fields is a plugin, that allows you to add custom fields in many places throughout the WordPress administration area, supporting a total of more than 30 field types, including repeaters, layouts and etc.

## Installation

### As a standard plugin
Ultimate Fields is available in the WordPress.org plugins repository, which means that you can install it as a standard plugin through the dashboard.

### As a Composer package

Please read the [As a Composer package](https://www.ultimate-fields.com/docs/quick-start/installation/#as-a-composer-package) section of the documentation.

### As a repository

If you'd like to contribute back to the plugin, you can clone this repository as a plugin, but you will need to manually compile the stylesheets, which is done through NPM:

```sh
cd /PATH-TO-YOUR-WEBSITE/wp-content/plugins
git clone git@github.com:RadoslavGeorgiev/ultimate-fields.git
cd ultimate-fields
npm install
npm run build
```

Once you do so, the plugin will be fully compiled and ready to use. During development you might want to execute `npm run watch` in order for your changes to be applied immediately.

## Links

- [Plugin homepage](https://www.ultimate-fields.com/)
- [Docs](https://www.ultimate-fields.com/docs/)
- [Support](https://www.ultimate-fields.com/support/)
- [Demo](https://www.ultimate-fields.com/demo/)

### Related repositories

- [Ultimate Fields: Showcase Theme](https://github.com/RadoslavGeorgiev/ultimate-fields-showcase-theme)
- [Ultimate Post Types](https://github.com/RadoslavGeorgiev/ultimate-post-types)
