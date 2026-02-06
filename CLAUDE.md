# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

TYPO3 extension `ok_ai_news` (oliverkroener/ok-ai-news) — receives webhook POST requests via TYPO3 Reactions and creates EXT:news records with FAL image attachments.

**TYPO3 compat:** 12.4 and 13.4 | **PHP:** >= 8.1

## Architecture

**Webhook flow:** External system → TYPO3 Reactions (validates API key) → `CreateNewsReaction` → Services → news record + FAL image

Three core classes:

- **`Classes/Reaction/CreateNewsReaction.php`** — Implements `TYPO3\CMS\Reactions\Reaction\ReactionInterface`. Entry point for webhook payloads. Validates input, delegates to services, returns JSON response.
- **`Classes/Service/FileHandlingService.php`** — Decodes base64 image data, writes to FAL via `ResourceFactory`/`StorageRepository`. Handles data URI prefix stripping. Stores files in configurable subfolder of fileadmin.
- **`Classes/Service/NewsCreationService.php`** — Creates `tx_news_domain_model_news` records via TYPO3 `DataHandler`. Handles `sys_file_reference` creation to link FAL images to news records via `fal_media` field. News records are created as hidden (hidden=1) by default.

## Expected Webhook Payload

```json
{
  "title": "News Title (required)",
  "bodytext": "<p>HTML body</p>",
  "image": "base64-encoded-data (with or without data URI prefix)",
  "imageFilename": "photo.jpg",
  "imageFolder": "ai_news",
  "storagePid": 123
}
```

`storagePid` falls back to the reaction's configured storage PID if not in payload.

## Key Conventions

- Extension key: `ok_ai_news`, namespace: `OliverKroener\OkAiNews`
- TCA override in `Configuration/TCA/Overrides/sys_reaction.php` registers the custom reaction type
- Reaction type identifier: `create-news-from-webhook`
- DataHandler is used (not direct DB queries) to ensure TYPO3 hooks/events fire correctly
- Labels use XLIFF in `Resources/Private/Language/locallang.xlf`

## Testing with cURL

```bash
curl -X POST 'https://example.com/typo3/reaction/{identifier}' \
  -H 'accept: application/json' \
  -H 'x-api-key: {secret}' \
  -H 'Content-Type: application/json' \
  -d '{"title":"Test","bodytext":"<p>Hello</p>","image":"<base64>","imageFilename":"test.jpg"}'
```

## Setup in TYPO3

1. `composer require oliverkroener/ok-ai-news`
2. Activate extension
3. Backend → Admin Tools → Reactions → Add reaction of type "Create news record from webhook"
4. Configure storage PID and impersonate user (needs write access to news table + FAL)
