<?php

// eXhibition - A PHP/MySQL Art Publishing System
// copyright (c) 2006 sketchdude

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

// validation functions

// filter $_GET[] and $_POST[] requests
function gp_filter($req) {
  if(get_magic_quotes_gpc()) {
    $req = stripslashes($req);
  }

  if(function_exists("mysql_real_escape_string")) {
    $req = mysql_real_escape_string($req);
  }
  else {
    $req = addslashes($req);
  }

  return $req;
}

// clean up string input for entry into database
function clean_string($string, $maxlength) {
  $string = trim(strip_tags(substr($string, 0, $maxlength)));
  return $string;
}

// query the database to ensure a field is unique
function is_unique($field, $dbtable, $postfield) {
  global $errors;
  $query = "SELECT $field FROM $dbtable WHERE $field = '$postfield'";
  $result = mysql_query($query);
  if (!$result) {
    return false;
  }
  else {
    if (mysql_num_rows($result) > 0) {
      $same = mysql_fetch_array($result);
      if (!empty($same[$field]) && $same[$field] == $postfield) {
        $errors[$field]['unique'] = '<p class="errors">' . $field . ' must be unique. "' . $same[$field] . '" is already being used.</p>' . "\n";
        return $errors;
      }
      else {
        return false;
      }
    }
    else {
      return false;
    }
  }
}

// check the format of an email address
function format_email($email) {
  global $errors;
  $pattern = "/" .
             "^[a-z0-9_-]+" .        // valid chars (at least once)
             "(\.[a-z0-9_-]+)*" .    // dot valid chars (0-n times)
             "@" .
             "[a-z0-9][a-z0-9-]*" .  // valid chars (at least once)
             "(\.[a-z0-9-]+)*" .     // dot valid chars (0-n times)
             "\.([a-z]{2,6})$" .     // dot valid chars
             "/i";                   // case insensitive
  if (!preg_match($pattern, $email)) {
    $errors['email']['format'] = '<p class="errors">Invalid format for email address.</p>' . "\n";
    return $errors;
  }
  return false;
}

// enforce minimum and maximum lengths before updating database
function length($fieldname, $fieldvalue, $minimum, $maximum) {
  global $errors;
  if (strlen($fieldvalue) < $minimum || strlen($fieldvalue) > $maximum) {
    $errors[$fieldname]['length'] = '<p class="errors">' . $fieldname . ' must be between ' . $minimum . ' and ' . $maximum . ' characters.</p>' . "\n";
    return $errors;
  }
  return false;
}

// enforce minimum length only before updating database
function any_length($fieldname, $fieldvalue, $minimum) {
  global $errors;
  if (strlen($fieldvalue) < $minimum) {
    $errors[$fieldname]['length'] = '<p class="errors">' . $fieldname . ' must be at least ' . $minimum . ' characters long.</p>' . "\n";
    return $errors;
  }
  return false;
}

// debug function
function var_dump_pre($mixed=null) {
  echo '<pre>';
  var_dump($mixed);
  echo '</pre>';
  return null;
}

function validate_configuration() {
  global $errors;
  $errors = array();
  // The following silent checks will correct errors with no input required from the user
  $_SESSION['title']       = clean_string($_SESSION['title'], 55);
  $_SESSION['description'] = clean_string($_SESSION['description'], 255);
  $_SESSION['keywords']    = clean_string($_SESSION['keywords'], 255);

  // The following routines will throw an error back to the user for correction
  // Title must be between 1 and 55 chars long (error check)
  length('title', $_SESSION['title'], 1, 55);

  // Description must be between 0 and 255 chars long (error check)
  length('description', $_SESSION['description'], 0, 255);

  // Keywords must be between 0 and 255 chars long (error check)
  length('keywords', $_SESSION['keywords'], 0, 255);

  return $errors;
}

