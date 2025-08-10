<?php
function fetchStatus($containerNumber)
{
    // Clean and validate container number
    $containerNumber = trim(strtoupper($containerNumber));
    if (empty($containerNumber)) {
        return null;
    }

    // Encode container number for URL
    $encodedContainer = urlencode($containerNumber);
    $url = "https://www.track-trace.com/container?number={$encodedContainer}";

    // Set up cURL options
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1'
        ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Check for errors
    if ($response === false || $httpCode !== 200) {
        return null;
    }

    // Parse the response to find status
    $status = parseTrackTraceStatus($response);

    if ($status) {
        return [
            'status' => $status['normalized'],
            'status_raw' => $status['raw'],
            'source' => 'tracktrace'
        ];
    }

    return null;
}

function parseTrackTraceStatus($html)
{
    // Common status patterns to look for
    $statusPatterns = [
        '/loaded\s+on\s+vessel/i' => 'In Transit',
        '/discharged\s+at\s+destination/i' => 'Arrived at Port',
        '/gate\s+out\s+full/i' => 'Delivered',
        '/arrived\s+at\s+port/i' => 'Arrived at Port',
        '/in\s+transit/i' => 'In Transit',
        '/delivered/i' => 'Delivered',
        '/pending/i' => 'Pending',
        '/booked/i' => 'Booked'
    ];

    // Look for status in common div patterns
    $patterns = [
        '/<div[^>]*id=["\']tracking-info["\'][^>]*>(.*?)<\/div>/is',
        '/<div[^>]*class=["\'][^"\']*status[^"\']*["\'][^>]*>(.*?)<\/div>/is',
        '/<span[^>]*class=["\'][^"\']*status[^"\']*["\'][^>]*>(.*?)<\/span>/is',
        '/<td[^>]*class=["\'][^"\']*status[^"\']*["\'][^>]*>(.*?)<\/td>/is'
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $html, $matches)) {
            $content = strip_tags($matches[1]);
            $content = trim($content);

            if (!empty($content)) {
                // Check against known status patterns
                foreach ($statusPatterns as $regex => $normalized) {
                    if (preg_match($regex, $content)) {
                        return [
                            'normalized' => $normalized,
                            'raw' => $content
                        ];
                    }
                }

                // If no pattern matches, return the raw content
                return [
                    'normalized' => 'Unknown',
                    'raw' => $content
                ];
            }
        }
    }

    // Fallback: look for any text that might contain status information
    $lines = explode("\n", strip_tags($html));
    foreach ($lines as $line) {
        $line = trim($line);
        if (strlen($line) > 10 && strlen($line) < 200) {
            foreach ($statusPatterns as $regex => $normalized) {
                if (preg_match($regex, $line)) {
                    return [
                        'normalized' => $normalized,
                        'raw' => $line
                    ];
                }
            }
        }
    }

    return null;
}
