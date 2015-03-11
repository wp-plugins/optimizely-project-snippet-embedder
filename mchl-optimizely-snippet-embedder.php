<?php
/*
	Plugin Name: Optimizely Snippet Embedder
	Plugin URI: http://michaelkjeldsen.com/optimizely-embedder/
	Description: Easily add the Optimizely script on your website.
	Author: Michael Kjeldsen
	Version: 1.1.2
	Author URI: http://michaelkjeldsen.com/
	Text Domain: mchl-optimizely-snippet-embedder

	RELEASE NOTES
	---------------------------------------
	1.1.2 - Refactored code
	1.1.1 - Added "Create account" link
	1.1.0 - I18n Ready
	1.0.1 - Now with 100 % more input fields (for, like, you know,
			this plugin to actually make sense)
	1.0.0 - A Working Release

	FEATURE WISH LIST
	---------------------------------------
	* API-connect to select Project
	* API-connect to get test results
	* API-to-Dashboard to showcase running test(s)
*/

	define( 'MCHL_OSE_VERSION', '1.1.2' );

	if ( !is_admin() )
		{
			wp_enqueue_script( 'mchl_optimizely_snippet_embedder', '//cdn.optimizely.com/js/' . get_option('mchl_optimizely_data') . '.js', '', MCHL_OSE_VERSION, false );
			add_action( 'wp_enqueue_scripts', 'mchl_optimizely_snippet_embedder' );

			// Get plugin version
			function mchl_optimizely_plugin_get_version()
				{
					if ( ! function_exists( 'get_plugins' ) )
						{
							require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
						}

					$plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
					$plugin_file = basename( ( __FILE__ ) );
					echo $plugin_folder[$plugin_file]['Version'];
				}
		}

	if ( is_admin() )
		{
			// Runs when plugin is activated
			register_activation_hook(__FILE__,'mchl_optimizely_install');
			function mchl_optimizely_install()
				{
					add_option("mchl_optimizely_data", '', '', 'yes');
				} 

			// Runs on plugin deactivation
			register_deactivation_hook( __FILE__, 'mchl_optimizely_remove' );
			function mchl_optimizely_remove()
				{
					delete_option('mchl_optimizely_data');
				}

			// Add Admin submenu page
			add_action('admin_menu', 'mchl_optimizely_project_id_options_panel');
			function mchl_optimizely_project_id_options_panel()
				{
					add_submenu_page( 'tools.php', 'Optimizely Snippet Embedder', 'Optimizely Snippet Embedder', 'manage_options', 'mchl-optimizely-snippet-embedder', 'mchl_optimizely_snippet_embedder_html_page', plugins_url('lh-admin/lederne-cirkler.png') );
				}

			// Optimizely Project ID
			// @todo: Refactor
			add_action('init','mchl_optimizely_project_id');
			function mchl_optimizely_project_id()
				{
					$mchl_optimizely_project_id = get_option('mchl_optimizely_project_id');
					return '<span class="mchl_optimizely_project_id">' . $mchl_optimizely_project_id . '</span>';
				}

			// SaveAdmin
			// @todo: Refactor
			function mchl_optimizely_saveadmin()
				{
					$mchl_optimizely_project_id = get_option('mchl_optimizely_saveadmin_data');
					return '<span class="mchl_optimizely_project_id">' . $mchl_optimizely_project_id . '</span>';
				}

			// Add settings link on plugin page
			$plugin = plugin_basename(__FILE__);
			add_filter("plugin_action_links_$plugin", 'optimizely_snippet_embedder_donate_link' );
			function optimizely_snippet_embedder_donate_link($links)
				{
					$donate_link = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=69NSYMHNFPEFJ&lc=DK&item_name=Optimizely%20Snippet%20Embedder%20donation&item_number=optsnipemb%2ddonation&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHostedGuest">' . __( 'Donate', 'mchl-optimizely-snippet-embedder' ) . '</a>';
					array_unshift($links, $donate_link);
					return $links;
				}

			// Add donate link on plugin page
			$plugin = plugin_basename(__FILE__); 
			add_filter("plugin_action_links_$plugin", 'optimizely_snippet_embedder_settings_link' );
			function optimizely_snippet_embedder_settings_link($links)
				{
					$settings_link = '<a href="tools.php?page=mchl-optimizely-snippet-embedder.php">' . __( 'Settings', 'mchl-optimizely-snippet-embedder' ) . '</a>';
					array_unshift($links, $settings_link);
					return $links;
				}

			// Markup for the settings page
			function mchl_optimizely_snippet_embedder_html_page ()
				{
					$output = '
						<div class="wrap">
							<div id="icon-themes" class="icon32"><br></div>
							<h2>Optimizely Snippet Embedder&trade; <small>' . __( 'by', 'mchl-optimizely-snippet-embedder' ) . ' Michael Kjeldsen</small></h2>';

							if ( isset($_GET['settings-updated']) )
								{
									$output .= '<div id="message" class="updated">
										<p><strong>' . __( 'Settings updated.', 'mchl-optimizely-snippet-embedder' ) . '</strong></p>
									</div>';
								}

							$output .= '<form method="post" action="options.php">
							' . wp_nonce_field('update-options') . '
							
								<input type="hidden" name="action" value="update" />
								<input type="hidden" name="page_options" value="mchl_optimizely_data" />
								
								<table class="form-table">
								<tbody>
									<tr valign="top">
										<th scope="row"><label for="mchl_optimizely_data">Project Snippet ID:</label></th>
										<td><input name="mchl_optimizely_data" class="small-text" style="width:200px;" type="text" id="mchl_optimizely_data" value="' . get_option('mchl_optimizely_data') . '" placeholder="XXXXXXXXXX" required autofocus></td>
									</tr>
									<tr>
										<th>' . __( "Don't have an account?", 'mchl-optimizely-snippet-embedder' ) . '</th>
										<td><em><a href="https://www.optimizely.com/?utm_source=vwo+snippet-embedder+wp+plugin&amp;utm_medium=link&amp;&utm_campaign=create+free+account" target="_blank">' . __( "Create a free account here", 'mchl-optimizely-snippet-embedder' ) . ' &raquo;</a></em></td>
									</tr>
								</tbody>
								</table>
								
								<p>
								<input type="submit" name="submit" id="submit" class="button button-primary" value="' . __( 'Update settings', 'mchl-optimizely-snippet-embedder' ) . '" />
								</p>
							
							</form>
							<hr>
							<p>
								<em>Optimizely Snippet Embedder&trade;</em> ' . __( 'is a forever-free plugin by', 'mchl-optimizely-snippet-embedder' ) .' <a href="http://michaelkjeldsen.com">Michael Kjeldsen</a>.
								' . __( 'Feel free to share with everyone you know.', 'mchl-optimizely-snippet-embedder' ) .'
								' . __( 'Do you like this plugin, please', 'mchl-optimizely-snippet-embedder' ) .' <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=69NSYMHNFPEFJ&lc=DK&item_name=Optimizely%20Snippet%20Embedder%20donation&item_number=optsnipemb%2ddonation&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHostedGuest" target="_blank">' . __( 'donate a beer to the developer', 'mchl-optimizely-snippet-embedder' ) . '</a>.
							</p>
						</div>';

					echo $output;
				}
		}

// Here be dragons...	