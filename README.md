# Data Sync

=== Data Sync ===
Contributors: nazmulhosen
Tags: api, data, sync
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

A WordPress plugin that fetches data from an external API, saves it, and displays the results in the WordPress admin panel.

## Features

- Securely store and manage API tokens
- Fetch data from external APIs with proper error handling
- Display data in a paginated table format
- View detailed information for each item
- Simple and intuitive user interface

## Installation

1. Download the plugin zip file
2. Go to WordPress admin > Plugins > Add New
3. Click "Upload Plugin" and select the zip file
4. Activate the plugin

## Configuration

1. Go to Settings > Data Sync
2. Enter the base URL of your API   
3. Enter your API token (if required by the API)
4. Configure items per page for the data table
5. Save your settings

## Usage

1. Navigate to Settings > Data Sync
2. Click on the "Data" tab
3. Click the "Sync Now" button to fetch data from the API
4. View the data in the table
5. Click "View Details" to see more information about an item

## API Integration

This plugin is designed to work with any RESTful API that returns JSON data. By default, it's configured to work with the JSONPlaceholder API (https://jsonplaceholder.typicode.com) for demonstration purposes.

The API integration works as follows:

1. The plugin sends a request to the API endpoint
2. If an API token is provided, it's included in the request headers
3. The response is validated and parsed
4. Data is stored in a custom database table
5. The data is displayed in the admin interface

## Data Storage

The plugin creates a custom database table (`wp_data_sync_items`) to store the fetched data.

- Better performance 
- More flexibility for future enhancements

## Security Considerations

- All user inputs are sanitized
- All outputs are escaped
- API tokens are stored securely in the WordPress options table
- Proper nonce verification for all form submissions
- Capability checks to ensure only authorized users can access the plugin

## Challenges and Solutions

when sync now button is clicked, it takes a long time to fetch data from the API.And Suddently it stops working.Then I try to found the issue but I can't find any solution. Then i Use Ai to check my code where i could did the wrong then I found that I was mispeled the filename And My errore debugger didn't work. I appologize for that.

## License

This plugin is licensed under the GPL v3 or later.

## Credits

- Created by Nazmul Hosen
- Uses the WordPress Plugin Boilerplate for structure(wppb)
