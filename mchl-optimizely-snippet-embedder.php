<?php
/*
	Plugin Name: Optimizely Snippet Embedder
	Plugin URI: http://michaelkjeldsen.com/optimizely-embedder/
	Description: Easily add the Optimizely script on your website.
	Author: Michael Kjeldsen
	Version: trunk
	Author URI: http://michaelkjeldsen.com/
	Text Domain: mchl-optimizely-snippet-embedder

	FEATURE WISH LIST
	---------------------------------------
	* API-connect to select Project
	* API-connect to get test results
	* API-to-Dashboard to showcase running test(s)
*/

	define( 'MCHL_OSE_VERSION', '2.0.0' );

	if ( !is_admin() )
		{
			if ( get_option('mchl_optimizely_data') )
				{
					add_action( 'wp_enqueue_scripts', 'mchl_optimizely_snippet_embedder_script' );
					function mchl_optimizely_snippet_embedder_script()
						{
							wp_enqueue_script( 'mchl_optimizely_snippet_embedder', '//cdn.optimizely.com/js/' . get_option('mchl_optimizely_data') . '.js', array(), MCHL_OSE_VERSION, false );
						}
				}
		}

	/*
		<script>
			(function() { 
				var projectId = ' . get_option('mchl_optimizely_data') . ';
				var protocol = ('https:' == document.location.protocol ? 
				'https://' : 'http://');
				var scriptTag = document.createElement('script');
				scriptTag.type = 'text/javascript';
				scriptTag.async = true;
				scriptTag.src = protocol + 'cdn.optimizely.com/js/' + 
				projectId + '.js';
				var s = document.getElementsByTagName('script')[0];
				s.parentNode.insertBefore(scriptTag, s);
			})();
			function optimizelyTimeout() {
				window.optimizely = window.optimizely|| [];
				if (!window.optimizely.data) {
					window.optimizely.push("timeout");
				}
			}
			setTimeout(optimizelyTimeout, 1000);
		</script>
	*/

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

			// Add nagging error-message in admin, until the user add their Project Snipper ID in the plugin settings
			add_action( 'admin_notices', 'no_project_snippet_admin_notice' );
			function no_project_snippet_admin_notice()
				{
					if ( !get_option('mchl_optimizely_data') )
						{
							?>
							<div class="update-nag">
								<p><?php _e( 'Please provide your Optimizely Project Snippet ID in the <a href="tools.php?page=mchl-optimizely-snippet-embedder">plugin settings</a>.', 'mchl-optimizely-snippet-embedder' ); ?></p>
							</div>
							<?php
						}
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
					$donate_link = '<a href="http://michaelkjeldsen.com/donate-opt/">' . __( 'Donate', 'mchl-optimizely-snippet-embedder' ) . '</a>';
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
										<td><input name="mchl_optimizely_data" class="large-text" style="width:200px;" type="text" pattern="[0-9]*" id="mchl_optimizely_data" value="' . get_option('mchl_optimizely_data') . '" placeholder="XXXXXXXXXX" required autofocus></td>
									</tr>
									<tr>
										<th>' . __( "Don't have an account?", 'mchl-optimizely-snippet-embedder' ) . '</th>
										<td><em><a href="https://www.optimizely.com/?utm_source=optimizely+snippet+embedder+wp+plugin&amp;utm_medium=link&amp;&utm_campaign=create+free+account" target="_blank">' . __( "Create a free account here", 'mchl-optimizely-snippet-embedder' ) . ' &raquo;</a></em></td>
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
								' . __( 'Do you like this plugin, please', 'mchl-optimizely-snippet-embedder' ) .' <a href="http://michaelkjeldsen.com/donate-opt/" target="_blank">' . __( 'donate a beer to the developer', 'mchl-optimizely-snippet-embedder' ) . '</a>.
							</p>
						</div>';

					echo $output;
				}
		}

// Here be dragons...	