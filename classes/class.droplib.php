<?php
define('VERSION_DROPLIB','2.1.0.c');
/*
Version History:
  2.1.0.c (2012-10-14)
    1) Additional parameter for DropLib_Http::fetch() - use_post
    2) New method DropLib::delta() - takes cursor and returns delta list of changes

  2.1.0.b (2012-07-04)
    1) Initial release as Ecclesiact packaged class set
  2.1.0
    1) Release obtained from CodeCanyon on Extended licence
*/

/**
 * DropLib - DropBox API Class
 *
 * @package DropLib
 * @version 2.1.0
 * @copyright Copyright 2011 by Jonas Doebertin. All rights reserved.
 * @author Jonas Doebertin
 * @license Sold exclusively on CodeCanyon
 */

/*
LICENCE CERTIFICATE : Envato Marketplace Item
==============================================

This document certifies the purchase of:
ONE EXTENDED LICENSE
as defined in the standard terms and conditions on the Envato Marketplaces.

Licensor's Author Username: JonasDoebertin
Licensee: Martin Francis

For the item:
DropLib - DropBox API Class

http://codecanyon.net/item/droplib-dropbox-api-class/173311
Item ID: 173311

Item Purchase Code: 9b10f871-d9bd-40cd-9258-16c3eea9b47e



For any queries related to this document or license please contact envato support via
http://enva.to/marketplacesupport

Envato Pty. Ltd. (ABN 11 119 159 741)
PO Box 21177, Little Lonsdale Street, VIC 8011, Australia

==== THIS IS NOT A TAX RECEIPT OR INVOICE ====
*/

class DropLib extends DropLib_Base{

	/**
	 * API Base URL
	 */
	const API_BASE = 'https://api.dropbox.com/1/';

	/**
	 * API Base URL for downloading files
	 */
	const API_CONTENT_BASE = 'https://api-content.dropbox.com/1/';

	/**
	 * API Base URL for authorization
	 */
	const API_AUTHORIZATION_BASE = 'https://www.dropbox.com/1/oauth/authorize';

	/**
	 * Root folder for full mode
	 */
	const API_ROOT_DROPBOX = 'dropbox';

	/**
	 * Root folder for sandbox mode
	 */
	const API_ROOT_SANDBOX = 'sandbox';

	/**
	 * DropLib_Http Object
	 */
	protected $Http;

	/**
	 * Default DropBox root directory
	 */
	protected $root = self::API_ROOT_DROPBOX;

	/**
	 * API language code
	 */
	//protected $locale;

	/**
	 * Constructor
	 *
	 * @param Array $params Array with DropLib configuration. See constructor code for possible values.
	 * @throws DropLibException
	 */
	public function __construct($params = null){

		$params = (is_array($params)) ? $params : array();
		$defaultParams = array(

			'sslCheck' =>			true,
			'consumerKey' =>		null,
			'consumerSecret' =>		null,
			'tokenKey' =>			null,
			'tokenSecret' =>		null,
			'locale' =>				'en',
			'rootDirectory' =>		self::API_ROOT_DROPBOX

		);
		$params = array_merge($defaultParams, $params);

		if(!$this->strParamsSet($params['consumerKey'], $params['consumerSecret'])){
			throw new DropLibException_InvalidArgument('No consumer token found.');;
		}

		$this->Http = new DropLib_Http($params);
		$this->sslCheck = $params['sslCheck'];

	}

	/**
	 * Get current DropBox root folder
	 *
	 * @return String Current DropBox root folder
	 */
	public function getRoot(){

		return $this->root;

	}

	/**
	 * Set DropBox root folder
	 *
	 * @param String $newRoot New root folder. Either "dropbox" or "sandbox".
	 * @throws DropLibException
	 */
	public function setRoot($newRoot){

		if(in_array(mb_strtolower($newRoot), array('dropbox', 'sandbox'))){
			$this->root = mb_strtolower($newRoot);
		} else{
			throw new DropLibException_InvalidArgument('Invalid argument value.');
		}

	}

	/**
	 * Get current SSL check state
	 *
	 * @return Bool SSL check state
	 */
	public function getSslCheck(){

		return $this->sslCheck;

	}

	/**
	 * Set SSL check state
	 *
	 * @param Bool $newSslCheck New SSL check state
	 * @throws DropLibException
	 */
	public function setSslCheck($newSslCheck){

		if(is_bool($newSslCheck)){
			$this->sslCheck = $newSslCheck;
		} else{
			throw new DropLibException_InvalidArgument('Invalid argument type.');
		}

	}

	/**
	 * Get current locale
	 *
	 * @return String Current locale (language code)
	 */
	public function getLocale(){

		return $this->Http->getLocale();

	}

	/**
	 * Set API locale
	 *
	 * @param String $newLocale New locale (language code)
	 * @throws DropLibException
	 */
	public function setLocale($newLocale){

		$this->Http->setLocale($newLocale);

	}

	/**
	 * Get current oAuth token
	 *
	 * @return Array Current oAuth token. Array(key, secret).
	 */
	public function getToken(){

		return $this->Http->getToken();

	}

	/**
	 * (Deprecated) Fetch oAuth token by passing email adress and password
	 */
	public function authorize($email, $password){

		throw new DropLibException_Deprecated('The function authorize() is not availabe anymore.');

	}

