# AI News Writer for TYPO3

A TYPO3 extension that creates news records from external webhook requests via the TYPO3 Reactions system.

## Features

- **Webhook Integration** — Receive POST requests from external systems (AI services, automation tools, etc.)
- **Automatic News Creation** — Creates `tx_news_domain_model_news` records via TYPO3 DataHandler
- **Image Support** — Accepts base64-encoded images and stores them as proper FAL file references
- **Flexible Storage** — Configure target folder and storage PID per request or via reaction settings
- **Review Workflow** — News records are created as hidden by default for editorial review

## Requirements

| Dependency | Version |
|------------|---------|
| PHP | >= 8.1 |
| TYPO3 | 12.4 LTS or 13.4 LTS |
| EXT:reactions | ^12.4 \|\| ^13.4 |
| EXT:news | ^11.0 \|\| ^12.0 |

## Installation

```bash
composer require oliverkroener/ok-ai-news
```

Then activate the extension in the TYPO3 Extension Manager or via CLI:

```bash
vendor/bin/typo3 extension:activate ok_ai_news
```

## Configuration

1. Navigate to **Admin Tools → Reactions** in the TYPO3 backend
2. Create a new reaction of type **"Create news record from webhook"**
3. Configure:
   - **Name** — A descriptive name for this webhook endpoint
   - **Secret** — API key for authentication (auto-generated or custom)
   - **Storage PID** — Default page UID where news records will be stored
   - **Impersonate User** — Backend user with write access to the news table and FAL storage

After saving, TYPO3 provides the webhook URL and displays the secret for use in your external system.

## Usage

### Webhook Endpoint

```
POST https://your-site.com/typo3/reaction/{reaction-identifier}
```

### Headers

| Header | Value |
|--------|-------|
| `Content-Type` | `application/json` |
| `x-api-key` | Your reaction secret |

### Payload

```json
{
  "title": "Breaking News",
  "bodytext": "<p>Full HTML content of the news article...</p>",
  "image": "data:image/jpeg;base64,/9j/4AAQSkZJRg...",
  "imageFilename": "headline-photo.jpg",
  "imageFolder": "ai_news",
  "storagePid": 123
}
```

| Field | Required | Description |
|-------|----------|-------------|
| `title` | Yes | News headline |
| `bodytext` | No | HTML body content |
| `image` | No | Base64-encoded image (with or without data URI prefix) |
| `imageFilename` | No | Target filename (default: auto-generated with timestamp) |
| `imageFolder` | No | Subfolder in fileadmin (default: `ai_news`) |
| `storagePid` | No | Override storage PID from reaction config |

### Response

**Success (201):**
```json
{
  "success": true,
  "newsUid": 42
}
```

**Error (400/500):**
```json
{
  "success": false,
  "error": "Field \"title\" is required"
}
```

## Example: cURL Request

```bash
curl -X POST 'https://example.com/typo3/reaction/a1b2c3d4-e5f6-7890-abcd-ef1234567890' \
  -H 'Content-Type: application/json' \
  -H 'x-api-key: your-secret-key-here' \
  -d '{
    "title": "AI Generated Article",
    "bodytext": "<p>This article was created automatically.</p>",
    "image": "'"$(base64 -w0 photo.jpg)"'",
    "imageFilename": "ai-photo.jpg"
  }'
```

## Architecture

```
External System
      │
      ▼ POST /typo3/reaction/{id}
┌─────────────────────────────────────┐
│  TYPO3 Reactions (auth + routing)   │
└─────────────────────────────────────┘
      │
      ▼
┌─────────────────────────────────────┐
│  CreateNewsReaction                 │
│  - Validates payload                │
│  - Orchestrates services            │
└─────────────────────────────────────┘
      │
      ├──────────────────┐
      ▼                  ▼
┌─────────────┐   ┌─────────────────┐
│ FileHandling│   │ NewsCreation    │
│ Service     │   │ Service         │
│ (base64→FAL)│   │ (DataHandler)   │
└─────────────┘   └─────────────────┘
      │                  │
      ▼                  ▼
   sys_file      tx_news_domain_model_news
                 + sys_file_reference
```

## License

GPL-2.0-or-later

See [LICENSE](LICENSE) for details.
