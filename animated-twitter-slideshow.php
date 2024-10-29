<?php
/*
Plugin Name: Animated Twitter Slideshow
Plugin URI: http://www.wholahoop.com/animated-twitter-slideshow/
Description: A Simple, easy to use plug-in for creating a responsive, attractive slideshow based on a Twitter&trade; user timeline.
Author: Jeff Kowalski
Author URI: http://wholahoop.com
Text Domain: animated-twitter-slideshow
Version: 1.0
Licenced under the GNU GPL:

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if ( ! defined( 'ABSPATH' ) ) exit;//don't allow peekaboos

define( 'ATS_PLUGIN_ROOT_PATH', plugin_dir_path( __FILE__ ) );
define( 'ATS_PLUGIN_ROOT_URL', plugins_url( '/',__FILE__) );

/**
 *
 * Class: animatedTwitterSlideshow
 *
 */
 
class animatedTwitterSlideshow 
{
 	
 	/**
 	 * function __constructor
 	 */
	public function __construct()	{
		include_once( ATS_PLUGIN_ROOT_PATH . 'includes/scripts.php' );
		include_once( ATS_PLUGIN_ROOT_PATH . 'includes/twitterCurlRequest.php' );
	} 	
	
	
	static function atsInstall() {		
	}
	
	/**
	 * delete plugin options in WP
	 */
	static function atsUninstall() {
		delete_option( 'ats-cached-tweets' );
		delete_option( 'ats-cached-tweets-timestamp' );
		delete_option( 'ats-bearer-token' );
		delete_option( 'ats-bearer-token-timestamp' );
		delete_option( 'ats-settings' );
	}
	
/*******************************************/
/* Admin Related Methods etc.
/*******************************************/
 	
 	/**
 	 * function atsCreateAdmin
 	 */
 	public function atsCreateAdmin() {
 		add_action('admin_menu', array( $this, 'atsAdminMenu' ));
 		add_action( 'admin_init', array( $this,'atsRegisterPlugInFields' ));
 	}
 	
 	 /**
 	 * function atsAdminMenu
 	 */
 	public 	function atsAdminMenu() {
	
		//create new top-level menu
		add_menu_page(
		'Twitter Slideshow',//admin wp page title
		'Twitter Slideshow',//admin menu display title 
		'administrator',//access
		'animated-twitter-slideshow',//menu slug
		array($this,'atsAdminOptionsPage'),//callback
		'dashicons-twitter',//menu icon
		99//menu position
		);
		
	}

	/**
 	* function atsAdminOptionsPage
 	*/
	public	function atsAdminOptionsPage() {
	
	 if ( isset( $_GET['settings-updated'] ) ) {
	 	add_settings_error( 'ats-settings', 'plugin_message', __( 'Settings Saved', 'animated-twitter-slidewhow' ), 'updated' );
	 	add_settings_error( 'ats-settings', 'plugin_message', __( 'Cache Cleared', 'animated-twitter-slidewhow' ), 'updated' );
	 	//clear cache saved in wp option
		delete_option( 'ats-cached-tweets' );
	 }
	
	add_settings_error( 'ats-settings', 'plugin_message', __( 'To add the Animated Twitter Slideshow to your site, simply use the <em>[animated-twitter-slideshow]</em> shortcode in your content section.<br>To add directly to your php template code add <em>echo do_shortcode(\'[animated-twitter-slideshow]\');</em> directly to the portion of the template you wish to display in.', 'animated-twitter-slideshow' ), 'update-nag' );
	
	?>
	  <div class="wrap">	
	    	<h2><?php _e( 'Animated Twitter Slideshow', 'textdomain'); ?></h2>
	      <?php  settings_errors( 'ats-settings' ); ?>
	      <form id="slideshow-settings" action="options.php" method="POST">
	        <?php settings_fields( 'ats-settings-group' ); ?>
	        <?php do_settings_sections( 'ats-settings' ); ?>
	        <?php submit_button(); ?>
	      </form>
  	</div>
	<?php 

	}
	
