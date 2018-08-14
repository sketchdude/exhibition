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

function rss_select($choice=null) {
  switch ($choice) {
    case 'disable':
      $yes = null;
      $no  = ' checked="checked"';
    default:
      break;
    case 'enable':
      $yes = ' checked="checked"';
      $no  = null;
      break;
  }
  $select_box = '<input type="radio" name="rss_feed" value="enable"' . $yes . '>Enable' . "\n" .
                '<input type="radio" name="rss_feed" value="disable"' . $no . '>Disable' . "\n";
  return $select_box;
}

// creates an html select box for choosing a display mode
function display_selectbox($display=null) {
  switch ($display) {
    case 'show':
    default:
      $sel_yes = ' checked="checked"';
      $sel_no  = null;
      break;
    case 'hide':
      $sel_yes = null;
      $sel_no  = ' checked="checked"';
      break;
  }
  $select_box = '    <input type="radio" name="display" value="show"' . $sel_yes . '>Show' . "\n" .
                '    <input type="radio" name="display" value="hide"' . $sel_no . '>Hide' . "\n";
  return $select_box;
}

function comment_selectbox($select=null) {
  switch ($select) {
    case 'disable':
    default:
      $sel_yes = null;
      $sel_no  = ' checked="checked"';
      break;
    case 'enable':
      $sel_yes = ' checked="checked"';
      $sel_no  = null;
      break;
  }
  $select_box = '    <input type="radio" name="comments" value="disable"' . $sel_no . '>Disable' . "\n" .
                '    <input type="radio" name="comments" value="enable"' . $sel_yes . '>Enable' . "\n";
  return $select_box;
}

function category_selectbox($category_id=null, $where) {
  $query = "SELECT category_id, cat_name FROM category $where";
  $result = mysql_query($query);
  $select_box = '<select name="category_id">' . "\n";
  if ($result) {
    if (mysql_num_rows($result) < 1) {
      // no categories found
      $select_box .= '<option value="0">No Categories Exist</option>' . "\n";
    }
    // categories found
    while ($row = mysql_fetch_assoc($result)) {
      if ($row['category_id'] == $category_id) {
        $selected = ' selected';
      }
      else {
        $selected = null;
      }
      $select_box .= '<option value="' . $row['category_id'] . '"' . $selected . '>' . $row['cat_name'] . '</option>' . "\n";
    }
  }
  else {
    // no categories found
    $select_box .= '<option value="0">No Categories Exist</option>' . "\n";
  }
  $select_box .= '</select>' . "\n";
  return $select_box;
}

?>