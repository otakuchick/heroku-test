<?php

/**
 * This sample app is provided to kickstart your experience using Facebook's
 * resources for developers.  This sample app provides examples of several
 * key concepts, including authentication, the Graph API, and FQL (Facebook
 * Query Language). Please visit the docs at 'developers.facebook.com/docs'
 * to learn more about the resources available to you
 */

// Provides access to app specific values such as your app id and app secret.
// Defined in 'AppInfo.php'
require_once('AppInfo.php');

// Enforce https on production
if (substr(AppInfo::getUrl(), 0, 8) != 'https://' && $_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
  header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
  exit();
}

// This provides access to helper functions defined in 'utils.php'
require_once('utils.php');


/*****************************************************************************
 *
 * The content below provides examples of how to fetch Facebook data using the
 * Graph API and FQL.  It uses the helper functions defined in 'utils.php' to
 * do so.  You should change this section so that it prepares all of the
 * information that you want to display to the user.
 *
 ****************************************************************************/

require_once('sdk/src/facebook.php');

$facebook = new Facebook(array(
  'appId'  => AppInfo::appID(),
  'secret' => AppInfo::appSecret(),
));

$user_id = $facebook->getUser();
if ($user_id) {
  try {
    // Fetch the viewer's basic information
    $basic = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    // If the call fails we check if we still have a user. The user will be
    // cleared if the error is because of an invalid accesstoken
    if (!$facebook->getUser()) {
      header('Location: '. AppInfo::getUrl($_SERVER['REQUEST_URI']));
      exit();
    }
  }

  // This fetches some things that you like . 'limit=*" only returns * values.
  // To see the format of the data you are retrieving, use the "Graph API
  // Explorer" which is at https://developers.facebook.com/tools/explorer/
  $likes = idx($facebook->api('/me/likes?limit=4'), 'data', array());

  // This fetches 4 of your friends.
  $friends = idx($facebook->api('/me/friends?limit=4'), 'data', array());

  // And this returns 16 of your photos.
  $photos = idx($facebook->api('/me/photos?limit=16'), 'data', array());

  $squishes = idx($facebook->api('/me/ttestbox:squish'), 'data', array());

  $user_agent = trim($_SERVER['HTTP_USER_AGENT']);

  $http_referer = trim($_SERVER['HTTP_REFERER']);

  $currentFile = $_SERVER["SCRIPT_NAME"];
  $parts = Explode('/', $currentFile);
  $currentFile = $parts[count($parts) - 1];
  $file_hash = hash_file('md5', $currentFile);

  $on_facebook = (stristr($http_referer, 'facebook') !== FALSE);
  $on_iphone = (stristr($user_agent, 'iphone') !== FALSE);
  $on_ipad = (stristr($user_agent, 'ipad') !== FALSE);
  
  $content_text = 'Desktop';
  if ($on_facebook) {
    $content_text = 'Facebook';
  } elseif($on_iphone) {
   $content_text = 'iPhone';
  } elseif ($on_ipad) {
    $content_text = 'iPad';
  }
}

// Fetch the basic info of the app that they are using
$app_info = $facebook->api('/'. AppInfo::appID());

$app_name = idx($app_info, 'name', '');

