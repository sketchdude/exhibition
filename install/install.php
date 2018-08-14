<?php

// eXhibition - A PHP/MySQL Art Publishing System
// copyright (c) 2009 sketchdude

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

// install/install.php
// based on install script written for phpbb (C) 2001 The phpBB Group

function page_header($text, $form_action = false) {
  global $php_ext;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>Welcome to Exhibition Installation</title>
<link rel="stylesheet" href="./subSilver.css" type="text/css">
<style type="text/css">
<!--
  th { background-image: url('./images/cellpic3.gif') }
  td.cat { background-image: url('./images/cellpic1.gif') }
  td.rowpic { background-image: url('./images/cellpic2.jpg'); background-repeat: repeat-y }
  td.catHead, td.catSides, td.catLeft, td.catRight, td.catBottom { background-image: url('./images/cellpic1.gif') }
  /* Import the fancy styles for IE only (NS4.x doesn't use the @import function) */
  @import url("./formIE.css");
//-->
</style>
</head>
<body bgcolor="#a4f88f" text="#000000" link="#006699" vlink="#5584AA">
<table border="0" width="100%" cellpadding="10" cellspacing="0" align="center">
    <tr>
        <td class="bodyline" width="100%">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td>
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td align="center">
                                    <img src="./images/exhibition.jpg" alt="Exhibition Home" title="Exhibition Home" width="250" height="45" vspace="1">
                                    <p class="gen">A PHP/MySQL Art Publishing System</p>
                                </td>
                                <td align="center" width="100%" valign="middle">
                                    <span class="maintitle">Welcome to Exhibition Installation</span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        <br><br>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table width="90%" border="0" align="center" cellspacing="0" cellpadding="0">
                            <tr>
                                <td>
                                    <span class="gen"><?php echo $text ?></span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        <br><br>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table width="100%" cellpadding="2" cellspacing="1" border="0" class="forumline">
                        <form action="<?php echo($form_action) ? $form_action : 'install.'.$php_ext; ?>" name="install" method="post">
<?php

}

function page_footer() {

?>
                        </form>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
<?php

}

function page_common_form($hidden, $submit) {

?>
                            <tr>
                                <td colspan="2" class="catBottom" align="center">
                                    <input class="mainoption" type="submit" value="<?php echo $submit; ?>">
                                    <?php echo $hidden ?>
                                </td>
                            </tr>
<?php

}

function page_error($error_title, $error) {

?>
                            <tr>
                                <th colspan="2">
                                    <?php echo $error_title ?>
                                </th>
                            </tr>
                            <tr>
                                <td colspan="2" class="row1" align="center">
                                    <span class="gen"><?php echo $error ?></span>
                                </td>
                            </tr>
<?php

}

// Begin

// Disable reporting of uninitialized variables
error_reporting  (E_ERROR | E_WARNING | E_PARSE);
// Disable magic_quotes_runtime
set_magic_quotes_runtime(0);

// Slash data if it isn't slashed
if (!get_magic_quotes_gpc()) {
  if (is_array($_GET)) {
    while (list($key, $value) = each($_GET)) {
      if (is_array($_GET[$key])) {
        while (list($key2, $value2) = each($_GET[$key])) {
          $_GET[$key][$key2] = addslashes($value2);
        }
        reset($_GET[$key]);
      }
      else {
        $_GET[$key] = addslashes($value);
      }
    }
    reset($_GET);
  }

  if (is_array($_POST)) {
    while (list($key, $value) = each($_POST)) {
      if (is_array($_POST[$key])) {
        while (list($key2, $value2) = each($_POST[$key])) {
          $_POST[$key][$key2] = addslashes($value2);
        }
        reset($_POST[$key]);
      }
      else {
        $_POST[$key] = addslashes($value);
      }
    }
    reset($_POST);
  }

  if (is_array($_COOKIE)) {
    while (list($key, $value) = each($_COOKIE)) {
      if (is_array($_COOKIE[$key])) {
        while (list($key2, $value2) = each($_COOKIE[$key])) {
          $_COOKIE[$key][$key2] = addslashes($value2);
        }
        reset($_COOKIE[$key]);
      }
      else {
        $_COOKIE[$key] = addslashes($value);
      }
    }
    reset($_COOKIE);
  }
}

