#!/usr/bin/php
<?php
/**
 * @brief        Cloudlog rigctld Interface
 * @date         2018-12-02
 * @author       Manawyrm
 * @copyright    MIT-licensed
 *
 */

include("config.php");
include("rigctld.php");

$rigctl = new rigctldAPI($rigctl_host, $rigctl_port);

$lastFrequency = false;
$lastMode = false;

while (true) {
    $data = $rigctl->getFrequencyAndMode();

    // debug: show what we got from rigctld
    echo "Got from rigctld: freq='{$data['frequency']}' mode='{$data['mode']}'\n";

    // Validate frequency and mode are non-empty strings
    if ($data !== false && !empty($data['frequency']) && !empty($data['mode'])) {
        
        // Only send POST to cloudlog if frequency or mode changed
        if ($lastFrequency !== $data['frequency'] || $lastMode !== $data['mode']) {
            $postData = [
                "radio" => $radio_name,
                "frequency" => $data['frequency'],
                "mode" => $data['mode'],
                "key" => $cloudlog_apikey
                /* Optional additional parameters, if needed
                "sat_name" => "",
                "downlink_freq" => 0,
                "uplink_freq" => 0,
                "downlink_mode" => 0,
                "uplink_mode" => 0,
                "key" => $cloudlog_apikey
                */
            ];

            // Debug: show what we are about to post
            echo "Posting to Cloudlog: " . json_encode($postData) . "\n";

            postInfoToCloudlog($cloudlog_url, $postData);

            $lastFrequency = $data['frequency'];
            $lastMode = $data['mode'];

            echo "Updated info. Frequency: " . $lastFrequency . " - Mode: " . $lastMode . "\n";
        }
    } else {
        echo "Invalid or empty frequency/mode received; reconnecting...\n";
        $rigctl->connect();
    }

    sleep($interval);
}

function postInfoToCloudlog($url, $data)
{
    $json = json_encode($data, JSON_PRETTY_PRINT);
    $ch = curl_init($url . '/index.php/api/radio');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($json)
    ]);

    $result = curl_exec($ch);

    if ($result === false) {
        echo "Curl error: " . curl_error($ch) . "\n";
    } else {
        echo "Cloudlog API response: " . $result . "\n";
    }

    curl_close($ch);
}
