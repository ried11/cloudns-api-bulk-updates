<?php

/**
 * Settings
 */

// Auth ID and Password
define("AUTH_ID", farid.steady@yahoo.com);
define("AUTH_PASS", "meliodas11");

$list = "https://dichvusocks.us,91.ri
domain2.com,127.0.0.2";

/**
 * Code
 */

// checking if we can log in successfully
$login = apiCall('dns/login.json', "");
if (isset($login['status']) && $login['status'] == 'Failed') {
	die($login['statusDescription']);
}

foreach (explode("\n", $list) as $row) {
	$fields = explode(",", $row);
	if (!isset($fields[0], $fields[1])) {
		echo "Invalid row: {$row}\n";
		continue;
	}
	
	$response = apiCall('dns/register.json', "domain-name={$fields[0]}&zone-type=master");
	if (!isset($response['status'])) {
		echo "{$fields[0]} cannot be added: failed to connect\n";
		continue;
	}
	if ($response['status'] == 'Failed') {
		echo "{$fields[0]} cannot be added: {$response['statusDescription']}\n";
		continue;
	}
	echo "{$fields[0]} created\n";
	
	$response = apiCall('dns/axfr-import.json', "domain-name={$fields[0]}&server={$fields[1]}");
	if (!isset($response['status'])) {
		echo "{$fields[0]} cannot be imported from {$fields[1]}: failed to connect\n";
		continue;
	}
	if ($response['status'] == 'Failed') {
		echo "{$fields[0]} cannot be imported from {$fields[1]}: {$response['statusDescription']}\n";
		continue;
	}
	echo "{$fields[0]} imported from {$fields[1]}\n";
}

// function to connect to the API
function apiCall ($url, $data) {
	$url = "https://api.cloudns.net/{$url}";
	$data = "auth-id=".AUTH_ID."&auth-password=".AUTH_PASS."&{$data}";

	$init = curl_init();
	curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($init, CURLOPT_URL, $url);
	curl_setopt($init, CURLOPT_POST, true);
	curl_setopt($init, CURLOPT_POSTFIELDS, $data);
	curl_setopt($init, CURLOPT_USERAGENT, 'cloudns_api_script/0.1 (+https://github.com/ClouDNS/cloudns-api-bulk-updates/tree/master/zones-import-via-transfer)');

	$content = curl_exec($init);

	curl_close($init);

	return json_decode($content, true);
}
