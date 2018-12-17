<?php
/**
 * @brief        Cloudlog rigctld Interface
 * @date         2018-12-02
 * @author       Tobias Mädel <t.maedel@alfeld.de>
 * @copyright    MIT-licensed
 *
 */

include("config.php");
include("rigctld.php"); 

$rigctl = new rigctldAPI($rigctl_host, $rigctl_port); 

$lastFrequency = false; 
$lastMode = false; 

while (true)
{
	$frequency = $rigctl->getFrequency(); 
	$mode = $rigctl->getMode();

	// check if we've gotten a proper response from rigctld
	if ($frequency !== false && $mode !== false)
	{
		// only send POST to cloudlog if the settings have changed
		if ($lastFrequency != $frequency || $lastMode != $mode['mode'] )
		{
			$data = [
				"radio" => $radio_name,
				"frequency" => $frequency,
				"mode" => $mode['mode'],

				/* Found these additional parameter in magicbug's SatPC32 application. 
				   I'm not much of a satellite op yet, so I'm not sure how these should be implemented (probably with the secondary VFOs?)
				   PR or Issues with details welcome! 

				   I'm still sending these values in order to mitigate a nasty "Message: Undefined variable: uplink_mode" PHP error in one of the AJAX calls.
				*/ 
				"sat_name" => "",
				"downlink_freq" => 0,
				"uplink_freq" => 0,
				"downlink_mode" => 0,
				"uplink_mode" => 0,
			];

			postInfoToCloudlog($cloudlog_url, $data);
			$lastMode = $mode['mode'];
			$lastFrequency = $frequency;

			echo "Updated info. Frequency: " . $frequency . " - Mode: " . $mode['mode'] . "\n";
		}
		
	}
	else
	{
		$rigctl->connect();
	}

	sleep($interval);
}


function postInfoToCloudlog($url, $data)
{
	$json = json_encode($data, JSON_PRETTY_PRINT);
	$ch = curl_init( $url . '/index.php/api/radio' );
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); 
	curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Content-Type: application/json',
		'Content-Length: ' . strlen($json)
	]); 

	$result = curl_exec($ch);

}