<?php
/*
 * Plugin Name: Social Messenger
 * Plugin URI: http://codeot.com/projects/messenger
 * Description: Add Facebook's Messenger on your website just by adding your profile or page id
 * Version: 1.0
 * Author: Talha Habib
 * Author URI: https://linkedin.com/in/imultithinker/
 */

// adding settings page
function mt_messenger_setting_page( $links ) {
    $settings_link = '<a href="admin.php?page=mt_messenger_admin">' . __( 'Settings' ) . '</a>';
    array_push( $links, $settings_link );
  	return $links;
}
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'mt_messenger_setting_page' );

// plugin activation
register_activation_hook( __FILE__, 'mt_messenger_activate' );
function mt_messenger_activate(){
  // add option if not exist
  $exID = get_option("mtmessenger_profileid");
  if($exID==''){
    add_option('mtmessenger_profileid', 1, '', 'yes' );
  }
  // else it already exist.
}
register_uninstall_hook(__FILE__, 'mt_messenger_uninstall');
function mt_messenger_uninstall(){
  // its no use keeping dead body.
  delete_option('mtmessenger_profileid');
}

// facebook sdk
add_action("wp_head","mt_messenger_sdk");
function mt_messenger_sdk(){
?>
<script src='http://connect.facebook.net/en_US/all.js'>
  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = "https://connect.facebook.net/en_US/sdk.js";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));
</script>
<?php
}

// add admint page
add_action('admin_menu', 'mt_messenger_admin_menu');
function mt_messenger_admin_menu() {
	add_menu_page('Messenger',
				  'Facebook Messenger',
				  'administrator',
				  'mt_messenger_admin',
				  'mt_messenger_admin_settings',
				  plugins_url( 'messenger.svg', __FILE__ )
				 );
}

// front end, adding div in wp_head goes after body.
add_action('wp_head', 'mt_messenger_front_end');
function mt_messenger_front_end(){
	// file path
	$code = get_option("mtmessenger_profileid");
?>
<!-- Load Facebook SDK for JavaScript -->
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = 'https://connect.facebook.net/en_US/sdk/xfbml.customerchat.js#xfbml=1&version=v2.12&autoLogAppEvents=1';
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>

<!-- Your customer chat code -->
<div class="fb-customerchat"
  attribution="setup_tool"
  page_id="<?=$code?>">
</div>
<?php
}
// admin page
function mt_messenger_admin_settings(){
  $err = '';
	// existing value
	$content = get_option("mtmessenger_profileid");
	// if user submit
	if(isset($_POST['mt_messenger_code']) && $_POST['mt_messenger_code']!=''){
		// get input
		$updateCode = $_POST['mt_messenger_code'];
    // run checks
    $re = '/[0-9]/m';
    preg_match_all($re, $updateCode, $matches);
    // extract only numeric
    $updateCode = implode("",$matches[0]);
    // if there is any script-kidies this server side checks will prevent him
    // just additional check to see length and if the regex is fine
		if(is_numeric($updateCode) && strlen($updateCode)<=25){
      // save the option
			update_option("mtmessenger_profileid", $updateCode, 'yes');
      // get latest option in content
      // it was simpler if it was from updateCode variable
      // but using get_option confirms that options are working.
      $content = get_option("mtmessenger_profileid");
		}else{
      // in case of error
			$err = 'Make sure your input is integer and is less then 25 characters';
		}
	}
	?>
  <style>
  .centered{ margin-left:auto;margin-right:auto;margin-top:10%;text-align:center;}
  .mt_mntop{margin-top:0% !important}
  </style>
  <!-- tiny little static inline style to make it centeralized !-->
	<form method='post' class='centered'>
    <!-- client side restrictions for non-script-kidies !-->
		Facebook Page ID: <input type='number'name='mt_messenger_code' maxlength="25" required value='<?=$content?>' />
    <!-- save !-->
		<input type='submit' class='button' />
    <!-- line won't get print until there is no error !-->
    <p style='color:red;font-weight:bold'><?=$err?></p>
	</form>
  <h3>Instructions</h3>
  <ol>
    <li>Go to your page</li>
    <li>Go to settings</li>
    <li>Go to Messenger Platform</li>
    <li>find <b>White-listed domain</b> and add your domain</li>
    <li>You can find your page id by clicking on "view as" on page home, your id will be visible in url</li>
    <li>In Messenger Platform > Custom Chat Plugin, You can run the wizard and when wizard prompt you to copy script, You can view your id in <b>Code Snippet</b> like this <code>page_id='123'</code></li>
    <li>Copy that id and paste in in plugin and submit</li>
    <li>The chat plugin will work on all pages of your site</li>
  </ol>
  <p class='centered'>You can contribute to this plugin on <a href='https://github.com/Multi-Thinker/WordPress-Facebook-Messenger-Plugin'>github</a>, <a href='mailto:talha@codeot.com'>contact me</a> for your ideas and suggestions</p>
  <p class='centered mt_mntop'>~<a href='https://twitter.com/iMultiThinker'>Talha Habib</a></p>
	<?php
	}
?>