	/**
	 * Step 1 of authentication.
	 * Obtain an OAuth request token to be used for the rest of the
	 * authentication process.
	 *
	 * @return Array oAuth request token. Array(key, secret).
	 * @throws DropLibException
	 */
	public function requestToken(){

		$response = $this->Http->fetch(self::API_BASE.'oauth/request_token', null, false);

		if($response['status'] === 200){
			$token = array();
			parse_str($response['response'], $token);
			$this->Http->setToken($token['oauth_token'], $token['oauth_token_secret']);
			return $this->Http->getToken();
		} else{
			throw new DropLibException_OAuth('Unable to retrieve request token.');
		}

	}

	/**
	 * Step 2 of authentication.
	 * Applications should direct the user to this URL to let the user log in
	 * to Dropbox and choose whether to grant the application the ability to
	 * access files on their behalf.
	 *
	 * Without the user's authorization in this step, it isn't possible for
	 * your application to obtain an access token from accessToken().
	 *
	 * @param String $callback After the user authorizes an application, the user is redirected to the application-served URL provided by this parameter.
	 * @throws DropLibException
	 */
	public function authorizeUrl($callback = null){

		$token = $this->getToken();

		if(is_null($token)){
			throw new DropLibException_OAuth('You need to get an request token before generating the authorization url.');
		}

		$url = self::API_AUTHORIZATION_BASE . '?oauth_token=' . $token['key'];
		if($this->strParamSet($callback)){
			$url .= '&oauth_callback=' . $callback;
		}

		return $url;
	}

	/**
	 * Step 3 of authentication.
	 * After step 2 is complete, the application can call accessToken() to
	 * acquire an access token.
	 *
	 * @return Array oAuth access token. Array(key, secret).
	 * @throws DropLibException
	 */
	public function accessToken(){

		$response = $this->Http->fetch(self::API_BASE . 'oauth/access_token');
		if($response['status'] === 200){
			$token = array();
			parse_str($response['response'], $token);
			$this->Http->setToken($token['oauth_token'], $token['oauth_token_secret']);
			return $this->Http->getToken();
		} else{
			throw new DropLibException_OAuth('Unable to retrieve access token.');
		}
	}

	/**
	 * Retrieves information about the user's account.
	 *
	 * @return Array Associative array. See documentation for examples.
	 */
	public function accountInfo(){

		$response = $this->Http->fetch(self::API_BASE . 'account/info');
		return $this->decodeResponse($response);

	}

	/**
	 * (Deprecated) Create a new DropBox account
	 */
	public function createAccount($firstName, $lastName, $email, $password){

		throw new DropLibException_Deprecated('The function createAccount() is not availabe anymore.');

	}

	/**
	 * Get contents for specified file
	 *
	 * @param String $path The path to the file you want to retrieve.
	 * @param String $revision The revision of the file to retrieve. Defaults to the most recent revision.
	 * @return String Raw file contents
	 * @throws DropLibException
	 */
	public function download($path, $revision = null){

		if(!$this->strParamSet($path)){
			throw new DropLibException_InvalidArgument('Invalid or missing argument.');
		}

		$params = array();
		if($this->strParamSet($revision)){
			$params['rev'] = $revision;
		}

		$response = $this->Http->fetch(self::API_CONTENT_BASE.'files/'.$this->root.'/'.$this->encodePath($path), $params);
		return $this->decodeResponse($response);

	}

	/**
	 * Upload a file to DropBox (max. 300MB)
	 *
	 * @param String $path The path to the folder the file should be uploaded into. This parameter should not point to a file.
	 * @param String $file Absolute(!) local file path.
	 * @param Bool This value determines what happens when there's already a file at the specified path. See documentation.
	 * @return Bool True if upload was successfull
	 * @throws DropLibException
	 */
	public function upload($path, $file, $overwrite = true){

		if(!$this->strParamsSet($path, $file)){
			throw new DropLibException_InvalidArgument('Invalid or missing argument(s).');
		}

		if(!$this->validFile($file)){
			throw new DropLibException_InvalidArgument('File doesn\'t exist or not readable.');
		}

		$file = $this->correctSlashes($file);

		$response = $this->Http->fetch(self::API_CONTENT_BASE . 'files/' . $this->root . '/' . $this->encodePath($path), array(
			'file' => $file,
			'overwrite' => $overwrite
		),true, $file);

		return ($response['status'] === 200) ? true : $this->decodeResponse($response);
	}

	/**
	 * Retrieves file and folder metadata.
	 *
	 * @param String $path File or folder path, relative to DropBox root
	 * @param Bool $list Include directory listing
	 * @param String $hash If a hash is set, this method simply returns true if nothing has changed since the last request. Good for caching.
	 * @param Int $fileLimit Maximum number of file-information to receive
	 * @return Array Associative array. See documentation for examples.
	 * @throws DropLibException
	 */
	public function metadata($path, $list = true, $hash = null, $fileLimit = 10000, $revision = null, $includeDeleted = false){

		if(!$this->strParamSet($path)){
			throw new DropLibException_InvalidArgument('Invalid or missing argument.');
		}

		$params = array(
			'list' => $list,
			'file_limit' => $fileLimit,
			'include_deleted' => $includeDeleted
		);
		if($this->strParamSet($hash)){
			$params['hash'] = $hash;
		}
		if($this->strParamSet(strval($revision))){
			$params['rev'] = $revision;
		}

		$response = $this->Http->fetch(self::API_BASE . 'metadata/' . $this->root . '/' . $this->encodePath($path), $params);
		return ($response['status'] == 304) ? true : $this->decodeResponse($response);

	}

