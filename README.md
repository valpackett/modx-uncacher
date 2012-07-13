# Uncacher for MODX Revolution
Do you have a *big* MODX website? Like, thousands of resources and lots of stuff in the templates? Are your pages taking several seconds to render when they aren't cached? When search engines start crawling a site like that when it's not fully cached, it may become extremely slow. So **you can't afford cleaning the whole cache every time you save a resource**.

This module provides a different strategy: when you save a resource (or its pub_date time comes), it clears and regenerates the cache of this resource, its parents and the index page.

This module was extracted from a live high-traffic website of a Russian magazine.

## Installation
First, download and install it with the MODX package manager (System, Package Management) like any other package.

Second, uncheck the "Empty Cache" flag on every resource you edit.

If you don't use the publishing-at-a-specified-time functionality, you can stop reading right there.

If you do need it, set [CronManager](http://rtfm.modx.com/display/ADDON/CronManager) up and add a cron job calling the `uncacheRecent` snippet every `n` minutes and with options `minutes: n`, where `n` is how often to check. Five minutes is a good choice, but you can set it to one minute if you need that much precision.
![Screenshot](http://mfwb.us/bDRC+)

## Hacking
Uncacher is tested with [Behat](http://behat.org) and built like any other MODX package (`php _build/build.transport.php`).

By default, it assumes you have MODX installed in ~/Sites; if you don't, set the `MODX_PATH` environment variable to the path to your MODX installation.