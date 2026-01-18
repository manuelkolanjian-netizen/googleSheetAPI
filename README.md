# Google Sheets API Backend

A minimal, clean PHP backend that integrates with Google Sheets API v4 to retrieve spreadsheet data via HTTP requests.

## Features

- ✅ Plain PHP 8.x (no framework dependencies)
- ✅ Google Sheets API v4 integration
- ✅ API key authentication (no OAuth required)
- ✅ Input validation and sanitization
- ✅ Proper HTTP status codes
- ✅ JSON response format
- ✅ Secure API key handling via environment variables

## Requirements

- PHP 8.0 or higher
- cURL extension enabled
- Google Sheets API key (with Sheets API enabled)
- Web server or PHP built-in server

## Setup

### 1. Get a Google Sheets API Key

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the Google Sheets API
4. Create credentials (API Key)
5. (Optional) Restrict the API key to Google Sheets API only for better security

### 2. Set Environment Variable

**Windows (PowerShell):**
```powershell
$env:GOOGLE_SHEETS_API_KEY = "your-api-key-here"
```

**Windows (Command Prompt):**
```cmd
set GOOGLE_SHEETS_API_KEY=your-api-key-here
```

**Linux/macOS:**
```bash
export GOOGLE_SHEETS_API_KEY="your-api-key-here"
```

**For persistent environment variables:**

- **Windows:** Set via System Properties > Environment Variables
- **Linux/macOS:** Add to `~/.bashrc`, `~/.zshrc`, or `/etc/environment`

### 3. Run the Server

**Using PHP built-in server:**
```bash
php -S localhost:8000 -t public
```

The API will be available at `http://localhost:8000`

**Using a web server (Apache/Nginx):**
- Point your web server document root to the `public` directory
- Ensure `.htaccess` or server configuration allows routing to `index.php`

## API Usage

### Endpoint

```
GET /?spreadsheet_id={id}&sheet={sheet_name}&range={optional_range}
```

### Parameters

| Parameter | Required | Description | Example |
|-----------|----------|-------------|---------|
| `spreadsheet_id` | Yes | Google Spreadsheet ID | `1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms` |
| `sheet` | Yes | Sheet name within the spreadsheet | `Sheet1` |
| `range` | No | A1 notation range. If omitted, returns entire sheet | `A1:C10` |

### Example Requests

**Get entire sheet:**
```
GET http://localhost:8000/?spreadsheet_id=1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms&sheet=Sheet1
```

**Get specific range:**
```
GET http://localhost:8000/?spreadsheet_id=1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms&sheet=Sheet1&range=A1:C10
```

**Get entire column:**
```
GET http://localhost:8000/?spreadsheet_id=1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms&sheet=Sheet1&range=A:A
```

### Example Response

**Success (HTTP 200):**
```json
{
  "range": "Sheet1!A1:C3",
  "majorDimension": "ROWS",
  "values": [
    ["Name", "Age", "City"],
    ["John", "30", "New York"],
    ["Jane", "25", "London"]
  ]
}
```

**Error Responses:**

Missing parameter (HTTP 400):
```json
{
  "error": "Missing required parameter: spreadsheet_id"
}
```

Invalid spreadsheet ID (HTTP 400):
```json
{
  "error": "spreadsheet_id is required and cannot be empty"
}
```

API error (HTTP 502):
```json
{
  "error": "Failed to fetch data from Google Sheets API: [error message from Google]"
}
```

Network failure (HTTP 500):
```json
{
  "error": "Failed to fetch data from Google Sheets API: Network request failed: [details]"
}
```

## HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success - Data retrieved successfully |
| 400 | Bad Request - Missing or invalid parameters |
| 405 | Method Not Allowed - Only GET requests are supported |
| 500 | Internal Server Error - Network failure or unexpected error |
| 502 | Bad Gateway - Google Sheets API returned an error |

## Project Structure

```
google-sheets-api/
├── public/
│   └── index.php          # Entry point
├── src/
│   ├── GoogleSheetsClient.php  # Google Sheets API client
│   └── Response.php            # HTTP response utility
└── README.md                   # This file
```

## Security Notes

- The API key is read from environment variables, never hardcoded
- All inputs are validated and sanitized
- Stack traces are not exposed to clients
- Only GET requests are accepted
- cURL SSL verification is enabled by default

## Design Decisions

1. **cURL over file_get_contents**: cURL provides better error handling, timeout control, and HTTP status code access, which is essential for proper error responses.

2. **Plain PHP (no framework)**: To keep the project minimal and dependency-free, allowing for easy deployment and understanding.

3. **Environment variables for API key**: Following security best practices to avoid exposing credentials in source code or version control.

4. **Strict typing**: Using `declare(strict_types=1)` and type hints for better code clarity and catching errors early.

5. **Separate classes**: Organized into logical components (Response, GoogleSheetsClient) for maintainability while keeping it simple.

6. **Input validation**: All user inputs are validated and sanitized to prevent injection attacks and ensure data integrity.

7. **Error handling**: Errors are caught and transformed into appropriate HTTP status codes without exposing internal details.

## Troubleshooting

**Error: "GOOGLE_SHEETS_API_KEY environment variable is not set"**
- Make sure you've set the environment variable in your current shell session
- Verify the variable is accessible: `echo $env:GOOGLE_SHEETS_API_KEY` (PowerShell) or `echo $GOOGLE_SHEETS_API_KEY` (bash)

**Error: "API request failed with HTTP 403"**
- Verify your API key is valid
- Ensure Google Sheets API is enabled for your project
- Check if the spreadsheet is publicly readable (if using public access)
- Verify API key restrictions allow Google Sheets API

**Error: "API request failed with HTTP 404"**
- Verify the spreadsheet_id is correct
- Ensure the sheet name exists in the spreadsheet
- Check that the range (if provided) is valid

**Error: Network request failed**
- Check your internet connection
- Verify firewall settings allow outbound HTTPS connections
- Ensure cURL is properly configured in PHP
