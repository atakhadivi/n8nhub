# n8n WordPress Integration

A WordPress plugin that provides seamless integration between WordPress and [n8n](https://n8n.io/) workflow automation platform.

## Description

This plugin creates a bidirectional connection between WordPress and n8n, allowing you to:

1. **Trigger n8n workflows from WordPress events** - Send WordPress data to n8n when specific events occur
2. **Execute WordPress actions from n8n workflows** - Create or update WordPress content from n8n

## Features

### WordPress to n8n (Triggers)

Send data to n8n when these WordPress events occur:

- **Post Save**: When a post is created or updated
- **User Register**: When a new user registers
- **Comment Post**: When a new comment is posted
- **WooCommerce New Order**: When a new order is created (requires WooCommerce)

### n8n to WordPress (Actions)

Perform these WordPress actions from n8n:

- **Create Post**: Create new posts with title, content, status, etc.
- **Update Post**: Update existing posts
- **Delete Post**: Delete posts
- **Create User**: Create new users
- **Update User**: Update existing users
- **Custom Action**: Extensible hook for custom actions

## Installation

1. Download the plugin zip file
2. Go to WordPress Admin > Plugins > Add New
3. Click "Upload Plugin" and select the zip file
4. Activate the plugin

## Configuration

### 1. Settings

1. Go to WordPress Admin > n8n Integration > Settings
2. Enter your n8n URL (e.g., http://localhost:5678 or your hosted n8n instance)
3. Generate or enter an API key
4. Optionally, enable Debug Mode to log webhook requests for troubleshooting
5. Save settings and test the connection

### 2. Triggers (WordPress to n8n)

1. Go to WordPress Admin > n8n Integration > Triggers
2. Enable the triggers you want to use
3. For each trigger, enter the webhook URL from your n8n workflow
4. Optionally, add a name and description for each webhook URL for better organization
5. Save the trigger settings

### 3. Actions (n8n to WordPress)

1. Go to WordPress Admin > n8n Integration > Actions
2. Note the webhook URL and API key for use in your n8n workflows

## Usage

### Setting up a WordPress to n8n Trigger

1. In n8n, create a new workflow
2. Add a "Webhook" node as the trigger
3. Copy the webhook URL
4. In WordPress, go to n8n Integration > Triggers
5. Enable the desired trigger and paste the webhook URL
6. Optionally, add a name and description for the webhook URL
7. Save the settings

### Using Debug Mode and Logs

1. Go to WordPress Admin > n8n Integration > Settings
2. Enable the Debug Mode checkbox
3. Save settings
4. When debug mode is enabled, all webhook requests will be logged
5. Logs include timestamp, URL, data sent, success status, and response
6. The plugin stores up to 100 log entries
7. View logs by going to WordPress Admin > n8n Integration > Logs
8. Filter logs by type and status
9. Click "View" to see detailed information about each log entry
10. Use the "Clear Logs" button to remove all logs

### Setting up an n8n to WordPress Action

1. In n8n, create a workflow with an "HTTP Request" node
2. Configure the HTTP Request node:
   - Method: POST
   - URL: Your WordPress webhook URL (found in n8n Integration > Actions)
   - Headers:
     - X-N8N-API-KEY: Your API key
     - Content-Type: application/json
   - Body: JSON with action and parameters (examples in the Actions page)

## Example: Creating a Post from n8n

```json
{
  "action": "create_post",
  "title": "Post Title",
  "content": "Post content goes here.",
  "status": "publish",
  "post_type": "post",
  "meta": {
    "custom_field": "custom value"
  },
  "categories": [1, 2],
  "tags": ["tag1", "tag2"]
}
```

## Example: Updating a User from n8n

```json
{
  "action": "update_user",
  "user_id": 1,
  "email": "newemail@example.com",
  "first_name": "New First Name",
  "last_name": "New Last Name",
  "meta": {
    "phone": "123-456-7890"
  }
}
```

## Logs

You can monitor all integration activities in the Logs section (n8n Integration > Logs).

## Security

- All requests from n8n to WordPress are authenticated using the API key
- All webhook URLs include a security token
- Admin capabilities are required for sensitive operations

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- n8n instance (self-hosted or cloud)

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the GPL v2 or later.

## Credits

Developed by [Your Name/Company]