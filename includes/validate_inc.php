<?php

// eXhibition - A PHP/MySQL Art Publishing System
// copyright (c) 2005 sketchdude

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

// enforce minimum and maximum lengths before updating database
function length($fieldname, $fieldvalue, $minimum, $maximum) {
  global $errors;
  if (strlen($fieldvalue) < $minimum || strlen($fieldvalue) > $maximum) {
    $errors[$fieldname]['length'] = '<p class="errors">' . $fieldname . ' must be between ' . $minimum . ' and ' . $maximum . ' characters.</p>' . "\n";
    return $errors;
  }
  return false;
}

function validate_comment() {
  global $errors;

  $comment_fields = array();

  // variables comming from post

  // title is a reqired field so get one if neccessary
  if (strlen(gp_filter($_POST['title'])) < 1) {
    if (gp_filter($_POST['parent_type']) == 'art') {
      // content is artwork so get title for this artwork id
      $artwork_id = gp_filter($_POST['comment_parent']);
      $query = "SELECT m.title FROM meta_data m, artwork a WHERE a.artwork_id = '$artwork_id' AND m.meta_data_id = a.meta_data_id";
    }
    elseif (gp_filter($_POST['parent_type']) == 'page') {
      // content is an about page
      $about_id = gp_filter($_POST['comment_parent']);
      $query = "SELECT m.title FROM meta_data m, about a WHERE a.about_id = '$about_id' AND m.meta_data_id = a.meta_data_id";
    }
    elseif (gp_filter($_POST['parent_type']) == 'comment') {
      // this is a reply to another comment
      $comment_id = gp_filter($_POST['comment_parent']);
      $query = "SELECT m.title FROM meta_data m, comment a WHERE a.comment_id = '$comment_id' AND m.meta_data_id = a.meta_data_id";
    }
    if ($query) {
      $result = mysql_query($query);
      if ($result) {
        $title = mysql_result($result, 0, 'title');
        $comment_fields['title'] = 'RE: ' . $title;
      }
      else {
        // can't find title so abort
        $errors['title'] = '<p class="errors">No title entered.</p>';
      }
    }
    else {
      // can't read content type so abort
      $errors['title'] = '<p class="errors">No title entered.</p>';
    }
  }
  else {
    $comment_fields['title'] = clean_string($_POST['title'], 55);
  }
  // message is not optional but required
  if (!empty($_POST['message'])) {
    $comment_fields['message'] = clean_string($_POST['message'], 65536);
  }
  else {
    $errors['message'] = '<p class="errors">No message entered.</p>';
  }
  $comment_fields['author'] = clean_string($_POST['author'], 25);

  if(!$_POST['msg_type'] == 'public' || !$_POST['msg_type'] == 'private') {
    $_POST['msg_type'] = 'private';
  }
  $comment_fields['msg_type'] = clean_string($_POST['msg_type'], 7);

  // other variables
  $now = get_datetime();
  $comment_fields['pub_date'] = $now;
  $comment_fields['last_updated'] = $now;
  $comment_fields['comment_parent'] = gp_filter($_POST['comment_parent']);
  $comment_fields['parent_type'] = clean_string($_POST['parent_type'], 7);

  if (empty($errors)) {
    return $comment_fields;
  }
  else {
    return false;
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
    $errors['email'] = '<p class="errors">Invalid format for email address.</p>' . "\n";
    return $errors;
  }
  return false;
}

// clean up string input for entry into database
function clean_string($string, $maxlength) {
  $string = trim(strip_tags(substr($string, 0, $maxlength)));
  return $string;
}

?>