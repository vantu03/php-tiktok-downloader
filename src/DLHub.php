<?php

namespace DLHub;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use DOMDocument;
use DOMXPath;
use Exception;

class DLHub {
    private $input_url;
    private $output_prefix;
    private $output_dir;
    private $filename;
    private $headers;
    private $result = ["media" => [], "trys" => 0];
    private $count = 0;
    private $client;
    private $cookieJar;

    public function __construct($url, $headers = [], $output_prefix = "dlhub_", $output_dir = null, $filename = null) {
        $this->input_url = $url;
        $this->output_prefix = $output_prefix;
        $this->output_dir = $output_dir;
        $this->filename = $filename;

        $defaultHeaders = [
            'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 16_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.2 Mobile/15E148 Safari/604.1',
            'Accept-Language: en-US,en;q=0.9',
        ];

        $this->headers = array_merge($defaultHeaders, $headers);
        $this->cookieJar = new CookieJar();

        // Convert headers to associative array
        $headerAssoc = [];
        foreach ($this->headers as $h) {
            [$key, $val] = explode(':', $h, 2);
            $headerAssoc[trim($key)] = trim($val);
        }

        $this->client = new Client([
            'headers' => $headerAssoc,
            'cookies' => $this->cookieJar,
            'http_errors' => false,
            'timeout' => 10,
        ]);
    }

    private function getFileCount($base, $ext) {
        $this->count += 1;
        return $this->count === 1 ? "$base.$ext" : "$base ($this->count).$ext";
    }

    private function getFileName($media_id, $ext) {
        $base = $this->filename ? $this->filename : "{$this->output_prefix}{$media_id}";
        return $this->getFileCount($base, $ext);
    }

    private function downloadFile($media) {
        try {
            $response = $this->client->request('GET', $media['url']);
            if ($response->getStatusCode() !== 200) return null;

            $content = $response->getBody()->getContents();
            $file_path = $this->output_dir ? "{$this->output_dir}/{$media['filename']}" : $media['filename'];

            if ($this->output_dir && !is_dir($this->output_dir)) {
                mkdir($this->output_dir, 0777, true);
            }

            file_put_contents($file_path, $content);
            return $file_path;
        } catch (Exception $e) {
            return null;
        }
    }

    public function run($download = false, $maxtrys = 3) {
        while ($this->result['trys'] < $maxtrys) {
            $this->result['trys']++;

            try {
                $response = $this->client->request('GET', $this->input_url);
                $resp = $response->getBody()->getContents();
                $this->result["final_url"] = $response->getHeaderLine('X-Guzzle-Effective-URL') ?: $this->input_url;

                if (preg_match('#/(video|photo)/(\d+)#', $this->input_url, $matches)) {
                    $media_type = $matches[1];
                    $media_id = $matches[2];
                } else {
                    throw new Exception("Cannot extract media ID.");
                }

                $doc = new DOMDocument();
                libxml_use_internal_errors(true);
                $doc->loadHTML($resp);
                libxml_clear_errors();

                $xpath = new DOMXPath($doc);
                $script = $xpath->query('//script[@id="__UNIVERSAL_DATA_FOR_REHYDRATION__"]')->item(0);

                if ($script) {
                    $json_data = json_decode($script->nodeValue, true);
                    $scopes = ["webapp.video-detail", "webapp.reflow.video.detail"];

                    foreach ($scopes as $scope) {
                        $default = $json_data["__DEFAULT_SCOPE__"][$scope] ?? [];

                        // Meta
                        if (!empty($default['shareMeta'])) {
                            $this->result['title'] = $default['shareMeta']['title'] ?? '';
                            $this->result['desc'] = $default['shareMeta']['desc'] ?? '';
                            $this->result['cover_url'] = $default['shareMeta']['cover_url'] ?? '';
                        }

                        // Video
                        $video = $default['itemInfo']['itemStruct']['video'] ?? [];
                        if (!empty($video['playAddr'])) {
                            $this->result['media'][] = [
                                'type' => 'video',
                                'url' => $video['playAddr'],
                                'cookies' => $this->cookieJar->toArray(),
                                'id' => $media_id,
                                'width' => $video['width'] ?? null,
                                'height' => $video['height'] ?? null,
                                'filename' => $this->getFileName($media_id, 'mp4'),
                            ];
                        }

                        // Music
                        $music = $default['itemInfo']['itemStruct']['music'] ?? [];
                        if (!empty($music['playUrl'])) {
                            $this->result['media'][] = [
                                'type' => 'music',
                                'url' => $music['playUrl'],
                                'cookies' => $this->cookieJar->toArray(),
                                'id' => $media_id,
                                'width' => null,
                                'height' => null,
                                'filename' => $this->getFileName($media_id, 'mp3'),
                            ];
                        }

                        // Images
                        $images = $default['itemInfo']['itemStruct']['imagePost']['images'] ?? [];
                        foreach ($images as $image) {
                            foreach ($image['imageURL']['urlList'] ?? [] as $imgURL) {
                                $this->result['media'][] = [
                                    'type' => 'image',
                                    'url' => $imgURL,
                                    'cookies' => $this->cookieJar->toArray(),
                                    'id' => $media_id,
                                    'width' => $image['imageWidth'] ?? null,
                                    'height' => $image['imageHeight'] ?? null,
                                    'filename' => $this->getFileName($media_id, 'jpg'),
                                ];
                            }
                        }
                    }

                    if ($download) {
                        foreach ($this->result['media'] as &$media) {
                            $path = $this->downloadFile($media);
                            if ($path) {
                                $media['path'] = $path;
                            }
                        }
                    }

                    $this->result['success'] = true;
                    if (!empty($this->result['media'])) break;
                }

            } catch (Exception $e) {
                $this->result['error'] = ($this->result['error'] ?? '') . "\n[Try {$this->result['trys']}] " . $e->getMessage();
                if ($this->result['trys'] == $maxtrys) {
                    $this->result['success'] = false;
                } else {
                    sleep(1);
                }
            }
        }

        return $this->result;
    }
}

// Ví dụ sử dụng:
$dl = new DLHub("https://www.tiktok.com/@damodadroneshow/video/7484399220221316395?is_from_webapp=1&sender_device=pc");
$result = $dl->run(true);
print_r($result);
