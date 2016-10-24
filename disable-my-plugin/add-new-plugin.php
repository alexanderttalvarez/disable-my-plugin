<?php

if(isset($_POST['currentNumber'])) {
  add_new_plugin($_POST['currentNumber']);
}

function add_new_plugin($arg1) {
  ?>
  <select name="disable_my_plugin_option_name[<?php echo $arg1; ?>][plugin_select]" id="plugin_select">
    <?php
    foreach($this->plugins_list as $plugin_path => $a_plugin) {
      $selected = (isset( $this->disable_my_plugin_options[0]['plugin_select'] ) && $this->disable_my_plugin_options[0]['plugin_select'] === $plugin_path) ? 'selected' : '' ;
      $name=$this->disable_my_plugin_path_to_name($plugin_path);
      echo '<option value="'.$plugin_path.'" '.$selected.'>'.$name.'</li>';
    }
    ?>
  </select> <?php
}
?>
