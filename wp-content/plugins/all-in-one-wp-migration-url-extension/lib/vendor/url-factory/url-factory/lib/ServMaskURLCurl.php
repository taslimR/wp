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

class ServMaskURLCurl
{
	protected $scheme   = null;

	protected $port     = null;

	protected $hostname = null;

	protected $username = null;

	protected $password = null;

	protected $path     = null;

	protected $query    = null;

	protected $handler  = null;

	protected $options  = array();

	protected $headers  = array('User-Agent' => 'curl/7.43.0');

	public function __construct() {
		// Check the cURL extension is loaded
		if (!extension_loaded('curl')) {
			throw new Exception('cURL extension is required.');
		}

		// Default configuration
		$this->setOption(CURLOPT_HEADER, false);
		$this->setOption(CURLOPT_RETURNTRANSFER, true);
		$this->setOption(CURLOPT_BINARYTRANSFER, true);
		$this->setOption(CURLOPT_FOLLOWLOCATION, true);
		$this->setOption(CURLOPT_SSL_VERIFYHOST, false);
		$this->setOption(CURLOPT_SSL_VERIFYPEER, false);
		$this->setOption(CURLOPT_CONNECTTIMEOUT, 120);
		$this->setOption(CURLOPT_TIMEOUT, 0);
	}

	/**
	 * Set scheme
	 *
	 * @param  string          $value URL scheme
	 * @return ServMaskURLCurl
	 */
	public function setScheme($value) {
		$this->scheme = $value;
		return $this;
	}

	/**
	 * Get scheme
	 *
	 * @return string
	 */
	public function getScheme() {
		return $this->scheme;
	}

	/**
	 * Set port
	 *
	 * @param  integer         $value URL Port
	 * @return ServMaskURLCurl
	 */
	public function setPort($value) {
		$this->port = intval($value);
		return $this;
	}

	/**
	 * Get port
	 *
	 * @return integer
	 */
	public function getPort() {
		return $this->port;
	}

	/**
	 * Set hostname
	 *
	 * @param  string          $value URL hostname
	 * @return ServMaskURLCurl
	 */
	public function setHostname($value) {
		$this->hostname = $value;
		return $this;
	}

	/**
	 * Get hostname
	 *
	 * @return string
	 */
	public function getHostname() {
		return $this->hostname;
	}

	/**
	 * Set username
	 *
	 * @param  string          $username URL Username
	 * @return ServMaskURLCurl
	 */
	public function setUsername($value) {
		$this->username = $value;
		return $this;
	}

	/**
	 * Get username
	 *
	 * @return string
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * Set password
	 *
	 * @param  string          $value URL Password
	 * @return ServMaskURLCurl
	 */
	public function setPassword($value) {
		$this->password = $value;
		return $this;
	}

	/**
	 * Get password
	 *
	 * @return string
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 * Set path
	 *
	 * @param  string          $value URL path
	 * @return ServMaskURLCurl
	 */
	public function setPath($value) {
		$this->path = $value;
		return $this;
	}

	/**
	 * Get path
	 *
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * Set query
	 *
	 * @param  string          $value URL query
	 * @return ServMaskURLCurl
	 */
	public function setQuery($value) {
		$this->query = $value;
		return $this;
	}

	/**
	 * Get query
	 *
	 * @return string
	 */
	public function getQuery() {
		return $this->query;
	}

	/**
	 * Set cURL option
	 *
	 * @param  int             $name  cURL option name
	 * @param  mixed           $value cURL option value
	 * @return ServMaskURLCurl
	 */
	public function setOption($name, $value) {
		$this->options[$name] = $value;
		return $this;
	}

	/**
	 * Get cURL option
	 *
	 * @param  int   $name cURL option name
	 * @return mixed
	 */
	public function getOption($name) {
		return $this->options[$name];
	}

	/**
	 * Set cURL header
	 *
	 * @param  string          $name  cURL header name
	 * @param  string          $value cURL header value
	 * @return ServMaskURLCurl
	 */
	public function setHeader($name, $value) {
		$this->headers[$name] = $value;
		return $this;
	}

	/**
	 * Get cURL header
	 *
	 * @param  string $name cURL header name
	 * @return string
	 */
	public function getHeader($name) {
		return $this->headers[$name];
	}

	/**
	 * Make cURL request
	 *
	 * @return array
	 */
	public function makeRequest() {
		// cURL handler
		$this->handler = curl_init();

		// Set URL address
		if ($this->getPort()) {
			$this->setOption(CURLOPT_URL, sprintf(
				'%s://%s:%d%s?%s',
				$this->getScheme(),
				$this->getHostname(),
				$this->getPort(),
				$this->getPath(),
				$this->getQuery()
			));
		} else {
			$this->setOption(CURLOPT_URL, sprintf(
				'%s://%s%s?%s',
				$this->getScheme(),
				$this->getHostname(),
				$this->getPath(),
				$this->getQuery()
			));
		}

		// Set username and password
		if ($this->getUsername()) {
			$this->setOption(CURLOPT_USERPWD, sprintf(
				'%s:%s',
				$this->getUsername(),
				$this->getPassword()
			));
		}

		// Apply cURL headers
		$httpHeaders = array();
		foreach ($this->headers as $name => $value) {
			$httpHeaders[] = "$name: $value";
		}

		$this->setOption(CURLOPT_HTTPHEADER, $httpHeaders);

		// Apply cURL options
		foreach ($this->options as $name => $value) {
			curl_setopt($this->handler, $name, $value);
		}

		// HTTP request
		$response = curl_exec($this->handler);
		if ($response === false) {
			throw new Exception(sprintf('Unable to connect to URL address. Error code: %d', curl_errno($this->handler)));
		}

		// HTTP headers
		if ($this->getOption(CURLOPT_HEADER)) {
			return $this->httpParseHeaders($response);
		}

		return $response;
	}

	/**
	 * Parse HTTP headers
	 *
	 * @param  string $headers HTTP headers
	 * @return array
	 */
	public function httpParseHeaders($headers) {
		$headers = preg_split("/(\r|\n)+/", $headers, -1, PREG_SPLIT_NO_EMPTY);

		$parseHeaders = array();
		for ($i = 1; $i < count($headers); $i++) {
			if (strpos($headers[$i], ':') !== false) {
				list($key, $rawValue) = explode(':', $headers[$i], 2);
				$key = trim($key);
				$value = trim($rawValue);
				if (array_key_exists($key, $parseHeaders)) {
					// See HTTP RFC Sec 4.2 Paragraph 5
					// http://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4.2
					// If a header appears more than once, it must also be able to
					// be represented as a single header with a comma-separated
					// list of values.  We transform accordingly.
					$parseHeaders[$key] .= ',' . $value;
				} else {
					$parseHeaders[$key] = $value;
				}
			}
		}

		return $parseHeaders;
	}

	/**
	 * Destroy cURL handler
	 *
	 * @return void
	 */
	public function __destruct() {
		if ($this->handler !== null) {
			curl_close($this->handler);
		}
	}
}