    /**
     * Return "delta entries", intructing you how to update
     * your application state to match the server's state
     * Important: This method does not make changes to the application state
     * @param null|string $cursor Used to keep track of your current state
     * @return array Array of delta entries
     */
    public function delta($cursor = '')
    {
       $params = array();
       if ($cursor){
          $params = array('cursor' => $cursor);
       }
     	$response = $this->Http->fetch(self::API_BASE . 'delta/', $params, true, '', true);
        return $response;
    }


	/**
	 * Obtains metadata for previous revisions of a file.
	 *
	 * Only revisions up to thirty days old are available. You can use the
	 * revision number in conjunction with the restore() call to revert the
	 * file to its previous state.
	 *
	 * @param String $path The path to the file.
	 * @param String $limit The service will not report listings containing more than $limit revisions.
	 * @return Array Associative Array. See documentation for examples.
	 * @throws DropLibException
	 */
	public function revisions($path, $limit = 10){

		if(!$this->strParamSet($path)){
			throw new DropLibException_InvalidArgument('Invalid or missing argument.');
		}

		$params = array(
			'rev_limit' => $limit
		);

		$response = $this->Http->fetch(self::API_BASE . 'revisions/' . $this->root . '/' . $this->encodePath($path), $params);
		return $this->decodeResponse($response);

	}

	/**
	 * Restores a file path to a previous revision.
	 *
	 * @param String $path The path to the file.
	 * @param String $revision The revision of the file to restore.
	 * @return Array Associative Array. See documentation for examples.
	 * @throws DropLibException
	 */
	public function restore($path, $revision){

		if(!$this->strParamSet($path) or !$this->strParamSet(strval($revision))){
			throw new DropLibException_InvalidArgument('Invalid or missing argument(s).');
		}

		$params = array(
			'rev' => $revision
		);

		$response = $this->Http->fetch(self::API_BASE . 'restore/' . $this->root . '/' . $this->encodePath($path), $params);
		return $this->decodeResponse($response);

	}

	/**
	 * Returns metadata for all files and folders that match the search query.
	 *
	 * Searches are limited to the folder path and its sub-folder hierarchy
	 * provided in the call.
	 *
	 * @param String $path The path to the folder you want to search in.
	 * @param String $query The search string. Must be at least three characters long.
	 * @param Int $fileLimit The service will not report listings containing more than file_limit files.
	 * @param Bool $includeDeleted Include deleted files and folders in the search results.
	 * @return Array Associative Array. See documentation for examples.
	 * @throws DropLibException
	 */
	public function search($path, $query, $fileLimit = 10000, $includeDeleted = false){

		if(!$this->strParamsSet($path, strval($query))){
			throw new DropLibException_InvalidArgument('Invalid or missing argument(s).');
		}

		$params = array(
			'query' => $query,
			'file_limit' => $fileLimit,
			'include_deleted' => $includeDeleted
		);

		$response = $this->Http->fetch(self::API_BASE . 'search/' . $this->root . '/' . $this->encodePath($path), $params);
		return $this->decodeResponse($response);

	}

	/**
	 * Creates and returns a shareable link to files or folders.
	 *
	 * Note: Links created by the share() API call expire after thirty days.
	 *
	 * @param String $path The path to the file you want a sharable link to.
	 * @return Array Associative Array. See documentation for examples.
	 * @throws DropLibException
	 */
	public function share($path){

		if(!$this->strParamSet($path)){
			throw new DropLibException_InvalidArgument('Invalid or missing argument.');
		}

		$response = $this->Http->fetch(self::API_BASE . 'shares/' . $this->root . '/' . $this->encodePath($path));
		return $this->decodeResponse($response);

	}

	/**
	 * Returns a link directly to a file.
	 *
	 * Similar to share(). The difference is that this bypasses the Dropbox
	 * webserver, used to provide a preview of the file, so that you can
	 * effectively stream the contents of your media.
	 *
	 * @param String $path The path to the media file you want a direct link to.
	 * @return Array Associative Array. See documentation for examples.
	 * @throws DropLibException
	 */
	public function media($path){

		if(!$this->strParamSet($path)){
			throw new DropLibException_InvalidArgument('Invalid or missing argument.');
		}

		$response = $this->Http->fetch(self::API_BASE . 'media/' . $this->root . '/' . $this->encodePath($path));
		return $this->decodeResponse($response);

	}

	/**
	 * Get a thumbnail of a picture inside the dropbox
	 *
	 * @param String $path Path to image file, relative to DropBox root
	 * @param String $size Thumbnail size. See documentation.
	 * @param String $format Thumbnail file format, either JPEG or PNG
	 * @return String Base64 representation of the thumbnail file
	 * @throws DropLibException
	 */
	public function thumbnail($path, $size = 'small', $format = 'JPEG'){

		if(!$this->strParamSet($path) or
		   !in_array(strtolower($format), array('jpeg', 'png'))){
			throw new DropLibException_InvalidArgument('Invalid or missing argument(s).');
		}

		$params = array(
			'size' => $size,
			'format' => $format
		);

		$response = $this->Http->fetch(self::API_CONTENT_BASE . 'thumbnails/' . $this->root . '/' . $this->encodePath($path), $params);
		return base64_encode($this->decodeResponse($response));

	}

	/**
	 * Copy a file or directory
	 *
	 * @param String $from Path to source, relative to DropBox root
	 * @param String $to Path to destination, relative to DropBox root
	 * @return Array Associative Array. See documentation for examples.
	 * @throws DropLibException
	 */
	public function copy($from, $to){

		if(!$this->strParamsSet($from, $to)){
			throw new DropLibException_InvalidArgument('Invalid or missing argument(s).');
		}

		$response = $this->Http->fetch(self::API_BASE . 'fileops/copy', array(
			'from_path' => $from,
			'to_path' => $to,
			'root' => $this->root
		));
		return $this->decodeResponse($response);

	}

