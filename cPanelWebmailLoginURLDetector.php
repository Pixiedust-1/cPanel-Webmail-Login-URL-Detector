<?php
function isURL($url) {
	if(!$url || !is_string($url) || !preg_match('/^http(s)?:\/\/[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(\/.*)?$/i', $url)){
	  return false;
	} else {
		return true;
	}
}

function iscPanelURL($url = '') {
	if (!isURL($url)) {
		return false;
	}

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_NOBODY, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	$result = curl_exec($ch);
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	if ($http_code != '200') {
		return false;
	}

	preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result, $matches);

	$cookies = array();
	foreach ($matches[1] as $item) {
	  parse_str($item, $cookie);
	  $cookies = array_merge($cookies, $cookie);
	}

	$arr = array('webmailrelogin', 'webmailsession', 'roundcube_sessid', 'roundcube_sessauth', 'roundcube_cookies');
	foreach ($arr as $value) {
		if (array_key_exists($value, $cookies)) {
			return true;
		}
	}
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	if (isset($_GET['domain']) && !empty($_GET['domain'])) {
		$domain = $_GET['domain'];

		$arr = [
			'http://webmail.' . $domain,
			'http://mail.' . $domain,
			'http://' . $domain . ':2096',
			'http://' . $domain . '/webmail',
			'https://webmail.' . $domain,
			'https://mail.' . $domain,
			'https://' . $domain . ':2096',
			'https://' . $domain . '/webmail',
		];

		foreach ($arr as $url) {
			if (iscPanelURL($url)) {
				echo $url;
				break;
			}
		}
	}
}
