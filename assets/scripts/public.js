// import external dependencies
import 'jquery';

// Import everything from autoload
import "./autoload/**/*"

// import local dependencies
import init from './core/init';

// Load Events
jQuery(document).ready(() => init());