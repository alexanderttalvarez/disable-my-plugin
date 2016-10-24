<?php
/**
 * Plugin Name: Disable my Plugin
 * Description: It allows you to disable your plugins during certain hours.
 * Version: 0.0.1
 * Author: Alexander Torres
 * Author URI: http://alexandertt.com
 * License: GPL2
 */

class DisableMyPlugin {

 	private $disable_my_plugin_options;
  private $plugins_list;

  // Construct
 	public function __construct() {
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    $this->plugins_list = get_plugins();
    $this->disable_my_plugin_options = get_option( 'disable_my_plugin_option_name' );

    add_action( 'admin_enqueue_scripts', array($this,'disable_my_plugin_admin_scripts') );
 		add_action( 'admin_menu', array( $this, 'disable_my_plugin_add_plugin_page' ) );
    add_action( 'init', array( $this, 'disable_my_plugin_register_init' ) );

    //AJAX Handlers
		add_action( 'wp_ajax_disable_my_plugin_row_code_raw', array( $this, 'disable_my_plugin_row_code_raw' ));
		add_action( 'wp_ajax_nopriv_disable_my_plugin_row_code_raw', array( $this, 'disable_my_plugin_row_code_raw' ));

    if(!is_admin()) {
      add_filter( 'option_active_plugins', array( $this, 'disable_my_plugin_disable' ) );
    }

 	}

  // Registering settings
  public function disable_my_plugin_register_init() {

    register_setting(
      'disable_my_plugin_option_group', // option_group
      'disable_my_plugin_option_name' // option_name
    );

  }

  // Adding a page to the admin panel
 	public function disable_my_plugin_add_plugin_page() {
 		add_management_page(
 			'Disable my Plugin', // page_title
 			'Disable my Plugin', // menu_title
 			'manage_options', // capability
 			'disable-my-plugin', // menu_slug
 			array( $this, 'disable_my_plugin_create_admin_page' ) // function
 		);
 	}

  // It returns the name of the plugin if you provide the path
  public function disable_my_plugin_path_to_name($path) {
    $name='';

    if(!empty($this->plugins_list[$path]['Name'])) {
        $name = $this->plugins_list[$path]['Name'];
    }

    return($name);
  }

  // Filter for disabling the Plugins
  public function disable_my_plugin_disable($plugins) {


    foreach ( $this->disable_my_plugin_options as $plugin ) {

      if($plugin['hour_location'] === "server") { // Hour from server

        $date = new DateTime();
        $hour=$date->format('H:i');

      } else { // Hour from a particular location

        $date = new DateTime(null, new DateTimeZone($plugin['time_zone']));
        $hour=$date->format('H:i');

      }

      // Comparing the hours. We use strtotime to transform it into a date type variable
      if( (strtotime($hour) >= strtotime($plugin['start_hour'])) && (strtotime($hour) < strtotime($plugin['end_hour'])) ) {

        // Deleting the plugin from the array of active plugins
        $key = array_search( $plugin['plugin_select'], $plugins );
        if ( false !== $key ) {
          unset( $plugins[ $key ] );
        }

      }

    }

    return $plugins;
  }

  // Admin Page structure
 	public function disable_my_plugin_create_admin_page() {
 	?>
 		<div class="wrap">
 			<h2>Disable my Plugin</h2>
      <div class="dmp-current-folder" style="display:none"><?php echo WPMU_PLUGIN_URL."/disable-my-plugin/"; ?></div>
 			<p><?php _e('Choose the plugins you want to disable during certain hours.'); ?></p>
 			<?php settings_errors(); ?>
 			<form method="post" action="options.php" id="dmp-form">
 				<?php
 					settings_fields( 'disable_my_plugin_option_group' );
        ?>
        <div class='disable-my-plugin-group'>
        <?php
        $this->disable_my_plugin_page_init();
        ?>
        </div>
        <?php
          echo "<div class='dmp-errors'><strong>".__('Error!','dmp')."</strong> <span class='dmp-errors-text'></span></div>";
          echo "<a class='dmp-add-plugin'>".__("Add another plugin")."</a>";
 					submit_button();
 				?>
 			</form>
 		</div>
 	  <?php
  }

