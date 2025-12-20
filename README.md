# Import Luma Events

A WordPress plugin to import and sync events from Luma (lu.ma) into WordPress as a custom post type.

## Description

Import Luma Events allows you to automatically synchronize your Luma calendar events with your WordPress website. Events are imported as a custom post type with full support for dates, locations, virtual events, categories, tags, and featured images.

### Features

- ✅ **Setup Wizard** - Guided first-time setup with automatic page creation
- ✅ **Works with Any Theme** - Includes default templates, no theme customization required
- ✅ **Automatic Calendar Sync** - Schedule automatic imports (hourly, daily, or twice daily)
- ✅ **Manual Import** - Import events on-demand with one click
- ✅ **Event Update Detection** - Automatically updates existing events when they change on Luma
- ✅ **Custom Post Type** - Events stored as `luma_events` with dedicated taxonomies
- ✅ **Shortcode Support** - Display events anywhere with `[luma_events]` shortcode
- ✅ **Virtual Event Support** - Detects and displays virtual events (Zoom, etc.)
- ✅ **Location Data** - Full address and Google Maps integration for in-person events
- ✅ **Featured Images** - Automatically imports event cover images
- ✅ **Admin Interface** - Easy-to-use settings and import pages
- ✅ **Developer Friendly** - Clean, object-oriented code following WordPress standards

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- A Luma Plus subscription (required for API access)
- Luma API key

## Installation

1. Upload the `import-luma-events` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. You'll be automatically redirected to the **Setup Wizard** on first activation
4. Follow the wizard to configure your API credentials and import your first events

**Note:** The plugin automatically creates an "Events" page with the `[luma_events]` shortcode when activated.

## First-Time Setup

On first activation, you'll see a friendly setup wizard that guides you through:

1. **Welcome Screen** - See what the plugin can do and confirm your Events page was created
2. **API Configuration** - Enter your Luma API key and calendar ID
3. **First Import** - Automatically imports your events when you complete the wizard

The wizard only appears once, but you can always change settings later in **Luma Events > Settings**.

## Configuration

### Getting Your Luma API Key

1. Log in to your Luma account
2. Navigate to your account settings
3. Generate a new API key (requires Luma Plus subscription)
4. Copy the API key (starts with `secret-`)

### Getting Your Calendar ID

1. Visit your Luma calendar page
2. Copy the calendar ID from the URL (starts with `cal-`)
3. Example: `https://lu.ma/calendar/cal-XXXXXXXXXXXXX` → Calendar ID is `cal-XXXXXXXXXXXXX`

### Plugin Settings

Navigate to **Luma Events > Settings**:

1. **Luma API Key** - Enter your API key
2. **Calendar ID** - Enter your calendar ID
3. **Enable Automatic Sync** - Turn on/off scheduled syncing
4. **Sync Frequency** - Choose how often to sync (hourly, twice daily, or daily)

Click **Test Connection** to verify your API credentials are working.

## Usage

### Manual Import

1. Go to **Luma Events > Import Events**
2. Click **Import Events Now**
3. The plugin will fetch all events from your Luma calendar

### Automatic Sync

1. Enable automatic sync in **Luma Events > Settings**
2. Choose your preferred sync frequency
3. Events will automatically sync in the background

### Displaying Events

#### Using the Shortcode

```
[luma_events posts_per_page="10" past_events="no" columns="3"]
```

**Shortcode Parameters:**

- `posts_per_page` - Number of events to display (default: 10)
- `category` - Filter by category slug (comma-separated)
- `tag` - Filter by tag slug (comma-separated)
- `past_events` - Show past events: "yes", "no", or "all" (default: "no")
- `order` - Sort order: "ASC" or "DESC" (default: "ASC")
- `orderby` - Sort by: "event_start_date", "title", etc. (default: "event_start_date")
- `columns` - Number of columns: 1-4 (default: 3)

**Examples:**

```
<!-- Show upcoming events only -->
[luma_events]

<!-- Show 20 past events -->
[luma_events posts_per_page="20" past_events="yes" order="DESC"]

<!-- Filter by category -->
[luma_events category="networking,workshops"]

<!-- Show events in 2 columns -->
[luma_events columns="2"]
```

#### Using Template Files

The plugin includes default templates that work with any WordPress theme out of the box. No additional setup is required.

**Default Templates Included:**
- Single event page (`/luma-events/`)
- Events archive/list page (`/luma-events/archive/`)
- Basic responsive styling

**Customizing Templates:**

You can override the default templates by creating custom templates in your theme:

**Option 1: Create in theme's luma-events folder (recommended)**
```
your-theme/
└── luma-events/
    ├── single-luma_events.php
    ├── archive-luma_events.php
    ├── taxonomy-luma_category.php
    ├── taxonomy-luma_tag.php
    └── events.css (optional custom styles)
```

**Option 2: Create in theme root**
```
your-theme/
├── single-luma_events.php
├── archive-luma_events.php
├── taxonomy-luma_category.php
└── taxonomy-luma_tag.php
```

