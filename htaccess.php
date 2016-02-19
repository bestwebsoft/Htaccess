<?php
/*
Plugin Name: Htaccess by BestWebSoft
Plugin URI: http://bestwebsoft.com/products/
Description: The plugin Htaccess allows controlling access to your website using the directives Allow and Deny. Access can be controlled based on the client's hostname, IP address, or other characteristics of the client's request.
Author: BestWebSoft
Text Domain: htaccess
Domain Path: /languages
Version: 1.7.1
Author URI: http://bestwebsoft.com/
License: GPLv2 or later
*/

/*  © Copyright 2016  BestWebSoft  ( http://support.bestwebsoft.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! function_exists( 'add_htccss_admin_menu' ) ) {
	function add_htccss_admin_menu() {
		if ( is_multisite() && ! is_network_admin() )
			return;
		bws_general_menu();
		$settings = add_submenu_page( 'bws_plugins', 'Htaccess ' . __( 'Settings', 'htaccess' ), 'Htaccess', 'manage_options', "htaccess.php", 'htccss_settings_page' );
		add_action( 'load-' . $settings, 'htccss_add_tabs' );
	}
}

if ( ! function_exists( 'htccss_plugins_loaded' ) ) {
	function htccss_plugins_loaded() {
		/* Internationalization, first(!) */
		load_plugin_textdomain( 'htaccess', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

if ( ! function_exists ( 'htccss_plugin_init' ) ) {
	function htccss_plugin_init() {
		global $htccss_plugin_info;

		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );

		if ( empty( $htccss_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) )
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			$htccss_plugin_info = get_plugin_data( __FILE__ );
		}

		/* Function check if plugin is compatible with current WP version */
		bws_wp_min_version_check( plugin_basename( __FILE__ ), $htccss_plugin_info, '3.8', '3.5' );
	}
}

if ( ! function_exists ( 'htccss_plugin_admin_init' ) ) {
	function htccss_plugin_admin_init() {
 		global $bws_plugin_info, $htccss_plugin_info;

 		if ( ! isset( $bws_plugin_info ) || empty( $bws_plugin_info ) )
			$bws_plugin_info = array( 'id' => '110', 'version' => $htccss_plugin_info["Version"] );

		/* Call register settings function */
		if ( isset( $_GET['page'] ) && "htaccess.php" == $_GET['page'] )
			register_htccss_settings();
	}
}

/* register settings function */
if ( ! function_exists( 'register_htccss_settings' ) ) {
	function register_htccss_settings() {
		global $htccss_options, $htccss_plugin_info, $htccss_option_defaults;

		$htccss_option_defaults = array(
			'order'						=> 'Order Allow,Deny',
			'allow'						=> '',
			'deny'						=> '',
			'plugin_option_version' 	=> $htccss_plugin_info["Version"],
			'allow_xml'					=> htccss_check_xml_access(),
			'display_settings_notice'	=>	1,
			'first_install'				=>	strtotime( "now" )
		);

		/* Install the option defaults */
		if ( is_multisite() ) {
			if ( ! get_site_option( 'htccss_options' ) )
				add_site_option( 'htccss_options', $htccss_option_defaults );
		} else {
			if ( ! get_option( 'htccss_options' ) )
				add_option( 'htccss_options', $htccss_option_defaults );
		}
		/* Get options from the database */
		$htccss_options = ( is_multisite() ) ? get_site_option( 'htccss_options' ) : get_option( 'htccss_options' );

		/* Array merge incase this version has added new options */
		if ( ! isset( $htccss_options['plugin_option_version'] ) || $htccss_options['plugin_option_version'] != $htccss_plugin_info["Version"] ) {
			$htccss_option_defaults['display_settings_notice'] = 0;
			$htccss_options = array_merge( $htccss_option_defaults, $htccss_options );
			$htccss_options['plugin_option_version'] = $htccss_plugin_info["Version"];
			/* show pro features */
			$htccss_options['hide_premium_options'] = array();

			if ( is_multisite() )
				update_site_option( 'htccss_options', $htccss_options );
			else
				update_option( 'htccss_options', $htccss_options );
		}
	}
}

/* add help tab  */
if ( ! function_exists( 'htccss_add_tabs' ) ) {
	function htccss_add_tabs() {
		$screen = get_current_screen();
		$args = array(
			'id' 			=> 'htccss',
			'section' 		=> '200538709'
		);
		bws_help_tab( $screen, $args );
	}
}

if ( ! function_exists( 'htccss_plugin_action_links' ) ) {
	function htccss_plugin_action_links( $links, $file ) {
		if ( ( is_multisite() && is_network_admin() ) || ( ! is_multisite() && is_admin() ) ) {
			/* Static so we don't call plugin_basename on every plugin row. */
			static $this_plugin;
			if ( ! $this_plugin )
				$this_plugin = plugin_basename(__FILE__);
			if ( $file == $this_plugin ) {
				$settings_link = '<a href="admin.php?page=htaccess.php">' . __( 'Settings', 'htaccess' ) . '</a>';
				array_unshift( $links, $settings_link );
			}
		}
		return $links;
	}
}
/* End function htccss_plugin_action_links */

if ( ! function_exists( 'htccss_register_plugin_links' ) ) {
	function htccss_register_plugin_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file == $base ) {
			if ( ( is_multisite() && is_network_admin() ) || ( ! is_multisite() && is_admin() ) )
				$links[] = '<a href="admin.php?page=htaccess.php">' . __( 'Settings', 'htaccess' ) . '</a>';
			$links[] = '<a href="http://wordpress.org/plugins/htaccess/faq/" target="_blank">' . __( 'FAQ', 'htaccess' ) . '</a>';
			$links[] = '<a href="http://support.bestwebsoft.com">' . __( 'Support', 'htaccess' ) . '</a>';
		}
		return $links;
	}
}

/**
 * Check if string is a number and it less than specified number
 * @param    string      $value     the string that we have to check
 * @param    int         $max       the maximum number that can take the $value
 * @return   boolean
 */
if ( ! function_exists( 'htcsss_is_less' ) ) {
	function htcsss_is_less( $value, $max ) {
		return is_numeric( $value ) && intval( $value ) == $value && $max >= $value;
	}
}

/**
 * Check if string is an IP address
 * @param    string      $value     the string that we have to check
 * @return   boolean
 */