function validate_gallery() {
  global $errors;
  $errors = array();
  // The following silent checks will correct errors with no input required from the user
  $_SESSION['category_id']       = clean_string($_SESSION['category_id'], 11);
  $_SESSION['title']             = clean_string($_SESSION['title'], 55);
  $_SESSION['gallery_name']      = clean_string($_SESSION['gallery_name'], 75);
  $_SESSION['display']           = clean_string($_SESSION['display'], 4);
  $_SESSION['art_per_page']      = clean_string($_SESSION['art_per_page'], 2);
  $_SESSION['art_per_row']       = clean_string($_SESSION['art_per_row'], 2);
  $_SESSION['thumbnail_max']     = clean_string($_SESSION['thumbnail_max'], 3);
  $_SESSION['new_gallery_icon']  = clean_string($_SESSION['new_gallery_icon'], 7);
  $_SESSION['gallery_icon']      = clean_string($_SESSION['gallery_icon'], 155);
  $_SESSION['priority']          = clean_string($_SESSION['priority'], 5);
  $_SESSION['rss_feed']          = clean_string($_SESSION['rss_feed'], 7);
  $_SESSION['description']       = clean_string($_SESSION['description'], 255);
  $_SESSION['keywords']          = clean_string($_SESSION['keywords'], 255);
  // Display must say 'show' or 'hide'. Default is 'show'. (silent check)
  if (!$_SESSION['display'] == 'show' || !$_SESSION['display'] == 'hide') {
    // enforce the default
    $_SESSION['display'] = 'show';
  }

  // Rss Feed must say 'enable' or 'disable'. Default is 'disable'. (silent check)
  if (!$_SESSION['rss_feed'] == 'enable' || !$_SESSION['rss_feed'] == 'disable') {
    // enforce the default
    $_SESSION['rss_feed'] = 'disable';
  }

  // The following routines will throw an error back to the user for correction
  // Gallery Name length must be between 1 and 75 characters (error check)
  length('gallery_name', $_SESSION['gallery_name'], 1, 75);

  // Art Per Page must be between 1 and 2 chars long (error check)
  length('art_per_page', $_SESSION['art_per_page'], 1, 2);
 
  // Art Per Row must be between 1 and 2 chars long (error check)
  length('art_per_row', $_SESSION['art_per_row'], 1, 2);

  // Thumbnail Max must be between 1 and 3 chars long (error check)
  length('thumbnail_max', $_SESSION['thumbnail_max'], 1, 3);

  // Title must be between 1 and 55 chars long (error check)
  length('title', $_SESSION['title'], 1, 55);

  // Description must be between 0 and 255 chars long (error check)
  length('description', $_SESSION['description'], 0, 255);

  // Keywords must be between 0 and 255 chars long (error check)
  length('keywords', $_SESSION['keywords'], 0, 255);

  return $errors;
}

// ensures a day of the month is properly 
// formatted for insertion into mysql
function check_day($day) {
  if ($day == '1' || $day == '01') {
    $check_day = '01';
  }
  elseif ($day == '2' || $day == '02') {
    $check_day = '02';
  }
  elseif ($day == '3' || $day == '03') {
    $check_day = '03';
  }
  elseif ($day == '4' || $day == '04') {
    $check_day = '04';
  }
  elseif ($day == '5' || $day == '05') {
    $check_day = '05';
  }
  elseif ($day == '6' || $day == '06') {
    $check_day = '06';
  }
  elseif ($day == '7' || $day == '07') {
    $check_day = '07';
  }
  elseif ($day == '8' || $day == '08') {
    $check_day = '08';
  }
  elseif ($day == '9' || $day == '09') {
    $check_day = '09';
  }
  elseif ($day == '10') {
    $check_day = '10';
  }
  elseif ($day == '11') {
    $check_day = '11';
  }
  elseif ($day == '12') {
    $check_day = '12';
  }
  elseif ($day == '13') {
    $check_day = '13';
  }
  elseif ($day == '14') {
    $check_day = '14';
  }
  elseif ($day == '15') {
    $check_day = '15';
  }
  elseif ($day == '16') {
    $check_day = '16';
  }
  elseif ($day == '17') {
    $check_day = '17';
  }
  elseif ($day == '18') {
    $check_day = '18';
  }
  elseif ($day == '19') {
    $check_day = '19';
  }
  elseif ($day == '20') {
    $check_day = '20';
  }
  elseif ($day == '21') {
    $check_day = '21';
  }
  elseif ($day == '22') {
    $check_day = '22';
  }
  elseif ($day == '23') {
    $check_day = '23';
  }
  elseif ($day == '24') {
    $check_day = '24';
  }
  elseif ($day == '25') {
    $check_day = '25';
  }
  elseif ($day == '26') {
    $check_day = '26';
  }
  elseif ($day == '27') {
    $check_day = '27';
  }
  elseif ($day == '28') {
    $check_day = '28';
  }
  elseif ($day == '29') {
    $check_day = '29';
  }
  elseif ($day == '30') {
    $check_day = '30';
  }
  elseif ($day == '31') {
    $check_day = '31';
  }
  else {
    $check_day = '00';
  }
  return $check_day;
}

