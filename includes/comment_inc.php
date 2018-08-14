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

// As written this will only work for comments posted to an artwork
function comment_form($title) {
  global $artwork_id;
  $form = '<fieldset>' . "\n" .
          '    <legend>Add a comment</legend>' . "\n" .
          '    <p><label for="name">Name: </label> <input type="text" id="name" name="author" value="" size="25" maxlength="25"></p>' . "\n" .
          '    <p><label for="title">Title: </label><input type="text" id="title" name="title" value="' . $title . '" size="55" maxlength="55"></p>' . "\n" .
          '    <p><label for="message">Message: </label><textarea rows="7" cols="45" id="message" name="message"></textarea></p>' . "\n" .
          '    <input type="hidden" name="comment_parent" value="' . $artwork_id . '"><input type="hidden" name="parent_type" value="art">' . "\n" .
          '    <input type="hidden" name="msg_type" value="public">' . "\n" .
          '    <p class="submit"><input type="submit" name="comment" value="Comment"></p>' . "\n" .
          '</fieldset>' . "\n";
  return $form;
}

function comment_display($type, $id) {
  // find comments for this item
  // database query depends on which type of item is the parent
  if ($type == 'art') {
    $table = 'comment c, artwork t';
    $and = 'AND c.comment_parent = t.artwork_id AND t.artwork_id = ' . $id;
  }
  elseif ($type == 'page') {
    $table = 'comment c, about t';
    $and = 'AND c.comment_parent = t.about_id AND t.about_id = ' . $id;
  }
  elseif ($type == 'comment') {
    $table = 'comment c';
    $and = 'AND c.comment_parent = c.comment_id AND c.comment_id =' . $id;
  }

  $query = "SELECT
              c.comment_id,
              c.comment_parent,
              c.parent_type,
              c.author,
              c.message,
              m.title,
              m.pub_date
            FROM
              $table,
              meta_data m
            WHERE
              c.meta_data_id = m.meta_data_id
            AND 
              c.msg_type = 'public'
            $and
            ORDER BY m.pub_date";
  //echo $query . '<hr>';
  $result = mysql_query($query) or die(mysql_error());
  if (!$result) {
    // no messages found
    $message_list = '<p>No comments have been left.</p>';
  }
  else {
    $message_list = '';
    while ($row = mysql_fetch_assoc($result)) {
      // format the date for display
      $pub_date = date_format_long($row['pub_date']);
      // make sure some kind of name appears for every post
      if (strlen($row['author']) < 1) {
        $row['author'] = 'Anonymous';
      }
      $message_list .= '<em>Posted by: </em><b>' . $row['author'] . '</b>&nbsp;&nbsp;&nbsp;&nbsp;<em>On:</em> <b>' . $pub_date . '</b><br>' . "\n" .
                        '<b>' . $row['title'] . '</b><br>' . "\n" .
                        $row['message'] . '<br><br>' . "\n";
    } 
  }

  $display = '<hr>' . "\n" .
             '<h4>Comments:</h4>' . "\n" .
             '' . $message_list . "\n" .
             '<hr>' . "\n";
  return $display;
}

function add_comment($comment_fields) {
  // create a meta_data_id first
  $query = "INSERT INTO
              meta_data (meta_data_id,
                         title,
                         description,
                         keywords,
                         display,
                         rss_feed,
                         priority,
                         link,
                         pub_date,
                         last_updated)
            VALUES (NULL,
                    '$comment_fields[title]',
                    '',
                    '',
                    'show',
                    'disable',
                    0,
                    '',
                    '$comment_fields[pub_date]',
                    '$comment_fields[last_updated]')";

  $result = mysql_query($query) or die(mysql_error());
  if ($result) {
    // get the newly created meta id
    $meta_data_id = mysql_insert_id();

    // Now add the comment with the new meta id
    $query = "INSERT INTO
                comment (comment_id,
                         comment_parent,
                         parent_type,
                         author,
                         message,
                         msg_type,
                         meta_data_id)
              VALUES (NULL,
                     '$comment_fields[comment_parent]',
                     '$comment_fields[parent_type]',
                     '$comment_fields[author]',
                     '$comment_fields[message]',
                     '$comment_fields[msg_type]',
                     '$meta_data_id')";

    $results = mysql_query($query);
    if ($results) {
      return true;
    }
    else {
      return false;
    }
  }
}

?>