	/**
	 * function atsRegisterPlugInFields
	 */
	public function atsRegisterPlugInFields() {
 
		//Register Settings Group
	  register_setting( 'ats-settings-group', 'ats-settings' );
		
		//Register Settings Sections
		add_settings_section( 'ats-twitter-display-section', __( 'Twitter Slideshow Settings', 'textdomain' ), array( $this, 'atsDisplaySectionCallback' ), 'ats-settings' );
		
		//Display Section Fields Register
		
		add_settings_field( 'ats-api-key', __( 'Twitter Consumer Key (API Key)', 'textdomain' ), array( $this, 'atsPlugInFieldCreate' ), 'ats-settings', 'ats-twitter-display-section', $args=array('name'=>'ats-api-key', 'type'=>'text', 'instructions'=>'Enter the Consumer Key for your registered Twitter application. Go to <a href="https://apps.twitter.com/" target="_blank">Apps.Twitter.com</a> and follow the instructions from the <strong>readme.txt</strong> to setup your API Key and Secret below.') );
		
		add_settings_field( 'ats-api-secret', __( 'Twitter Consumer Secret (API Secret)', 'textdomain' ), array( $this, 'atsPlugInFieldCreate' ), 'ats-settings', 'ats-twitter-display-section', $args=array('name'=>'ats-api-secret', 'type'=>'text', 'instructions'=>'Enter the Consumer Secret for your registered Twitter application (see <strong>readme.txt</strong>).') );
		
		add_settings_field( 'ats-twitter-users-field', __( 'Twitter User', 'textdomain' ), array( $this, 'atsPlugInFieldCreate' ), 'ats-settings', 'ats-twitter-display-section', $args=array('name'=>'ats-twitter-users-field', 'type'=>'text', 'instructions'=>'Enter the Twitter User Screen Name to display. Example: @wholahoopmedia.') );
		
		add_settings_field( 'ats-tweet-count', __( 'Max Tweets to Display', 'textdomain' ), array( $this, 'atsPlugInFieldCreate' ), 'ats-settings', 'ats-twitter-display-section', $args=array('name'=>'ats-tweet-count', 'type'=>'select', 'items'=>array("1","2","3","4","5","6","7","8","9","10"), 'instructions'=>'Enter the maximum number of tweets (slides) to display.<br>The actual number displayed may be lower due to Twitters calculations, and retweets.') );
		
		add_settings_field( 'ats-slideshow-delay', __( 'Slideshow Delay', 'textdomain' ), array( $this, 'atsPlugInFieldCreate' ), 'ats-settings', 'ats-twitter-display-section', $args=array('name'=>'ats-slideshow-delay', 'type'=>'select', 'items'=>array("4","5","6","7","8","9","10"), 'instructions' =>'Select the slideshow delay in seconds.' ) );
		
		add_settings_field( 'ats-cache-timer', __( 'Set Twitter API Cache', 'textdomain' ), array( $this, 'atsPlugInFieldCreate' ), 'ats-settings', 'ats-twitter-display-section', $args=array('name'=>'ats-cache-timer', 'type'=>'select', 'items'=>array("15","30","45","60"), 'instructions' =>'Select the cache hold for the Twitter API in minutes. This value ensures that you will not make too many calls to the Twitter API server.' ) );
		
		add_settings_field( 'ats-clear-cache', __( 'Clear Twitter API Cache', 'textdomain' ), array( $this, 'atsPlugInFieldCreate' ), 'ats-settings', 'ats-twitter-display-section', $args=array('name'=>'ats-clear-cache', 'type'=>'clear-cache', 'instructions' =>'Clear the current cache of Twitter tweet responses. Do this when you change/add/remove twiiter users to follow.' ) );
		}
	
	/**
	 * function atsDisplaySectionCallback
	 */
	
	public function atsDisplaySectionCallback() {
		_e( 'Enter all fields below.', 'textdomain' );
	}	
	
