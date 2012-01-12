<?php

/**
 * FileRest is a REST simulation client for PHP.
 *
 * It takes the same parameters and arguments as Pest but instead of actually calling an API it uses the local file system
 * as if the remote side was an API. This is similar to fixtures in concept but could be used on a standalone system in
 * production to help bridge JS to PHP.
 *
 * See http://github.com/educoder/pest for details on the project that inspired this.
 *
 * (c) Darren Schreiber, 2011
 * (c) 2600hz http://www.2600hz.org/
 *
 * This code is licensed for use, modification, and distribution
 * under the terms of the MIT License (see http://en.wikipedia.org/wiki/MIT_License)
 */
class FileRest {
    public $base_dir;
	public $last_error;
	public $regex;


    public function __construct($base_url,$rest) {
        $this->base_dir = $base_url;
		$this->restapi = $rest;
    }

    public function get($url) {
        // Simulate a GET from a file & decode
        $filename = $this->filename($url);
		if(file_exists($filename)) {
	        $fp = fopen($filename, 'r');
	        $body = fgets($fp);
	        fclose($fp);
			return $body;
		} else {
			$this->last_error = 'Account Does Not Exist';
        	return FALSE;	
		}
    }

    public function post($url, $data) {
        // Simulate a POST from a file and return a response on success
        $filename = $this->filename($url);
       	$fp = fopen($filename, 'w');
       	fputs($fp, $data);
       	fclose($fp);
		return TRUE;
    }

    public function put($url, $data) {
        // Simulate a PUT from a file & decode
        $filename = $this->filename($url);
       	$fp = fopen($filename, 'w');
       	fputs($fp, $data);
       	fclose($fp);
		return TRUE;
    }

    public function delete($url) {
        // Simulate a DELETE from a file & decode
        $filename = $this->filename($url);

		if(file_exists($filename)) {
			unlink($filename);
			return TRUE;
		} else {
			$this->last_error = 'Account Does Not Exist';
			return FALSE;
		}
        
    }

	public function options($url) {
		$this->last_error = 'Not Supported';
		return FALSE;
	}

    public function filename($uri) {
        // Turn a URL into a filename instead. Ensure consistency across GET/PUT/POST/DELETE requests
        preg_match($this->regex, $uri, $matches);
        // echo print_r($matches, TRUE);

        $version = $matches[1];
        $account_id = $matches[2];
        $method = $matches[3];
        $id = $matches[4];
		$key = isset($matches[5]) ? $matches[5] : 'data';

        $folder = $this->base_dir . '' . $account_id . '/' . $method . '/' . $id . '/';
        if (!file_exists($folder)) {
            mkdir ($folder, 0777, TRUE);
        }
        return $folder . $key;
    }

	public function json_error() {
		    switch (json_last_error()) {
		        case JSON_ERROR_NONE:
		            return FALSE;
		        break;
		        case JSON_ERROR_DEPTH:
		            $this->last_error = 'JSON: - Maximum stack depth exceeded';
					return TRUE;
		        break;
		        case JSON_ERROR_STATE_MISMATCH:
		            $this->last_error = 'JSON: - Underflow or the modes mismatch';
					return TRUE;
		        break;
		        case JSON_ERROR_CTRL_CHAR:
		            $this->last_error = 'JSON: - Unexpected control character found';
					return TRUE;
		        break;
		        case JSON_ERROR_SYNTAX:
		            $this->last_error = 'JSON: - Syntax error, malformed JSON';
					return TRUE;
		        break;
		        case JSON_ERROR_UTF8:
		            $this->last_error = 'JSON: - Malformed UTF-8 characters, possibly incorrectly encoded';
					return TRUE;
		        break;
		        default:
		            $this->last_error = 'JSON: - Unknown error';
					return TRUE;
		        break;
		    }
	}

	public function send($data) {
		if(!empty($this->last_error) && $data == 'null') {
			$stuff['data']['success'] = FALSE;
			$stuff['data']['message'] = $this->last_error;
		} else {
			if($data === TRUE or $data === FALSE) {
				$stuff['data']['success'] = $data;
				if(!empty($this->last_error)) {
					$stuff['data']['message'] = $this->last_error;
				}
			} else {
				//Don't allow double data'ssssssss
				if(array_key_exists('data',$data)) {
					$stuff = $data;
				} else {
					$stuff['data'] = $data;
				}		
			}
		}
		$this->restapi->sendData($stuff);
	}
}