	/**
	 * Create a new folder inside of the DropBox
	 *
	 * @param String $path Path of new folder, relative to DropBox root.
	 * @return Array Associative array. See documentation for examples.
	 * @throws DropLibException
	 */
	public function createFolder($path){

		if(!$this->strParamSet($path)){
			throw new DropLibException_InvalidArgument('Invalid or missing argument.');
		}

		$response = $this->Http->fetch(self::API_BASE . 'fileops/create_folder', array(
			'path' => $path,
			'root' => $this->root
		));
		return $this->decodeResponse($response);

	}

	/**
	 * Delete a folder or file from the DropBox
	 *
	 * @param String $path Path of folder or file, relative to DropBox root.
	 * @return Array Associative array. See documentation for examples.
	 * @throws DropLibException
	 */
	public function delete($path){

		if(!$this->strParamSet($path)){
			throw new DropLibException_InvalidArgument('Invalid or missing argument.');
		}

		$response = $this->Http->fetch(self::API_BASE . 'fileops/delete', array(
			'path' => $path,
			'root' => $this->root
		));
		return $this->decodeResponse($response);

	}

	/**
	 * Move a file or directory
	 *
	 * @param String $from Path to source, relative to DropBox root
	 * @param String $to Path to destination, relative to DropBox root
	 * @return Array Associative Array. See documentation for examples.
	 * @throws DropLibException
	 */
	public function move($from, $to){

		if(!$this->strParamsSet($from, $to)){
			throw new DropLibException_InvalidArgument('Invalid or missing argument(s).');
		}

		$response = $this->Http->fetch(self::API_BASE . 'fileops/move', array(
			'from_path' => $from,
			'to_path' => $to,
			'root' => $this->root
		));
		return $this->decodeResponse($response);

	}

  public function get_version(){
    return VERSION_DROPLIB;
  }
}

class DropLib_Base{

	/**
	 * Checks if a variable is set (not empty) and of string type
	 */
	protected function strParamSet($strParam){

		return isset($strParam) and !empty($strParam) and is_string($strParam);

	}

	/**
	 * Checks if all passed variables are set (not empty) and of string type
	 */
	protected function strParamsSet(){

		$result = true;
		foreach(func_get_args() as $param){
			$result = $result and (isset($param) and !empty($param) and is_string($param));
		}
		return $result;

	}

	/**
	 * Checks if a file exists and is readable
	 */
	protected function validFile($file){

		return is_readable($file);

	}

	/**
	 * Changes double-backslashes (\\) to slashes (/). Necessary on some windows systems.
	 */
	protected function correctSlashes($file){
		return preg_replace("/\\\\/", "/", $file);
	}

	/**
	 * Encode a path parameter the way DropBox likes it.
	 */
	protected function encodePath($path){

		return ltrim(str_replace(array('%2F','~'), array('/','%7E'), rawurlencode($path)), '/');

	}

	/**
	 * Decodes an API response.
	 *
	 * Throws an Exception, if the request was not successfull.
	 *
	 * @throws DropLibException
	 */
	protected function decodeResponse($response){

		/* try to decode json */
		$result = json_decode($response['response'], true);

		/* if deconding was successfull use decoded array, else use raw response */
		$result = ((!is_null($result)) ? $result : $response['response']);

		if($response['status'] !== 200){
			$error = (isset($result['error'])) ? $result['error'] : 'Unknown API error. Check status code (error code)!';
			throw new DropLibException_API($error, $response['status']);
		}

		return $result;

	}

}

/**
 * Generic Exception class
 */
class DropLibException extends Exception{

}

/**
 * Will be thrown if an invalid argument is passed to a DropLib function
 */
class DropLibException_InvalidArgument extends DropLibException{

}

/**
 * Will be throw if any oAuth issues occur
 */
class DropLibException_OAuth extends DropLibException{

}

/**
 * Will be thrown if cURL is not available
 */
class DropLibException_Curl extends DropLibException{

}

/**
 * Will be thrown if the API responds with an error
 */
class DropLibException_API extends DropLibException{

}

/**
 * Will be thrown if an api function is deprecated
 */
class DropLibException_Deprecated extends DropLibException{

}

/**
 * Not used, yet.
 */
class DropLibException_NotImplemented extends DropLibException{

}

class DropLib_Http extends DropLib_Base{

	/**
	 * Perform SSL certificate validation
	 */
	protected $sslCheck;

	/**
	 * API language code
	 */
	protected $locale;

	/**
	 * oAuthConsumer object
	 */
	protected $OAuthConsumer;

	/**
	 * OAuthToken object
	 */
	protected $OAuthToken = null;

	/**
	 * OAuthSignatureMethod object
	 */
	protected $OAuthSignatureMethod;

	/**
	 * Constructor
	 *
	 * Create oAuth consumer, token and signature method objects and set parameters
	 */
	public function __construct($params){

		$this->OAuthConsumer = new OAuthConsumer($params['consumerKey'], $params['consumerSecret']);
		if($this->strParamsSet($params['tokenKey'], $params['tokenSecret'])){
			$this->OAuthToken = new OAuthToken($params['tokenKey'], $params['tokenSecret']);
		}
		$this->OAuthSignatureMethod = new OAuthSignatureMethod_HMAC_SHA1;
		$this->sslCheck = $params['sslCheck'];
		$this->locale = $params['locale'];
	}

