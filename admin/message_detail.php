<?php

// eXhibition - A PHP/MySQL Art Publishing System
// copyright (c) 2007 sketchdude

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
require_once('../includes/time_inc.php');
require_once('inc/validate_inc.php');

// activate the login script
require_once('inc/login_inc.php');

function signature_select() {
  $query = 'SELECT signature_id, author FROM signature';
  $result = mysql_query($query);
  $selectbox = '<select name="signature">' . "\n";
  if ($result) {
    while ($row = mysql_fetch_assoc($result)) {
      $selectbox .= '<option value="' . $row['signature_id'] . '">' . $row['author'] . '</option>' . "\n";
    }
  }
  else {
    $selectbox .= '<option value="1">Web Master</option>' . "\n";
  }
  $selectbox .= '</select>' . "\n";
  return $selectbox;
}

// look for a valid login session
if (isset($_SESSION['administrator']) && $_SESSION['authorized'] == 'admin') {
  // check to see if someone is logging out
  if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    admin_logout();
  }

  // user is successfully logged in
  $errors         = null;
  $update_message = null;
  $info           = null;

  // set message vars
  $message = 'Logged in as: ' . $_SESSION['administrator'];
  $info = 'Viewing details for message.';
  $page_title = 'Message Detail -Administration- eXhibition';

  $comment_id = gp_filter($_POST['comment_id']);
  $_SESSION['comment_id'] = $comment_id;

  // the reply button is clicked
  if (isset($_POST['reply']) && $_POST['reply'] == 'Reply') {
    $comment_id = gp_filter($_POST['comment_id']);
    $comment = array();
    if (isset($_POST['title']) && strlen($_POST['title']) > 0 && isset($_POST['message']) && strlen($_POST['message']) > 0) {
      $comment['title'] = gp_filter($_POST['title']);
      $comment['message'] = gp_filter($_POST['message']);
      $signature_id = gp_filter($_POST['signature']);
      // get signature
      $query = "SELECT author FROM signature WHERE signature_id = '$signature_id'";
      $result = mysql_query($query);
      $comment['author'] = mysql_result($result, 0, 'author');
      $comment['comment_parent'] = $_SESSION['comment_parent'];
      $comment['parent_type'] = gp_filter($parent_type);
      $comment['msg_type'] = 'public';

      $now = get_datetime();
      $comment['pub_date'] = $now;
      $comment['last_updated'] = $now;

      include_once('../includes/comment_inc.php');
      add_comment($comment);

      unset($_SESSION['comment_parent']);

      header('location: comments.php');
      exit();
    }
    else {
      header('location: message_detail.php?content=message&type=public&id=' . $comment_id);
      exit();
    }
  }
  // the quit button is clicked
  elseif (isset($_POST['quit']) && $_POST['quit'] == 'Quit') {
    header('location: comments.php');
    exit();
  }
  // the delete button is clicked
  elseif (isset($_POST['delete']) && $_POST['delete'] == 'Delete') {
    $comment_id = gp_filter($_POST['comment_id']);
    $query = "SELECT meta_data_id FROM comment WHERE comment_id = '$comment_id'";
    $result = mysql_query($query);
    $meta_data_id = mysql_result($result, 0, 'meta_data_id');
    switch ($_POST['delete_request']) {
      case 'request':
      default:
        $info = '<p>Are you sure you want to delete this message?</p>' . "\n";
        $info .= '<p id="border"><input type="submit" id="deleteinput" name="delete" value="Delete"></p>' . "\n";
        $info .= 'Delete this message' . "\n";
        $info .= '<input type="hidden" name="delete_request" value="confirm">' . "\n";
        $info .= '<input type="hidden" name="comment_id" value="' . $comment_id . '">' . "\n";
        include_once($admin_tpl . '/header' . $admin_tplext);
        include_once($admin_tpl . '/message' . $admin_tplext);
        include_once($admin_tpl . '/footer' . $admin_tplext);        
        break;
      case 'confirm':
        $query = "DELETE FROM 
                    comment, meta_data
                  USING
                    comment, meta_data
                  WHERE
                    comment.comment_id = '$comment_id'
                  AND
                    comment.meta_data_id = meta_data.meta_data_id";

        if ($result = mysql_query($query)) {
          header('location: comments.php');
          exit();
        }
        else {
          $info = '<h4>Not deleted! Please try again later.</h4>' . "\n";
        }

        include_once($admin_tpl . '/header' . $admin_tplext);
        include_once($admin_tpl . '/message' . $admin_tplext);
        include_once($admin_tpl . '/footer' . $admin_tplext);
        break;
    }
  }
  // no button is clicked so show message detail
  else {
    $content = gp_filter($_GET['content']);

    $comment_id = gp_filter($_GET['id']);

    $query = "SELECT
                c.comment_parent,
                c.parent_type,
                c.author,
                c.message,
                c.msg_type,
                m.title,
                m.pub_date
              FROM
                comment c,
                meta_data m
              WHERE
                comment_id = '$comment_id'
              AND
                c.meta_data_id = m.meta_data_id";
    $result = mysql_query($query) or die(mysql_error());
    if ($result) {
      $details = '<table width="50%" bgcolor="#fefefe" cellpadding="4" cellspacing="4">' . "\n" .
                 '    <tr>' . "\n";
 
      while ($row = mysql_fetch_array($result)) {
        $details .= '<td><em>From:</em> <b>' . $row['author'] . '</b></td>' .
                    '<td align="right"><em>On:</em> <b>' . date_format_long($row['pub_date']) . '</b></td>' . "\n" .
                    '</tr><tr>' . "\n" .
                    '<td colspan="2">' . $row['message'] . '</td>' . "\n";

                    $title = $row['title'];
                    $comment_parent = $row['comment_parent'];
                    $parent_type = $row['parent_type'];
      }
      $details .= '    </tr>' . "\n" .
                  '</table>' . "\n";
    }

    $signature_select = signature_select();

    //  activate templates
    require_once($admin_tpl . '/header' . $admin_tplext);
    require_once($admin_tpl . '/message_detail' . $admin_tplext);
    require_once($admin_tpl . '/footer' . $admin_tplext);
  }
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