**Template Priority:**
1. Theme's `luma-events/` folder templates (checked first)
2. Theme root templates
3. Plugin's default templates (fallback)

**Styling:**
- Default styles are automatically loaded from the plugin
- Add `luma-events/events.css` to your theme to use custom styles
- Plugin styles won't load if theme provides custom CSS

### Querying Events

```php
$args = array(
    'post_type' => 'luma_events',
    'posts_per_page' => 10,
    'meta_key' => 'event_start_date',
    'orderby' => 'meta_value',
    'order' => 'ASC',
    'meta_query' => array(
        array(
            'key' => 'start_ts',
            'value' => current_time('timestamp'),
            'compare' => '>=',
            'type' => 'NUMERIC'
        )
    )
);

$events = new WP_Query($args);
```

## Event Meta Fields

Each event has the following meta fields:

### Core Fields
- `luma_event_id` - Unique Luma event ID
- `luma_event_url` - Event URL on Luma
- `event_start_date` - Start date/time (Y-m-d H:i:s format)
- `event_end_date` - End date/time
- `start_ts` - Start timestamp (for sorting)
- `end_ts` - End timestamp
- `event_timezone` - Event timezone (e.g., "America/Los_Angeles")
- `duration` - ISO 8601 duration string

### Location Fields (In-Person Events)
- `venue_name` - Venue name
- `venue_address` - Full address
- `venue_city` - City
- `venue_state` - State/Region
- `venue_country` - Country
- `venue_lat` - Latitude
- `venue_lon` - Longitude
- `google_place_id` - Google Maps Place ID

### Virtual Event Fields
- `meeting_url` - Virtual meeting URL
- `zoom_meeting_url` - Zoom meeting URL

### Organizer Fields
- `organizer_name` - Host/organizer name
- `organizer_email` - Host email
- `organizer_avatar` - Host avatar URL

### Other Fields
- `cover_url` - Event cover image URL
- `visibility` - Event visibility (public/private)
- `luma_origin` - Always "luma"
- `luma_last_synced` - Last sync timestamp

## Taxonomies

- `luma_category` - Hierarchical categories
- `luma_tag` - Non-hierarchical tags

## Import Behavior

- **New Events** - Created as new WordPress posts
- **Existing Events** - Updated based on `luma_event_id` match
- **Deleted Events** - Not automatically deleted (manual cleanup required)
- **Event Images** - Downloaded and set as featured image
- **Event Status** - Published by default

## Hooks and Filters

### Actions

```php
// After an event is imported/updated
do_action('ile_event_imported', $post_id, $event_data, $status);

// After sync completes
do_action('ile_sync_completed', $results);
```

### Filters

```php
// Modify event data before import
apply_filters('ile_event_data', $event_data, $luma_event);

// Modify post data before creation
apply_filters('ile_post_data', $post_data, $event_data);

// Modify import options
apply_filters('ile_import_options', $options);
```

## Troubleshooting

### API Connection Issues

1. Verify your API key is correct
2. Ensure you have a Luma Plus subscription
3. Check that your calendar ID is correct
4. Use the "Test Connection" button in settings

### Events Not Importing

1. Check the "Last Sync Status" on the import page
2. Verify your calendar has events
3. Check WordPress error logs
4. Ensure your server can make outbound HTTP requests

### Rate Limits

Luma API limits:
- GET endpoints: 500 requests per 5 minutes
- POST endpoints: 100 requests per 5 minutes

If you hit rate limits, reduce sync frequency or wait before retrying.

## Development

### File Structure

```
import-luma-events/
├── import-luma-events.php              # Main plugin file
├── README.md                            # This file
├── includes/
│   ├── class-import-luma-events.php            # Main plugin class
│   ├── class-import-luma-events-cpt.php        # Custom post type
│   ├── class-import-luma-events-luma-api.php   # Luma API integration
│   ├── class-import-luma-events-import-manager.php  # Import logic
│   ├── class-import-luma-events-admin.php      # Admin interface
│   ├── class-import-luma-events-cron.php       # Scheduled sync
│   └── class-import-luma-events-shortcode.php  # Shortcode rendering
├── admin/
│   ├── partials/
│   │   └── import-page.php             # Import page template
│   └── js/
│       └── admin.js                    # Admin JavaScript
└── templates/                           # (Reserved for future use)
```

### Contributing

This plugin is designed to be generic and reusable. Contributions are welcome!

## Changelog

### 1.0.0
- Initial release
- Setup wizard for first-time configuration
- Automatic Events page creation
- Default templates (works with any theme)
- Theme template override support
- Automatic calendar sync
- Manual import functionality
- Event update detection
- Shortcode support
- Virtual event support
- Admin settings page
- WP Cron integration
- Fix missing images utility

## License

GPL-2.0+

## Author

Built with ❤️ for the WordPress community.

## Support

For issues, questions, or feature requests, please open an issue on GitHub.
