=== Coronavirus COVID-19 Watch ===
Contributors: mmediagroup
Donate link: https://github.com/sponsors/M-Media-Group
Tags: covid, coronavirus, covid-19, Corona Virus, Corona, Virus, coronavirus-covid19
Requires at least: 4.6
Tested up to: 5.7
Requires PHP: 7.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Free live data shown on your dashboard and via shortcode or widget. Show up to date confirmed cases, deaths, or vaccinations in your country or globally.

== Description ==
Free live data shown on your dashboard and via shortcode or widget. Show up to date confirmed cases or deaths in your country or globally. The plugin will also add a helpful link in the top toolbar to quickly get more info. There's also a widget that you can add to your footers or menus.

Check out examples and more on our blog containing the [full documentation](https://blog.mmediagroup.fr/post/coronavirus-covid-19-watch-wordpress-plugin?utm_source=wordpress&utm_medium=covid_plugin&utm_campaign=all&utm_content=readme) and [examples](https://blog.mmediagroup.fr/post/coronavirus-covid-19-watch-wordpress-plugin#examples?utm_source=wordpress&utm_medium=covid_plugin&utm_campaign=all&utm_content=readme).

= Live data shortcode =
The shortcode you can use in your posts will always reference the most up to date number of confirmed cases.

Use the shortcode `[covid-watch]` in your code to show the current number of confirmed cases.

You can pass a country and status, like `[covid-watch country="France" status="confirmed"]` or `[covid-watch country="US" status="deaths"]` to get results only from that country.

= Listing all countries =
To get a table of all countries and their respective current Coronavirus COVID-19 data, try `[covid-watch country="All"]`, which will return a table of all current cases sorted alphabetically.

Pass the `sort` attribute to sort by confirmed cases or deaths, like so: `[covid-watch country="All" sort="confirmed"]`.

Pass the `limit` attribute to limit the amount of results, like so: `[covid-watch country="All" sort="confirmed" limit="3"]`. The `limit` attribute only works in conjunction with the `country="All"` attribute.

= Live map shortcode =
This shortcode will display a map of all cases.

Use the shortcode `[covid-live-map]` in your posts and pages to show the map. You can pass the `sort` parameter to change how countries are coloured. Colour by `population`, `confirmed` (default), `deaths`, or `mortality`.

A full example might look like `[covid-live-map sort="confirmed" height="250px"]`.

= Historical data shortcode =
You can also use the historical shortcode, `[covid-history]` to show a table of historical data.

Just like in the live data shortcode, you can pass attributes to the history shortcode, like so `[covid-history country="France" status="confirmed" limit="3"]`.

= Vaccine data shortcode =
The shortcode you can use in your posts will always reference the most up to date number of vaccinations.

Use the shortcode `[covid-vaccines]` in your code to show the current number of administered vaccines.

You can pass a country and status, like `[covid-vaccines country="France" status="people_vaccinated"]` or `[covid-vaccines country="US" status="people_partially_vaccinated"]` to get results only from that country.

You an also create a table of all country data, as you can with cases. See "Listing all countries" and replace `covid-watch` with `covid-vaccines`.

= About recovered data =
Some similar plugins may provide "recovered" data. We've been informed that a lot of countries are no longer reporting on recovered data because medical teams don't have the time to follow up with each patient. Because of this, Johns Hopkins and other data sources have stopped reporting recovered cases, and so have we. We recommend you don't show recovered cases as no accurate data is available.

== Features ==

1. 100% free
2. Up to date information from Johns Hopkins University
3. Incredibly fast - no performance impact on your site
4. Shortcodes to have self-updating case numbers in your posts
5. Admin dashboard box showing live confirmed cases
6. Widget for confirmed cases that you can add to your footers or menus

== Installation ==

1. Search for the "Coronavirus COVID-19 Watch" plugin in Plugins > Add New.
2. Press "Install Now".
3. When the installation is complete, press "Activate plugin".
4. Go to Settings > Reading to choose the country to show on your admin dashboard.

== Frequently Asked Questions ==

= Is it free? =

Yes, this plugin is free to use.

= Can I show data for my country only? =

Yes, you can pass your country in the shortcode attributes.

= How often is data updated? =

Our data is updated every hour (on average).

= Where is the data from? =

The data is provided from Johns Hopkins University via the M Media API.

= Do you have recovered data? =
We recommend you don't show recovered cases as no accurate data is available.

We've been informed that a lot of countries are no longer reporting on recovered data because medical teams don't have the time to follow up with each patient. Because of this, Johns Hopkins and other data sources have stopped reporting recovered cases, and so have we.

== Screenshots ==

1. Example of the dashboard widget in the admin area.
2. Example of how to use the shortcode to get live Coronavirus cases in your posts.
2. Example of the live map shortcode.

== Changelog ==

= 1.5.0 =
* Added vaccine data

= 1.4.9 =
* Added support for newest version of WordPress, 5.7
* Added donation link

= 1.4.8 =
* Readme updates

= 1.4.7 =
* Added auto-update
* Changed map to static function so it can be referenced in other PHP code

= 1.4.6 =
* Added `limit` attribute to as per user suggestion

= 1.4.5 =
* Moved external map CSS and JS into the plugin as per the guidelines

= 1.4.4 =
* Reordered the map country attributes
* Made attribution off by default
* Made the toolbar menu item off by default

= 1.4.3 =
* Added `height` attribute to the map

= 1.4.2 =
* Removed one map per page limitation

= 1.4.1 =
* Added new `sort` attribute to `[covid-live-map]` shortcode which will change how countries are coloured. Colour by population, confirmed, deaths, or mortality.

= 1.4 =
* Added new `[covid-live-map]` shortcode which will show a live map of cases.

= 1.3 =
* Added new "All" countries parameter to `[covid-watch]` shortcode, `[covid-watch country="All" sort="deaths"]`, which will return a table of all current cases sorted by deaths.

= 1.2.1 =
* Made more strings translatable
* Added settings option to remove toolbar menu
* Changed country settings input to type 'select'

= 1.2 =
* Added history shortcode.

= 1.1 =
* Added settings page. Go to Settings > Reading, then scroll down to the section "COVID-19 Watch"

= 1.0.7 =
* Updates

= 1.0.6 =
* Added base for historical data shortcode

= 1.0.5 =
* Added local caching

= 1.0.4 =
* Fixed bug

= 1.0.3 =
* Made translatable

= 1.0.2 =
* Fixed bug

= 1.0.1 =
* Added attribute `"country"` and `"status"` to shortcode.

= 1.0 =
* Initial commit.

== Upgrade Notice ==
= 1.5.0 =
New shortcode - vaccine data! Use [covid-vaccines] for up to date vaccination data.

== Privacy policy ==
This plugin uses data from Johns Hopkins University and ARCGIS, provided via API thanks to M Media (mmediagroup.fr). Here's a link to [M Media](https://mmediagroup.fr/), the API provider. Access the API directly here [M Media](https://covid-api.mmediagroup.fr/v1/cases). The M Media API privacy policy is [here](https://mmediagroup.fr/privacy-policy), the terms of use [here](https://mmediagroup.fr/terms-and-conditions) and the JHU Data Usage policy is [here](https://github.com/CSSEGISandData/COVID-19).