// ensures a month is properly formatted for insertion into mysql
function check_month($month) {
  if ($month == '1' || $month == '01') {
    $check_month = '01';
  }
  elseif ($month == '2' || $month == '02') {
    $check_month = '02';
  }
  elseif ($month == '3' || $month == '03') {
    $check_month = '03';
  }
  elseif ($month == '4' || $month == '04') {
    $check_month = '04';
  }
  elseif ($month == '5' || $month == '05') {
    $check_month = '05';
  }
  elseif ($month == '6' || $month == '06') {
    $check_month = '06';
  }
  elseif ($month == '7' || $month == '07') {
    $check_month = '07';
  }
  elseif ($month == '8' || $month == '08') {
    $check_month = '08';
  }
  elseif ($month == '9' || $month == '09') {
    $check_month = '09';
  }
  elseif ($month == '10') {
    $check_month = '10';
  }
  elseif ($month == '11') {
    $check_month = '11';
  }
  elseif ($month == '12') {
    $check_month = '12';
  }
  else {
    $check_month = '00';
  }
  return $check_month;
}

// performs validation routines on session vars and formats session birth & death dates
function validate_artist() {
  global $errors;
  $errors = array();

  if (isset($_SESSION['name'])) {
    $_SESSION['name'] = clean_string($_SESSION['name'], 125);

    // Name must be between 1 and 125 chars long (error check)
    length('name', $_SESSION['name'], 1, 125);
  }

  if (isset($_SESSION['location'])) {
    $_SESSION['location'] = clean_string($_SESSION['location'], 75);
  }

  if (isset($_SESSION['birth_year'])) {
    $birth_year = clean_string($_SESSION['birth_year'], 4);

    if (is_numeric($birth_year)) {
      $birth_year = $birth_year;
    }
    else {
      $birth_year = '0000';
    }
  }

  if (isset($_SESSION['birth_month'])) {
    $_SESSION['birth_month'] = clean_string($_SESSION['birth_month'], 2);

    $birth_month = check_month($_SESSION['birth_month']);
  }
  else {
    $birth_month = '00';
  }

  if (isset($_SESSION['birth_day'])) {
    $_SESSION['birth_day'] = clean_string($_SESSION['birth_day'], 2);

    $birth_day = check_day($_SESSION['birth_day']);
  }
  else {
    $birth_day = '00';
  }

  if (isset($_SESSION['death_year'])) {
    $death_year = clean_string($_SESSION['death_year'], 4);

    if (is_numeric($death_year)) {
      $death_year = $death_year;
    }
    else {
      $death_year = '0000';
    }
  }

  if (isset($_SESSION['death_month'])) {
    $_SESSION['death_month'] = clean_string($_SESSION['death_month'], 2);

    $death_month = check_month($_SESSION['death_month']);
  }
  else {
    $death_month = '00';
  }

  if (isset($_SESSION['death_day'])) {
    $_SESSION['death_day'] = clean_string($_SESSION['death_day'], 2);

    $death_day = check_day($_SESSION['death_day']);
  }
  else {
    $death_day = '00';
  }

  if (isset($birth_year)) {
    if ($birth_year == '0000') {
      $birth_month = '00';
      $birth_day   = '00';
    }
  }
  else {
    $birth_year = '0000';
    $birth_month = '00';
    $birth_day   = '00';
  }

  if (isset($death_year)) {
    if ($death_year == '0000') {
      $death_month = '00';
      $death_day   = '00';
    }
  }
  else {
    $death_year = '0000';
    $death_month = '00';
    $death_day   = '00';
  }

  // preparing session vars for insertion into mysql
  $_SESSION['birth_date'] = $birth_year . '-' . $birth_month . '-' . $birth_day;
  $_SESSION['death_date'] = $death_year . '-' . $death_month . '-' . $death_day;

  return $errors;
}