// Begin main prog
define('IN_EXHIBITION', true);

if (!defined('IN_EXHIBITION')) {
  // Hacking attempt (?)
  die('Exhibition failed! Please try again later.');
}

$php_ext      = 'php';
$start_time   = 0;
$user_data    = array();
$error        = false;
$ex_root_path = './../';
$admin_dir    = 'admin';

// include required functions & sessions here
require_once($ex_root_path . 'includes/config_inc.' . $php_ext);
require_once($ex_root_path . 'includes/navigate_inc.' . $php_ext);

// Define schema info

$confirm = (isset($_POST['confirm'])) ? true : false;
$cancel  = (isset($_POST['cancel']))  ? true : false;

if (isset($_POST['install_step']) || isset($_GET['install_step'])) {
  $install_step = (isset($_POST['install_step'])) ? $_POST['install_step'] : $_GET['install_step'];
}
else {
  $install_step = '';
}

$admin_name  = (!empty($_POST['admin_name']))  ? $_POST['admin_name']  : '';
$admin_pass  = (!empty($_POST['admin_pass']))  ? $_POST['admin_pass']  : '';
$admin_email = (!empty($_POST['admin_email'])) ? $_POST['admin_email'] : '';

if (!empty($_POST['server_name'])) {
  $server_name = $_POST['server_name'];
}
else {
  // Get this info from php if needed
  if (!empty($_SERVER['SERVER_NAME']) || !empty($_ENV['SERVER_NAME'])) {
    $server_name = (!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : $_ENV['SERVER_NAME'];
  }
  elseif (!empty($_SERVER['HTTP_HOST']) || !empty($_ENV['HTTP_HOST'])) {
    $server_name = (!empty($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : $_ENV['HTTP_HOST'];
  }
  else {
    $server_name = '';
  }
}

if (!empty($_POST['server_port'])) {
  $server_port = $_POST['server_port'];
}
else {
  // Get this info from php if needed
  if (!empty($_SERVER['SERVER_PORT']) || !empty($_ENV['SERVER_PORT'])) {
    $server_port = (!empty($_SERVER['SERVER_PORT'])) ? $_SERVER['SERVER_PORT'] : $_ENV['SERVER_PORT'];
  }
  else {
    $server_port = '80';
  }
}

// Is exhibition already installed? Yes? Redirect to the index
if (defined("EXHIBITION_INSTALLED")) {
  header('location: ../index.php');
  exit();
}

if ((empty($install_step) || empty($admin_pass) || empty($dbhost))) {
  // begin the install process
  $instruction_text = '<p>Thank you for choosing eXhibition.</p><p>Before visiting this page, you should have already created a MySQL database in phpMyAdmin for this installation. You should also have created a MySQL database user and granted this user full permissions on this database. Finally, you should have edited the file config_inc.php and added this data to the MySQL Settings section.</p><p>On this page, you will create a new username and password for the administrator of the site. The Admin username and password is what you will use to log in to the administration area of your site.</p>';

  $s_hidden_fields = '<input type="hidden" name="install_step" value="1">' . "\n";

  page_header($instruction_text);
?>
                        <tr>
                            <th colspan="2">
                                Administration Configuration
                            </th>
                        </tr>
<?php

  if ($error) {

?>
                        <tr>
                            <td class="row1" colspan="2" align="center">
                                <span class="gen" style="color:red"><?php echo $error; ?></span>
                            </td>
                        </tr>
<?php

  }

?>
                        <tr>
                            <td class="row1" align="right">
                                <span class="gen">Admin Email Address:</span>
                            </td>
                            <td class="row2">
                                <input type="text" size="55" maxlength="55" name="admin_email" value="<?php echo ($admin_email != '') ? $admin_email : ''; ?>">
                            </td>
                        </tr>
                        <tr>
                            <td class="row1" align="right">
                                <span class="gen">Admin Username:</span>
                            </td>
                            <td class="row2">
                                <input type="text" name="admin_name" value="<?php echo ($admin_name != '') ? $admin_name : ''; ?>">
                            </td>
                        </tr>
                        <tr>
                            <td class="row1" align="right">
                                <span class="gen">Admin Password:</span>
                            </td>
                            <td class="row2">
                                <input type="password" name="admin_pass" value="<?php echo ($admin_pass != '') ? $admin_pass : ''; ?>"> <span class="gen">Six Characters!</span>
                            </td>
                        </tr>
                        <tr>
                            <th colspan="2">
                                Site Configuration
                            </th>
                        </tr>
                        <tr>
                            <td class="row1" align="right">
                                <span class="gen">Site Title:</span>
                            </td>
                            <td class="row2">
                                <input type="text" name="title" size="55" maxlength="55"> <span class="gen">55 characters maximum</span> 
                            </td>
                        </tr>
                        <tr>
                            <td class="row1" align="right">
                                <span class="gen">Site Description:</span>
                            </td>
                            <td class="row2">
                                <textarea rows="5" cols="55" name="description"></textarea> <span class="gen">255 characters maximum</span> 
                            </td>
                        </tr>
                        <tr>
                            <td class="row1" align="right">
                                <span class="gen">Site Keywords:</span>
                            </td>
                            <td class="row2">
                                <textarea rows="5" cols="55" name="keywords"></textarea> <span class="gen">255 characters maximum, separated with commas</span> 
                            </td>
                        </tr>
<?php

  page_common_form($s_hidden_fields, 'Start Install');
  page_footer();
  exit();
}
else {
  // Go ahead and populate the database
  if ($install_step == 1) {
    // sql to populate the database
    $queries = array("CREATE TABLE admin ( admin_id int(10) unsigned NOT NULL auto_increment, admin_name varchar(25) NOT NULL default '', admin_pass varchar(32) NOT NULL default '', admin_email varchar(55) NOT NULL default '', last_visited timestamp(14) NOT NULL, google_settings_id int(10) unsigned NOT NULL default '0', PRIMARY KEY (admin_id)) TYPE=MyISAM",
                     "CREATE TABLE artist ( artist_id int(10) unsigned NOT NULL auto_increment, name varchar(125) NOT NULL default '', birth_date date NOT NULL default '0000-00-00', death_date date NOT NULL default '0000-00-00', location varchar(75) NOT NULL default '', PRIMARY KEY (artist_id)) TYPE=MyISAM",
                     "CREATE TABLE artwork ( artwork_id int(10) unsigned NOT NULL auto_increment, thumbnail varchar(255), type enum('sale','exhibit') NOT NULL default 'sale', artist_id int(10) unsigned NOT NULL default '0', medium varchar(35) default NULL, size varchar(25) default NULL, style varchar(45) default NULL, subject varchar(45) default NULL, price double(16,2) NOT NULL default '0.00', shipping double(16,2) NOT NULL default '0.00', handling double(16,2) NOT NULL default '0.00', sale_amount double(16,2) NOT NULL default '0.00',sale_date datetime NOT NULL default '0000-00-00 00:00:00', date_completed date NOT NULL default '0000-00-00', status enum('available','pending','sold') NOT NULL default 'available', qty_instock int(10) unsigned NOT NULL default '1', comments enum('enable','disable') NOT NULL default 'disable', gallery_id int(10) unsigned NOT NULL default '0', meta_data_id int(10) unsigned NOT NULL default '0', PRIMARY KEY (artwork_id)) TYPE=MyISAM AUTO_INCREMENT=1",
                     "CREATE TABLE category ( category_id int(10) unsigned NOT NULL auto_increment, cat_name varchar(35) NOT NULL default '', cat_type enum('page', 'gallery', 'product', 'site') NOT NULL default 'site', sidebar enum('one','two') NOT NULL default 'one', rss_channel enum('yes','no') NOT NULL default 'no', meta_data_id int(10) unsigned NOT NULL default '0', PRIMARY KEY (category_id)) TYPE=MyISAM AUTO_INCREMENT=1",
                     "CREATE TABLE comment ( comment_id int(10) unsigned NOT NULL auto_increment, comment_parent tinyint(5) unsigned NOT NULL default '0', parent_type enum('art','page','comment') NOT NULL default 'art', author varchar(25) NOT NULL default '', salutation tinytext NOT NULL default '', message text NOT NULL, msg_type enum('public','private') NOT NULL default 'public', meta_data_id int(10) unsigned NOT NULL default '0', PRIMARY KEY (comment_id)) TYPE=MyISAM AUTO_INCREMENT=1",
                     "CREATE TABLE customer ( customer_id int(10) unsigned NOT NULL auto_increment, public_name varchar(75) NOT NULL default '', first_name varchar(35) NOT NULL default '', last_name varchar(35) NOT NULL default '', address1 varchar(65) NOT NULL default '', address2 varchar(65) NOT NULL default '', city varchar(40) NOT NULL default '', state tinyint(2) unsigned NOT NULL default 0, post_code varchar(25) NOT NULL default '', country tinyint(3) unsigned NOT NULL default 0, phone varchar(16) NOT NULL default '', email varchar(55) NOT NULL default '', bill_address varchar(65) NOT NULL default '', bill_address2 varchar(65) NOT NULL default '', bill_city varchar(40) NOT NULL default '', bill_state tinyint(2) unsigned NOT NULL default 0, bill_post_code varchar(25) NOT NULL default '', bill_country tinyint(3) unsigned NOT NULL default 0, ship_address varchar(65) NOT NULL default '', ship_address2 varchar(65) NOT NULL default '', ship_city varchar(40) NOT NULL default '', ship_state tinyint(2) unsigned NOT NULL default 0, ship_post_code varchar(25) NOT NULL default '', ship_country tinyint(3) unsigned NOT NULL default 0, date_entered datetime NOT NULL default '0000-00-00 00:00:00', PRIMARY KEY  (customer_id)) TYPE=MyISAM AUTO_INCREMENT=1",
                     "CREATE TABLE gallery ( gallery_id int(10) unsigned NOT NULL auto_increment, category_id int(10) unsigned NOT NULL default '0', gallery_name varchar(75) NOT NULL default '', gallery_icon varchar(155) NOT NULL default '', art_per_page int(2) unsigned NOT NULL default '0', art_per_row int(2) unsigned NOT NULL default '0', thumbnail_max int(3) unsigned NOT NULL default '0', rss_channel enum('yes','no') NOT NULL default 'no', meta_data_id int(10) unsigned NOT NULL default '0', PRIMARY KEY (gallery_id)) TYPE=MyISAM AUTO_INCREMENT=1",
                     "CREATE TABLE google_settings ( google_settings_id int(10) unsigned NOT NULL auto_increment, google_name varchar(32) NOT NULL default 'Google Sandbox Account', google_merchant_id varchar(15) NOT NULL default '', google_merchant_key varchar(22) NOT NULL default '', google_server_type enum('sandbox', 'live') NOT NULL default 'sandbox', security_protocol enum('http', 'https') NOT NULL default 'http', PRIMARY KEY  (google_settings_id)) TYPE=MyISAM AUTO_INCREMENT=1",
                     "CREATE TABLE image ( image_id int(10) unsigned NOT NULL auto_increment, artwork_id int(10) unsigned NOT NULL default '0', path varchar(255) NOT NULL default '', caption varchar(255), PRIMARY KEY  (image_id)) TYPE=MyISAM AUTO_INCREMENT=1",
                     "CREATE TABLE meta_data ( meta_data_id int(10) unsigned NOT NULL auto_increment, meta_type enum('site','artwork','gallery','page','product','category','comment') NOT NULL default 'site', content_id int(10) unsigned NOT NULL default '0', title varchar(55) NOT NULL default '', description varchar(255) NOT NULL default '', keywords varchar(255) NOT NULL default '', display enum('show','hide') NOT NULL default 'show', rss_feed enum('enable', 'disable') NOT NULL default 'disable', priority smallint(5) unsigned NOT NULL default '0', link varchar(155) NOT NULL default '', pub_date datetime NOT NULL default '0000-00-00 00:00:00', last_updated datetime NOT NULL default '0000-00-00 00:00:00', PRIMARY KEY (meta_data_id)) TYPE=MyISAM AUTO_INCREMENT=1",
                     "CREATE TABLE orders ( order_id int(10) unsigned NOT NULL auto_increment, order_number varchar(32) NOT NULL default '', order_date datetime NOT NULL default '0000-00-00 00:00:00', payment_date datetime NOT NULL default '0000-00-00 00:00:00', completed datetime NOT NULL default '0000-00-00 00:00:00', ship_date  datetime NOT NULL default '0000-00-00 00:00:00', notes tinytext NOT NULL default '', trans_status enum('new', 'paid', 'shipped', 'complete', 'cancelled') NOT NULL default 'new', customer_id int(10) unsigned NOT NULL default '0', artwork_id int(10) unsigned NOT NULL default '0', product_id int(10) unsigned NOT NULL default '0', PRIMARY KEY (order_id)) TYPE=MyISAM AUTO_INCREMENT=1",
                     "CREATE TABLE page ( page_id int(10) unsigned NOT NULL auto_increment, category_id int(10) unsigned NOT NULL default '0', name varchar(35) NOT NULL default '', text text NOT NULL, comments enum('enable','disable') NOT NULL default 'disable', meta_data_id int(10) unsigned NOT NULL default '0', PRIMARY KEY (page_id)) TYPE=MyISAM AUTO_INCREMENT=1",
                     "CREATE TABLE product ( product_id int(10) unsigned NOT NULL auto_increment, category_id int(10) unsigned NOT NULL default '0', product_name varchar(55) NOT NULL default '', picture varchar(255) NOT NULL default '', qty_instock int(10) unsigned NOT NULL default '1', price double(16,2) NOT NULL default '0.00', shipping double(16,2) NOT NULL default '0.00', handling double(16,2) NOT NULL default '0.00', meta_data_id int(10) unsigned NOT NULL default '0', PRIMARY KEY (product_id)) TYPE=MyISAM AUTO_INCREMENT=1",
                     "CREATE TABLE signature ( signature_id int(10) unsigned NOT NULL auto_increment, author varchar(25) NOT NULL default 'Webmaster', salutation tinytext NOT NULL default '', PRIMARY KEY (signature_id)) TYPE=MyISAM AUTO_INCREMENT=1",
                     "CREATE TABLE underground_channel ( channel_id int(10) unsigned NOT NULL auto_increment, provider_id int(10) unsigned NOT NULL default '0', copyright varchar(155) NOT NULL default '', pub_date datetime NOT NULL default '0000-00-00 00:00:00', last_updated datetime NOT NULL default '0000-00-00 00:00:00', channel_link varchar(155) NOT NULL default '', channel_title varchar(55) NOT NULL default '', channel_description varchar(255) NOT NULL default '', image_url varchar(155) NOT NULL default '', image_title varchar(55) NOT NULL default '', image_link varchar(155) NOT NULL default '', image_width tinyint unsigned NOT NULL default '0', image_height tinyint unsigned NOT NULL default '0', PRIMARY KEY (channel_id)) TYPE=MyISAM AUTO_INCREMENT=1",
                     "CREATE TABLE underground_item ( item_id int(10) unsigned NOT NULL auto_increment, channel_id int(10) unsigned NOT NULL default '0', title varchar(55) NOT NULL default '', link varchar(155) NOT NULL default '', description text NOT NULL default '', pub_date datetime NOT NULL default '0000-00-00 00:00:00', last_updated  datetime NOT NULL default '0000-00-00 00:00:00', PRIMARY KEY (item_id)) TYPE=MyISAM AUTO_INCREMENT=1",
                     "CREATE TABLE underground_message ( message_id int(10) unsigned NOT NULL auto_increment, provider_id int(10) unsigned NOT NULL default '0', author varchar(55) NOT NULL default '', title varchar(55) NOT NULL default '', message text NOT NULL default '', PRIMARY KEY (message_id)) TYPE=MyISAM AUTO_INCREMENT=1",
                     "CREATE TABLE underground_provider ( provider_id int(10) unsigned NOT NULL auto_increment, provider_name varchar(55) NOT NULL default '', provider_url varchar(155) NOT NULL default '', max_items int(11) NOT NULL default '0', blocked enum('on','off') NOT NULL default 'off', PRIMARY KEY (provider_id)) TYPE=MyISAM AUTO_INCREMENT=1",
                     "CREATE TABLE waiting_list ( list_id int(10) unsigned NOT NULL auto_increment, name varchar(55) NOT NULL default '', email varchar(155) NOT NULL default '', add_date datetime NOT NULL default '0000-00-00 00:00:00', offer_date datetime NOT NULL default '0000-00-00 00:00:00', PRIMARY KEY (waiting_list_id)) TYPE=MyISAM AUTO_INCREMENT=1");
    foreach($queries as $query) {
      $result = mysql_query($query);
      if ($err = mysql_error()) {
        $retmsg = $err . '<br>' . $query;
        die($retmsg);
        exit();
      }
    }

    // admin_pass is md5 encrypted
    if (!empty($_POST['admin_pass']) && strlen($_POST['admin_pass']) == 6) {
      $admin_pass = md5($_POST['admin_pass']);
    }
    elseif (!empty($_POST['admin_pass']) && strlen($_POST['admin_pass']) == 32) {
      $admin_pass = $_POST['admin_pass'];
    }
    else {
      $error_title = 'Password Error:';
      $error = 'The administrator password field was not filled out. Use the back button on your browser to go back and fill it out.';
      page_header('Administrator Password Empty');
      page_error($error_title, $error);
      page_footer();
      exit();
    }

    // this sets default site meta data
    if (isset($_POST['title'])) {
      $title = $_POST['title'];
    }
    else {
      $title = 'eXhibition - A PHP/MySQL Art Publishing System';
    }

    if (isset($_POST['description'])) {
      $description = $_POST['description'];
    }
    else {
      $description = 'eXhibition is a simple open source content management site tailored to the visual arts community. Create and organize virtual galleries. List works of art for sale or exhibition. Publish informational pages on any subject.';
    }

    if (isset($_POST['keywords'])) {
      $keywords = $_POST['keywords'];
    }
    else {
      $keywords = 'visual art, content management, art work, art sale, exhibition, php';
    }

    // sql for default inserts
    $inserts = array("INSERT INTO admin (admin_name, admin_pass, admin_email) VALUES ('$admin_name', '$admin_pass', '$admin_email')",
                     "INSERT INTO meta_data (meta_data_id,meta_type,title,description,keywords) VALUES (null,'site','$title','$description','$keywords')");

    foreach($inserts as $insert) {
      $result = mysql_query($insert);
      if ($err = mysql_error()) {
        $retmsg = $err . '<br>' . $insert;
        die($retmsg);
        exit();
      }
    }
    page_header('Database updated successfully!');
?>
                            <tr>
                                <td colspan="2" class="row1" align="center">
                                    <p class="gen">You should now be able to log in to the administration area with the username and password you just created:</p>
                                    <p><a href="../admin/">Administration</a></p>
                                    <p class="gen">Once you've logged in, you should delete the install folder from your server.</p>
                                </td>
                            </tr>
<?php
    page_footer();
    exit();
  }
}

?>