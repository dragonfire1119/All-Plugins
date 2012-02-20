<?php
/**

 * @package All Plugins

 * @author Christopher

 * @version 1.0.2

 */
/*

  Plugin Name: All Plugins

  Plugin URI: http://www.dragonfire1119.com/

  Description: One click installer for all "must have" plugins for wordpress

  Author: Christopher

  Version: 1.0.2

  Author URI: http://www.dragonfire1119.com/

 */





$plugindir = str_replace('\\', '/', dirname(__FILE__));





define('PLUGINDIR', $plugindir);

function ap_install() {

// Required for all WordPress database manipulations
    global $wpdb;

// Grabbing DB prefix and settings table names to variable
    $allplugins = $wpdb->prefix . "allplugins";
    $allplugins_settings = $wpdb->prefix . "allplugins_settings";

// Current DB Version
    $current_db_query = mysql_query("SELECT value FROM $allplugins_settings WHERE name='db_version'");
    $row = mysql_fetch_array($current_db_query);
    $current_db_ver = $row['value'];

// Settings current DB version for future upgrades
    $db_version_new = '0.1';

// Does the database already exist?
    if ($wpdb->get_var("show tables like '$allplugins'") != $allplugins) { // No, it doesn't
// Creating the testimonials table!
        $sql1 = "CREATE TABLE " . $allplugins . " (
		id INT(12) NOT NULL AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
		pname VARCHAR(255) NOT NULL,
                date DATE NULL,
		PRIMARY KEY (id)
		);";

// Creating the testimonials settings table!
        $sql2 = "CREATE TABLE " . $allplugins_settings . " (
			id INT(12) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			name VARCHAR(255) NOT NULL,
			value VARCHAR(100)
		);";

// Requiring WP upgrade and running SQL query
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql1);
        dbDelta($sql2);

// Populating the DB with test entry
        $name = "allplugins";
        $pname = "allplugins";
        $date = date("Y-m-d");

// Inserting testimonial into the DB
        $insert = "INSERT INTO " . $allplugins .
                " (id, name, pname, date) " .
                "VALUES ('','$name', '$pname','$date')";

// Running the query
        $results = $wpdb->query($insert);
    }

    add_option('ap_redirect', true);
}

function ap_redirect() {

    if (get_option('ap_redirect', false)) {

        delete_option('ap_redirect');

        wp_redirect(home_url('/wp-admin/admin.php?page=allplugins'));
    }
}

function ap_unzip($zipf, $dest) {

    $zip = zip_open($zipf);

    if ($zip) {

        while ($zip_entry = zip_read($zip)) {

            $fp = @fopen($dest . zip_entry_name($zip_entry), "w");

            if (!$fp)
                @mkdir($dest . zip_entry_name($zip_entry));

            if (zip_entry_open($zip, $zip_entry, "r")) {

                $buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

                fwrite($fp, "$buf");

                zip_entry_close($zip_entry);

                fclose($fp);
            }
        }

        zip_close($zip);
    }
}

/*
  function ap_suggestedplugins(){

  ?>

  <div class="wrap">

  <div class="icon32" id="icon-plugins"><br></div>

  <h2>Seggested Plugins For Your Wordpress Site</h2> <br>

  Coming with next update!

  </div>

  <?php

  }
 */

function ap_install_plugin() {

    if (file_exists(ABSPATH . "wp-content/plugins/{$_REQUEST[plugin]}/")) {

        @unlink(dirname(__FILE__) . "/{$_REQUEST[plugin]}.zip");

        die('Already Exist');
    }

    $handle = fopen("http://downloads.wordpress.org/plugin/{$_REQUEST[plugin]}.zip", "rb");

    $data = '';

    while (!feof($handle)) {

        $data .= fread($handle, 8192);
    }

    fclose($handle);

    $zipf = dirname(__FILE__) . "/{$_REQUEST[plugin]}.zip";

    file_put_contents($zipf, $data);

    mkdir(ABSPATH . "wp-content/plugins/{$_REQUEST[plugin]}/");

    ap_unzip($zipf, ABSPATH . "wp-content/plugins/");

    @unlink($zipf);

    die('done');
}