?>
<!DOCTYPE html>
<html xmlns:fb="http://ogp.me/ns/fb#" lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, width=device-width">

    <title><?php echo he($app_name); ?></title>
    <link rel="stylesheet" href="stylesheets/initial.css" media="Screen" type="text/css" />

    <!-- These are Open Graph tags.  They add meta data to your  -->
    <!-- site that facebook uses when your content is shared     -->
    <!-- over facebook.  You should fill these tags in with      -->
    <!-- your data.  To learn more about Open Graph, visit       -->
    <!-- 'https://developers.facebook.com/docs/opengraph/'       -->
    <meta property="og:title" content="<?php echo he($app_name); ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?php echo AppInfo::getUrl(); ?>" />
    <meta property="og:image" content="<?php echo AppInfo::getUrl('/logo.png'); ?>" />
    <meta property="og:site_name" content="<?php echo he($app_name); ?>" />
    <meta property="og:description" content="T Test Box" />
    <meta property="fb:app_id" content="<?php echo AppInfo::appID(); ?>" />

    <script type="text/javascript" src="/javascript/jquery-1.7.1.min.js"></script>
    <script type="text/javascript" src="/javascript/card.flip-1.0.js"></script>

    <script type="text/javascript">
      function logResponse(response) {
        if (console && console.log) {
          console.log('The response was', response);
        }
      }

      $(function(){
        // Set up so we handle click on the buttons
        $('#postToWall').click(function() {
          FB.ui(
            {
              method : 'feed',
              link   : $(this).attr('data-url')
            },
            function (response) {
              // If response is null the user canceled the dialog
              if (response != null) {
                logResponse(response);
              }
            }
          );
        });

        $('#sendToFriends').click(function() {
          FB.ui(
            {
              method : 'send',
              link   : $(this).attr('data-url')
            },
            function (response) {
              // If response is null the user canceled the dialog
              if (response != null) {
                logResponse(response);
              }
            }
          );
        });

        $('#sendRequest').click(function() {
          FB.ui(
            {
              method  : 'apprequests',
              message : $(this).attr('data-message')
            },
            function (response) {
              // If response is null the user canceled the dialog
              if (response != null) {
                logResponse(response);
              }
            }
          );
        });
      });
    </script>

    <!--[if IE]>
      <script type="text/javascript">
        var tags = ['header', 'section'];
        while(tags.length)
          document.createElement(tags.pop());
      </script>
    <![endif]-->
  </head>
  <body>
    <div id="fb-root"></div>
    <script type="text/javascript">
      window.fbAsyncInit = function() {
        FB.init({
          appId      : '<?php echo AppInfo::appID(); ?>', // App ID
          channelUrl : '//<?php echo $_SERVER["HTTP_HOST"]; ?>/channel.html', // Channel File
          status     : true, // check login status
          cookie     : true, // enable cookies to allow the server to access the session
          xfbml      : true // parse XFBML
        });

        // Listen to the auth.login which will be called when the user logs in
        // using the Login button
        FB.Event.subscribe('auth.login', function(response) {
          // We want to reload the page now so PHP can read the cookie that the
          // Javascript SDK sat. But we don't want to use
          // window.location.reload() because if this is in a canvas there was a
          // post made to this page and a reload will trigger a message to the
          // user asking if they want to send data again.
          window.location = window.location;
        });

        FB.Canvas.setAutoGrow();
      };

      // Load the SDK Asynchronously
      (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/all.js";
        fjs.parentNode.insertBefore(js, fjs);
      }(document, 'script', 'facebook-jssdk'));
    </script>

    <header class="clearfix">
      <?php if (isset($basic)) { ?>
      <p id="picture" style="background-image: url(https://graph.facebook.com/<?php echo he($user_id); ?>/picture?type=normal)"></p>

      <div>
        <h1>Welcome, <strong><?php echo he(idx($basic, 'name')); ?></strong></h1>
        <p class="tagline">
          This is your app
          <a href="<?php echo he(idx($app_info, 'link'));?>" target="_top"><?php echo he($app_name); ?></a>
        </p>

        <div id="share-app">
          <p>Share your app:</p>
          <ul>
            <li>
              <a href="#" class="facebook-button" id="postToWall" data-url="<?php echo AppInfo::getUrl(); ?>">
                <span class="plus">Post to Wall</span>
              </a>
            </li>
            <li>
              <a href="#" class="facebook-button speech-bubble" id="sendToFriends" data-url="<?php echo AppInfo::getUrl(); ?>">
                <span class="speech-bubble">Send Message</span>
              </a>
            </li>
            <li>
              <a href="#" class="facebook-button apprequests" id="sendRequest" data-message="Test this awesome app">
                <span class="apprequests">Send Requests</span>
              </a>
            </li>
          </ul>
        </div>
      </div>
      <?php } else { ?>
      <div>
        <h1>Welcome</h1>
        <div class="fb-login-button" data-scope="user_likes,user_photos"></div>
      </div>
      <?php } ?>
    </header>

    <section id="get-started">
      <p><span>User Agent:</span></p>
	  <p><?php echo $user_agent; ?></p>
	  <p><span>Referer:</span></p>
	  <p><?php echo $http_referer; ?></p>
      <p><span>Code fingerprint (md5):</span></p>
	  <p class="code"><?php echo $file_hash; ?></p>
    </section>

    <section id="guides" class="clearfix">
		<h1>Based on the information above we are providing you with the</h1>
		<p class=<?php echo "\"" . $content_text . "\">" . $content_text ?></p>
		<h1>format of the website.</h1>
    </section>
    	
    <?php if ($user_id) { ?>
    <section id="cards" class="clearfix">
	<div id="main">	
		<div class="card-container">
            <div class="card" onclick="flip(event)">
                <div class="front face">
                    <div class="corner social"></div>
                    <div class="offer-header">
                    	<span class="offer-value">Buy 1 Get 1 FREE</span>
                    	<h3 class="offer-title">Room Essentials Bedroom Collection 1</h3>
                    </div>                        
                    <img class="offer-image" src="images/offer.jpg" />
                    <a class="icon-bar" href="#">
                    	<div class="icon friends">
                        	<div></div>
                            <span>23</span>
                        </div>
                        <div class="icon redeemed">
                        	<div></div>
                            <span>23</span>
                        </div>
                        <div class="icon hearts">
                        	<div></div>
                            <span>23</span>
                        </div>
                    </a><!-- /icon-bar -->
                    <div class="shine"></div>
                </div><!-- /front -->
                <div class="back face">
                	<div class="offer-header">
                		<span class="offer-value">Buy 1 Get 1 FREE</span>
                  		<h3 class="offer-title">Room Essentials Bedroom Collection</h3>
                    	<span class="expiration">Expires 6/4/12</span>
                    </div>
                    <p class="description"><span>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent imperdiet sem sit amet orci gravida quis viverra leo malesuada. Morbi sapien tortor, lobortis posuere iaculis.</span></p>
                    <div class="buttons">
                    	<a class="btn add" href="#"><span>add</span></a>
                        <a class="btn private" href="#"><span><span>private</span></span></a>
                        <a class="btn heart" href="#"><span><span>heart</span></span></a>
                        <a class="btn details" href="#"><span>details</span></a>
                    </div><!-- /buttons -->
                </div><!-- /back -->
            </div><!-- /card -->
        </div><!-- /card-container -->
        <div class="card-container">
            <div class="card" onclick="flip(event)">
                <div class="front face">
                    <div class="corner social"></div>
                    <div class="offer-header">
                    	<span class="offer-value">Buy 1 Get 1 FREE</span>
                    	<h3 class="offer-title">Room Essentials Bedroom Collection 2</h3>
                    </div>                        
                    <img class="offer-image" src="images/offer.jpg" />
                    <a class="icon-bar" href="#">
                    	<div class="icon friends">
                        	<div></div>
                            <span>23</span>
                        </div>
                        <div class="icon redeemed">
                        	<div></div>
                            <span>23</span>
                        </div>
                        <div class="icon hearts">
                        	<div></div>
                            <span>23</span>
                        </div>
                    </a><!-- /icon-bar -->
                    <div class="shine"></div>
                </div><!-- /front -->
                <div class="back face">
                	<div class="offer-header">
                		<span class="offer-value">Buy 1 Get 1 FREE</span>
                  		<h3 class="offer-title">Room Essentials Bedroom Collection</h3>
                    	<span class="expiration">Expires 6/4/12</span>
                    </div>
                    <p class="description"><span>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent imperdiet sem sit amet orci gravida quis viverra leo malesuada. Morbi sapien tortor, lobortis posuere iaculis.</span></p>
                    <div class="buttons">
                    	<a class="btn add" href="#"><span>add</span></a>
                        <a class="btn private" href="#"><span><span>private</span></span></a>
                        <a class="btn heart" href="#"><span><span>heart</span></span></a>
                        <a class="btn details" href="#"><span>details</span></a>
                    </div><!-- /buttons -->
                </div><!-- /back -->
            </div><!-- /card -->
        </div><!-- /card-container --><div class="card-container">
            <div class="card" onclick="flip(event)">
                <div class="front face">
                    <div class="corner social"></div>
                    <div class="offer-header">
                    	<span class="offer-value">Buy 1 Get 1 FREE</span>
                    	<h3 class="offer-title">Room Essentials Bedroom Collection 3</h3>
                    </div>                        
                    <img class="offer-image" src="images/offer.jpg" />
                    <a class="icon-bar" href="#">
                    	<div class="icon friends">
                        	<div></div>
                            <span>23</span>
                        </div>
                        <div class="icon redeemed">
                        	<div></div>
                            <span>23</span>
                        </div>
                        <div class="icon hearts">
                        	<div></div>
                            <span>23</span>
                        </div>
                    </a><!-- /icon-bar -->
                    <div class="shine"></div>
                </div><!-- /front -->
                <div class="back face">
                	<div class="offer-header">
                		<span class="offer-value">Buy 1 Get 1 FREE</span>
                  		<h3 class="offer-title">Room Essentials Bedroom Collection</h3>
                    	<span class="expiration">Expires 6/4/12</span>
                    </div>
                    <p class="description"><span>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent imperdiet sem sit amet orci gravida quis viverra leo malesuada. Morbi sapien tortor, lobortis posuere iaculis.</span></p>
                    <div class="buttons">
                    	<a class="btn add" href="#"><span>add</span></a>
                        <a class="btn private" href="#"><span><span>private</span></span></a>
                        <a class="btn heart" href="#"><span><span>heart</span></span></a>
                        <a class="btn details" href="#"><span>details</span></a>
                    </div><!-- /buttons -->
                </div><!-- /back -->
            </div><!-- /card -->
        </div><!-- /card-container -->
        <div class="card-container">
            <div class="card" onclick="flip(event)">
                <div class="front face">
                    <div class="corner social"></div>
                    <div class="offer-header">
                    	<span class="offer-value">Buy 1 Get 1 FREE</span>
                    	<h3 class="offer-title">Room Essentials Bedroom Collection 4</h3>
                    </div>                        
                    <img class="offer-image" src="images/offer.jpg" />
                    <a class="icon-bar" href="#">
                    	<div class="icon friends">
                        	<div></div>
                            <span>23</span>
                        </div>
                        <div class="icon redeemed">
                        	<div></div>
                            <span>23</span>
                        </div>
                        <div class="icon hearts">
                        	<div></div>
                            <span>23</span>
                        </div>
                    </a><!-- /icon-bar -->
                    <div class="shine"></div>
                </div><!-- /front -->
                <div class="back face">
                	<div class="offer-header">
                		<span class="offer-value">Buy 1 Get 1 FREE</span>
                  		<h3 class="offer-title">Room Essentials Bedroom Collection</h3>
                    	<span class="expiration">Expires 6/4/12</span>
                    </div>
                    <p class="description"><span>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent imperdiet sem sit amet orci gravida quis viverra leo malesuada. Morbi sapien tortor, lobortis posuere iaculis.</span></p>
                    <div class="buttons">
                    	<a class="btn add" href="#"><span>add</span></a>
                        <a class="btn private" href="#"><span><span>private</span></span></a>
                        <a class="btn heart" href="#"><span><span>heart</span></span></a>
                        <a class="btn details" href="#"><span>details</span></a>
                    </div><!-- /buttons -->
                </div><!-- /back -->
            </div><!-- /card -->
        </div><!-- /card-container -->
        <div class="card-container">
            <div class="card" onclick="flip(event)">
                <div class="front face">
                    <div class="corner social"></div>
                    <div class="offer-header">
                    	<span class="offer-value">Buy 1 Get 1 FREE</span>
                    	<h3 class="offer-title">Room Essentials Bedroom Collection 5</h3>
                    </div>                        
                    <img class="offer-image" src="images/offer.jpg" />
                    <a class="icon-bar" href="#">
                    	<div class="icon friends">
                        	<div></div>
                            <span>23</span>
                        </div>
                        <div class="icon redeemed">
                        	<div></div>
                            <span>23</span>
                        </div>
                        <div class="icon hearts">
                        	<div></div>
                            <span>23</span>
                        </div>
                    </a><!-- /icon-bar -->
                    <div class="shine"></div>
                </div><!-- /front -->
                <div class="back face">
                	<div class="offer-header">
                		<span class="offer-value">Buy 1 Get 1 FREE</span>
                  		<h3 class="offer-title">Room Essentials Bedroom Collection</h3>
                    	<span class="expiration">Expires 6/4/12</span>
                    </div>
                    <p class="description"><span>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent imperdiet sem sit amet orci gravida quis viverra leo malesuada. Morbi sapien tortor, lobortis posuere iaculis.</span></p>
                    <div class="buttons">
                    	<a class="btn add" href="#"><span>add</span></a>
                        <a class="btn private" href="#"><span><span>private</span></span></a>
                        <a class="btn heart" href="#"><span><span>heart</span></span></a>
                        <a class="btn details" href="#"><span>details</span></a>
                    </div><!-- /buttons -->
                </div><!-- /back -->
            </div><!-- /card -->
        </div><!-- /card-container -->
        <div class="card-container">
            <div class="card" onclick="flip(event)">
                <div class="front face">
                    <div class="corner social"></div>
                    <div class="offer-header">
                    	<span class="offer-value">Buy 1 Get 1 FREE</span>
                    	<h3 class="offer-title">Room Essentials Bedroom Collection 6</h3>
                    </div>                        
                    <img class="offer-image" src="images/offer.jpg" />
                    <a class="icon-bar" href="#">
                    	<div class="icon friends">
                        	<div></div>
                            <span>23</span>
                        </div>
                        <div class="icon redeemed">
                        	<div></div>
                            <span>23</span>
                        </div>
                        <div class="icon hearts">
                        	<div></div>
                            <span>23</span>
                        </div>
                    </a><!-- /icon-bar -->
                    <div class="shine"></div>
                </div><!-- /front -->
                <div class="back face">
                	<div class="offer-header">
                		<span class="offer-value">Buy 1 Get 1 FREE</span>
                  		<h3 class="offer-title">Room Essentials Bedroom Collection</h3>
                    	<span class="expiration">Expires 6/4/12</span>
                    </div>
                    <p class="description"><span>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent imperdiet sem sit amet orci gravida quis viverra leo malesuada. Morbi sapien tortor, lobortis posuere iaculis.</span></p>
                    <div class="buttons">
                    	<a class="btn add" href="#"><span>add</span></a>
                        <a class="btn private" href="#"><span><span>private</span></span></a>
                        <a class="btn heart" href="#"><span><span>heart</span></span></a>
                        <a class="btn details" href="#"><span>details</span></a>
                    </div><!-- /buttons -->
                </div><!-- /back -->
            </div><!-- /card -->
        </div><!-- /card-container -->
        <div class="card-container">
            <div class="card" onclick="flip(event)">
                <div class="front face">
                    <div class="corner social"></div>
                    <div class="offer-header">
                    	<span class="offer-value">Buy 1 Get 1 FREE</span>
                    	<h3 class="offer-title">Room Essentials Bedroom Collection 7</h3>
                    </div>                        
                    <img class="offer-image" src="images/offer.jpg" />
                    <a class="icon-bar" href="#">
                    	<div class="icon friends">
                        	<div></div>
                            <span>23</span>
                        </div>
                        <div class="icon redeemed">
                        	<div></div>
                            <span>23</span>
                        </div>
                        <div class="icon hearts">
                        	<div></div>
                            <span>23</span>
                        </div>
                    </a><!-- /icon-bar -->
                    <div class="shine"></div>
                </div><!-- /front -->
                <div class="back face">
                	<div class="offer-header">
                		<span class="offer-value">Buy 1 Get 1 FREE</span>
                  		<h3 class="offer-title">Room Essentials Bedroom Collection</h3>
                    	<span class="expiration">Expires 6/4/12</span>
                    </div>
                    <p class="description"><span>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent imperdiet sem sit amet orci gravida quis viverra leo malesuada. Morbi sapien tortor, lobortis posuere iaculis.</span></p>
                    <div class="buttons">
                    	<a class="btn add" href="#"><span>add</span></a>
                        <a class="btn private" href="#"><span><span>private</span></span></a>
                        <a class="btn heart" href="#"><span><span>heart</span></span></a>
                        <a class="btn details" href="#"><span>details</span></a>
                    </div><!-- /buttons -->
                </div><!-- /back -->
            </div><!-- /card -->
        </div><!-- /card-container -->
	</div>
    </section>
    <?php } ?>
  </body>
</html>
