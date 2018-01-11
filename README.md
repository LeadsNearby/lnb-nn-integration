# LeadsNearby Nearby Now Integration (For Wordpress)

Wordpress plugin to efficiently interface with Nearby Now API

This plugin is designed to use the server to get and cache data from the Nearby Now API and store it in the database rather than using the default setup.

**Note:** This plugin relies on the <a href="https://wordpress.org/plugins/nearby-now/" target="blank">Nearby Now Wordpress plugin</a> for the api key option. Plugin must be installed and API key set for this plugin to work.

### This accomplishes two things:
* Makes user experience better by making it faster. Page load is quicker and user doesn't have to wait for Nearby Now data to populate.
* Data is more easily manipulated and we're not bound to the default Nearby Now setup.

### What's Included

#### NN_API Class

Publicly accessible functions:

* **get_data()** - static function which returns an array of Nearby Now data
* **clear_cache()** - static function which resets Nearby Now data transient in database

#### Nearby Now "Stynamic" Widget

Shortcode: **[static-nn-widget]**

A customized version of the default Nearby Now widget which uses the NN_API class.
