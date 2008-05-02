<?php
include_once( 'reason_header.php' );
reason_include_once( 'function_libraries/user_functions.php' );

/**
 * EditLink Module
 *
 * @package reason
 * @subpackage minisite_modules
 */
	$GLOBALS[ '_module_class_names' ][ basename( __FILE__, '.php' ) ] = 'EditLinkModule';
	
	/*
	 * Create page editing links for site users with editing access and login/logout link.
	 */
	class EditLinkModule extends DefaultMinisiteModule
	{
		var $user_netid;
		var $edit_link;
		
		/**
		 * Tells the template that this module always contains content
		 */
		function has_content() // {{{
		{
			return true;
		}
		
		/**
		 * Check whether or not the logged in user (if any) has the right privileges to edit the current page.
		 */
		function init( $args = array() ) 
		{
			$this->user_netid = reason_check_authentication();
			if( ($this->user_netid) && user_has_access_to_site($this->site_id) && reason_user_has_privs(get_user_id($this->user_netid), 'edit') )
			{
				$type_id = id_of('minisite_page');
				$fromweb = carl_make_link(array(), '', 'relative');
				$qs_array = array('site_id' => $this->site_id, 'type_id' => $type_id, 'id' => $this->page_id, 'cur_module' => 'Editor', 'fromweb' => $fromweb);
				$qs = carl_make_link($qs_array, '', 'qs_only', true, false);
				$this->edit_link = securest_available_protocol() . '://' . REASON_WEB_ADMIN_PATH . $qs;	
			}
		}
		
		/*
		 * Output appropriate HTML according to the users login state and access privileges
		 */
		function run() // {{{
		{
			if (!empty($this->edit_link))
			{
				echo '<div class="editDiv">'."\n";
				echo $this->user_netid . ': You may <a href="'.$this->edit_link.'" class="editLink">edit this page</a>'."\n";
				echo '</div>';
			}
			echo '<p id="footerLoginLink">';
			echo ($this->user_netid) ? 'Logged in as ' . $this->user_netid . '. ' : '';
			echo ($this->user_netid) ? '<a href="'.REASON_LOGIN_URL.'?logout=1">Logout</a>' : '<a href="'.REASON_LOGIN_URL.'">Login</a>';
			echo '</p>';	
		}

		function get_documentation()
		{
			return '<p>Provies a link to log in and log out of Reason. If you are logged in and have access to administer this site, this module provides a link to edit the current page.</p>';
		}
	}

?>
