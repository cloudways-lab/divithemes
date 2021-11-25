<?php

namespace WP_CLI\DiviThemes;

use WP_CLI;
use WP_CLI_Command;

class DiviThemesCommand extends WP_CLI_Command {

	private $_token = 'E1sZ29QLzvbSSJ9lGRmEcxL4VeYtI9qBoan9NOVc';

	private function prefillApiKey($args) {
		$options = array(
			'return' => true,
			'parse' => 'json',
			'launch' => false,
			'exit_error' => true,
		);
		$ret = WP_CLI::runCommand('option update et_automatic_updates_options \'{"username": "' . $args['username'] . '", "api_key": "' . $args['apiKey'] . '"}\'', $options);
		WP_CLI::success('Theme updates enabled successfully');

	}

	public function postToDivi($args) {
		$url = 'https://www.elegantthemes.com/api/hosting_partners/';
		$array['action'] = 'check_subscription';
		$array['username'] = $args['username'];
		$array['api_key'] = $args['apiKey'];
		$array['token'] = $this->_token;
		
		$this->sendRequest($url, 'POST', $array);
	}

	/**
	 *
	 * divitheme verifyApi [--apiKey=<key>] [--username=<username>]
	 *
	 */
	public function verifyApi($args, $assoc_args) {
		$apiKey = $assoc_args['apiKey'];
		$username = $assoc_args['username'];

		if ($apiKey && $username) {
			$url = 'https://www.elegantthemes.com/api/hosting_partners/?action=check_subscription&username=' . $username . '&api_key=' . $apiKey;
			list($response, $code) = $this->sendRequest($url, 'GET');
			if ($code == 200) {
				$response = json_decode($response, true);
				if ($response['success'] == 'true') {
					WP_CLI::success('API key is valid');
				} else {
					WP_CLI::error($response['error']);
				}
			}
		} else {
			WP_CLI::error('Please provide username and password');
		}

	}

	/**
	 *
	 * divi-theme install [--apiKey=<key>] [--username=<username>]
	 *
	 */
	public function install($args, $assoc_args) {
		$apiKey = isset($assoc_args['apiKey'])?$assoc_args['apiKey']:null;
		$username = isset($assoc_args['username'])?$assoc_args['username']:null;
		if ($apiKey && $username) {
			$url = 'https://www.elegantthemes.com/api/hosting_partners/?action=check_subscription&username=' . $username . '&api_key=' . $apiKey;
			list($response, $code) = $this->sendRequest($url, 'GET');
			if ($code == 200) {
				$response = json_decode($response, true);
				if ($response['success'] == 'true') {
					$this->downloadTheme($assoc_args);
					$this->prefillApiKey($assoc_args);
					$this->postToDivi($assoc_args);
				} else {
					WP_CLI::error($response['error']);
				}
			}
		} else {
			WP_CLI::error('Please provide username and password');
		}
	}

	private function downloadTheme($args) {
		$url = 'https://www.elegantthemes.com/api/api_downloads.php?api_update=1&theme=Divi&api_key=' . $args['apiKey'] . '&username=' . $args['username'];
		$options = array(
			'return' => true,
			'parse' => 'json',
			'launch' => false,
			'exit_error' => true
		);
		$ret = WP_CLI::runCommand('theme install ' . $url . ' --activate', $options);
		return;
	}

	// public function test($args, $assoc_args) {

	// 	$url = 'https://downloads.wordpress.org/theme/go.1.4.4.zip';
	// 	$options = array(
	// 		// 'return' => true,
	// 		// 'parse' => 'json',
	// 		// 'launch' => false,
	// 		'exit_error' => true,
	// 	);
	// 	$ret = WP_CLI::runCommand('theme install ' . $url . ' --activate', $options);

	// }

	private function sendRequest($url, $type = 'GET', $array = []) {
		if ($type == 'GET') {
			$ch = curl_init();
			$headers = array();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_HEADER, 0);

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$response = curl_exec($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			return [$response, $httpcode];
		} else {
			$ch = curl_init();
			$headers = array();
			$post = $array;

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS,($post));

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_HEADER, 0);

			$response = curl_exec($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			print_r($response);
			return [$response, $httpcode];
		}

	}

}