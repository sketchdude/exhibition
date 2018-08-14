<?php

// eXhibition - A PHP/MySQL Art Publishing System
// copyright (c) 2008 sketchdude

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

// start the session and connect to the database
require_once('../includes/config_inc.php');
require_once('inc/validate_inc.php');
require_once('../includes/time_inc.php');
require_once('inc/login_inc.php');
require_once('inc/underground_inc.php');

// look for a valid login session
if (isset($_SESSION['administrator']) && $_SESSION['authorized'] == 'admin') {
  // check to see if someone is logging out
  if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    admin_logout();
  }

  // user is successfully logged in
  $errors     = null;
  $message    = 'Logged in as: ' . $_SESSION['administrator'];
  $page_title = 'eXhibition XML Underground Network';
  $info       = 'Welcome!';
  $text       = null;

  // check to see if publish button should be shown
  $rss_address = $site['url'] . '/rss.php';
  echo $rss_address;

  if (isset($_POST['publish']) && $_POST['publish'] == 'Publish') {
    // call function to add this domain
  }

  if (isset($_POST['update']) && $_POST['update'] == 'Update') {
    /// this needs to be rewritten to retrieve only new providers to prevent multiple entries.
    $feedinfo = '<p>Testing system</p>' . "\n";
    $update_button = null;

    // calls on the cURL function to get underground feed sources and encodes them
    $xml = get_sources();

    $xml_parser = new gc_xmlparser($xml);
    $root = $xml_parser->GetRoot();
    $data = $xml_parser->GetData();
    $db_inserts = array();

    $count = count($data[$root]['RssFeedSourceInfo']);

    for ($i = 0; $i < $count; $i++) {
      $name = $data[$root]['RssFeedSourceInfo'][$i]['rssFeedProviderName'][VALUE]; 
      $urls = $data[$root]['RssFeedSourceInfo'][$i]['rssFeedProviderUrl'][VALUE];
      $maxi = $data[$root]['RssFeedSourceInfo'][$i]['maximumRssItemsToBeReturned'][VALUE];

      $db_inserts[] .= "INSERT INTO underground_provider (provider_id, provider_name, provider_url, max_items, blocked) VALUES (null,'$name','$urls','$maxi','off');";
    }

    // Adding feed providers to mysql
    foreach ($db_inserts as $db_insert) {
      $result = mysql_query($db_insert);
      if ($err = mysql_error()) {
        $retmsg = $err . '<br>' . $query;
        die($retmsg);
        exit();
      }
    }
  }
  else {
    $feedinfo = "<p>Click update to see who's on the underground</p>\n";
    $update_button = '<p><input type="submit" id="addinput" name="update" value="Update"></p>' . "\n";
    $received_rss = array();

    // retrieve rss channels from providers
    $query = "SELECT 
                provider_id,
                provider_name,
                provider_url,
                max_items
              FROM
                underground_provider
              WHERE
                blocked = 'off'";
    $result = mysql_query($query);
    if ($result) {
      while ($row = mysql_fetch_array($result)) {
        $rss = get_feeds($row['provider_url']);
        $received_rss = new gc_xmlparser($rss);
        $rss_root = $received_rss->GetRoot();
        $rss_data = $received_rss->GetData();

        //print_r($rss_data);
      }
    }
    else {
      // could not find any providers
      echo "No Results!";
    }
  }

  // activate the template
  include_once($admin_tpl . '/header' . $admin_tplext);
  include_once($admin_tpl . '/underground' . $admin_tplext);
  include_once($admin_tpl . '/footer' . $admin_tplext);

}
else {
  // user needs to login
  if (!empty($_POST['action']) && $_POST['action'] == 'login') {
    admin_login(gp_filter($_POST['username']), gp_filter($_POST['password']));
  }

  $message    = 'Please Log In';
  $info       = 'Administration Login - eXhibition';
  $page_title = 'Administration Login - eXhibition';
  $text       = '<p align="center">Welcome to the eXhibition administrative back-end.</p><p align="center">This area is password protected, so you will need to login to access this part of your site.</p><p>&nbsp;</p>';
  // show the login form
  include_once($admin_tpl . '/header_dead' . $admin_tplext);
  include_once($admin_tpl . '/login' . $admin_tplext);
  include_once($admin_tpl . '/footer' . $admin_tplext);
  exit();
}

?>