function validate_page() {
  global $errors;
  $errors = array();
  // The following silent checks will correct errors with no input required from the user
  $_SESSION['category_id'] = clean_string($_SESSION['category_id'], 11);
  $_SESSION['priority']    = clean_string($_SESSION['priority'], 11);
  $_SESSION['name']        = clean_string($_SESSION['name'], 85);
  $_SESSION['display']     = clean_string($_SESSION['display'], 4);
  $_SESSION['title']       = clean_string($_SESSION['title'], 55);
  $_SESSION['keywords']    = clean_string($_SESSION['keywords'], 255);
  // Display must say 'show' or 'hide'. Default is 'show'. (silent check)
  if (!$_SESSION['display'] == 'show' || !$_SESSION['display'] == 'hide') {
    // enforce the default
    $_SESSION['display'] = 'show';
  }
  // Comments must say 'enable' or 'disable'. Default is 'enable'. (silent check)
  if (!$_SESSION['comments'] == 'enable' || !$_SESSION['comments'] == 'disable') {
    // enforce the default
    $_SESSION['comments'] = 'disable';
  }

  // rss_feed must say 'enable' or 'disable'
  if (!$_SESSION['rss_feed'] == 'enable' || !$_SESSION['rss_feed'] == 'disable') {
    // enforce the default
    $_SESSION['rss_feed'] = 'disable';
  }

  // The following routines will throw an error back to the user for correction
  // Name must be between 1 and 35 chars long (error check)
  length('name', $_SESSION['name'], 1, 35);

  // Text must be between 1 and 65536 chars long (error check)
  length('text', $_SESSION['text'], 1, 65536);

  // Title must be between 1 and 55 chars long (error check)
  length('title', $_SESSION['title'], 1, 55);

  // Description may be between 0 and 255 chars long (error check)
  length('description', $_SESSION['description'], 0, 255);

  // Keywords may be between 0 and 255 chars long (error check)
  length('keywords', $_SESSION['keywords'], 0, 255);
  return $errors;
}

