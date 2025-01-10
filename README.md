# Kalender Hijriyah

Kalender Hijriyah is a WordPress plugin for displaying Islamic dates using a Hijri calendar (specially use KHGT method). It includes additional features such as displaying Islamic holidays and special dates with contextual information.

## Description

This plugin provides a comprehensive and interactive Hijri calendar for WordPress websites, allowing users to view Islamic dates and holidays with additional contextual information.

## Features

- Display Hijri calendar with Islamic dates
- Highlight special Islamic holidays and events
- Tooltip information for significant dates
- Responsive design using CSS Grid
- Print functionality for the calendar (not implement yet)

## Installation

### Prerequisites

- WordPress 5.0 or higher
- PHP 7.0 or higher

### Steps

1. **Download the Plugin:**
   - Download the plugin files from the [GitHub repository](https://github.com/your-repo/kalender-hijriyah) or the provided zip file.

2. **Upload the Plugin:**
   - Go to your WordPress admin dashboard.
   - Navigate to `Plugins` > `Add New`.
   - Click on the `Upload Plugin` button.
   - Choose the downloaded zip file and click `Install Now`.

3. **Activate the Plugin:**
   - After the installation is complete, click on the `Activate Plugin` button.

4. **Include Year Data:**
   - Ensure that the `tahun.php` file is included in the plugin directory. This file should contain the Hijri calendar data.

5. **Add the Shortcode:**
   - To display the Hijri calendar on a page or post, use the shortcode `[khgt_calendar "1446"]`.

## Usage

1. **Adding the Calendar to a Page or Post:**
   - Edit the page or post where you want to display the calendar.
   - Add the shortcode `[khgt_calendar "1446"]` to the content area.

2. **Customizing the Calendar:**
   - The calendar can be customized by editing the `style.css` file located in the plugin directory.

## Changelog

### Version 1.0
- Initial release with basic Hijri calendar functionality.

## Author

- **Name:** acepby
- **Website:** [https://r3volt.xyz](https://r3volt.xyz)

## License

This plugin is licensed under the GPL2 license.