  // It loads the scripts for the admin panel
  public function disable_my_plugin_admin_scripts() {

     // SCRIPTS
     wp_enqueue_script('disable_my_plugin_admin_scripts_js', WPMU_PLUGIN_URL.'/disable-my-plugin/admin-scripts.js', array('jquery'),'', true);
     wp_localize_script( 'disable_my_plugin_admin_scripts_js', 'DMP', array(

  			// URL to wp-admin/admin-ajax.php
  			'ajaxurl' => admin_url( 'admin-ajax.php' ),

  			// Generating a nonce for security reasons
  			'security' => wp_create_nonce( 'dmp-admin' ),
 		 ));

     // STYLES
     wp_register_style( 'disable_my_plugin_admin_style_css', WPMU_PLUGIN_URL.'/disable-my-plugin/admin-style.css' );
	   wp_enqueue_style( 'disable_my_plugin_admin_style_css' );
  }

  // Table with the information of the plugins and the time
 	public function disable_my_plugin_page_init() {
  ?>
    <table class="dmp-table">

      <thead>
        <tr>
          <th>Plugin</th>
          <th>Type of hour</th>
          <th>Starting hour</th>
          <th>Ending hour</th>
          <th>Time Zone</th>
        </tr>
      </thead>

      <tbody>
        <?php
        // Checking there's some data
        if(!empty($this->disable_my_plugin_options)) {

          foreach( $this->disable_my_plugin_options as $key => $option ) {
            echo "<tr>";
            $this->disable_my_plugin_row_code($key);
            echo "</tr>";
          }

        } else {
          echo "<tr><td colspan='5'>There are no plugins in the list at this moment.</td></tr>";
        }
        ?>
      </tbody>

    </table>
  <?php
 	}

  // It loads a row of the table
  public function disable_my_plugin_row_code($i) {
  ?>
    <!-------- PLUGIN SELECTION --------->
    <td>
      <select name="disable_my_plugin_option_name[<?php echo $i; ?>][plugin_select]" class="plugin_select">
        <?php
        foreach($this->plugins_list as $plugin_path => $a_plugin) {
          $selected = (isset( $this->disable_my_plugin_options[$i]['plugin_select'] ) && $this->disable_my_plugin_options[$i]['plugin_select'] === $plugin_path) ? 'selected' : '' ;
          $name=$this->disable_my_plugin_path_to_name($plugin_path);
          echo '<option value="'.$plugin_path.'" '.$selected.'>'.$name.'</li>';
        }
        ?>
      </select>
    </td>

    <!-------- SERVER OR TIME ZONE --------->
    <td>
        <?php $checked = ( isset( $this->disable_my_plugin_options[$i]['hour_location'] ) && $this->disable_my_plugin_options[$i]['hour_location'] === 'server' ) ? 'checked' : '' ; ?>
        <label for="hour_location-0"><input type="radio" name="disable_my_plugin_option_name[<?php echo $i; ?>][hour_location]" class="hour_location-0 radio-hour" value="server" <?php echo $checked; ?>> Server</label><br>
        <?php $checked = ( isset( $this->disable_my_plugin_options[$i]['hour_location'] ) && $this->disable_my_plugin_options[$i]['hour_location'] === 'timezone' ) ? 'checked' : '' ; ?>
        <label for="hour_location-1"><input type="radio" name="disable_my_plugin_option_name[<?php echo $i; ?>][hour_location]" class="hour_location-1 radio-hour" value="timezone" <?php echo $checked; ?>> Time Zone</label>
    </td>

    <!-------- STARTING HOUR --------->
    <td>
      <?php $valueS = ( isset( $this->disable_my_plugin_options[$i]['start_hour'] ) ) ? esc_attr( $this->disable_my_plugin_options[$i]['start_hour']) : ''; ?>
      <input type="time" name="disable_my_plugin_option_name[<?php echo $i; ?>][start_hour]" class="start_hour" value="<?php echo $valueS; ?>">
    </td>

    <!-------- ENDING HOUR --------->
    <td>
      <?php $valueS = ( isset( $this->disable_my_plugin_options[$i]['end_hour'] ) ) ? esc_attr( $this->disable_my_plugin_options[$i]['end_hour']) : ''; ?>
      <input type="time" name="disable_my_plugin_option_name[<?php echo $i; ?>][end_hour]" class="end_hour" value="<?php echo $valueS; ?>">
    </td>

    <!-- TIME ZONE -->
    <td>
      <?php $this->disable_my_plugin_options[$i]['time_zone']; ?>
      <select name="disable_my_plugin_option_name[<?php echo $i; ?>][time_zone]" class="time_zone" <?php if($this->disable_my_plugin_options[$i]['hour_location'] == 'timezone' ) { echo 'style="visibility: visible;"'; } ?>>
        <?php
        foreach($this->tz_list() as $tzelement) {
        ?>
    			<?php $selected = (isset( $this->disable_my_plugin_options[$i]['time_zone'] ) && $this->disable_my_plugin_options[$i]['time_zone'] === $tzelement['zone']) ? 'selected' : '' ; ?>
    			<option value="<?php echo $tzelement['zone']; ?>" <?php echo $selected; ?>><?php echo $tzelement['diff_from_GMT'] . ' - ' . $tzelement['zone']; ?></option>
        <?php } ?>
  		</select>
    </td>

    <td>
      <a class="delete-button">-</a>
      <div class="current-pos"><?php echo $i; ?></div>
    </td>

    <?php
  }