function validate_artwork() {
  global $errors;
  $errors = array();
  // The following silent checks will correct errors with no input required from the user
  $_SESSION['gallery_id']      = clean_string($_SESSION['gallery_id'], 11);
  $_SESSION['title']           = clean_string($_SESSION['title'], 55);
  $_SESSION['new_thumbnail']   = clean_string($_SESSION['new_thumbnail'], 7);
  if(isset($_SESSION['thumbnail'])) {
    $_SESSION['thumbnail'] = clean_string($_SESSION['thumbnail'], 155);
  }
  $_SESSION['display']         = clean_string($_SESSION['display'], 4);
  $_SESSION['type']            = clean_string($_SESSION['type'], 7);
  $_SESSION['year_completed']  = clean_string($_SESSION['year_completed'], 4);
  $_SESSION['month_completed'] = clean_string($_SESSION['month_completed'], 9);
  $_SESSION['day_completed']   = clean_string($_SESSION['day_completed'], 2);
  $_SESSION['status']          = clean_string($_SESSION['status'], 9);
  $_SESSION['qty_instock']     = clean_string($_SESSION['qty_instock'], 8);
  $_SESSION['price']           = clean_string($_SESSION['price'], 19);
  $_SESSION['shipping']        = clean_string($_SESSION['shipping'], 19);
  $_SESSION['handling']        = clean_string($_SESSION['handling'], 19);
  $_SESSION['priority']        = clean_string($_SESSION['priority'], 5);
  $_SESSION['comments']        = clean_string($_SESSION['comments'], 7);
  $_SESSION['rss_feed']        = clean_string($_SESSION['rss_feed'], 7);
  $_SESSION['artist_id']       = clean_string($_SESSION['artist_id'], 10);
  $_SESSION['medium']          = clean_string($_SESSION['medium'], 35);
  $_SESSION['size']            = clean_string($_SESSION['size'], 25);
  $_SESSION['style']           = clean_string($_SESSION['style'], 45);
  $_SESSION['subject']         = clean_string($_SESSION['subject'], 45);
  $_SESSION['description']     = clean_string($_SESSION['description'], 255);
  $_SESSION['keywords']        = clean_string($_SESSION['keywords'], 255);

  // Display must say 'show' or 'hide'. Default is 'show'. (silent check)
  if (!$_SESSION['display'] == 'show' || !$_SESSION['display'] == 'hide') {
    // enforce the default
    $_SESSION['display'] = 'show';
  }

  // Comments must say 'enable' or 'disable'. Default is 'disable'. (silent check)
  if (!$_SESSION['comments'] == 'enable' || !$_SESSION['comments'] == 'disable') {
    // enforce the default
    $_SESSION['comments'] = 'disable';
  }
  // rss_feed must say 'enable' or 'disable'
  if (!$_SESSION['rss_feed'] == 'enable' || !$_SESSION['rss_feed'] == 'disable') {
    // enforce the default
    $_SESSION['rss_feed'] = 'disable';
  }

  // Type must say 'sale' or 'exhibit'. Default is 'sale'. (silent check)
  if (!$_SESSION['type'] == 'sale' || !$_SESSION['type'] == 'exhibit') {
    // enforce the default
    $_SESSION['type'] = 'sale';
  }
  // Status must say 'available', 'pending' or 'sold'. Default is 'available'. (silent check)
  if (!$_SESSION['status'] == 'available' || !$_SESSION['status'] == 'pending' || !$_SESSION['status'] == 'sold') {
    // enforce the default
    $_SESSION['status'] = 'available';
  }

  // Price needs a default zero value
  if (empty($_SESSION['price'])) {
    $_SESSION['price'] = '0.00';
  }

  // Shipping needs a default zero value
  if (empty($_SESSION['shipping'])) {
    $_SESSION['shipping'] = '0.00';
  }

  // Handling needs a default zero value
  if (empty($_SESSION['handling'])) {
    $_SESSION['handling'] = '0.00';
  }

  // Year needs a default zero value
  if (empty($_SESSION['year_completed'])) {
    $_SESSION['year_completed'] = '0000';
  }

  // Month needs a default zero value
  if (empty($_SESSION['month_completed'])) {
    $_SESSION['month_completed'] = '00';
  }

  // Day needs a default zero value
  if (empty($_SESSION['day_completed'])) {
    $_SESSION['day_completed'] = '00';
  }

  // The following routines will throw an error back to the user for correction

  // Title must be between 1 and 55 chars long (error check)
  length('title', $_SESSION['title'], 1, 55);

  return $errors;
}

