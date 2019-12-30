<?php
namespace App\Deezer;
/**
* This class will help you to interact with the Deezer API
*
* This is a really simple implementation and it will just help to bootstrap a project using the Deezer API.
*
* For more informations about the api please visit http://www.deezer.com/fr/developers/simpleapi
*
* @author Mathieu BUONOMO <mbuonomo@gmail.com>
* @version 0.1
*/
class DZApi
{
	
	/**
	 * This is your API key
	 *
	 * You have to fill this with your own key
	 *
	 * @var string
	 */
	private $_sApiKey 		= "";
	/**
	 * This is your Secret key
	 *
	 * You have to fill this with your own key
	 *
	 * @var string
	 */
	private $_sSecretKey	= "";
	/**
	 * This token will be set during the oAuth process
	 *
	 * @var string
	 */
	private $_sToken		= null;
	
	/**
	 * This is the url used to connect
	 *
	 * @var string
	 */
	private $_sAuthUrl		= "http://connect.deezer.com/oauth/";
	/**
	 * This is the url to call the API
	 *
	 * @var string
	 */
	private $_sApiUrl		= "http://api.deezer.com/2.0";
	/**
	 * This method will be called to send a request
	 *
	 * Really simple cUrl :)
	 *
	 * @param string $sUrl 
	 * @return void
	 */
	public function sendRequest($sUrl){
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $sUrl);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_HEADER, false);
		$output = curl_exec($c);
		if($output === false)
		{
			trigger_error('Erreur curl : '.curl_error($c),E_USER_WARNING);
		}
		else
		{
			curl_close($c);
			return $output;
		}
	}
	
	/**
	 * This method return the url to call for the authentification
	 *
	 * @param string $sRedirectUrl 
	 * @param array $aPerms 
	 * @return string
	 */
	public function getAuthUrl($sRedirectUrl, $aPerms = array("basic_access")){
		return $this->_sAuthUrl."auth.php?app_id=".$this->_sApiKey."&redirect_uri=".$sRedirectUrl."&perms=".implode(',', $aPerms);
	}
	
	/**
	 * This method will get the token
	 *
	 * @param string $sCode 
	 * @return string
	 * @author Mathieu BUONOMO
	 */
	public function apiconnect($sCode){
		$sUrl = $this->_sAuthUrl."access_token.php?app_id=".$this->_sApiKey."&secret=".$this->_sSecretKey."&code=".$sCode;
		$response = $this->sendRequest($sUrl);
		$params    = null;
		parse_str($response, $params);		
		$this->_sToken = $params['access_token'];
		
		return $params['access_token'];
	}
	
	/**
	 * Call the api
	 *
	 * @param string $sUrl 
	 * @param array $aParams 
	 * @return void
	 * @author Mathieu BUONOMO
	 */
	public function api($sUrl, $aParams){
		$sGet = $this->_sApiUrl.$sUrl."?access_token=".$this->_sToken;
		return json_decode($this->sendRequest($sGet));
	}
	
	
}
?>