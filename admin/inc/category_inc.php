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

function ctype_select($cat_type=null) {
  switch ($cat_type) {
    case 'gallery':
    default:
      $galcheck = ' checked="checked"';
      $pagcheck = null;
      $procheck = null;
      break;
    case 'page':
      $galcheck = null;
      $pagcheck = ' checked="checked"';
      $procheck = null;
      break;
    case 'product':
      $galcheck = null;
      $pagcheck = null;
      $procheck = ' checked="checked"';
      break;
  }
  $select_box = '<input type="radio" name="cat_type" value="gallery"' . $galcheck . '>Gallery' . "\n" .
                '<input type="radio" name="cat_type" value="page"' . $pagcheck . '>Page' . "\n" .
                '<input type="radio" name="cat_type" value="product"' . $procheck . '>Product' . "\n";
  return $select_box;
}

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

function sidebar_selectbox($select=null) {
  switch ($select) {
    case 'one':
    default:
      $sel_one = ' selected';
      $sel_two = null;
      break;
    case 'two':
      $sel_one = null;
      $sel_two = ' selected';
      break;
  }
  $select_box = '<select name="sidebar_select">' . "\n" .
                '    <option value="one"' . $sel_one . '>Left Side</option>' . "\n" .
                '    <option value="two"' . $sel_two . '>Right Side</option>' . "\n" .
                '</select>' . "\n";
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

// compare elements of 2 arrays and return the difference
function page_fields_diff() {

  // get the old fields from mysql
  $query = "SELECT
              a.category_id,
              a.name,
              a.text,
              a.comments,
              m.title,
              m.description,
              m.keywords,
              m.display,
              m.rss_feed,
              m.priority
            FROM
              page a,
              meta_data m
            WHERE
              a.page_id = $_SESSION[page_id]
            AND
              a.meta_data_id = m.meta_data_id";

  $result = mysql_query($query);

  // load all current/new fields into an array
  $new_fields = array('category_id' => $_SESSION['category_id'],
                      'name'        => $_SESSION['name'],
                      'text'        => $_SESSION['text'],
                      'comments'    => $_SESSION['comments'],
                      'title'       => $_SESSION['title'],
                      'description' => $_SESSION['description'],
                      'keywords'    => $_SESSION['keywords'],
                      'display'     => $_SESSION['display'],
                      'rss_feed'    => $_SESSION['rss_feed'],
                      'priority'    => $_SESSION['priority']);

  if ($old_fields = mysql_fetch_assoc($result)) {
    // compare the category_id fields
    if ($old_fields['category_id'] == $new_fields['category_id']) {
      $update['category_id'] = false;
    }
    else {
      $update['category_id'] = $new_fields['category_id'];
    }

    // compare the name fields
    if ($old_fields['name'] == $new_fields['name']) {
      $update['name'] = false;
    }
    else {
      $update['name'] = $new_fields['name'];
    }

    // compare the text fields
    if ($old_fields['text'] == $new_fields['text']) {
      $update['text'] = false;
    }
    else {
      $update['text'] = $new_fields['text'];
    }

    // compare the comments fields
    if ($old_fields['comments'] == $new_fields['comments']) {
      $update['comments'] = false;
    }
    else {
      $update['comments'] = $new_fields['comments'];
    }

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

    // compare the display fields
    if ($old_fields['display'] == $new_fields['display']) {
      $update['display'] = false;
    }
    else {
      $update['display'] = $new_fields['display'];
    }

    // compare the rss_feed fields
    if ($old_fields['rss_feed'] == $new_fields['rss_feed']) {
      $update['rss_feed'] = false;
    }
    else {
      $update['rss_feed'] = $new_fields['rss_feed'];
    }

    // compare the priority fields
    if ($old_fields['priority'] == $new_fields['priority']) {
      $update['priority'] = false;
    }
    else {
      $update['priority'] = $new_fields['priority'];
    }
  }
  // get rid of the used arrays
  unset($old_fields);
  unset($new_fields);

  if (empty($update)) {
    return false;
  }
  else {
    $query_string = null;
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

function create_page() {
  global $home_dir, $site;

  include_once('../includes/time_inc.php');
  $now = get_datetime();

  // meta data comes first, except for the link field which is still unknown
  $query = "INSERT INTO 
              meta_data (meta_data_id,
                         title,
                         description,
                         keywords,
                         display,
                         rss_feed,
                         priority,
                         pub_date,
                         last_updated)
            VALUES ('',
                    '$_SESSION[title]',
                    '$_SESSION[description]',
                    '$_SESSION[keywords]',
                    '$_SESSION[display]',
                    '$_SESSION[rss_feed]',
                    '$_SESSION[priority]',
                    '$now',
                    '$now')";

  $result = mysql_query($query);

  if ($result) {
    // retrieve the new meta_data_id
    $query = "SELECT LAST_INSERT_ID() AS meta_data_id FROM meta_data";
    $result = mysql_query($query);

    $meta_data_id = mysql_result($result, 0, 'meta_data_id');

    if ($result) {
      // insert page details
      $query = "INSERT INTO
                   page (page_id,
                         category_id,
                         name,
                         text,
                         comments,
                         meta_data_id)
                VALUES ('',
                        '$_SESSION[category_id]',
                        '$_SESSION[name]',
                        '$_SESSION[text]',
                        '$_SESSION[comments]',
                        '$meta_data_id')";

      $result = mysql_query($query);

      if ($result) {
        // retrieve the new page_id
        $query = "SELECT LAST_INSERT_ID() AS page_id FROM page";
        $result = mysql_query($query);
        $page_id = mysql_result($result, 0, 'page_id');
        if ($result) {
          // cat together a page link from page_id
          $link = $site['url'] . '/' . $home_dir . '/page.php?id=' . $category_id;
          // update meta_data row with link
          $query = "UPDATE meta_data SET link = '$link' WHERE meta_data_id = $meta_data_id";
          $result = mysql_query($query);
          if ($result) {
            // page added
            return true;
          }
          else {
            return false;
          }
        }
        else {
          return false;
        }
      }
      else {
        return false;
      }
    }
    else {
      return false;
    }
    return true;
  }
  else {
    return false;
  }
}

function create_category() {
  global $home_dir, $site;

  include_once('../includes/time_inc.php');
  $now = get_datetime();

  // meta data comes first, except for the link field which is still unknown
  $query = "INSERT INTO 
              meta_data (meta_data_id,
                         title,
                         description,
                         keywords,
                         display,
                         rss_feed,
                         priority,
                         pub_date,
                         last_updated)
            VALUES ('',
                    '$_SESSION[title]',
                    '$_SESSION[description]',
                    '$_SESSION[keywords]',
                    '$_SESSION[display]',
                    'disable',
                    '$_SESSION[priority]',
                    '$now',
                    '$now')";

  $result = mysql_query($query);

  if ($result) {
    // retrieve the new meta_data_id
    $query = "SELECT LAST_INSERT_ID() AS meta_data_id FROM meta_data";
    $result = mysql_query($query);

    $meta_data_id = mysql_result($result, 0, 'meta_data_id');

    if ($result) {
      // insert category details
      $query = "INSERT INTO 
                  category (category_id,
                            cat_name,
                            cat_type,
                            sidebar,
                            rss_channel,
                            meta_data_id)
                VALUES ('',
                        '$_SESSION[cat_name]',
                        '$_SESSION[cat_type]',
                        '$_SESSION[sidebar]',
                        '$_SESSION[rss_channel]',
                        '$meta_data_id')";

      $result = mysql_query($query);

      if ($result) {
        // retrieve the new category_id
        $query = "SELECT LAST_INSERT_ID() AS category_id FROM category";
        $result = mysql_query($query);
        $category_id = mysql_result($result, 0, 'category_id');
        if ($result) {
          // cat together a page link from category_id
          $link = $site['url'] . '/' . $home_dir . '/category.php?id=' . $category_id;
          // update meta_data row with link
          $query = "UPDATE meta_data SET link = '$link' WHERE meta_data_id = $meta_data_id";
          $result = mysql_query($query);
          if ($result) {
            // category added
            return true;
          }
          else {
            return false;
          }
        }
        else {
          return false;
        }
      }
      else {
        return false;
      }
    }
    else {
      return false;
    }
    return true;
  }
  else {
    return false;
  }
}

// compare elements of 2 arrays and return the difference
function category_fields_diff() {

  // get the old field from mysql
  $query = "SELECT
              c.cat_name,
              c.sidebar,
              c.rss_channel,
              m.title,
              m.description,
              m.keywords,
              m.display,
              m.priority
            FROM
              category c,
              meta_data m 
            WHERE
              category_id = $_SESSION[category_id]
            AND
              c.meta_data_id = m.meta_data_id";

  $result = mysql_query($query);

  // load all current/new fields into an array
  $new_fields = array('cat_name'    => $_SESSION['cat_name'],
                      'sidebar'     => $_SESSION['sidebar'],
                      'rss_channel' => $_SESSION['rss_channel'],
                      'title'       => $_SESSION['title'],
                      'description' => $_SESSION['description'],
                      'keywords'    => $_SESSION['keywords'],
                      'display'     => $_SESSION['display'],
                      'priority'    => $_SESSION['priority']);

  if ($old_fields = mysql_fetch_assoc($result)) {
    // compare the cat_name fields
    if ($old_fields['cat_name'] == $new_fields['cat_name']) {
      $update['cat_name'] = false;
    }
    else {
      $update['cat_name'] = $new_fields['cat_name'];
    }

    // compare the sidebar fields
    if ($old_fields['sidebar'] == $new_fields['sidebar']) {
      $update['sidebar'] = false;
    }
    else {
      $update['sidebar'] = $new_fields['sidebar'];
    }

    // compare the rss_channel fields
    if ($old_fields['rss_channel'] == $new_fields['rss_channel']) {
      $update['rss_channel'] = false;
    }
    else {
      $update['rss_channel'] = $new_fields['rss_channel'];
    }

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

    // compare the display fields
    if ($old_fields['display'] == $new_fields['display']) {
      $update['display'] = false;
    }
    else {
      $update['display'] = $new_fields['display'];
    }

    // compare the priority fields
    if ($old_fields['priority'] == $new_fields['priority']) {
      $update['priority'] = false;
    }
    else {
      $update['priority'] = $new_fields['priority'];
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