  // It loads an empty row of the table - Prepared for JavaScript
  public function disable_my_plugin_row_code_raw() {

    check_ajax_referer( 'dmp-admin', 'security' );

    $i = $_POST['pos'];
    ?>
    <tr>
      <!-------- PLUGIN SELECTION --------->
      <td>
        <select name="disable_my_plugin_option_name[<?php echo $i; ?>][plugin_select]" class="plugin_select">
          <?php
          foreach($this->plugins_list as $plugin_path => $a_plugin) {
            $name=$this->disable_my_plugin_path_to_name($plugin_path);
            echo '<option value="'.$plugin_path.'">'.$name.'</li>';
          }
          ?>
        </select>
      </td>

      <!-------- SERVER OR TIME ZONE --------->
      <td>
          <label for="hour_location-0"><input type="radio" name="disable_my_plugin_option_name[<?php echo $i; ?>][hour_location]" class="hour_location-0 radio-hour" value="server" checked> Server</label><br>
          <label for="hour_location-1"><input type="radio" name="disable_my_plugin_option_name[<?php echo $i; ?>][hour_location]" class="hour_location-1 radio-hour" value="timezone"> Time Zone</label>
      </td>

      <!-------- STARTING HOUR --------->
      <td>
        <input type="time" name="disable_my_plugin_option_name[<?php echo $i; ?>][start_hour]" class="start_hour">
      </td>

      <!-------- ENDING HOUR --------->
      <td>
        <input type="time" name="disable_my_plugin_option_name[<?php echo $i; ?>][end_hour]" class="end_hour">
      </td>

      <!-- TIME ZONE -->
      <td>
        <select name="disable_my_plugin_option_name[<?php echo $i; ?>][time_zone]" class="time_zone">
          <?php
          foreach($this->tz_list() as $tzelement) {
          ?>
      			<option value="<?php echo $tzelement['zone']; ?>"><?php echo $tzelement['diff_from_GMT'] . ' - ' . $tzelement['zone']; ?></option>
          <?php } ?>
    		</select>
      </td>

      <td>
        <a class="delete-button">-</a>
        <div class="current-pos"><?php echo $i; ?></div>
      </td>
    </tr>

    <?php
    die();
  }

  // Creating a list of Time Zones
  // -----------------------------
  // Code by Christos Pontikis found in http://www.pontikis.net/tip/?id=24
  function tz_list() {
    $zones_array = array();
    $timestamp = time();
    foreach(timezone_identifiers_list() as $key => $zone) {
      date_default_timezone_set($zone);
      $zones_array[$key]['zone'] = $zone;
      $zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
    }
    return $zones_array;
  }

}

$disable_my_plugin = new DisableMyPlugin();