	/**
	 * Set oAuth token
	 */
	public function setToken($key, $secret){

		if(!$this->strParamsSet($key, $secret)){
			throw new DropLibException_InvalidArgument;
		}

		if(is_null($this->OAuthToken)){
			$this->OAuthToken = new OAuthToken($key, $secret);
		} else{
			$this->OAuthToken->key = $key;
			$this->OAuthToken->secret = $secret;
		}

	}

	/**
	 * Returns the current oAuth token
	 */
	public function getToken(){

		return (is_null($this->OAuthToken)) ? null : array(
			'key' => $this->OAuthToken->key,
			'secret' => $this->OAuthToken->secret
		);

	}

	/**
	 * Set new API locale
	 */
	public function setLocale($newLocale){

		if(!$this->strParamSet($newLocale)){
			throw new DropLibException_InvalidArgument('Empty argument or invalid argument type.');
		}

		$this->locale = $newLocale;

	}

	/**
	 * Returns the current API locale
	 */
	public function getLocale(){

		return $this->locale;

	}

	/**
	 *
	 */
	public function fetch($url, $params = array(), $useToken = true, $file = null, $use_post = false){

		$defaultParams = array(
			'locale' => $this->locale
		);
		$params = array_merge($defaultParams, (is_array($params)) ? $params : array());

		/**
		 * Check for token and sign request
		 */
		if($useToken and is_null($this->OAuthToken)){
			throw new DropLibException_OAuth('No oAuth token set.');
		}
		$Request = OAuthRequest::from_consumer_and_token($this->OAuthConsumer, (($useToken) ? $this->OAuthToken : null), (($file === null) ? 'GET' : 'POST'), $url, $params);
		$Request->sign_request($this->OAuthSignatureMethod, $this->OAuthConsumer, (($useToken) ? $this->OAuthToken : null));

		/**
		 * Initialize cURL instance
		 */
		if(!function_exists('curl_init')){
			throw new DropLibException_Curl('cURL not available.');
		}
		$ch = curl_init();
//		echo $Request->to_url().chr(10);
		curl_setopt($ch, CURLOPT_URL, $Request->to_url());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		/**
		 * Disable SSL check if necessary
		 */
		if (!$this->sslCheck){
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		}

		/**
		 * Set file upload, if necessary
		 */
		if ($file !== null){
			$postdata = array('file' => "@$file");
			@curl_setopt($ch, CURLOPT_POST, true);
			@curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		}
        if ($use_post){
			@curl_setopt($ch, CURLOPT_POST, true);
			@curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
		/**
		 * Execute request and get response status code
		 */

		$response = curl_exec($ch);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		return array(
			'response' => $response,
			'status' => $status
		);

	}

}

/*
The MIT License

Copyright (c) 2007 Andy Smith - Modified 2011 by Jonas Doebertin

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

class OAuthConsumer {
  public $key;
  public $secret;

  function __construct($key, $secret, $callback_url=NULL) {
    $this->key = $key;
    $this->secret = $secret;
    $this->callback_url = $callback_url;
  }

  function __toString() {
    return "OAuthConsumer[key=$this->key,secret=$this->secret]";
  }
}

class OAuthToken {
  // access tokens and request tokens
  public $key;
  public $secret;

  /**
   * key = the token
   * secret = the token secret
   */
  function __construct($key, $secret) {
    $this->key = $key;
    $this->secret = $secret;
  }

  /**
   * generates the basic string serialization of a token that a server
   * would respond to request_token and access_token calls with
   */
  function to_string() {
    return "oauth_token=" .
           OAuthUtil::urlencode_rfc3986($this->key) .
           "&oauth_token_secret=" .
           OAuthUtil::urlencode_rfc3986($this->secret);
  }

  function __toString() {
    return $this->to_string();
  }
}

/**
 * A class for implementing a Signature Method
 * See section 9 ("Signing Requests") in the spec
 */
abstract class OAuthSignatureMethod {
  /**
   * Needs to return the name of the Signature Method (ie HMAC-SHA1)
   * @return string
   */
  abstract public function get_name();

  /**
   * Build up the signature
   * NOTE: The output of this function MUST NOT be urlencoded.
   * the encoding is handled in OAuthRequest when the final
   * request is serialized
   * @param OAuthRequest $request
   * @param OAuthConsumer $consumer
   * @param OAuthToken $token
   * @return string
   */
  abstract public function build_signature($request, $consumer, $token);

  /**
   * Verifies that a given signature is correct
   * @param OAuthRequest $request
   * @param OAuthConsumer $consumer
   * @param OAuthToken $token
   * @param string $signature
   * @return bool
   */
  public function check_signature($request, $consumer, $token, $signature) {
    $built = $this->build_signature($request, $consumer, $token);
    return $built == $signature;
  }
}

/**
 * The HMAC-SHA1 signature method uses the HMAC-SHA1 signature algorithm as defined in [RFC2104]
 * where the Signature Base String is the text and the key is the concatenated values (each first
 * encoded per Parameter Encoding) of the Consumer Secret and Token Secret, separated by an '&'
 * character (ASCII code 38) even if empty.
 *   - Chapter 9.2 ("HMAC-SHA1")
 */
class OAuthSignatureMethod_HMAC_SHA1 extends OAuthSignatureMethod {
  function get_name() {
    return "HMAC-SHA1";
  }