if ( ! function_exists( 'htccss_is_ip' ) ) {
	function htccss_is_ip( $value ) {
		return
			/* IP v4 or v6*/
			filter_var( $value, FILTER_VALIDATE_IP ) ||
			/* numbers from 0 to 255 - an IP range */
			htcsss_is_less( $value, 255 ) ||
			/* IP v4 range */
			preg_match( '/(^((25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])\.){1,3}$)|(^((25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])\.){1,3}\*{1}$)|(^((25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])\.){2}(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9]){1}$)/', $value ) ||
			/* slash is in string */
			( preg_match_all( "/^(.*?)\/(.*?)$/", $value, $matches ) &&

				(	/* IP v6 CIDR */
					( filter_var( $matches[1][0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) && htcsss_is_less( $matches[2][0], 128 ) ) ||
					/* IP v4 CIDR */
					( filter_var( $matches[1][0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) && ( htcsss_is_less( $matches[2][0], 32 ) || filter_var( $matches[2][0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) )
				)
			);
	}
}

/**
 * Check if string is a hostname
 * @param    string      $value     the string that we have to check
 * @return   boolean
 */
if ( ! function_exists( 'htccss_is_host' ) ) {
	function htccss_is_host( $value ) {
		return
			! is_numeric( $value ) &&
			! preg_match( "/^\d[\.\d]*$/", $value ) && /* string must not contains numbers and dots only */
			preg_match( "/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $value ) &&
			preg_match("/^.{1,253}$/", $value ) && /* hostnames used can be as long as 253 bytes */
			preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $value ); /* the first label (removing '.' and anything after it from the hostname) can only be up to 63 bytes */
	}
}

/**
 * Check if string is an environment variable
 * @param    string      $value     the string that we have to check
 * @return   boolean
 */
if ( ! function_exists( 'htccss_is_env' ) ) {
	function htccss_is_env ( $value ) {
		return preg_match( "/^(env={1}([a-zA-Z0-9]|[a-zA-Z0-9\-]|[a-zA-Z0-9_])+)$/", $value );
	}
}

/**
 * Check entered value
 * @param    string    $value    the string that we have to check
 * @return   boolean
 */
if ( ! function_exists( 'htccss_is_wrong' ) ) {
	function htccss_is_wrong( $value ) {
		return
			! empty( $value ) &&
			preg_match( "/[a-z]|[A-Z]|[0-9]/", $value ) &&
			( htccss_is_ip( $value ) || htccss_is_env( $value ) || htccss_is_host( $value ) );
	}
}

/**
 * Filters option before saving to database
 * @param     string    $list_order     <textarea>`s value form pligin`s page
 * @return    string                    escaped string with list of IPs.
 */
if ( ! function_exists( 'htcss_esc_directive' ) ) {
	function htcss_esc_directive( $list_order ) {
		$list_order = trim( esc_textarea( $list_order ) );
		$list_order = preg_replace( "/(Allow from)|(Deny from)/i", '', $list_order );

		if ( empty( $list_order ) )
			return '';

		/* split the string by any number of commas, colons, whitespaces, \r, \t, \n */
		$array_order = preg_split( "/[\t\n\r\s\,]+/", $list_order, -1, PREG_SPLIT_NO_EMPTY );
		$array_order = array_unique( $array_order );
		$array_order = array_filter( $array_order, 'htccss_is_wrong' );

		return empty( $array_order ) ? '' : strtolower( implode( "\n", $array_order ) );
	}
}

/* Function for display htaccess settings page in the admin area */
if ( ! function_exists( 'htccss_settings_page' ) ) {
	function htccss_settings_page() {
		global $htccss_admin_fields_enable, $htccss_options, $htccss_plugin_info, $wp_version, $htccss_option_defaults;
		$error = $message = "";
		$all_plugins = get_plugins();
		$plugin_basename = plugin_basename(__FILE__);
		if ( ! isset( $_GET['action'] ) ) {
			/* Save data for settings page */
			if ( isset( $_REQUEST['htccss_form_submit'] ) && check_admin_referer( $plugin_basename, 'htccss_nonce_name' ) ) {
				if ( isset( $_POST['bws_hide_premium_options'] ) ) {
					$hide_result = bws_hide_premium_options( $htccss_options );
					$htccss_options = $hide_result['options'];
				}
				$htccss_options['order']     = isset( $_REQUEST['htccss_order'] ) ? $_REQUEST['htccss_order'] : 'Order Allow,Deny';
				$htccss_options['allow_xml'] = isset( $_REQUEST['htccss_allow_xml'] ) ? 1 : 0;
				$htccss_options['allow']     = htcss_esc_directive( $_REQUEST['htccss_allow'] );
				$htccss_options['deny']      = htcss_esc_directive( $_REQUEST['htccss_deny'] );
				$htccss_options = array_map( 'stripslashes_deep', $htccss_options );

				if ( "" == $error ) {
					/* Update options in the database */
					if ( is_multisite() )
						update_site_option( 'htccss_options', $htccss_options );
					else
						update_option( 'htccss_options', $htccss_options );

					$message = __( "Settings saved.", 'htaccess' );
					htccss_generate_htaccess();
				}
			} else {
				htccss_get_htaccess();
			}
		}
		/* Add restore function */
		if ( isset( $_REQUEST['bws_restore_confirm'] ) && check_admin_referer( $plugin_basename, 'bws_settings_nonce_name' ) ) {
			$htccss_options = $htccss_option_defaults;
			if ( is_multisite() )
				update_site_option( 'htccss_options', $htccss_options );
			else
				update_option( 'htccss_options', $htccss_options );
			htccss_generate_htaccess();
			$message = __( 'All plugin settings were restored.', 'htaccess' );
		}
		/* end */

		$bws_hide_premium_options_check = bws_hide_premium_options_check( $htccss_options );

		/* GO PRO */
		if ( isset( $_GET['action'] ) && 'go_pro' == $_GET['action'] ) {
			$go_pro_result = bws_go_pro_tab_check( $plugin_basename, 'htccss_options' );
			if ( ! empty( $go_pro_result['error'] ) )
				$error = $go_pro_result['error'];
			elseif ( ! empty( $go_pro_result['message'] ) )
				$message = $go_pro_result['message'];
		} /* Display form on the setting page */ ?>
		<div class="wrap">
			<h1><?php _e( 'Htaccess Settings', 'htaccess' ); ?></h1>
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab <?php if ( ! isset( $_GET['action'] ) ) echo ' nav-tab-active'; ?>" href="admin.php?page=htaccess.php"><?php _e( 'Settings', 'htaccess' ); ?></a>
				<a class="nav-tab bws_go_pro_tab<?php if ( isset( $_GET['action'] ) && 'go_pro' == $_GET['action'] ) echo ' nav-tab-active'; ?>" href="admin.php?page=htaccess.php&amp;action=go_pro"><?php _e( 'Go PRO', 'htaccess' ); ?></a>
			</h2>
			<div class="updated fade below-h2" <?php if ( '' == $message || $error != "" ) echo "style=\"display:none\""; ?>><p><strong><?php echo $message; ?></strong></p></div>
			<div class="error below-h2" <?php if ( "" == $error ) echo "style=\"display:none\""; ?>><p><?php echo $error; ?></p></div>
			<?php if ( ! isset( $_GET['action'] ) ) {
				if ( isset( $_REQUEST['bws_restore_default'] ) && check_admin_referer( $plugin_basename, 'bws_settings_nonce_name' ) ) {
					bws_form_restore_default_confirm( $plugin_basename );
				} else {
					if ( ! empty( $hide_result['message'] ) ) { ?>
						<div class="updated fade"><p><strong><?php echo $hide_result['message']; ?></strong></p></div>
					<?php }
					bws_show_settings_notice(); ?>
					<div class="error below-h2">
						<p><strong><?php _e( "Notice:", 'htaccess' ); ?></strong> <?php _e( "It is very important to be extremely attentive when making changes to .htaccess file. If after making changes your site stops functioning, please see", 'htaccess' ); ?> <a href="http://wordpress.org/plugins/htaccess/faq/" target="_blank" title=""><?php _e( 'FAQ', 'htaccess' ); ?></a></p>
						<p><?php _e( 'The changes will be applied immediately after saving the changes, if you are not sure - do not click the "Save changes" button.', 'htaccess' ); ?></p>
					</div>
					<form id="htccss_settings_form" class="bws_form" method="post" action="admin.php?page=htaccess.php">
						<table class="form-table">
							<tr valign="top">
								<th scope="row"><?php _e( 'Order fields', 'htaccess' ); ?></th>
								<td>
									<fieldset>
										<label><input type="radio" name="htccss_order" value="Order Allow,Deny" <?php if ( 'Order Allow,Deny' == $htccss_options['order'] ) echo "checked=\"checked\" "; ?>/> Order Allow,Deny</label><br />
										<label><input type="radio" name="htccss_order" value="Order Deny,Allow" <?php if ( 'Order Deny,Allow' == $htccss_options['order'] ) echo "checked=\"checked\" "; ?>/> Order Deny,Allow</label>
									</fieldset>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Allow from', 'htaccess' ); ?></th>
								<td>
									<textarea name="htccss_allow"><?php echo $htccss_options['allow']; ?></textarea><br />
									<span class="bws_info"><?php _e( "Info about the arguments to the Allow directive", 'htaccess' ) ?>: <a href="http://bestwebsoft.com/controlling-access-to-your-website-using-the-htaccess/#Allow_Directive"><?php _e( "Controlling access to your website using the .htaccess", 'htaccess' ); ?></a></span>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Deny from', 'htaccess' ); ?></th>
								<td>
									<textarea name="htccss_deny"><?php echo $htccss_options['deny']; ?></textarea><br />
									<span class="bws_info"><?php _e( "Info about the arguments to the Deny directive", 'htaccess' ) ?>: <a href="http://bestwebsoft.com/controlling-access-to-your-website-using-the-htaccess/#Deny_Directive"><?php _e( "Controlling access to your website using the .htaccess", 'htaccess' ); ?></a></span>
								</td>
							</tr>
						</table>
						<?php if ( ! $bws_hide_premium_options_check ) { ?>
							<div class="bws_pro_version_bloc">
								<div class="bws_pro_version_table_bloc">
									<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php _e( 'Close', 'htaccess' ); ?>"></button>
									<div class="bws_table_bg"></div>
									<table class="form-table bws_pro_version">
										<tr valign="top">
											<th scope="row"><?php _e( 'Access to xmlrpc.php', 'htaccess' ); ?></th>
											<td>
												<label><input type="checkbox" name="htccsspr_xmlrpc" value="1" disabled="disabled"> </label>
												<div class="bws_help_box dashicons dashicons-editor-help"></div><br />
												<span class="bws_info htaccess_info_link"><?php _e( "Learn more", 'htaccess' ) ?>: <a target="_blank" href="http://bestwebsoft.com/what-is-xml-rpc/"><?php _e( "What is XML-RPC?", 'htaccess' ); ?></a></span>
											</td>
										</tr>
										<tr valign="top">
											<th scope="row"><?php _e( 'Disable hotlinking', 'htaccess' ); ?></th>
											<td>
												<label><input type="checkbox" name="htccsspr_hotlink_deny" value="1" disabled="disabled" /> </label>
												<div class="bws_help_box dashicons dashicons-editor-help"></div><br />
												<span class="bws_info htaccess_info_link"><?php _e( "Learn more", 'htaccess' ) ?>: <a target="_blank" href="http://bestwebsoft.com/how-to-prevent-hotlinking/"><?php _e( "How to Prevent Hotlinking?", 'htaccess' ); ?></a></span>
											</td>
										</tr>
										<tr valign="top">
										<th scope="row"><?php _e( 'Allow hotlinking for', 'htaccess' ); ?></th>
											<td>
												<textarea name="htccsspr_hotlink_alow" disabled="disabled"></textarea></br>
												<span class="bws_info"><?php _e( 'Allowed hosts should be entered comma separated', 'htaccess' ); ?></span></br>
											</td>
										</tr>
									</table>
									<table>
										<tr valign="top">
											<th scope="row" colspan="2">
												* <?php _e( 'If you upgrade to Pro version all your settings will be saved.', 'htaccess' ); ?>
											</th>
										</tr>
									</table>
								</div>
								<div class="bws_pro_version_tooltip">
									<div class="bws_info">
										<?php _e( 'Unlock premium options by upgrading to Pro version', 'htaccess' ); ?>
									</div>
									<a class="bws_button" href="http://bestwebsoft.com/products/htaccess/?k=ac1e1061bf4e95ba51406b4cc32f61fa&pn=110&v=<?php echo $htccss_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="Htaccess Plugin"><?php _e( 'Learn More', 'htaccess' ); ?></a>
									<div class="htccss-clear"></div>
								</div>
							</div>
						<?php } ?>
						<table class="form-table">
							<?php if ( is_multisite() && ! is_subdomain_install() ) { ?>
								<tr valign="top">
									<th scope="row"><?php _e( 'Allow access to XML files', 'htaccess' ); ?></th>
									<td>
										<label><input type="checkbox" name="htccss_allow_xml" value="1"<?php echo 1 == $htccss_options['allow_xml'] ? ' checked="checked"' : ''; ?> /></label>
										<div class="bws_help_box dashicons dashicons-editor-help">
											<div class="bws_hidden_help_text" style="min-width: 150px;">
												<p><?php _e( 'The following string will be added to your .htaccess file', 'htaccess' ); ?>:</p>
												<code>RewriteRule ([^/]+\.xml)$ $1 [L]</code>
											</div><!-- .bws_hidden_help_text -->
										</div>
									</td>
								</tr>
							<?php } ?>
						</table>
						<p class="submit">
							<input type="hidden" name="htccss_form_submit" value="submit" />
							<input id="bws-submit-button" type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'htaccess' ); ?>" />
						</p>
						<?php wp_nonce_field( $plugin_basename, 'htccss_nonce_name' ); ?>
					</form>
					<?php bws_form_restore_default_settings( $plugin_basename );
				}
			} elseif ( 'go_pro' == $_GET['action'] ) {
				bws_go_pro_tab_show( $bws_hide_premium_options_check, $htccss_plugin_info, $plugin_basename, 'htaccess.php', 'htaccess-pro.php', 'htaccess-pro/htaccess-pro.php', 'htaccess', 'ac1e1061bf4e95ba51406b4cc32f61fa', '110', isset( $go_pro_result['pro_plugin_is_activated'] ) );
			}
			bws_plugin_reviews_block( $htccss_plugin_info['Name'], 'htaccess' ); ?>
		</div>
	<?php }
}

/* check for access to XML files */
if ( ! function_exists( 'htccss_check_xml_access' ) ) {
	function htccss_check_xml_access() {
		$check = 0;
		if ( is_multisite() && ! is_subdomain_install() ) {
			if ( ! function_exists( 'get_home_path' ) )
				require_once ( ABSPATH . 'wp-admin/includes/file.php' );
			$htaccess_file = get_home_path() . '.htaccess';
			$check = file_exists( $htaccess_file ) && preg_match( "|\nRewriteRule \(\[\^/\]\+\\\.xml\)\$ \$1 \[L\]|", file_get_contents( $htaccess_file ) ) ? 1 : 0;
		}
		return $check;
	}
}

/**
 * Convert array of IPs to string.
 * Function is needed to easy viewing of "Deny" and "Allow" options
 * on plugin settings page
 * @param      array    $array
 * @return     string
 */
if ( ! function_exists( 'htccss_implode' ) ) {
	function htccss_implode( $array ) {
		foreach( $array as $key => $item )
			$array[ $key ] = preg_replace( "/\s/", "\n", trim( $item ) );
		$array = array_unique( $array );
		return implode( "\n", $array );
	}
}

/**
 * Forming directive
 * @param     array     $array      list of IP or CIDR
 * @param     string    $directive  'Allow from' or 'Deny From'
 */
if ( ! function_exists( 'htccss_prepare_directive' ) ) {
	function htccss_prepare_directive( $array, $directive ) {
		$result = '';
		foreach( $array as $item ) {
			$string  = trim( $item );
			$result .= empty( $string ) ? '' : $directive . ' ' . $string . "\n";
		}
		return $result;
	}
}

if ( ! function_exists ( 'htccss_get_htaccess' ) ) {
	function htccss_get_htaccess() {
		global $htccss_options;

		if ( empty( $htccss_options ) )
			$htccss_options = is_multisite() ? get_site_option( 'htccss_options' ) : get_option( 'htccss_options' );

		if ( ! function_exists( 'get_home_path' ) )
			require_once ( ABSPATH . 'wp-admin/includes/file.php' );

		$htaccess_file = get_home_path() . '.htaccess';

		if ( ! file_exists( $htaccess_file ) )
			return false;

		$handle = fopen( $htaccess_file, "r" );

		if ( $handle ) {
			$allow_array = $deny_array = array();
			$skip = false;
			/*
			 * Get all "Deny from" and "Allow from" directives except those
			 * which placed inside group directives
			 * <Files>, <FilesMatch>,
			 * <Directory>, <DirectoryMatch>
			 * <Proxy>, <ProxyMatch>
			 * <Location>, <LocationMatch> or <Limit>
			 */
			while ( ! feof( $handle ) ) {
				$string = fgets( $handle );
				if ( preg_match( "/^\#(.*?)$/", $string ) ) /* skip comments */
					continue;
				elseif ( preg_match( "/<(Files)|(Directory)|(Proxy)|(Location)|(Limit)[\s\S]+>/i", $string ) ) /* open tag */
					$skip = true;
				elseif ( preg_match( "/<\/(Files)|(Directory)|(Proxy)|(Location)|(Limit)[\s\S]+>/i", $string ) ) /* close tag */
					$skip = false;
				elseif ( preg_match( "/^[\s]*((?=.*Order)(?=.*Deny)(?=.*Allow))[\s]*$/i", $string ) && ! $skip ) /* last founded ORDER will be saved in plugin`s settings */
					$htccss_options['order'] = trim( $string );
				elseif ( preg_match( "/Allow from[\s\S]+/i", $string ) && ! $skip )
					$allow_array[] = trim( str_ireplace( 'Allow from ', '', $string ) );
				elseif ( preg_match( "/Deny from[\s\S]+/i", $string ) && ! $skip )
					$deny_array[] = trim( str_ireplace( 'Deny from ', '', $string ) );
			}

			fclose( $handle );

			$htccss_options['allow']     = htccss_implode( $allow_array );
			$htccss_options['deny']      = htccss_implode( $deny_array );
			$htccss_options['allow_xml'] = htccss_check_xml_access();
		}
	}
}

if ( ! function_exists ( 'htccss_mod_rewrite_rules' ) ) {
	function htccss_mod_rewrite_rules( $rules ) {
		global $htccss_options;
		$home_path = get_home_path();
		if ( ! file_exists( $home_path . '.htaccess' ) ) {

			$allow_array = preg_split( "/[\t\n\r\s\,]+/", trim( $htccss_options['allow'] ), -1, PREG_SPLIT_NO_EMPTY  );
			$deny_array  = preg_split( "/[\t\n\r\s\,]+/", trim( $htccss_options['deny'] ), -1, PREG_SPLIT_NO_EMPTY  );

			if (
				false == stripos( $rules, 'Order ' ) &&
				( ! empty( $allow_array ) || ! empty( $deny_array ) )
			) {
				$allow = stripos( $htccss_options['order'], 'Allow' );
				$deny  = stripos( $htccss_options['order'], 'Deny' );
				$allow_first = $allow < $deny;

				$allow_content = empty( $allow_array ) ? 'Allow from all' . "\n" : htccss_prepare_directive( $allow_array, 'Allow from' );
				$deny_content  = empty( $deny_array ) ? '' : htccss_prepare_directive( $deny_array, 'Deny from' );

				$content  = $htccss_options['order'] . "\n";
				$content .=
						$allow_first
					?
						$allow_content . $deny_content
					:
						$deny_content . $allow_content;

				$rules = $content . $rules ;
			}
		}
		return $rules;
	}
}

if ( ! function_exists ( 'htccss_generate_htaccess' ) ) {
	function htccss_generate_htaccess() {
		global $htccss_options;

		$htaccess_file = get_home_path() . '.htaccess';

		if ( ! file_exists( $htaccess_file ) )
			return false;

		$handle = fopen( $htaccess_file, "r+" );

		if ( $handle ) {

			/*
			 * Attempt to get a lock. If the filesystem supports locking, this will block until the lock is acquired.
			 * It is required for Windows
			 */
			flock( $handle, LOCK_EX );

			/* Get content of .htaccess without "Deny from" and "Allow from" directives */

			$content = trim( stream_get_contents( $handle ) );

			if ( ! empty( $content ) ) {
				/* get SetEnv directives that were early added to the top of .htaccess and remove them from the content of .htaccess  */
				if ( preg_match( "/# htccss_set_env_start #([\s\S]+)# htccss_set_env_end #/", $content, $matches ) ) {
					$setenv_directives = trim( $matches[1] ) . "\n";
					$content = preg_replace( "/# htccss_set_env_start([\s\S]+)htccss_set_env_end #/", "", $content );
				} else {
					$setenv_directives = '';
				}

				/* remove plugin`s "Deny from" and "Allow from" directives */
				if ( preg_match( '/htccss_order_start/', $content ) )
					$content = preg_replace( "/# htccss_order_start[\s\S]+?htccss_order_end #/", "", $content );

				/*
				 * Remove other "Deny from" and "Allow from" directives except those
				 * which placed inside group directives
				 * <Files>, <FilesMatch>,
				 * <Directory>, <DirectoryMatch>
				 * <Proxy>, <ProxyMatch>
				 * <Location>, <LocationMatch> or <Limit>
				 */
				$content_array = explode( "\n", $content );
				$env_vars = $setenv_temp = array();
				if ( is_array( $content_array ) && ! empty( $content_array ) ) {
					$skip = false;
					foreach( $content_array as $key => $string ) {
						if ( preg_match( "/^\#(.*?)$/", $string ) ) { /* skip comments */
							continue;
						} elseif ( preg_match( "/<(Files)|(Directory)|(Proxy)|(Location)|(Limit)[\s\S]+>/i", $string ) ) {/* open tag */
							$skip = true;
						} elseif ( preg_match( "/<\/(Files)|(Directory)|(Proxy)|(Location)|(Limit)[\s\S]+>/i", $string ) ) { /* close tag */
							$skip = false;
						} elseif ( preg_match( "/^[\s]*((?=.*Order)(?=.*Deny)(?=.*Allow))[\s]*$/i", $string ) && ! $skip ) {
							unset( $content_array[ $key ] );
						} elseif ( preg_match( "/((Allow from)|(Deny from))[\s\S]+/i", $string ) && ! $skip ) {
							/* if directive contains some environment variables and we found some SetEnv directives early */
							if ( preg_match_all( "/env=(.*?)(\S+)/", $string, $matches ) && ! empty( $setenv_temp ) ) {
								foreach ( $matches[2] as $var_name ) {
									foreach ( $setenv_temp as $key_temp => $item ) {
										if ( preg_match( "|" . preg_quote( $var_name ) . "|", $item['setenv_string'] ) ) {
											/* add SetEnv directives to the top part of .htaccess */
											if ( ! preg_match( "|" . preg_quote( $item['setenv_string'] ) . "|", $setenv_directives ) )
												$setenv_directives .= "{$item['setenv_string']}\n";
											/* remove it from the bottom part of .htaccess */
											unset( $content_array[ $item['setenv_key'] ] );
											/* remove it from the temporary array */
											unset( $setenv_temp[ $key_temp ] );
										}
									}
								}
							}
							unset( $content_array[ $key ] );
						} elseif ( preg_match( "/^[\s]*(SetEnv)|(SetEnvIf)|(SetEnvIfNoCase)|(SetEnvIfExpr)[\s\S]+/i", $string ) ) {
							/* add directive to the temporary array */
							$setenv_temp[] = array( 'setenv_key' => $key, 'setenv_string' => $string );
						}
					}
					$content = implode( "\n", $content_array );
					$content = trim( $content );
				}
			}

			$setenv_directives = empty( $setenv_directives ) ? '' : "# htccss_set_env_start #\n{$setenv_directives}# htccss_set_env_end #\n";

			/* get "Deny from" and "Allow from" directives from plugin`s settings */
			$allow_array  = preg_split( "/[\t\n\r\s\,]+/", $htccss_options['allow'], -1, PREG_SPLIT_NO_EMPTY );
			$deny_array   = preg_split( "/[\t\n\r\s\,]+/", $htccss_options['deny'], -1, PREG_SPLIT_NO_EMPTY );

			if ( ! empty( $allow_array ) || ! empty( $deny_array ) ) {

				$htccss_allow_array = $htccss_deny_array = array();
				/* split the arrays to form directives */
				if ( 400 < count( $allow_array ) ) {
					$allow_array_all = array_chunk( $allow_array, 400 );
					foreach ( $allow_array_all as $value )
						$htccss_allow_array[] = implode( ' ', $value );
				} else {
					$htccss_allow_array[] = implode( ' ', $allow_array );
				}

				if ( 400 < count( $deny_array ) ) {
					$deny_array_all = array_chunk( $deny_array, 400 );
					foreach ( $deny_array_all as $value )
						$htccss_deny_array[] = implode( ' ', $value );
				} else {
					$htccss_deny_array[] = implode( ' ', $deny_array );
				}

				/* get directives */
				$allow_content = empty( $htccss_allow_array ) ? '' : htccss_prepare_directive( $htccss_allow_array, 'Allow from' );
				$deny_content  = empty( $htccss_deny_array ) ? '' : htccss_prepare_directive( $htccss_deny_array, 'Deny from' );

				/* add directives to the content of .htaccess */
				if ( ! empty( $allow_content ) || ! empty( $deny_content ) ) {

					$allow = stripos( $htccss_options['order'], 'Allow' );
					$deny  = stripos( $htccss_options['order'], 'Deny' );

					if ( $allow < $deny && empty( $htccss_options['allow'] ) && ! empty( $htccss_options['deny'] ) ) {
						$allow_first = false;
						$htccss_options['order'] = "Order Deny,Allow";
					} else {
						$allow_first = $allow < $deny;
					}

					$order_allow_deny_content  = "# htccss_order_start #\n{$htccss_options['order']}\n";
					$order_allow_deny_content .=
							$allow_first
						?
							$allow_content . $deny_content
						:
							$deny_content . $allow_content;
					$order_allow_deny_content .= "# htccss_order_end #\n";

					$content = "{$setenv_directives}\n{$order_allow_deny_content}\n{$content}";
				}
			}

			/* allow access to XML files */
			if ( is_multisite() && ! is_subdomain_install() )
				$content = htccss_allow_xml( $content );

			fseek( $handle, 0 );
			$bytes = fwrite( $handle, $content );
			if ( $bytes )
				ftruncate( $handle, ftell( $handle ) );
			fflush( $handle );
			flock( $handle, LOCK_UN );
			fclose( $handle );
		}

		/* give htaccess_file 644 access rights */
		@chmod( $htaccess_file, 0777);//0644 );
	}
}


if ( ! function_exists( 'htccss_allow_xml' ) ) {
	function htccss_allow_xml( $content ) {
		global $htccss_options;
		$pattern = "|\nRewriteRule \(\[\^/\]\+\\\.xml\)\$ \$1 \[L\]|";
		if ( 1 == $htccss_options['allow_xml'] && ! preg_match( $pattern, $content ) ) {
			$content_array = preg_split( "/RewriteBase\s{1}\//", $content );
			if ( ! empty( $content_array ) ) {
				$content_array[1] = "\nRewriteRule ([^/]+\.xml)$ $1 [L]" . $content_array[1];
				$content = implode( "RewriteBase /", $content_array );
			}
		} elseif ( 0 == $htccss_options['allow_xml'] ) {
			$content = preg_replace( $pattern, '', $content );
		}
		return $content;
	}
}


if ( ! function_exists ( 'htccss_admin_head' ) ) {
	function htccss_admin_head() {
		if ( isset( $_REQUEST['page'] ) && 'htaccess.php' == $_REQUEST['page'] ) {
			wp_enqueue_style( 'htccss_stylesheet', plugins_url( 'css/style.css', __FILE__ ) );
		}
	}
}
if ( ! function_exists( 'htccss_plugin_banner' ) ) {
	function htccss_plugin_banner() {
		global $hook_suffix;
		if ( 'plugins.php' == $hook_suffix ) {
			if ( ( is_multisite() && is_network_admin() ) || ! is_multisite() ) {
				global $htccss_plugin_info, $htccss_options;
				if ( empty( $htccss_options ) )
					$htccss_options = ( is_multisite() ) ? get_site_option( 'htccss_options' ) : get_option( 'htccss_options' );

				if ( isset( $htccss_options['first_install'] ) && strtotime( '-1 week' ) > $htccss_options['first_install'] )
					bws_plugin_banner( $htccss_plugin_info, 'htccss', 'htaccess', 'd97ae872794372d2f58c3f55655bb693', '110', '//ps.w.org/htaccess/assets/icon-128x128.png' );

				bws_plugin_banner_to_settings( $htccss_plugin_info, 'htccss_options', 'htaccess', 'admin.php?page=htaccess.php' );
			}

			if ( is_multisite() && ! is_network_admin() && is_admin() ) { ?>
				<div class="update-nag"><strong><?php _e( 'Notice:', 'htaccess' ); ?></strong>
					<?php if ( is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
						_e( 'Due to the peculiarities of the multisite work, Htaccess plugin has only', 'htaccess' ); ?> <a target="_blank" href="<?php echo network_admin_url( 'admin.php?page=htaccess.php' ); ?>"><?php _e( 'Network settings page', 'htaccess' ); ?></a>
					<?php } else {
						_e( 'Due to the peculiarities of the multisite work, Htaccess plugin has the network settings page only and it should be Network Activated. Please', 'htaccess' ); ?> <a target="_blank" href="<?php echo network_admin_url( 'plugins.php' ); ?>"><?php _e( 'Activate Htaccess for Network', 'htaccess' ); ?></a>
					<?php } ?>
				</div>
			<?php }
		}
	}
}

if ( ! function_exists( 'htccss_lmtttmpts_copy_all' ) ) {
	function htccss_lmtttmpts_copy_all() {
		global $wpdb, $htccss_options;
		if ( empty( $htccss_options ) )
			$htccss_options = is_multisite() ? get_site_option( 'htccss_options' ) : get_option( 'htccss_options' );
		htccss_get_htaccess();
		$prefix = $wpdb->prefix . 'lmtttmpts_';
		$deny   = $allow = array();
		$flag   = false;

		/* add blocked IPs to "Deny" directive */
		$blocked_ips = $wpdb->get_col(
			"SELECT `ip` FROM `{$prefix}failed_attempts` WHERE `block` = true
			 UNION
			 SELECT `ip` FROM `{$prefix}blacklist`"
		);
		if ( ! empty( $blocked_ips ) ) {
			$flag    = true;
			$deny    = preg_split( "/[\t\n\r\s\,]+/", $htccss_options[ 'deny' ], -1, PREG_SPLIT_NO_EMPTY );
			$ip_list = htccss_prepare_data( (array)$blocked_ips );
			if ( empty( $deny ) ) {
				$htccss_options[ 'deny' ] = implode( "\n", $ip_list );
				$deny = $ip_list;
			} else {
				/* list of IPs that has not been added in the "Deny" directive yet */
				$deny_unique = array_diff( $ip_list, $deny );
				$htccss_options[ 'deny' ] .= "\n" . implode( "\n", $deny_unique );
				$deny = array_merge( $deny, $deny_unique );
			}
		}

		/* add whitelisted IPs to "Allow" directive */
		$whitelisted_ips = $wpdb->get_col( "SELECT `ip` FROM `{$prefix}whitelist`" );
		if ( ! empty( $whitelisted_ips ) ) {
			$flag      = true;
			$allow     = preg_split( "/[\t\n\r\s\,]+/", $htccss_options[ 'allow' ], -1, PREG_SPLIT_NO_EMPTY );
			$in_htccss = array_merge( $deny, $allow );
			$ip_list   = htccss_prepare_data( (array)$whitelisted_ips );
			if ( empty( $in_htccss ) ) {
				$htccss_options[ 'allow' ] = implode( "\n", $ip_list );
			} else {
				/* list of IPs that has not been added in the "Deny" or "Allow" directives yet */
				$allow_unique = array_diff( $ip_list, $in_htccss );
				$htccss_options[ 'allow' ] .= "\n" . implode( "\n", $allow_unique );
			}
		}
		if ( $flag ) {
			$htccss_options['deny']  = preg_replace( "/\n{2,}/", "\n", $htccss_options['deny'] );
			$htccss_options['allow'] = preg_replace( "/\n{2,}/", "\n", $htccss_options['allow'] );
			if ( preg_match( "/^\n*$/", $htccss_options['deny'] ) )
				$htccss_options['deny'] = "";
			if ( preg_match( "/^\n*$/", $htccss_options['allow'] ) )
				$htccss_options['allow'] = "";
			if ( empty( $htccss_options['deny'] ) && empty( $htccss_options['allow'] ) )
				$htccss_options['order'] = 'Order Deny,Allow';
			if ( is_multisite() )
				update_site_option( 'htccss_options', $htccss_options );
			else
				update_option( 'htccss_options', $htccss_options );
			htccss_generate_htaccess();
		}
	}
}

if ( ! function_exists( 'htccss_lmtttmpts_delete_all' ) ) {
	function htccss_lmtttmpts_delete_all() {
		global $wpdb, $htccss_options;

		if ( empty( $htccss_options ) )
			$htccss_options = is_multisite() ? get_site_option( 'htccss_options' ) : get_option( 'htccss_options' );

		htccss_get_htaccess();
		$prefix = $wpdb->prefix . 'lmtttmpts_';
		$flag   = false;
		$ip_tables = array(
			'blocked' => $wpdb->get_col(
					"SELECT `ip` FROM `{$prefix}failed_attempts` WHERE `block` = true
					 UNION
					 SELECT `ip` FROM `{$prefix}blacklist`"
				),
			'whitelisted' => $wpdb->get_col( "SELECT `ip` FROM `{$prefix}whitelist`" )
		);

		if ( ! empty( $ip_tables ) ) {
			foreach( $ip_tables as $key => $value ) {
				if ( ! empty( $value ) ) {
					$flag      = true;
					$option    = 'whitelisted' == $key ? 'allow' : 'deny';
					$ip_list   = htccss_prepare_data( (array)$value );
					$in_htccss = preg_split( "/[\t\n\r\s\,]+/", $htccss_options[ $option ], -1, PREG_SPLIT_NO_EMPTY );
					if ( ! empty( $in_htccss ) ) {
						$to_htccss = array_diff( $in_htccss, $ip_list );
						$htccss_options[ $option ] = implode( "\n", $to_htccss );
					}
				}
			}
		}

		if ( $flag ) {
			$htccss_options['deny']  = preg_replace( "/\n{2,}/", "\n", $htccss_options['deny'] );
			$htccss_options['allow'] = preg_replace( "/\n{2,}/", "\n", $htccss_options['allow'] );
			if ( preg_match( "/^\n*$/", $htccss_options['deny'] ) )
				$htccss_options['deny'] = "";
			if ( preg_match( "/^\n*$/", $htccss_options['allow'] ) )
				$htccss_options['allow'] = "";
			if ( empty( $htccss_options['deny'] ) && empty( $htccss_options['allow'] ) )
				$htccss_options['order'] = 'Order Deny,Allow';
			if ( is_multisite() )
				update_site_option( 'htccss_options', $htccss_options );
			else
				update_option( 'htccss_options', $htccss_options );
			htccss_generate_htaccess();
		}
	}
}

if ( ! function_exists( 'htccss_lmtttmpts_block' ) ) {
	function htccss_lmtttmpts_block( $ip ) {
		global $htccss_options;
		if ( empty( $htccss_options ) )
			$htccss_options = ( is_multisite() ) ? get_site_option( 'htccss_options' ) : get_option( 'htccss_options' );
		require_once ( ABSPATH . 'wp-admin/includes/file.php' );
		htccss_get_htaccess();
		$ip_list   = htccss_prepare_data( (array)$ip );
		$in_htccss = preg_split( "/[\t\n\r\s\,]+/", $htccss_options['deny'], -1, PREG_SPLIT_NO_EMPTY );
		if ( empty( $in_htccss ) ) {
			$htccss_options['deny'] = implode( "\n", $ip_list );
		} else {
			$new_ip = array_diff( $ip_list, $in_htccss );
			$htccss_options['deny'] .= "\n" . implode( "\n", $new_ip );
		}
		$htccss_options['deny'] = preg_replace( "/\n{2,}/", "\n", $htccss_options['deny'] );
		if ( preg_match( "/^\n*$/", $htccss_options['deny'] ) )
			$htccss_options['deny'] = "";
		if ( ! empty( $htccss_options['deny'] ) && empty( $htccss_options['allow'] ) )
			$htccss_options['order'] = 'Order Deny,Allow';
		if ( is_multisite() )
			update_site_option( 'htccss_options', $htccss_options );
		else
			update_option( 'htccss_options', $htccss_options );
		htccss_generate_htaccess();
	}
}

if ( ! function_exists( 'htccss_lmtttmpts_reset_block' ) ) {
	function htccss_lmtttmpts_reset_block( $ip ) {
		global $htccss_options;
		if ( empty( $htccss_options ) )
			$htccss_options = ( is_multisite() ) ? get_site_option( 'htccss_options' ) : get_option( 'htccss_options' );
		require_once ( ABSPATH . 'wp-admin/includes/file.php' );
		htccss_get_htaccess();
		$in_htccss = preg_split( "/[\t\n\r\s\,]+/", $htccss_options['deny'], -1, PREG_SPLIT_NO_EMPTY );
		if ( ! empty( $in_htccss ) ) {
			$ip_list = htccss_prepare_data( (array)$ip );
			$new_ip  = array_diff( $in_htccss, $ip_list );
			$htccss_options['deny'] = implode( "\n", $new_ip );
			$htccss_options['deny'] = preg_replace( "/\n{2,}/", "\n", $htccss_options['deny'] );
			if ( preg_match( "/^\n*$/", $htccss_options['deny'] ) )
				$htccss_options['deny'] = "";
			if ( empty( $htccss_options['deny'] ) && empty( $htccss_options['allow'] ) )
				$htccss_options['order'] = 'Order Deny,Allow';
			if ( is_multisite() )
				update_site_option( 'htccss_options', $htccss_options );
			else
				update_option( 'htccss_options', $htccss_options );
			htccss_generate_htaccess();
		}
	}
}

if ( ! function_exists( 'htccss_lmtttmpts_delete_from_whitelist' ) ) {
	function htccss_lmtttmpts_delete_from_whitelist( $ip ) {
		global $htccss_options;
		if ( empty( $htccss_options ) )
			$htccss_options = ( is_multisite() ) ? get_site_option( 'htccss_options' ) : get_option( 'htccss_options' );
		require_once ( ABSPATH . 'wp-admin/includes/file.php' );
		htccss_get_htaccess();
		$in_htccss = preg_split( "/[\t\n\r\s\,]+/", $htccss_options['allow'], -1, PREG_SPLIT_NO_EMPTY );
		if ( ! empty( $in_htccss ) ) {
			$ip_list = htccss_prepare_data( (array)$ip );
			$new_ip  = array_diff( $in_htccss, $ip_list );
			$htccss_options['allow'] = implode( "\n", $new_ip );
			$htccss_options['allow'] = preg_replace( "/\n{2,}/", "\n", $htccss_options['allow'] );
			if ( preg_match( "/^\n*$/", $htccss_options['allow'] ) )
				$htccss_options['allow'] = "";
			if ( empty( $htccss_options['deny'] ) && empty( $htccss_options['allow'] ) )
				$htccss_options['order'] = 'Order Deny,Allow';
			if ( is_multisite() )
				update_site_option( 'htccss_options', $htccss_options );
			else
				update_option( 'htccss_options', $htccss_options );
			htccss_generate_htaccess();
		}
	}
}

if ( ! function_exists( 'htccss_lmtttmpts_add_to_whitelist' ) ) {
	function htccss_lmtttmpts_add_to_whitelist( $ip ) {
		global $htccss_options;
		if ( empty( $htccss_options ) )
			$htccss_options = ( is_multisite() ) ? get_site_option( 'htccss_options' ) : get_option( 'htccss_options' );
		require_once ( ABSPATH . 'wp-admin/includes/file.php' );
		htccss_get_htaccess();
		$ip_list   = htccss_prepare_data( (array)$ip );
		$deny      = preg_split( "/[\t\n\r\s\,]+/", $htccss_options[ 'deny' ], -1, PREG_SPLIT_NO_EMPTY );
		$allow     = preg_split( "/[\t\n\r\s\,]+/", $htccss_options['allow'], -1, PREG_SPLIT_NO_EMPTY );
		$in_htccss = array_merge( $deny, $allow );
		if ( empty( $in_htccss ) ) {
			$htccss_options['allow'] = implode( "\n", $ip_list );
		} else {
			$new_ip = array_diff( $ip_list, $in_htccss );
			$htccss_options['allow'] .= "\n" . implode( "\n", $new_ip );
		}
		$htccss_options['allow'] = preg_replace( "/\n{2,}/", "\n", $htccss_options['allow'] );
		if ( preg_match( "/^\n*$/", $htccss_options['allow'] ) )
			$htccss_options['allow'] = "";
		if ( empty( $htccss_options['deny'] ) && ! empty( $htccss_options['allow'] ) )
			$htccss_options['order'] = 'Order Deny,Allow';
		if ( is_multisite() ) {
			update_site_option( 'htccss_options', $htccss_options );
		} else {
			update_option( 'htccss_options', $htccss_options );
		}
		htccss_generate_htaccess();
	}
}

/**
 * Prepare list of IPs before .htaccess editing
 * @param     array     $ip_list    List with IPs
 * @return    array     $args       List with IPs
 */
if ( ! function_exists( 'htccss_prepare_data' ) ) {
	function htccss_prepare_data( $ip_list ) {
		if ( empty( $ip_list ) )
			return false;

		$args = array();

		foreach( $ip_list as $ip ) {

			if (
				/* single IP */
				filter_var( $ip, FILTER_VALIDATE_IP ) ||
				/* CIDR */
				preg_match( '/^(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])(\.(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])){3}\/(3[0-2]|[1-2][0-9]|[0-9])$/', $ip )
			) {
				if ( ! in_array( $ip, $args ) )
					$args[] = $ip;
			} elseif (
				/* short mask like 10., 192.168. or 128.45.25. */
				preg_match( '/^(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])(\.(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])){0,2}\.$/', $ip )
			) {
				$dot_entry = substr_count( $ip, '.' );
				switch ( $dot_entry ) {
					case 3: /* in case if mask like xxx.xxx.xxx. */
						$cidr = $ip . '0/24';
						break;
					case 2: /* in case if mask like xxx.xxx. */
						$cidr = $ip . '0.0/16';
						break;
					case 1: /* in case if mask like xxx. */
						$cidr = $ip . '0.0.0/8';
						break;
					default:
						$cidr = '';
						break;
				}
				if ( ! empty( $cidr ) && ! in_array( $cidr, $args ) )
					$args[] = $cidr;
			} elseif (
				/* range like 128.45.25.0-188.5.5.5 */
				preg_match( '/^(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])(\.(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])){3}\-(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])(\.(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])){3}$/', $ip )
			) {
				$ips         = explode( '-', $ip );
				$ip_from_int = sprintf( '%u', ip2long( $ips[0] ) );
				$ip_to_int  = sprintf( '%u', ip2long( $ips[1] ) );
				if ( $ip_from_int <= $ip_to_int ) {
					$num       = $ip_to_int - $ip_from_int + 1;
					$bin       = decbin( $num );
					$chunk     = str_split( $bin );
					$chunk     = array_reverse( $chunk );
					$mask      = 32 - count( $chunk ) + 1;
					while ( $mask <= 32 ) {
						if ( $chunk[ 32 - $mask ] != 0 ) {
							$start_ip = isset( $end_ip ) ? long2ip( $end_ip ) : long2ip( $ip_from_int );
							$end_ip   = ip2long( $start_ip ) + pow( 2, 32 - $mask );
							$cidr     = $start_ip . '/' . $mask;
							if ( ! in_array( $cidr, $args ) )
								$args[] = $cidr;
						}
						$mask ++;
					}
				}
			}
		}
		return $args;
	}
}

/* Function for delete delete options */
if ( ! function_exists ( 'htccss_delete_options' ) ) {
	function htccss_delete_options() {
		global $wpdb;
		if ( is_multisite() ) {
			$old_blog = $wpdb->blogid;
			/* Get all blog ids */
			$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
			foreach ( $blogids as $blog_id ) {
				switch_to_blog( $blog_id );
				delete_option( 'htccss_options' );
			}
			switch_to_blog( $old_blog );
			delete_site_option( 'htccss_options' );
		} else {
			delete_option( 'htccss_options' );
		}
	}
}

register_activation_hook( __FILE__, 'register_htccss_settings' );

if ( function_exists( 'is_multisite' ) ) {
	if ( is_multisite() ) {
		add_action( 'network_admin_menu', 'add_htccss_admin_menu' );
	} else {
		add_action( 'admin_menu', 'add_htccss_admin_menu' );
	}
}

add_action( 'init', 'htccss_plugin_init' );
add_action( 'admin_init', 'htccss_plugin_admin_init' );
add_action( 'plugins_loaded', 'htccss_plugins_loaded' );

add_action( 'admin_enqueue_scripts', 'htccss_admin_head' );
add_action( 'admin_notices', 'htccss_plugin_banner' );
add_action( 'network_admin_notices', 'htccss_plugin_banner');
/* Adds "Settings" link to the plugin action page */
add_filter( 'plugin_action_links', 'htccss_plugin_action_links', 10, 2 );
/* Additional links on the plugin page */
add_filter( 'plugin_row_meta', 'htccss_register_plugin_links', 10, 2 );
add_filter( 'mod_rewrite_rules', 'htccss_mod_rewrite_rules' );
/* Adding hooks for interaction with Limit Attempts plugin */
add_action( 'lmtttmpts_htaccess_hook_for_copy_all', 'htccss_lmtttmpts_copy_all' );
add_action( 'lmtttmpts_htaccess_hook_for_delete_all', 'htccss_lmtttmpts_delete_all' );
add_action( 'lmtttmpts_htaccess_hook_for_block', 'htccss_lmtttmpts_block' );
add_action( 'lmtttmpts_htaccess_hook_for_reset_block', 'htccss_lmtttmpts_reset_block' );
add_action( 'lmtttmpts_htaccess_hook_for_delete_from_whitelist', 'htccss_lmtttmpts_delete_from_whitelist' );
add_action( 'lmtttmpts_htaccess_hook_for_add_to_whitelist', 'htccss_lmtttmpts_add_to_whitelist' );

register_uninstall_hook( __FILE__, 'htccss_delete_options' );