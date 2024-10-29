<?php

if ( ! defined( 'ABSPATH' ) ) exit;//don't allow peekaboos

/**
 *
 * Twitter API Request Class
 * 
 */
 
class atsCurlRequest {
	
	private $api_key;
	private $api_secret;
	public $bearer_token;
	
	public function __construct() {
		
	}
	
	/**
	 * function atsRequestBearerToken
	 */
	public function atsRequestBearerToken() {
		
		//set request url
		$request_url = 'https://api.twitter.com/oauth2/token';
		
		//encode credentials from api (consumer) key and secret per Twitter API docs.
		$settings = (array) get_option( 'ats-settings' );
		$this->api_key = trim($settings['ats-api-key']);
		$this->api_secret = trim($settings['ats-api-secret']);
		
		$oath_credentials = base64_encode( urlencode($this->api_key).':'.urlencode($this->api_secret) );
		
		$response = wp_remote_retrieve_body( wp_remote_post( $request_url, array( 'headers' => array('Authorization'=>'Basic '.$oath_credentials ), 'body'=> array( 'grant_type'=>'client_credentials') ) ) );
		
		$json = json_decode($response,true);
		
		if( is_array($json) && $json['token_type']=='bearer' ) {
			$this->bearer_token = $json['access_token'];
		}
		
		//save bearer_token as options && timestamp it
		update_option( 'ats-bearer-token', $this->bearer_token );
		update_option( 'ats-bearer-token-timestamp', time() );
		
		//close resource and return
		return $this->bearer_token;
	}
	
	/**
	 * function atsGetUserTweets
	 */
	public function atsGetUserTweets( $twitter_user, $tweet_count ) {
		
		//set request url
		$request_url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
		
		//check bearer token exist ! then create
		$bearer_token_get = get_option( 'ats-bearer-token' );
		if( $bearer_token_get!=''||null ) {
			$bearer_token_nonce = $bearer_token_get;
		}else{
			$bearer_token_nonce = $this->atsRequestBearerToken();
		}	
		
		$get_request = '?screen_name='.$twitter_user.'&trim_user=0&include_rts=0&exclude_replies=1&count='.$tweet_count.'&tweet_mode=extended';	
		$response = wp_remote_retrieve_body( wp_remote_get( $request_url.$get_request, array( 'headers' => array('Authorization'=>'Bearer '.$bearer_token_nonce) ) ) ); 		
		
		$json = json_decode($response);
		
		if( isset($json->errors[0]->code) && $json->errors[0]->code == 215 ) {
			return 'bad-auth';
		}else{
			return $response;
		}
	
	}
} 