	/**
	 * function atsPlugInFieldCreate
	 * params: array( string $name, string $type, (opt) array $items, (opt) string $instructions )
	 *
	 */
	 public function atsPlugInFieldCreate( $args ) {
	 		$settings = (array) get_option( 'ats-settings' );
	 		$field = $args['name'];
	 		$value = esc_attr( $settings[$field] );
	 		if( isset( $args['items'] ) ) { $items = $args['items']; }
	 		//switch on field input type
	 		switch ($args['type']) {
	 			case 'text' :
	 				echo "<input type='text' class='regular-text code' name=\"ats-settings[$field]\" value='$value' />";
	 			break;
	 			case 'select' :
	 				echo "<select id='' name=\"ats-settings[$field]\">";
	 					if( isset( $args['items'] ) ) {
	 						$items = $args['items'];
	 						foreach($items as $item) {
								$selected = ($value==$item) ? 'selected="selected"' : '';
								echo "<option value='$item' $selected>$item</option>";
							}
	 					}
	 				echo "</select>";
	 				break;
	 			case 'clear-cache' :
	 				echo('<input type="button" id="clear-cache" class="button button-large" value="Clear Tweet Cache" >');
	 			break;
	 			}
	 		//add instructions if needed	
	 		if( isset( $args['instructions'] ) ) { 
	 			$instructions = $args['instructions']; 
	 				echo "<p class='description'>$instructions</p>";
	 			}
	 }

/*******************************************/
/* Shortcode Related Methods etc.
/*******************************************/	
	
	/**
	 * function atsShortCodeInit
	 */
	public function atsShortCodeInit() {
		add_action('init', array( $this,'atsRegisterShortCode' ));
	}
	
	/**
	 * function atsRegisterShortCode
	 */
	public function atsRegisterShortCode() {
		add_shortcode('animated-twitter-slideshow', array( $this,'atsSlideshowShortCode' ));
	}
	