function validate_product() {
  global $errors;
  $errors = array();
  // The following silent check will correct errors with no input required from the user
  $_SESSION['category_id']  = clean_string($_SESSION['category_id'], 11);
  $_SESSION['product_name'] = clean_string($_SESSION['product_name'], 55);
  $_SESSION['new_picture']  = clean_string($_SESSION['new_picture'], 7);
  if(isset($_SESSION['picture'])) {
    $_SESSION['picture'] = clean_string($_SESSION['picture'], 155);
  }
  $_SESSION['qty_instock'] = clean_string($_SESSION['qty_instock'], 8);
  $_SESSION['price']       = clean_string($_SESSION['price'], 19);
  $_SESSION['shipping']    = clean_string($_SESSION['shipping'], 19);
  $_SESSION['handling']    = clean_string($_SESSION['handling'], 19);
  $_SESSION['title']       = clean_string($_SESSION['title'], 55);
  $_SESSION['description'] = clean_string($_SESSION['description'], 255);
  $_SESSION['keywords']    = clean_string($_SESSION['keywords'], 255);
  $_SESSION['display']     = clean_string($_SESSION['display'], 4);
  $_SESSION['priority']    = clean_string($_SESSION['priority'], 5);

  // Price needs a default zero value
  if (empty($_SESSION['price'])) {
    $_SESSION['price'] = '0.00';
  }

  // Shipping needs a default zero value
  if (empty($_SESSION['shipping'])) {
    $_SESSION['shipping'] = '0.00';
  }

  // Handling needs a default zero value
  if (empty($_SESSION['handling'])) {
    $_SESSION['handling'] = '0.00';
  }

  // enforce defaults for enum fields
  if (!$_SESSION['display'] == 'show' || !$_SESSION['display'] == 'hide') {
    $_SESSION['display'] = 'show';
  }
  else {
    $_SESSION['display'] = $_SESSION['display'];
  }

  // The following routines will throw an error back to the user for correction
  // Name must be between 1 and 35 chars long (error check)
  length('product_name', $_SESSION['product_name'], 1, 55);

  // Title must be between 1 and 55 chars long (error check)
  length('title', $_SESSION['title'], 1, 55);

  // Description may be between 0 and 255 chars long (error check)
  length('description', $_SESSION['description'], 0, 255);

  // Keywords may be between 0 and 255 chars long (error check)
  length('keywords', $_SESSION['keywords'], 0, 255);

  return $errors;
}

function validate_category() {
  global $errors;
  $errors = array();
  // The following silent check will correct errors with no input required from the user
  $_SESSION['cat_name']    = clean_string($_SESSION['cat_name'], 35);
  $_SESSION['title']       = clean_string($_SESSION['title'], 55);
  $_SESSION['description'] = clean_string($_SESSION['description'], 255);
  $_SESSION['keywords']    = clean_string($_SESSION['keywords'], 255);

  if (is_numeric($_SESSION['priority'])) {
    $_SESSION['priority'] = clean_string($_SESSION['priority'], 5);
  }
  else {
    $_SESSION['priority'] = 0;
  }

  // enforce defaults for enum fields
  if (!$_SESSION['cat_type'] == 'gallery' || !$_SESSION['cat_type'] == 'page' || !$_SESSION['cat_type'] == 'product') {
    $_SESSION['cat_type'] = 'gallery';
  }
  else {
    $_SESSION['cat_type'] = $_SESSION['cat_type'];
  }

  if (!$_SESSION['display'] == 'show' || !$_SESSION['display'] == 'hide') {
    $_SESSION['display'] = 'show';
  }
  else {
    $_SESSION['display'] = $_SESSION['display'];
  }

  if (!$_SESSION['sidebar'] == 'one' || !$_SESSION['sidebar'] == 'two') {
    $_SESSION['sidebar'] = 'one';
  }
  else {
    $_SESSION['sidebar'] = $_SESSION['sidebar'];
  }

  if (!$_SESSION['rss_channel'] == 'yes' || !$_SESSION['rss_channel'] == 'no') {
    $_SESSION['rss_channel'] = 'no';
  }
  else {
    $_SESSION['rss_channel'] = $_SESSION['rss_channel'];
  }

  // Cat name must be between 1 and 35 chars long (error check)
  length('cat_name', $_SESSION['cat_name'], 1, 35);

  // Title must be between 1 and 55 chars long (error check)
  length('title', $_SESSION['title'], 1, 55);

  return $errors;
}

?>