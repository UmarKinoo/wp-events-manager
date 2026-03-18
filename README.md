# WP Events Manager

A custom WordPress plugin that adds an Events post type with RSVP, filtering, email notifications, and REST API support.

Built as part of a technical assessment.

## Requirements

- WordPress 6.0+
- PHP 8.0+

## Installation

1. Clone into your `wp-content/plugins/` directory:
```bash
git clone https://github.com/UmarKinoo/wp-events-manager.git
```

2. Activate via the Plugins screen or WP-CLI:
```bash
wp plugin activate wp-events-manager
```

3. Go to **Settings → Permalinks** and hit Save Changes to flush rewrite rules.

## What it does

**Events CPT** — registers an `event` post type with start date, end date, location and capacity fields. Events are organised by `event_type` taxonomy.

**Front-end** — archive at `/events/` with filtering by event type, date range and keyword search. Single event page shows full details and the RSVP box.

**Shortcode** — embed events anywhere with `[events_list]`. Supports `count`, `type`, `order` and `orderby` attributes.

**RSVP** — logged-in users can RSVP and cancel. Capacity is enforced server-side. Admin can see the attendee list on the event edit screen.

**Emails** — admin gets notified on new event publish. RSVPed users get notified when an event is updated. Users get confirmation and cancellation emails.

**REST API** — events available at `/wp-json/wp/v2/event` with custom meta fields (`event_date`, `event_location`, `event_capacity`) included in the response.

**Caching** — shortcode queries are cached using transients. Cache clears automatically when an event is saved.

## RSVP note

RSVP requires users to be logged in. Users without an account are redirected to the WordPress login page. This keeps attendee data tied to user accounts and avoids the complexity of guest registration and email verification.

## Running tests

Install the test suite (run from your regular terminal, not Local shell):
```bash
bash bin/install-wp-tests.sh wordpress_test root root localhost latest
```

Install dependencies:
```bash
composer install
composer require --dev phpunit/phpunit:^9 yoast/phpunit-polyfills:^1.0
```

Run:
```bash
./vendor/bin/phpunit
```

Expected: `OK (7 tests, 10 assertions)`

## Translations

All strings use the `wp-events-manager` text domain. A `.pot` file is included in `languages/` for translators.

## Sample Data

To install sample events for testing, run:
```bash
wp eval-file sample-data.php
```

This creates 3 sample events with different event types, dates and locations.


## Author

Umar Kinoo