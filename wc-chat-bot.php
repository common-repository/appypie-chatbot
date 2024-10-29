<?php
/*
 * Plugin Name:     Appypie Chatbot 
 * Plugin URI:      https://www.appypie.com/
 * Description:     Easy to use chatbot platform for business. This plugin allows to quickly install ChatBot on any WordPress website..
 * Author:          Appypie
 * Author URI:      https://www.appypie.com/
 * Text Domain:     appypie-chatbot
 * Version:         1.0.1
 */
 
if (!class_exists('AppypieChatBot'))
{
	class AppypieChatBot {
		
		public function __construct() {
			global $wpdb;
			
			register_activation_hook(__FILE__, array($this, 'appypieChatBotPluginActivate'));
			register_deactivation_hook(__FILE__, array($this, 'appypieChatBotPluginActivateDeactivate'));
			
			add_action('admin_menu', array($this, 'add_chatbot_menu'));
			add_action('init', array($this, 'chatbot_addcss'));
			add_action('admin_footer', array($this, 'addChatBot_js'));
			add_action('wp_footer', array($this, 'add_chatbot_script'));
			
			add_action('wp_ajax_verify_token', array($this, 'verify_token_ajax_callback'));
			add_action('wp_ajax_nopriv_verify_token', array($this, 'verify_token_ajax_callback'));
			
			add_action('wp_ajax_wpcb_selected', array($this, 'wpcb_selected_ajax_callback'));
			add_action('wp_ajax_nopriv_wpcb_selected', array($this, 'wpcb_selected_ajax_callback'));
			
			add_action('wp_ajax_wpcb_hidebot', array($this, 'wpcb_hidebot_ajax_callback'));
			add_action('wp_ajax_nopriv_wpcb_hidebot', array($this, 'wpcb_hidebot_ajax_callback'));
			
			add_action('wp_ajax_wpcb_disconnect', array($this, 'wpcb_disconnect_ajax_callback'));
			add_action('wp_ajax_nopriv_wpcb_disconnect', array($this, 'wpcb_disconnect_ajax_callback'));
			
			add_action('wp_ajax_wpcb_enable', array($this, 'wpcb_enable_ajax_callback'));
			add_action('wp_ajax_nopriv_wpcb_enable', array($this, 'wpcb_enable_ajax_callback'));
		}
		function appypieChatBotPluginActivate() {
			global $wpdb; // wordpress connection variable
			$table_name = $wpdb->prefix . "chat_bot"; // write table name
			if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
				if ($wpdb->supports_collation()){
					if (!empty($wpdb->charset)) $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
					if (!empty($wpdb->collate)) $charset_collate.= " COLLATE $wpdb->collate";
				}
				
				/** Create table as per required */
				$sql = "CREATE TABLE IF NOT EXISTS " . $table_name . "(
					cb_id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
					user_id mediumint(8) NOT NULL,
					status mediumint(8) NOT NULL,
					email varchar(255) NOT NULL,
					selectedcb varchar(255) NOT NULL,
					bot_status varchar(255) NOT NULL,
					create_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					PRIMARY KEY (cb_id)
					) " . $charset_collate . ";";
				/** include upgrade.php for auto update and it has database library of wp. */
				require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
				dbDelta($sql); // execute sql
				
			}
		}
		function add_chatbot_menu() {
			add_menu_page('Chat Bot', 'Chat Bot', 'manage_options', 'chat-bot', array($this, 'chat_bottool_main_page'),'dashicons-format-status');

		}
		function chat_bottool_main_page() {
			if ($this->checkUserAaccess() == true) {
				$this->showChatBotListing();
			} else {
				$this->chatbot_newform();
			}
		}
		
		function chatbot_newform(){ ?>
			<div class="main-wrapper">
				<div class="content-box">
					<div class="text">
						<h2>Create your own Chatbot</h2>
						<h3>
							Build your custom AI bot in minutes. <br />
							No technical skills needed.
						</h3>
						<a href="javascript:void(0);" id="myBtn" class="themeButton">Add to your site</a>
						<h6>Don’t have an account? <a href="https://www.appypie.com/chatbot/builder" target="_blank">Get Started</a></h6>
					</div>
					<div class="image">
						<img src="<?php echo esc_url( plugins_url( 'assets/images', __FILE__ ) ); ?>/chatbot.svg" />
					</div>
				</div>
			</div>

			<!-- The Modal -->
			<div id="myModal" class="modal loginPop">
				<!-- Modal content -->
				<div class="modal-content">
					<span class="close">&times;</span>

					<div class="head">
						Say hello to your awesome bot. <br />
						verify your token to preview and customise it.
					</div>
					<div class="loginBox">
						<div class="loginEmail">
							<form id="tokenVerify" method="POST" action="admin.php?page=chat_bot">
								<div class="formField">
									<input type="text" name="token" id="token" placeholder="Enter your token key *" required />
								</div>
								<input type="submit" name="submit" class="verifytoken" value="Submit" />
							</form>
							<span class="errormsg" style="color:red;position: relative;top:-25px; left:38%;"></span>
						</div> 
					</div>

					<div class="foot">
						<img src="<?php echo esc_url( plugins_url( 'assets/images', __FILE__ ) ); ?>/lock.png" />
						Secure Area
					</div>
				 </div>
				</div>
				<script>
				 jQuery(function($) {
					 $("#tokenVerify").validate({
						rules: {
						  token: {
							required: true
						  }
						},
						messages: {
						  token: {
							required: "Field should not be blank"
						  },
						},
						submitHandler: function (form) { // for demo
						   $('.verifytoken').val('Processing...');
						   var token = $('#token').val();
						   $.ajax({
							method : 'POST',
							dataType: 'json',
							url    : wpcb.ajax_url,
							data: {
								action:'verify_token', 
								token:token,
							},
							})
						  .done( function( response ) {
							  if(response.status=="200"){
								  $('.verifytoken').val('Submit');
								  location.reload();
							  }else{
								  $('.verifytoken').val('Submit');
								  $('.errormsg').html("<strong>Sorry!</strong> Invalid Token Key.");
								  $('.errormsg').show();
							  }
							  setTimeout(function(){ $('.errormsg').hide(); }, 10000);
			 
						})
						.fail( function() {
							return false;
						})
						}
					});
				});
		</script>
	   <?php
	   }
			
	   function showChatBotListing(){
			global $wpdb;
			$table_name = $wpdb->prefix . "chat_bot"; // write table name
			$chatArr = $wpdb->get_row("SELECT * FROM $table_name");
			$token = $chatArr->token;
			if (!empty($token)) {
				$args = array('body' => array('token' => $token));
				$data = wp_remote_retrieve_body(wp_remote_post('https://chatbottest.appypie.com/botlist', $args));
			}
			$response = json_decode($data, true);
			?>
			<div class="main-wrapper">
				<div class="content-box">
					<div class="text"> 
						 <h2>Choose Your Bot</h2>
						 <?php 
						 if($response['status']==404 || $response['error']['name'] == "JsonWebTokenError" || $response['error']['message'] == "invalid signature" ){ ?>
						  <div style="color:red; margin-top:30px;">Sorry your token key expire disconnect your account and contact to Appypie chat bot team.</div>
						 <?php } ?>
						 <select name="chatbot" id="chatbot" style="background-image: url(<?php echo esc_url( plugins_url( 'assets/images', __FILE__ ) ); ?>/arrow.png);">
						 <option value="" selected="selected">Select Bot</option>
						   <?php 
						   if(!empty($response['res'])){
								   foreach($response['res'] as $bot){
									   $botcid = $response['user_id'].'-'.$response['agent_id'].'-'.$bot['id'].'&name='.$bot['file_type'];
									   $selected = ($botcid === $chatArr->selectedcb) ? 'selected="selected"' : '';?>
									   <option value="<?php echo esc_attr($botcid);?>" <?php echo esc_attr($selected); ?>><?php echo esc_html($bot['name']); ?></option>
						  <?php }
						   }						   
						   ?>
						 </select>
						 <div class="chatbotempty" style="display:none;">This field should not be blank </div>
						<p>
							Hide bot’s widget
							
							<label class="switch">
							  <input type="checkbox" name="botwidget" id="botwidget" value="<?php echo esc_html($chatArr->bot_status);?>">
							  <span class="slider round"></span>
							  <input type="hidden" name="disconnect" id="disconnect" value="<?php echo esc_html($chatArr->cb_id);?>">
							</label>
						</p>
						 <!--<button class="themeButton" disabled  style="width:300px; height:60px">Add to your site</button>-->
						  <a href="javascript:void(0);" class="themeButton <?php if(!empty($chatArr->bot_status)){?> not-active <?php } ?>">Add to your site</a>
							 <h6>Something went wrong? <a href="javascript:void(0);" class="disconnectact">Disconnect your account</a></h6>
							 <div class="success" style="color:green; font-size:18px;"></div>
						</div>
						<div class="image"> 
							 <img src="<?php echo esc_url( plugins_url( 'assets/images', __FILE__ ) ); ?>/choose.svg">
						</div>
				</div>
			</div>
		<?php 
		}
		
		function checkUserAaccess(){
			global $wpdb;
			$table_name = $wpdb->prefix . "chat_bot"; // write table name
			$chatArr = $wpdb->get_row("SELECT * FROM $table_name");
			$token = $chatArr->token;
			if($token){
				return true;
			}else{
				return false;
			}
		}
		
		function verify_token_ajax_callback(){
			global $wpdb;
			$table_name = $wpdb->prefix . "chat_bot";
			$token = sanitize_text_field($_POST['token']);
			$error = new WP_Error();
			if (empty($token))
			{
				$error->add(400, __("Token field 'token' is required.", 'wp-chatbot') , array(
					'status' => 400
				));
				return $error;
			}

			$args = array('body' => array('token' => $token));
			$data = wp_remote_retrieve_body(wp_remote_post('https://chatbottest.appypie.com/botlist', $args));
			$response = json_decode($data, true);
			$result = $response['status'];
			if($result==200){
				$current_user = wp_get_current_user();
				$user_id = $current_user->ID;
				$insertSQL = "INSERT INTO " . $table_name . " SET user_id = '$user_id', status = '$result', token = '$token', create_date = NOW()";
				$results = $wpdb->query($insertSQL);
			}
			$res = array(
					 'status'=>$response['status']
					);
			echo json_encode($res);
			wp_die();
		}
		
		function wpcb_selected_ajax_callback(){
			global $wpdb;
			$table_name = $wpdb->prefix . "chat_bot";
			$selectedcb = sanitize_text_field($_POST['selectedCB']);
			$botwidget = sanitize_text_field($_POST['botwidget']);
			$updateSql = "UPDATE ".$table_name." SET selectedcb='$selectedcb',bot_status='$botwidget'";
			$result = $wpdb->query($updateSql);
			if($result){
				$res = array(
					 'status'=>'200'
			  );
			  echo json_encode($res);
			}
			wp_die();
		}
		
		function wpcb_hidebot_ajax_callback(){
			global $wpdb;
			$table_name = $wpdb->prefix . "chat_bot";
			$botwidget = sanitize_text_field($_POST['botwidget']);
			$updateSql = "UPDATE ".$table_name." SET bot_status='$botwidget'";
			$result = $wpdb->query($updateSql);
			wp_die();
		}
		
		function wpcb_enable_ajax_callback(){
			global $wpdb;
			$table_name = $wpdb->prefix . "chat_bot";
			$botwidget = sanitize_text_field($_POST['botvalue']);
			$updateSql = "UPDATE ".$table_name." SET bot_status='$botwidget'";
			$result = $wpdb->query($updateSql);
			wp_die();
		}
		
		/*  
		 * Used for disconnect the account.
		 */
		function wpcb_disconnect_ajax_callback(){
			global $wpdb;
			$table_name = $wpdb->prefix . "chat_bot";
			$cb_id = sanitize_text_field($_POST['cb_id']);
			$delete = "DELETE FROM " . $table_name . " where cb_id='$cb_id'";
			$results = $wpdb->query($delete);
			wp_die();
		}
	   
		/*  
		 * Adding css file 
		 */
		function chatbot_addcss(){
			wp_enqueue_style('chatbot_css', '' . plugins_url( 'assets/css', __FILE__ ) . '/chatbot.css' );
		}
		
		function addChatBot_js(){
			wp_enqueue_script('jquery.validate','' . plugins_url( 'assets/js', __FILE__ ) . '/jquery.validate.min.js', '', true);
			wp_enqueue_script('custom-script','' . plugins_url( 'assets/js', __FILE__ ) . '/custom.js', array('jquery'), false, true );
			
			// Localize the script with new data
			$script_data_array = array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'security' => wp_create_nonce('wpcb_ajax_none')
			);
			
			wp_localize_script('custom-script', 'wpcb', $script_data_array );
			// Enqueued script with localized data.
			wp_enqueue_script( 'custom-script' );
		}
		
		function add_chatbot_script(){
			global $wpdb;
			$table_name = $wpdb->prefix . "chat_bot"; // write table name
			$chatArr = $wpdb->get_row("SELECT * FROM $table_name");
			$selectedcb = $chatArr->selectedcb;
			if(!empty($selectedcb)){
				if($chatArr->bot_status=="off"){?>
				  <script id="appyWidgetInit" src="https://chatbot.appypie.com/widget/loadbuild.js?cid=<?php echo $selectedcb; ?>"></script>
				<?php
				}
			}
		}

	}
   new AppypieChatBot();
}
