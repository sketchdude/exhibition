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

// look for a valid login session
if (isset($_SESSION['administrator']) && $_SESSION['authorized'] == 'admin') {
  // check to see if someone is logging out
  if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    admin_logout();
  }

  // user is successfully logged in
  $errors     = null;
  $message    = 'Logged in as: ' . $_SESSION['administrator'];
  $page_title = 'Administration Configuration - eXhibition';
  $info       = 'Welcome!';
  $text       = null;
  
    // the quit button is clicked
  if (isset($_POST['quit']) && $_POST['quit'] == 'Quit') {
    unset($_POST);
    unset($errors);

    // unset any remaining session variables
    unset($_SESSION['title']);
    unset($_SESSION['description']);
    unset($_SESSION['keywords']);

    header('location: index.php');
    exit();
  }
  
   // the save button is clicked:
  if (isset($_POST['save']) && $_POST['save'] == 'Save') {
    // load the session variables from post:
    if (isset($_POST['title'])) {
      $_SESSION['title'] = gp_filter($_POST['title']);
      $title = $_SESSION['title'];
    }

    if (isset($_POST['description'])) {
      $_SESSION['description'] = gp_filter($_POST['description']);
      $description = $_SESSION['description'];
    }

    if (isset($_POST['keywords'])) {
      $_SESSION['keywords'] = gp_filter($_POST['keywords']);
      $keywords = $_SESSION['keywords'];
    }

    // run validation routines on the session variables
    validate_configuration();

    if (empty($errors)) {
      // run a function to read and save changes
      $query_string = configuration_fields_diff();

      // main update query
      $update = "UPDATE meta_data
                 SET $query_string 
                 WHERE meta_type = 'site'";

      // if the query string shows changes, run the update
      if ($query_string) {
        $result = mysql_query($update);
        $info = '<h4>Changes saved.</h4>' . "\n";
      }
      else {
        $info = '<h4>No changes made.</h4>' . "\n";
      }

      // unset session variables
      unset($_SESSION['title']);
      unset($_SESSION['description']);
      unset($_SESSION['keywords']);

      // show a confirmation page
      $page_title = 'Adminstration Edit Configuration - eXhibition';
      $message = 'Logged in as: ' . $_SESSION['administrator'];
      include_once($admin_tpl . '/header' . $admin_tplext);
      include_once($admin_tpl . '/message' . $admin_tplext);
      include_once($admin_tpl . '/footer' . $admin_tplext);
    }
    else {
      // errors exist
      $page_title = 'Errors Exist: Adminstration Edit Configuration - eXhibition';
      $message = 'Logged in as: ' . $_SESSION['administrator'];
    }
  }
  else {
    // no button was clicked: Just populate and print the edit form
    $query = "SELECT title, description, keywords FROM meta_data WHERE meta_type = 'site'";
    $result = mysql_query($query);
    if (mysql_num_rows($result) > 0) {
      $title = mysql_result($result, 0, 'title');
      $description = mysql_result($result, 0, 'description');
      $keywords = mysql_result($result, 0, 'keywords');
    }
    else {
      $title = 'Please add a title here and save your changes';
      $description = 'Please add a description here and save your changes';
      $keywords = 'Please add keywords here and save your changes';
    } 
  }

  // activate the template
  include_once($admin_tpl . '/header' . $admin_tplext);
  include_once($admin_tpl . '/configuration' . $admin_tplext);
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

// compare elements of 2 arrays and return the difference
function configuration_fields_diff() {
  // get the old fields from mysql
  $query = "SELECT
              title,
              description,
              keywords
            FROM
              meta_data
            WHERE
              meta_type = 'site'";

  $result = mysql_query($query);

  // load all current/new fields into an array
  $new_fields = array('title'       => $_SESSION['title'],
                      'description' => $_SESSION['description'],
                      'keywords'    => $_SESSION['keywords']);

  if ($old_fields = mysql_fetch_assoc($result)) {
    // compare the title fields
    if ($old_fields['title'] == $new_fields['title']) {
      $update['title'] = false;
    }
    else {
      $update['title'] = $new_fields['title'];
    }

    // compare the description fields
    if ($old_fields['description'] == $new_fields['description']) {
      $update['description'] = false;
    }
    else {
      $update['description'] = $new_fields['description'];
    }

    // compare the keywords fields
    if ($old_fields['keywords'] == $new_fields['keywords']) {
      $update['keywords'] = false;
    }
    else {
      $update['keywords'] = $new_fields['keywords'];
    }
  }

  // get rid of the used arrays
  unset($old_fields);
  unset($new_fields);

  if (empty($update)) {
    return false;
  }
  else {
    $query_string = '';
    foreach ($update as $new => $entry) {
      if (!empty($entry)) {
        $query_string .= ", $new = " . "'" . $entry . "'";
      }
    }
    $query_string = substr($query_string, 1);
    // $update is no longer needed
    unset($update);

    if (empty($query_string)) {
      return false;
    }
    else {
      return $query_string;
    }
  }
}

?>