  public function build_signature($request, $consumer, $token) {
    $base_string = $request->get_signature_base_string();
    $request->base_string = $base_string;

    $key_parts = array(
      $consumer->secret,
      ($token) ? $token->secret : ""
    );

    $key_parts = OAuthUtil::urlencode_rfc3986($key_parts);
    $key = implode('&', $key_parts);

    return base64_encode(hash_hmac('sha1', $base_string, $key, true));
  }
}

/**
 * The PLAINTEXT method does not provide any security protection and SHOULD only be used
 * over a secure channel such as HTTPS. It does not use the Signature Base String.
 *   - Chapter 9.4 ("PLAINTEXT")
 */
class OAuthSignatureMethod_PLAINTEXT extends OAuthSignatureMethod {
  public function get_name() {
    return "PLAINTEXT";
  }

  /**
   * oauth_signature is set to the concatenated encoded values of the Consumer Secret and
   * Token Secret, separated by a '&' character (ASCII code 38), even if either secret is
   * empty. The result MUST be encoded again.
   *   - Chapter 9.4.1 ("Generating Signatures")
   *
   * Please note that the second encoding MUST NOT happen in the SignatureMethod, as
   * OAuthRequest handles this!
   */
  public function build_signature($request, $consumer, $token) {
    $key_parts = array(
      $consumer->secret,
      ($token) ? $token->secret : ""
    );

    $key_parts = OAuthUtil::urlencode_rfc3986($key_parts);
    $key = implode('&', $key_parts);
    $request->base_string = $key;

    return $key;
  }
}

/**
 * The RSA-SHA1 signature method uses the RSASSA-PKCS1-v1_5 signature algorithm as defined in
 * [RFC3447] section 8.2 (more simply known as PKCS#1), using SHA-1 as the hash function for
 * EMSA-PKCS1-v1_5. It is assumed that the Consumer has provided its RSA public key in a
 * verified way to the Service Provider, in a manner which is beyond the scope of this
 * specification.
 *   - Chapter 9.3 ("RSA-SHA1")
 */
abstract class OAuthSignatureMethod_RSA_SHA1 extends OAuthSignatureMethod {
  public function get_name() {
    return "RSA-SHA1";
  }

  // Up to the SP to implement this lookup of keys. Possible ideas are:
  // (1) do a lookup in a table of trusted certs keyed off of consumer
  // (2) fetch via http using a url provided by the requester
  // (3) some sort of specific discovery code based on request
  //
  // Either way should return a string representation of the certificate
  protected abstract function fetch_public_cert(&$request);

  // Up to the SP to implement this lookup of keys. Possible ideas are:
  // (1) do a lookup in a table of trusted certs keyed off of consumer
  //
  // Either way should return a string representation of the certificate
  protected abstract function fetch_private_cert(&$request);

  public function build_signature($request, $consumer, $token) {
    $base_string = $request->get_signature_base_string();
    $request->base_string = $base_string;

    // Fetch the private key cert based on the request
    $cert = $this->fetch_private_cert($request);

    // Pull the private key ID from the certificate
    $privatekeyid = openssl_get_privatekey($cert);

    // Sign using the key
    $ok = openssl_sign($base_string, $signature, $privatekeyid);

    // Release the key resource
    openssl_free_key($privatekeyid);

    return base64_encode($signature);
  }

  public function check_signature($request, $consumer, $token, $signature) {
    $decoded_sig = base64_decode($signature);

    $base_string = $request->get_signature_base_string();

    // Fetch the public key cert based on the request
    $cert = $this->fetch_public_cert($request);

    // Pull the public key ID from the certificate
    $publickeyid = openssl_get_publickey($cert);

    // Check the computed signature against the one passed in the query
    $ok = openssl_verify($base_string, $decoded_sig, $publickeyid);

    // Release the key resource
    openssl_free_key($publickeyid);

    return $ok == 1;
  }
}

class OAuthRequest {
  protected $parameters;
  protected $http_method;
  protected $http_url;
  // for debug purposes
  public $base_string;
  public static $version = '1.0';
  public static $POST_INPUT = 'php://input';

  function __construct($http_method, $http_url, $parameters=NULL) {
    $parameters = ($parameters) ? $parameters : array();
    $parameters = array_merge( OAuthUtil::parse_parameters(parse_url($http_url, PHP_URL_QUERY)), $parameters);
    $this->parameters = $parameters;
    $this->http_method = $http_method;
    $this->http_url = $http_url;
  }


