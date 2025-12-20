=== Import Luma Events ===
Contributors: mazespacestudios
Tags: events, luma, calendar, import, sync
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Import and sync events from Luma (lu.ma) into WordPress as custom posts with automatic calendar synchronization.

== Description ==

Import Luma Events is a powerful WordPress plugin that seamlessly integrates your Luma calendar with your WordPress website. Import all your events from Luma with a single click and keep them automatically synchronized.

= Features =

* **Easy Setup Wizard** - Get started in minutes with our guided setup process
* **One-Click Import** - Import all events from your Luma calendar instantly
* **Automatic Sync** - Keep events up-to-date with automatic scheduled imports (hourly, twice daily, or daily)
* **Beautiful Templates** - Professional default templates with responsive design and Google Maps integration
* **Virtual Events Support** - Special handling for virtual events with meeting links
* **Custom Post Type** - Events are stored as a dedicated post type with full WordPress editor support
* **Shortcode Support** - Display events anywhere with customizable shortcodes
* **Theme Override** - Customize templates by copying them to your theme
* **Event Images** - Automatically imports and syncs event cover images
* **Timezone Support** - Proper handling of event timezones
* **Location Data** - Full venue information with GPS coordinates and Google Maps
* **Missing Images Tool** - Re-download images that failed during initial import

= Perfect For =

* Event organizers using Luma for event management
* Communities running regular meetups and networking events
* Organizations that want to display their Luma calendar on their WordPress site
* Anyone who needs automatic event synchronization from Luma to WordPress

= How It Works =

1. Install and activate the plugin
2. Enter your Luma API credentials (API key and Calendar ID)
3. Click "Import Events" to sync your calendar
4. Events appear as custom posts with all details, images, and locations
5. Enable automatic sync to keep everything up-to-date

= Shortcode Examples =

Display a list of upcoming events:
`[luma_events]`

Show 6 events in a 3-column grid:
`[luma_events posts_per_page="6" columns="3"]`

Display past events in reverse order:
`[luma_events show_past="true" order="DESC"]`

= Developer Friendly =

* Override templates by copying to your theme's `luma-events/` folder
* Access to all event meta data in templates
* Well-documented code following WordPress coding standards
* Hooks and filters for extensibility
* Open source and actively maintained

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Navigate to Plugins > Add New
3. Search for "Import Luma Events"
4. Click "Install Now" and then "Activate"
5. Follow the setup wizard to configure your Luma API credentials

= Manual Installation =

1. Download the plugin zip file
2. Extract the contents
3. Upload the `import-luma-events` folder to `/wp-content/plugins/`
4. Activate the plugin through the 'Plugins' menu in WordPress
5. Go to Luma Events > Settings to configure

= After Installation =

1. Get your Luma API credentials:
   - Log in to your Luma account
   - Go to Settings → Developer
   - Copy your API key and Calendar ID

2. Configure the plugin:
   - In WordPress, go to Luma Events → Settings
   - Enter your API key and Calendar ID
   - Optionally enable automatic sync

3. Import events:
   - Go to Luma Events → Import Events
   - Click "Import Events Now"
   - Wait for the import to complete

== Frequently Asked Questions ==

= Where do I get my Luma API credentials? =

Log in to your Luma account, navigate to Settings → Developer, and you'll find your API key and Calendar ID there.

= How often are events automatically synced? =

You can choose from three frequencies: hourly, twice daily, or daily. Configure this in Luma Events → Settings.

= Can I customize the event templates? =

Yes! Copy the template files from `plugins/import-luma-events/templates/` to your theme's `luma-events/` folder and customize them as needed.

= What happens to existing events when I re-import? =

Existing events are updated with the latest information from Luma. Events are matched by their unique Luma event ID, so you won't get duplicates.

= Do I need a Luma account? =

Yes, you need an active Luma account and API access to use this plugin.

= Will this work with my theme? =

Yes! The plugin includes professional default templates that work with any WordPress theme. You can also customize them to match your theme's design.

= What data is imported from Luma? =

The plugin imports:
- Event title and description
- Event dates, times, and timezone
- Cover images
- Venue information (name, address, GPS coordinates)
- Virtual event meeting links
- Organizer information
- Registration URL

= Can I display only certain events? =

Yes, you can use shortcode parameters to filter events. For example, limit the number shown, change the order, or show/hide past events.

= What if some event images fail to download? =

Go to Luma Events → Fix Missing Images and run the tool to re-attempt downloading any missing images.

== Screenshots ==

1. Setup wizard - Get started in minutes
2. Import events page with one-click import
3. Beautiful event archive with card grid layout
4. Single event page with Google Maps integration
5. Settings page for API configuration
6. Events list in WordPress admin

== Changelog ==

= 1.0.0 =
* Initial release
* One-click event import from Luma
* Automatic scheduled sync (hourly, twice daily, daily)
* Setup wizard for first-time configuration
* Professional default templates with responsive design
* Shortcode support with customizable parameters
* Google Maps integration for physical events
* Virtual event support with placeholder image
* Missing images recovery tool
* Full theme override support
* WordPress coding standards compliant
* Comprehensive documentation

== Upgrade Notice ==

= 1.0.0 =
Initial release of Import Luma Events plugin.

== Support ==

For support, bug reports, or feature requests, please visit:
https://github.com/sambatesdesign/Luma-Events/issues

== Credits ==

Developed by MazeSpace Studios LTD
https://mazespacestudios.com
