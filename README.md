# TikTok Downloader PHP Library

A simple PHP library to download TikTok videos, images, and music without needing the official API. This library parses the page content directly and extracts media using Guzzle.

## Features

- Download **videos**, **images**, and **music** from TikTok posts
- Uses `GuzzleHttp\Client` for reliable HTTP requests
- Automatically extracts media metadata (title, description, cover)
- Supports auto-downloading files to disk
- Built-in retry mechanism
- Lightweight, no external API needed

---

## Requirements

- PHP 7.4+
- Composer

Install dependencies:

```bash
composer require guzzlehttp/guzzle
````

---

## Usage

```php
require 'vendor/autoload.php';

$dl = new DLHub("https://www.tiktok.com/@username/video/1234567890123456789");
$result = $dl->run(true); // true = auto download media

print_r($result);
```

### Sample Output

```php
Array
(
    [media] => Array
        (
            [0] => Array
                (
                    [type] => video
                    [url] => https://...
                    [filename] => dlhub_1234567890123456789.mp4
                    [path] => ./dlhub_1234567890123456789.mp4
                )
        )
    [title] => Video Title
    [desc] => Description of the TikTok post
    [cover_url] => https://...
    [success] => 1
)
```

---

## File Structure

* `DLHub.php`: Main class
* `vendor/`: Composer dependencies
* `README.md`: Documentation
* `.gitignore`: Ignores `vendor/` and unnecessary files

---

## How It Works

1. Fetches TikTok page via Guzzle
2. Parses embedded JSON inside `<script id="__UNIVERSAL_DATA_FOR_REHYDRATION__">`
3. Extracts video, image, and audio URLs
4. Optionally downloads files to your local machine

---

## Disclaimer

This project is for **educational purposes only**. Use it responsibly and in accordance with TikTok's [terms of service](https://www.tiktok.com/legal/terms-of-service).

---

## License

MIT License