  /**
   * attempt to build up a request from what was passed to the server
   */
  public static function from_request($http_method=NULL, $http_url=NULL, $parameters=NULL) {
    $scheme = (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on")
              ? 'http'
              : 'https';
    $http_url = ($http_url) ? $http_url : $scheme .
                              '://' . $_SERVER['HTTP_HOST'] .
                              ':' .
                              $_SERVER['SERVER_PORT'] .
                              $_SERVER['REQUEST_URI'];
    $http_method = ($http_method) ? $http_method : $_SERVER['REQUEST_METHOD'];

    // We weren't handed any parameters, so let's find the ones relevant to
    // this request.
    // If you run XML-RPC or similar you should use this to provide your own
    // parsed parameter-list
    if (!$parameters) {
      // Find request headers
      $request_headers = OAuthUtil::get_headers();

      // Parse the query-string to find GET parameters
      $parameters = OAuthUtil::parse_parameters($_SERVER['QUERY_STRING']);

      // It's a POST request of the proper content-type, so parse POST
      // parameters and add those overriding any duplicates from GET
      if ($http_method == "POST"
          &&  isset($request_headers['Content-Type'])
          && strstr($request_headers['Content-Type'],
                     'application/x-www-form-urlencoded')
          ) {
        $post_data = OAuthUtil::parse_parameters(
          file_get_contents(self::$POST_INPUT)
        );
        $parameters = array_merge($parameters, $post_data);
      }

      // We have a Authorization-header with OAuth data. Parse the header
      // and add those overriding any duplicates from GET or POST
      if (isset($request_headers['Authorization']) && substr($request_headers['Authorization'], 0, 6) == 'OAuth ') {
        $header_parameters = OAuthUtil::split_header(
          $request_headers['Authorization']
        );
        $parameters = array_merge($parameters, $header_parameters);
      }

    }

    return new OAuthRequest($http_method, $http_url, $parameters);
  }

  /**
   * pretty much a helper function to set up the request
   */
  public static function from_consumer_and_token($consumer, $token, $http_method, $http_url, $parameters=NULL) {
    $parameters = ($parameters) ?  $parameters : array();
    $defaults = array("oauth_version" => OAuthRequest::$version,
                      "oauth_nonce" => OAuthRequest::generate_nonce(),
                      "oauth_timestamp" => OAuthRequest::generate_timestamp(),
                      "oauth_consumer_key" => $consumer->key);
    if ($token)
      $defaults['oauth_token'] = $token->key;

    $parameters = array_merge($defaults, $parameters);

    return new OAuthRequest($http_method, $http_url, $parameters);
  }

  public function set_parameter($name, $value, $allow_duplicates = true) {
    if ($allow_duplicates && isset($this->parameters[$name])) {
      // We have already added parameter(s) with this name, so add to the list
      if (is_scalar($this->parameters[$name])) {
        // This is the first duplicate, so transform scalar (string)
        // into an array so we can add the duplicates
        $this->parameters[$name] = array($this->parameters[$name]);
      }

      $this->parameters[$name][] = $value;
    } else {
      $this->parameters[$name] = $value;
    }
  }

  public function get_parameter($name) {
    return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
  }

  public function get_parameters() {
    return $this->parameters;
  }

  public function unset_parameter($name) {
    unset($this->parameters[$name]);
  }

  /**
   * The request parameters, sorted and concatenated into a normalized string.
   * @return string
   */
  public function get_signable_parameters() {
    // Grab all parameters
    $params = $this->parameters;

    // Remove oauth_signature if present
    // Ref: Spec: 9.1.1 ("The oauth_signature parameter MUST be excluded.")
    if (isset($params['oauth_signature'])) {
      unset($params['oauth_signature']);
    }

    return OAuthUtil::build_http_query($params);
  }

  /**
   * Returns the base string of this request
   *
   * The base string defined as the method, the url
   * and the parameters (normalized), each urlencoded
   * and the concated with &.
   */
  public function get_signature_base_string() {
    $parts = array(
      $this->get_normalized_http_method(),
      $this->get_normalized_http_url(),
      $this->get_signable_parameters()
    );

    $parts = OAuthUtil::urlencode_rfc3986($parts);

    return implode('&', $parts);
  }

  /**
   * just uppercases the http method
   */
  public function get_normalized_http_method() {
    return strtoupper($this->http_method);
  }

  /**
   * parses the url and rebuilds it to be
   * scheme://host/path
   */
  public function get_normalized_http_url() {
    $parts = parse_url($this->http_url);

    $scheme = (isset($parts['scheme'])) ? $parts['scheme'] : 'http';
    $port = (isset($parts['port'])) ? $parts['port'] : (($scheme == 'https') ? '443' : '80');
    $host = (isset($parts['host'])) ? $parts['host'] : '';
    $path = (isset($parts['path'])) ? $parts['path'] : '';

    if (($scheme == 'https' && $port != '443')
        || ($scheme == 'http' && $port != '80')) {
      $host = "$host:$port";
    }
    return "$scheme://$host$path";
  }

  /**
   * builds a url usable for a GET request
   */
  public function to_url() {
    $post_data = $this->to_postdata();
    $out = $this->get_normalized_http_url();
    if ($post_data) {
      $out .= '?'.$post_data;
    }
    return $out;
  }

  /**
   * builds the data one would send in a POST request
   */
  public function to_postdata() {
    return OAuthUtil::build_http_query($this->parameters);
  }

  /**
   * builds the Authorization: header
   */
  public function to_header($realm=null) {
    $first = true;
	if($realm) {
      $out = 'Authorization: OAuth realm="' . OAuthUtil::urlencode_rfc3986($realm) . '"';
      $first = false;
    } else
      $out = 'Authorization: OAuth';

    $total = array();
    foreach ($this->parameters as $k => $v) {
      if (substr($k, 0, 5) != "oauth") continue;
      if (is_array($v)) {
        throw new DropLibException_OAuth('Arrays not supported in headers');
      }
      $out .= ($first) ? ' ' : ',';
      $out .= OAuthUtil::urlencode_rfc3986($k) .
              '="' .
              OAuthUtil::urlencode_rfc3986($v) .
              '"';
      $first = false;
    }
    return $out;
  }

  public function __toString() {
    return $this->to_url();
  }


  public function sign_request($signature_method, $consumer, $token) {
    $this->set_parameter(
      "oauth_signature_method",
      $signature_method->get_name(),
      false
    );
    $signature = $this->build_signature($signature_method, $consumer, $token);
    $this->set_parameter("oauth_signature", $signature, false);
  }

  public function build_signature($signature_method, $consumer, $token) {
    $signature = $signature_method->build_signature($this, $consumer, $token);
    return $signature;
  }

  /**
   * util function: current timestamp
   */
  private static function generate_timestamp() {
    return time();
  }

  /**
   * util function: current nonce
   */
  private static function generate_nonce() {
    $mt = microtime();
    $rand = mt_rand();

    return md5($mt . $rand); // md5s look nicer than numbers
  }
}

class OAuthUtil {
  public static function urlencode_rfc3986($input) {
  if (is_array($input)) {
    return array_map(array('OAuthUtil', 'urlencode_rfc3986'), $input);
  } else if (is_scalar($input)) {
    return str_replace(
      '+',
      ' ',
      str_replace('%7E', '~', rawurlencode($input))
    );
  } else {
    return '';
  }
}


