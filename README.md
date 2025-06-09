# 📥 TikTok Downloader - PHP Library

A simple PHP library for downloading videos, images, and music from TikTok posts without using the official API. Ideal for web automation, media scraping, or building custom downloader tools.

---

## ✨ Features

- ✅ Download **videos**, **music**, and **images**
- ✅ No need for TikTok API
- ✅ Auto-download to local storage (optional)
- ✅ Retrieves metadata (title, description, cover)
- ✅ Built-in retry mechanism
- ✅ PSR-4 compatible & Composer autoloading

---

## 🛠 Requirements

- PHP 7.4 or newer
- Composer
- Guzzle HTTP client

Install dependencies:

```bash
composer install
````

---

## 🚀 Usage Example

```php
<?php

require 'vendor/autoload.php';

use DLHub\DLHub;

$dl = new DLHub("https://www.tiktok.com/@username/video/1234567890123456789");
$result = $dl->run(true);

print_r($result);
```

**Sample Output:**

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

## 📁 Project Structure

```
php-tiktok-downloader/
├── src/
│   └── DLHub.php           # Main library class
├── examples/
│   └── example.php         # Sample usage
├── vendor/                 # Composer dependencies
├── composer.json
├── .gitignore
└── README.md
```

---

## ⚙️ Composer Autoloading (PSR-4)

Ensure your `composer.json` contains:

```json
{
    "autoload": {
        "psr-4": {
            "DLHub\\": "src/"
        }
    },
    "require": {
        "guzzlehttp/guzzle": "^7.0"
    }
}
```

Then run:

```bash
composer dump-autoload
```

---

## ⚠️ Disclaimer

This project is for **educational purposes only**. Please use it responsibly and in compliance with [TikTok’s Terms of Service](https://www.tiktok.com/legal/terms-of-service).

---

## 📄 License

Licensed under the [MIT License](LICENSE).