function ap_admin_options() {
    ?>

    <?php
    if (isset($_POST['Delete'])) {

// Required for all WordPress database manipulations
        global $wpdb;

// Grabbing DB prefix and settings table names to variable
        $allplugins = $wpdb->prefix . "allplugins";
        $allplugins_settings = $wpdb->prefix . "allplugins_settings";

// Deleting The Databases
        mysql_query("DROP table $allplugins");
        mysql_query("DROP table $allplugins_settings");

        echo '<div class="updated fade" id="message" style="background-color: rgb(255, 251, 204);"><p> ';
        echo __('Database Tables Deleted!', 'allplugins');
        echo '</p></div>';
    }

    /* if (isset($_POST['add'])) {

      // Required for all WordPress database manipulations
      global $wpdb;

      // Grabbing DB prefix and settings table names to variable
      $allplugins = $wpdb->prefix . "allplugins";
      $allplugins_settings = $wpdb->prefix . "allplugins_settings";

      // Deleting The Databases
      $insert = "INSERT INTO " . $allplugins .
      " (pname, date) " .
      "VALUES ('{$_REQUEST[plugin]}', '" . $currdate . "')";

      $results = $wpdb->query($insert);

      echo '<div class="updated fade" id="message" style="background-color: rgb(255, 251, 204);"><p> ';
      echo __('Add to your list!', 'allplugins');
      echo '</p></div>';
      } */
    ?>

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js" type="text/javascript"
    charset="utf-8"></script>

    <style type="text/css">

        .inm{

            padding-left: 10px;

            color: #008000;

            font-weight: bold;

        }

    </style>

    <div class="wrap">

        <div class="icon32" id="icon-plugins"><br></div>

        <?php
        if (isset($_POST['add'])) {
// Required for all WordPress database manipulations
            global $wpdb;

// Grabbing DB prefix and settings table names to variable
            $allplugins = $wpdb->prefix . "allplugins";

            $currdate = date("Y-m-d");
            $add = $_POST['install'];

            $seoname = preg_replace('/\%/', ' percentage', $add);
            $seoname = preg_replace('/\@/', ' at ', $seoname);
            $seoname = preg_replace('/\&/', ' and ', $seoname);
            $seoname = preg_replace('/\s[\s]+/', '-', $seoname);    // Strip off multiple spaces
            $seoname = preg_replace('/[\s\W]+/', '-', $seoname);    // Strip off spaces and non-alpha-numeric
            $seoname = preg_replace('/^[\-]+/', '', $seoname); // Strip off the starting hyphens
            $seoname = preg_replace('/[\-]+$/', '', $seoname); // // Strip off the ending hyphens
            $seoname = strtolower($seoname);

// Deleting The Databases
            $insert = "INSERT INTO " . $allplugins .
                    " (id, name, pname, date) " .
                    "VALUES ('', '" . $wpdb->escape($seoname) . "', '" . $wpdb->escape($add) . "', '" . $wpdb->escape($currdate) . "')";
//}
// Running the query
            $results = $wpdb->query($insert);
        }
        ?>

        <h3 class="title">All Plugins that are need for your Sites</h3> 

        <br>

        <form method="post" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>">

            Type the plugin name that you want to install: <input checked="checked" type="text" id="text" class="install" name="install" ><input type="button" id="installtype" class="button-primary" value="Install typed in Plugin"><input type="submit" id="addtolist" name="add" class="button-primary" value="Add this plugin to your list?"> <span class="inm" id="add"></span> <span class="inm" id="install"></span><br />
            <br /><br />

            <input type="button" id="btn" class="button-primary" value="Install Selected Plugins">

            <table cellspacing="0" class="widefat fixed">
                <thead>
                    <tr class="thead">
                        
                        <?php if ($_GET['dir'] == "desc" || !isset($_GET['dir']) || empty($_GET['dir'])) { ?>
                            <th class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"/></th>
                            <th class="column_id" id="id" scope="col" style="width:35px;"><a href="admin.php?page=allplugins&sort=id&dir=asc">ID</a></th>
                            <th class="column-name" id="name" scope="col" style="width:130px;"><a href="admin.php?page=allplugins&sort=name&dir=asc">Plugin</a></th>
                        <?php } else { ?>
                            <th class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"/></th>
                            <th class="column_id" id="id" scope="col" style="width:35px;"><a href="admin.php?page=allplugins&sort=id&dir=desc">ID</a></th>
                            <th class="column-name" id="name" scope="col" style="width:130px;"><a href="admin.php?page=allplugins&sort=name&dir=desc">Plugin</a></th>		
                        <?php } ?>
                            
                    </tr>
                </thead>
                <tbody>
                    <?php
                    global $wpdb;
                    $allplugins = $wpdb->prefix . "allplugins";

                    $sort = "";
                    if (isset($_GET['sort']) && !empty($_GET['sort'])) {
                        $sort = $_GET['sort'];
                    } else {
                        $sort = "id";
                    }

                    $dir = "";
                    if (isset($_GET['dir']) && !empty($_GET['dir'])) {
                        $dir = $_GET['dir'];
                    } else {
                        $dir = "asc";
                    }

                    $query = "SELECT * FROM $allplugins ORDER BY $sort $dir";
                    $results = mysql_query($query);
                    $count = mysql_num_rows($results);
                    while ($data = mysql_fetch_array($results)) {
                        $id = $data['id'];
                        $name = stripslashes($data['name']);
                        $pname = stripslashes($data['pname']);
                        $date = $data['date'];
                        ?>
                        <tr style="background-color:<?php
                if ($status == 0) {
                    echo "#8CFF8C";
                } else if ($status == 1) {
                    echo "#FF7171";
                } else if ($status == 2) {
                    echo "#FFFF80";
                }
                        ?>">
                            <th class="check-column" scope="row">
                                <input type="checkbox" class="administrator ins" id="<?php echo $pname; ?>" name="ins[]" value="<?php echo $pname; ?>"/>
                            </th>
                            <td class="id column-id">
                                <?php echo $id; ?>
                            </td>
                            <td class="name column-name" style="width: 105px;">
                                <span class="inm" id="all-in-one-seo-pack"></span><?php echo $name; ?>
                                <div class="row-actions"><span class='delete'><a class='submitdelete' title='Delete this testimonial' href='?page=allplugins&amp;delete&amp;id=<?php echo $id; ?>' onclick="if ( confirm('You are about to delete a testimonial by \'<?php echo $name; ?>\'\n \'Cancel\' to stop, \'OK\' to delete.') ) { return true;}return false;">Delete</a></span></div>
                            </td>
                        </tr>

                        <?php
                    }
                    mysql_free_result($results);
                    if ($count < 1) {
                        ?>
                        <tr>
                            <th class="check-column" scope="row"></th>
                            <td class="name column-name" colspan="6">
                                <p>There aren't any Plugins in your list yet!</p>
                            </td>
                        </tr>
                    <?php } ?>

                </tbody>
                <tfoot>
                    <tr class="thead">
                        <th class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"/></th>
                        <th class="column_id" id="id" scope="col">ID</th>
                        <th class="column-plugin" id="plugin" scope="col">Plugin</th>
                    </tr>
                </tfoot>
            </table>

            <br>

            <input type="button" id="btn2" class="button-primary" value="Install Selected Plugins"> | <input type="submit" name="Delete" class="button-secondary" value="Uninstall Plugin" onclick="if ( confirm('You are about to UNINSTALL this plugin! This cannot be undone! \n \'Cancel\' to stop, \'OK\' to uninstall.') ) { return true;}return false;" />

        </form>

    </div>

    <script language="JavaScript">

        <!--
        jQuery(function(){


            jQuery('#installtype').click(function(){

                jQuery('.install').each(function(){

                    if(this.checked){

                        var pid = '#install';

                        var plugin = this.value;

                        jQuery(pid).html('Installing...<img src="images/loading.gif"/>');

                        jQuery.post(ajaxurl, {action:'ap_install_plugin', plugin:plugin}, function(res){

                            jQuery(pid).html('Installed');

                        });

                    }

                });



            });

            jQuery('#addtolist').click(function(){

                jQuery('.install').each(function(){

                    if(this.checked){

                        var pid = '#install';

                        var plugin = this.value;

                        jQuery(pid).html('Adding...<img src="images/loading.gif"/>');

                        jQuery.post(ajaxurl, {action:'ap_addtolist', plugin:plugin}, function(res){

                            jQuery(pid).html('Added to your list');

                        });

                    }

                });



            });


            jQuery('#btn').click(function(){

                jQuery('.ins').each(function(){

                    if(this.checked){

                        var pid = '#'+this.value;

                        var plugin = this.value;

                        jQuery(pid).html('Installing...<img src="images/loading.gif"/>');

                        jQuery.post(ajaxurl, {action:'ap_install_plugin', plugin:plugin}, function(res){

                            jQuery(pid).html('Installed');

                        });

                    }

                });



            });

            jQuery('#btn2').click(function(){

                jQuery('.ins').each(function(){

                    if(this.checked){

                        var pid = '#'+this.value;

                        var plugin = this.value;

                        jQuery(pid).html('Installing...<img src="images/loading.gif"/>');

                        jQuery.post(ajaxurl, {action:'ap_install_plugin', plugin:plugin}, function(res){

                            jQuery(pid).html('Installed');

                        });

                    }

                });



            });





        });

        //-->

    </script>

    <?php
}

function ap_menu() {

    add_menu_page("All Plugins", "All Plugins", 'administrator', 'allplugins', 'ap_admin_options');
    /*
      add_submenu_page( 'allplugins', 'Sggested Plugins', 'Sggested Plugins', 'administrator', 'allplugins/suggest', 'ap_suggestedplugins');
     */
}

if (is_admin()) {

    add_action("admin_menu", "ap_menu");

    wp_enqueue_script("jquery");

    wp_enqueue_script("jquery-form", plugins_url() . '/all-plugins/jquery.form.js');

    add_action('wp_ajax_ap_install_plugin', 'ap_install_plugin');
}





register_activation_hook(__FILE__, 'ap_install');

add_action('admin_init', 'ap_redirect');
?>