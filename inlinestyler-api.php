<?php
/**
 * HTML Inline Styler API Interface
 *
 * This package interfaces Torchbox's Inline Styler.
 * Documentation for the API is here: http://bit.ly/8sRhoG
 * 
 * Requirements:
 * 		* PHP4 and later
 * 		* CURL extension enabled
 * 
 * 
 * @author 		Maor (Henry) Hazan <maorhaz@gmail.com>
 * @package 	InlineStylerAPI
 * @version		1.0
 * @license		http://opensource.org/licenses/gpl-2.0.php GNU Public License
 **/



/**
 * InlineStyler class
 * 
 * @example
 * <code>
 * $in = new InlineStyler('/path/to/file.html');
 * 
 * or
 * $in = new InlineStyler('http://mysite.com/about.html');
 * 
 * or insert the HTML as string
 * $in = new InlineStyler($htmlString);
 * 
 * echo $in->getInline(); // Get the final inline styled HTML code.
 * </code>
 *
 * @package 	InlineStylerAPI
 * @subpackage 	classes
 **/
class InlineStyler {
	
	// Debug mode
	const DEBUG = false;
	
	const API_URL = 'http://inlinestyler.torchboxapps.com/styler/convert/';
	
	const API_PORT = 80;
	
	const API_VERSION = '1.0';
	
	const VERSION = '0.1';
	
	/**
	 * A random hash that will be used for authenticating
	 * 
	 * @var mixed
	 */
	var $hash;
	
	/**
	 * The HTML code that will be used
	 * 
	 * @var string
	 */
	var $data;
	
	/**
	 * The timeout
	 *
	 * @var	int
	 */
	var $timeOut = 60;
	
	/**
	 * The user agent
	 *
	 * @var	string
	 */
	var $userAgent = 'InlineStylerAPI/1.0';
	
	/**
	 * The response returned from the API call in call()
	 * 
	 * @var string
	 */
	var $response;
	
	/**
	 * Store a URL if supplied
	 * 
	 * @var string
	 */
	var $url;
	
	/**
	 * The constructor of ths InlineStyler
	 * 
	 * @param string $data Can be the path to the file or the HTML content itself
	 * @return void
	 */
	function InlineStyler($data = null)
	{
		// Did we get a path or naybe just data?
		if (@file_exists($data)) {
			if ($fp = @fopen($data, 'r')) {
				// read the file contents...
				$this->data = @fread($fp, filesize($data));
				fclose($fp);
			}
		} elseif (preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $data)) {
			// We got a URI reference
			$this->url = $data;
		} else {
			// The HTML file itself?
			$this->data = $data;
		}
		// Generate the random hash
		$seed = 'JvKnrQWPsThuJteNQAuH';
		$this->hash = sha1(uniqid($seed . mt_rand(2,6), true));
		$this->hash = substr($this->hash, 32);
	}
	
	/**
	 * Makes the request to InlineStyler based on class variables set in other functions.
	 * The API response is stored in $this->response.
	 * 
	 * @return	void
	 * @access  private
	 */
	function _call()
	{
		// Is there any data to send?
		if (!$this->data) {
			if (!$this->url)
				die('There is no data to send. Please set up the data first.');
		}
		
		// Parameters quick set up
		$params = array(
			'returnraw'  => $this->hash,
			'source' 	 => $this->data,
			'source_url' => $this->url
		);
		
		// set options
		$options[CURLOPT_URL] = self::API_URL;
		$options[CURLOPT_PORT] = self::API_PORT;
		$options[CURLOPT_USERAGENT] = $this->userAgent;
		$options[CURLOPT_FOLLOWLOCATION] = true;
		$options[CURLOPT_RETURNTRANSFER] = true;
		$options[CURLOPT_TIMEOUT] = (int) $this->timeOut;
		$options[CURLOPT_POST] = true;
		$options[CURLOPT_POSTFIELDS] = $params;
		
		// init
		$curl = curl_init();

		// set options
		curl_setopt_array($curl, $options);

		// execute
		$response = curl_exec($curl);
		$headers = curl_getinfo($curl);
		
		// fetch errors
		$errorNumber = curl_errno($curl);
		$errorMessage = curl_error($curl);

		// close
		curl_close($curl);
		
		// Invalid headers
		if(!in_array($headers['http_code'], array(0, 200)))
		{
			// Should we provide debug information?
			if(self::DEBUG)
			{
				// make it output proper
				echo '<pre>';

				// dump the header-information
				var_dump($headers);

				// dump the raw response
				var_dump($response);

				// end proper format
				echo '</pre>';

				// stop the script
				exit;
			}

			// throw error
			echo 'Invalid headers ('. $headers['http_code'] .')';
		}
		
		// error?
		if($errorNumber != '') echo ($errorMessage . ' .Error Number:' . $errorNumber);
		
		// return
		$this->response = $response;
		
		return true;
	}
	
	/**
	 * Get the inlined content
	 * 
	 * @return string
	 */
	function getInline() {
		// now call
		if (true === $this->_call())
			return $this->response;
		else
			return false;
	}
	
}