	/**
	 * function atsSlideshowShortCode
	 */
	public function atsSlideshowShortCode() {
				
				//get slideshow settings
				$twitterDisplayOptions = get_option( 'ats-settings' );				
				$tweet_count = trim($twitterDisplayOptions['ats-tweet-count']);
				$slideshow_delay = trim($twitterDisplayOptions['ats-slideshow-delay']);
				$twitter_user = trim(str_replace('@','',$twitterDisplayOptions['ats-twitter-users-field']));
				
				//check cache
				$cache_timer = trim($twitterDisplayOptions['ats-cache-timer']);
				$cached_data = get_option( 'ats-cached-tweets' );
				$cached_time = get_option( 'ats-cached-tweets-timestamp' );
				$current_time = time();
				$expire_time = $cache_timer*60;			
				
				if( $cached_data && ($current_time - $expire_time < $cached_time) ) {
					$tweets =  $cached_data;
				}else{
					//call twitterAPI
					$tweets = array();
					$response = $this->atsAPIUserCall( $twitter_user,$tweet_count );
							if( $response === 'bad-auth' ) {
								$content = '<div class="ats-auth-error">There is an error with your Twitter API Key/Secret. Please check these in the Plugin admin and re-try.</div>';
								return $content;
							}else{
								$tweets[] = $response;
							} 	
					//cache to wp cache object
					update_option( 'ats-cached-tweets', $tweets );
					update_option( 'ats-cached-tweets-timestamp', $current_time );
				}
				
				/**
				 * Build Tweets Array
				 */
				if( is_array($tweets) && $twitterDisplayOptions!=''||null ) {
					$i = 0;
					foreach( $tweets as $tweet_group) {
						foreach( $tweet_group as $tweet ) {
	
							$tweetsArray[$i]['date'] = strtotime($tweet['created_at']);
							$tweetsArray[$i]['id'] = $tweet['id_str'];
							$tweetsArray[$i]['text'] = $tweet['full_text'];
							$tweetsArray[$i]['media'] = $tweet['extended_entities']['media'][0]['media_url'];
							$tweetsArray[$i]['tweet_link'] = $tweet['extended_entities']['media'][0]['url'];
							$tweetsArray[$i]['urls'] = $tweet['entities']['urls'];
							$tweetsArray[$i]['user_mentions'] = $tweet['entities']['user_mentions'];
							$tweetsArray[$i]['hashtags'] = $tweet['entities']['hashtags'];
							$tweetsArray[$i]['user_screen_name'] = $tweet['user']['screen_name'];
							$tweetsArray[$i]['user_name'] = $tweet['user']['name'];
							$tweetsArray[$i]['user_verified'] = $tweet['user']['verified'];
							$tweetsArray[$i]['profile_banner'] = $tweet['user']['profile_banner_url'];
							$tweetsArray[$i]['user_avatar'] = str_replace('normal','bigger',$tweet['user']['profile_image_url']);
							$tweetsArray[$i]['background-image'] = '';
							
							/**
							 * Check if Tweet link is enbedded if not use User Timeline link
							 */
							if( $tweetsArray[$i]['tweet_link'] !=''||null ) {
								//do nothing
							}else{
								$tweetsArray[$i]['tweet_link'] =' https://twitter.com/'.$tweetsArray[$i]['user_screen_name'].'/status/'.$tweetsArray[$i]['id'];
							}
							
							/**
							 * Check media if image -> set as background
							 */
							$media_check= explode( '.',$tweetsArray[$i]['media'] );
							$media_check_extention = end( $media_check );
							if( in_array( strtolower( $media_check_extention ), array('jpeg','jpg','png','gif') ) ) {
								$tweetsArray[$i]['background-image'] = "style=\"background-image:url('".$tweetsArray[$i]['media'] ."');\"";
							}else if( $tweetsArray[$i]['profile_banner'] !=''||null){
								$tweetsArray[$i]['background-image'] = "style=\"background-image:url('".$tweetsArray[$i]['profile_banner'] ."');\"";
							}
							 
							/**
							 * Get tweet links, hashtags, user-mentions, remove in-line tweet perma-link, and build html
							 */
							unset($tweetTextArray);
							$tweetTextArray = explode( ' ', $tweetsArray[$i]['text'] );	
							
							if( is_array( $tweetTextArray ) ) {
								
								foreach( $tweetTextArray as $key => $field ) {
									
									//remove permalink text
									if( $field == $tweetsArray[$i]['tweet_link']   ) {
										$tweet_new_text = '';
										$tweetTextArray[$key] = '';
									}
									
									//add embedded urls html
									if( is_array($tweetsArray[$i]['urls']) ) {
										foreach( $tweetsArray[$i]['urls'] as $url ) {
											if( strpos( $field, $url['url'] ) !==false  ) {
												$tweet_new_text = '<a href="'.$url['url'].'" target="_blank">'.  $url['display_url'] .'</a>';
												$tweetTextArray[$key] = $tweet_new_text; 
											}
										}
									}
									
									//build hashtag links								
									if( is_array($tweetsArray[$i]['hashtags']) ) {
										foreach( $tweetsArray[$i]['hashtags'] as $hashtag ) {
											if( strpos( $field, $hashtag['text'] ) !==false  ) {
												$tweet_new_text = '<a href="https://twitter.com/hashtag/'.$hashtag['text'].'?src=hash" target="_blank">'.$field .'</a>';
												$tweetTextArray[$key] = $tweet_new_text; 
											}
										}
									}
									
									//build user-mention links
									if( is_array($tweetsArray[$i]['user_mentions']) ) {
										unset($twitter_user_mention,$twitter_user_link,$twitter_user_title,$user_mention_replace_text );
										
										foreach( $tweet['entities']['user_mentions'] as $user_mention ) {
											$twitter_user_mention = '@'.$user_mention['screen_name'];
											$twitter_user_link = 'https://twitter.com/' . str_replace('@','',$twitter_user_mention);
											$tweet_new_text = '<a href="'. $twitter_user_link  .'" target="_blank">'.$field.'</a>';
											if( strpos( strtolower($field), $twitter_user_mention ) !==false  ) {
												$tweetTextArray[$key] = $tweet_new_text;
												break;
											}
										}
										//catch ill-formed usermentions (ie: plural, capitalized etc)
										if( strpos( $field, '@' ) !==false && strpos( strtolower($field), $user_mention['screen_name'] ) ==false  ) {
											$firstFieldArray = explode('@',$field);
											if( count( $firstFieldArray ) > 1 ) {
												$junk = array_shift( $firstFieldArray );
												$new_field = implode('',$firstFieldArray);
											}elseif( count( $firstFieldArray ) == 1 ) {
												$junk = '';
												$new_field = implode('',$firstFieldArray);
											}
											$fieldArray = explode('\'',$new_field);
											$twitter_user_link = 'https://twitter.com/' . strtolower(str_replace('@','',$fieldArray[0]));
											$tweet_new_text = $junk.'<a href="'. $twitter_user_link  .'" target="_blank">'.$field.'</a>';
											$tweetTextArray[$key] = $tweet_new_text;
										}
									}						
								}
							}
							$tweetsArray[$i]['text'] = implode( ' ', $tweetTextArray );						 			
				$i++;	} 							
					}
				} 
				
				/**
				 * sort TweetsArray by date desc
				 */
				function atsSortTweetsByDate($a,$b) {
					if ( $a['date'] == $b['date'] ) return 0;
   				return ( $a['date'] < $b['date'] ) ? -1 : 1;
				}
				
				if( is_array($tweetsArray) ) {
					usort( $tweetsArray, atsSortTweetsByDate );
					$tweetsArray = array_reverse( $tweetsArray );
				}
				
				/**
				 * Build Tweet Slideshow HTML
				 */
				$content ='';
				if( is_array( $tweetsArray ) ) {
					$num_slides = count( $tweetsArray );
					$content = '<div id="ats-slider" data-current-index="1" data-delay="'.$slideshow_delay.'" data-num-slides="'.$num_slides.'"><div class="ats-slider-wrapper">';
					$i=1;
					foreach( $tweetsArray as $tweetItem ) {
						/**
						 * Check if user is verified for icon
						 */
						 if( $tweetItem['user_verified'] ){ $user_verified = 'verified';}else{ $user_verified = '';}
						
						$content .= '<div id="ats-item-'.$i.'" class="ats-item-wrapper" data-index="'.$i.'">';
						$content .= '<div class="ats-bg-image" '.$tweetItem['background-image'].'></div><div class="ats-bg-mask"></div><div class="ats-item" >';
						$content .= '<div class="tweet-wrapper">
													<div class="tweet-inner-top">
														<a href="https://twitter.com/'.$tweetItem['user_screen_name'].'" target="_blank">
															<div class="tweet-user-avatar">
																<img src="'.$tweetItem['user_avatar'].'" alt="'.$tweetItem['user_name'].'" >																
															</div>
																<!--<span class="twitter-logo"></span>-->
																<span class="tweet-user-name '.$user_verified.'">'.$tweetItem['user_name'].'<br>
																<span class="tweet-user-screen-name">@'.$tweetItem['user_screen_name'].'</span></span>
																
														</a>
													</div>
													<div class="tweet-inner-main"><p data-link="'.$tweetItem['tweet_link'].'">'.$tweetItem['text'].'<span class="tweet-date">'.date( 'h:iA - M d, Y',$tweetItem['date']).'</span></p></div>	
												</div>';
						$content .= '</div></div><!--end ats-item-wrapper-->';
						$i++;
					}
						//Add Nav-controls
						$content .= '</div><ul class="tweet-counter-wrapper">';
							for( $j=1; $j<=count( $tweetsArray ); $j++ ) {
								$content .= '<li class="tweet-counter tweet-counter-'.$j.'" data-slide="'.$j.'"></li>';
								}
						$content .= '</ul>';
						$content .= '<div class="tweet-nav-bttn" id="next"></div><div class="tweet-nav-bttn" id="prev"></div>';
						$content .= '</div>';
				}	

		return $content;
	}
	
	/**
	 * function atsAPIUserCall
	 */	
	public function atsAPIUserCall( $twitter_user, $tweet_count ) {
		
		$tcr = new atsCurlRequest();
		$tweets = $tcr->atsGetUserTweets( $twitter_user, $tweet_count );
		if( $tweets !== 'bad-auth' ) {
			return json_decode($tweets,true);
		}else{
			return 'bad-auth';
		}
			
	}
	
}

//Install/Uninstall
register_activation_hook( __FILE__, array('animatedTwitterSlideshow','atsInstall') );
register_deactivation_hook( __FILE__, array('animatedTwitterSlideshow','atsUninstall') );

//Init
$ats = new animatedTwitterSlideshow;
$ats->atsShortCodeInit();

//Admin
if( is_admin() ) {
	$ats->atsCreateAdmin(); 	
}
