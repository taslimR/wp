<?php
/**
 * Copyright (C) 2014-2017 ServMask Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * ███████╗███████╗██████╗ ██╗   ██╗███╗   ███╗ █████╗ ███████╗██╗  ██╗
 * ██╔════╝██╔════╝██╔══██╗██║   ██║████╗ ████║██╔══██╗██╔════╝██║ ██╔╝
 * ███████╗█████╗  ██████╔╝██║   ██║██╔████╔██║███████║███████╗█████╔╝
 * ╚════██║██╔══╝  ██╔══██╗╚██╗ ██╔╝██║╚██╔╝██║██╔══██║╚════██║██╔═██╗
 * ███████║███████╗██║  ██║ ╚████╔╝ ██║ ╚═╝ ██║██║  ██║███████║██║  ██╗
 * ╚══════╝╚══════╝╚═╝  ╚═╝  ╚═══╝  ╚═╝     ╚═╝╚═╝  ╚═╝╚══════╝╚═╝  ╚═╝
 */

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ServMaskURLCurl.php';

class ServMaskURLClient
{
	const CHUNK_SIZE    = 4194304; // 4 MB

	/**
	 * URL scheme
	 *
	 * @var string
	 */
	protected $scheme   = null;

	/**
	 * URL port
	 *
	 * @var integer
	 */
	protected $port     = null;

	/**
	 * URL hostname
	 *
	 * @var string
	 */
	protected $hostname = null;

	/**
	 * URL username
	 *
	 * @var string
	 */
	protected $username = null;

	/**
	 * URL password
	 *
	 * @var string
	 */
	protected $password = null;

	/**
	 * URL path
	 *
	 * @var string
	 */
	protected $path     = null;

	/**
	 * URL query
	 *
	 * @var string
	 */
	protected $query    = null;

	/**
	 * Chunk stream
	 *
	 * @var resource
	 */
	protected $chunkStream = null;

	public function __construct($url) {
		$this->scheme   = parse_url($url, PHP_URL_SCHEME);
		$this->port     = parse_url($url, PHP_URL_PORT);
		$this->hostname = parse_url($url, PHP_URL_HOST);
		$this->username = parse_url($url, PHP_URL_USER);
		$this->password = parse_url($url, PHP_URL_PASS);
		$this->path     = parse_url($url, PHP_URL_PATH);
		$this->query    = parse_url($url, PHP_URL_QUERY);
	}

	/**
	 * Get file meta
	 *
	 * @return array
	 */
	public function getFileMeta() {
		$client = new ServMaskURLCurl;
		$client->setScheme($this->scheme);
		$client->setPort($this->port);
		$client->setHostname($this->hostname);
		$client->setUsername($this->username);
		$client->setPassword($this->password);
		$client->setPath($this->path);
		$client->setQuery($this->query);
		$client->setOption(CURLOPT_HEADER, true);
		$client->setOption(CURLOPT_NOBODY, true);

		// Get file meta
		return $client->makeRequest();
	}

	/**
	 * Download file
	 *
	 * @param  resource $fileStream File stream
	 * @param  array    $params     File parameters
	 * @return array
	 */
	public function downloadFile($fileStream, &$params = array()) {
		$client = new ServMaskURLCurl;
		$client->setScheme($this->scheme);
		$client->setPort($this->port);
		$client->setHostname($this->hostname);
		$client->setUsername($this->username);
		$client->setPassword($this->password);
		$client->setPath($this->path);
		$client->setQuery($this->query);
		$client->setOption(CURLOPT_RETURNTRANSFER, true);
		$client->setOption(CURLOPT_FILE, $fileStream);
		$client->setOption(CURLOPT_PROGRESSFUNCTION, array($this, 'downloadProgress'));
		$client->setOption(CURLOPT_NOPROGRESS, false);

		// Make request
		$client->makeRequest();

		return $params;
	}

	/**
	 * Function to track the download progress
	 *
	 * @param resource   $resource   File stream
	 * @param filesize   $filesize   File size
	 * @param downloaded $downloaded Downloaded size
	 */
	public function downloadProgress($resource, $filesize, $downloaded) {
		// Check if filesize is passed
		if ($filesize) {
			// Calculate the percentage
			$percent = (int) (($downloaded / $filesize) * 100);

			// Set progress
			Ai1wm_Status::progress($percent);
		} else {
			// Set progress
			Ai1wm_Status::info(__('Downloading the archive..', AI1WMLE_PLUGIN_NAME));
		}
	}

	/**
	 * Download file in chunks
	 *
	 * @param  resource $fileStream File stream
	 * @param  array    $params     File parameters
	 * @return array
	 */
	public function downloadFileChunk($fileStream, &$params = array()) {
		$this->chunkStream = fopen('php://temp', 'wb+');

		$client = new ServMaskURLCurl;
		$client->setScheme($this->scheme);
		$client->setPort($this->port);
		$client->setHostname($this->hostname);
		$client->setUsername($this->username);
		$client->setPassword($this->password);
		$client->setPath($this->path);
		$client->setQuery($this->query);
		$client->setOption(CURLOPT_WRITEFUNCTION, array($this, 'curlWriteFunction'));
		$client->setOption(CURLOPT_RANGE, "{$params['startBytes']}-{$params['endBytes']}");

		// Make request
		$client->makeRequest();

		// Copy chunk data into file stream
		if (fwrite($fileStream, stream_get_contents($this->chunkStream, -1, 0)) === false) {
			throw new Exception('Unable to save the file from URL address');
		}

		// Close chunk stream
		fclose($this->chunkStream);

		// Next startBytes
		if ($params['totalBytes'] < ($params['startBytes'] + self::CHUNK_SIZE)) {
			$params['startBytes'] = $params['totalBytes'];
		} else {
			$params['startBytes'] = $params['endBytes'] + 1;
		}

		// Next endBytes
		if ($params['totalBytes'] < ($params['endBytes'] + self::CHUNK_SIZE)) {
			$params['endBytes'] = $params['totalBytes'];
		} else {
			$params['endBytes'] += self::CHUNK_SIZE;
		}

		return $params;
	}

	/**
	 * Curl write function callback
	 *
	 * @param  resource $ch   Curl handler
	 * @param  string   $data Curl data
	 * @return integer
	 */
	public function curlWriteFunction($ch, $data) {
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($code !== 200 && $code !== 206) {
			throw new Exception(sprintf('Unable to connect to URL address. Error code: %d', $code), $code);
		}

		// Write data to stream
		fwrite($this->chunkStream, $data);

		return strlen($data);
	}
}