  // This decode function isn't taking into consideration the above
  // modifications to the encoding process. However, this method doesn't
  // seem to be used anywhere so leaving it as is.
  public static function urldecode_rfc3986($string) {
    return urldecode($string);
  }

  // Utility function for turning the Authorization: header into
  // parameters, has to do some unescaping
  // Can filter out any non-oauth parameters if needed (default behaviour)
  // May 28th, 2010 - method updated to tjerk.meesters for a speed improvement.
  //                  see http://code.google.com/p/oauth/issues/detail?id=163
  public static function split_header($header, $only_allow_oauth_parameters = true) {
    $params = array();
    if (preg_match_all('/('.($only_allow_oauth_parameters ? 'oauth_' : '').'[a-z_-]*)=(:?"([^"]*)"|([^,]*))/', $header, $matches)) {
      foreach ($matches[1] as $i => $h) {
        $params[$h] = OAuthUtil::urldecode_rfc3986(empty($matches[3][$i]) ? $matches[4][$i] : $matches[3][$i]);
      }
      if (isset($params['realm'])) {
        unset($params['realm']);
      }
    }
    return $params;
  }

  // helper to try to sort out headers for people who aren't running apache
  public static function get_headers() {
    if (function_exists('apache_request_headers')) {
      // we need this to get the actual Authorization: header
      // because apache tends to tell us it doesn't exist
      $headers = apache_request_headers();

      // sanitize the output of apache_request_headers because
      // we always want the keys to be Cased-Like-This and arh()
      // returns the headers in the same case as they are in the
      // request
      $out = array();
      foreach ($headers AS $key => $value) {
        $key = str_replace(
            " ",
            "-",
            ucwords(strtolower(str_replace("-", " ", $key)))
          );
        $out[$key] = $value;
      }
    } else {
      // otherwise we don't have apache and are just going to have to hope
      // that $_SERVER actually contains what we need
      $out = array();
      if( isset($_SERVER['CONTENT_TYPE']) )
        $out['Content-Type'] = $_SERVER['CONTENT_TYPE'];
      if( isset($_ENV['CONTENT_TYPE']) )
        $out['Content-Type'] = $_ENV['CONTENT_TYPE'];

      foreach ($_SERVER as $key => $value) {
        if (substr($key, 0, 5) == "HTTP_") {
          // this is chaos, basically it is just there to capitalize the first
          // letter of every word that is not an initial HTTP and strip HTTP
          // code from przemek
          $key = str_replace(
            " ",
            "-",
            ucwords(strtolower(str_replace("_", " ", substr($key, 5))))
          );
          $out[$key] = $value;
        }
      }
    }
    return $out;
  }

  // This function takes a input like a=b&a=c&d=e and returns the parsed
  // parameters like this
  // array('a' => array('b','c'), 'd' => 'e')
  public static function parse_parameters( $input ) {
    if (!isset($input) || !$input) return array();

    $pairs = explode('&', $input);

    $parsed_parameters = array();
    foreach ($pairs as $pair) {
      $split = explode('=', $pair, 2);
      $parameter = OAuthUtil::urldecode_rfc3986($split[0]);
      $value = isset($split[1]) ? OAuthUtil::urldecode_rfc3986($split[1]) : '';

      if (isset($parsed_parameters[$parameter])) {
        // We have already recieved parameter(s) with this name, so add to the list
        // of parameters with this name

        if (is_scalar($parsed_parameters[$parameter])) {
          // This is the first duplicate, so transform scalar (string) into an array
          // so we can add the duplicates
          $parsed_parameters[$parameter] = array($parsed_parameters[$parameter]);
        }

        $parsed_parameters[$parameter][] = $value;
      } else {
        $parsed_parameters[$parameter] = $value;
      }
    }
    return $parsed_parameters;
  }

  public static function build_http_query($params) {
    if (!$params) return '';

    // Urlencode both keys and values
    $keys = OAuthUtil::urlencode_rfc3986(array_keys($params));
    $values = OAuthUtil::urlencode_rfc3986(array_values($params));
    $params = array_combine($keys, $values);

    // Parameters are sorted by name, using lexicographical byte value ordering.
    // Ref: Spec: 9.1.1 (1)
    uksort($params, 'strcmp');

    $pairs = array();
    foreach ($params as $parameter => $value) {
      if (is_array($value)) {
        // If two or more parameters share the same name, they are sorted by their value
        // Ref: Spec: 9.1.1 (1)
        // June 12th, 2010 - changed to sort because of issue 164 by hidetaka
        sort($value, SORT_STRING);
        foreach ($value as $duplicate_value) {
          $pairs[] = $parameter . '=' . $duplicate_value;
        }
      } else {
        $pairs[] = $parameter . '=' . $value;
      }
    }
    // For each parameter, the name is separated from the corresponding value by an '=' character (ASCII code 61)
    // Each name-value pair is separated by an '&' character (ASCII code 38)
    return implode('&', $pairs);
  }
}


?>