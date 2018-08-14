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


function rss_select($choice=null) {
  switch ($choice) {
    case 'no':
      $yes = null;
      $no  = ' checked="checked"';
    default:
      break;
    case 'yes':
      $yes = ' checked="checked"';
      $no  = null;
      break;
  }
  $select_box = '<input type="radio" name="rss_channel" value="yes"' . $yes . '>Yes' . "\n" .
                '<input type="radio" name="rss_channel" value="no"' . $no . '>No' . "\n";
  return $select_box;
}
/*
function channel_selectbox($channel_id=null) {
  $query = "SELECT channel_name, channel_id FROM rss_channel";
  $result = mysql_query($query);
  $select_box = '<select name="channel_id">' . "\n";
  if ($result) {
    if (mysql_num_rows($result) < 1) {
      // no channels found
      $select_box .= '<option value="0">None</option>' . "\n";
    }
    // channels found
    $select_box .= '<option value="0">None</option>' . "\n";
    while ($row = mysql_fetch_assoc($result)) {
      if ($row['channel_id'] == $channel_id) {
        $selected = ' selected';
      }
      else {
        $selected = null;
      }
      $select_box .= '<option value="' . $row['channel_id'] . '"' . $selected . '>' . $row['channel_name'] . '</option>' . "\n";
    }
  }
  else {
    // no categories found
    $select_box .= '<option value="0">None</option>' . "\n";
  }
  $select_box .= '</select>' . "\n";
  return $select_box;
}
*/
function gallery_cat_selectbox($category_id=null) {
  $query = "SELECT category_id, cat_name FROM category WHERE cat_type = 'gallery'";
  $result = mysql_query($query);
  $select_box = '<select name="category_id">' . "\n";
  if ($result) {
    if (mysql_num_rows($result) < 1) {
      // no categories found
      $select_box .= '<option value="0">Galleries</option>' . "\n";
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
    $select_box .= '<option value="0">Galleries</option>' . "\n";
  }
  $select_box .= '</select>' . "\n";
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

// compare elements of 2 arrays and return the difference
function gallery_fields_diff() {

  // get the old fields from mysql
  $query = "SELECT
              g.category_id,
              m.title,
              g.gallery_name,
              m.display,
              g.art_per_page,
              g.art_per_row,
              g.thumbnail_max,
              g.gallery_icon,
              m.priority,
              g.rss_channel,
              m.description,
              m.keywords
            FROM
              gallery g,
              meta_data m
            WHERE
              g.gallery_id = $_SESSION[gallery_id]
            AND
              g.meta_data_id = m.meta_data_id";

  $result = mysql_query($query);

  // load all current/new fields into an array
  $new_fields = array('category_id'   => $_SESSION['category_id'],
                      'title'         => $_SESSION['title'],
                      'gallery_name'  => $_SESSION['gallery_name'],
                      'display'       => $_SESSION['display'],
                      'art_per_page'  => $_SESSION['art_per_page'],
                      'art_per_row'   => $_SESSION['art_per_row'],
                      'thumbnail_max' => $_SESSION['thumbnail_max'],
                      'gallery_icon'  => $_SESSION['gallery_icon'],
                      'priority'      => $_SESSION['priority'],
                      'rss_channel'   => $_SESSION['rss_channel'],
                      'description'   => $_SESSION['description'],
                      'keywords'      => $_SESSION['keywords']);

  if ($old_fields = mysql_fetch_assoc($result)) {
    // compare the category_id fields
    if ($old_fields['category_id'] == $new_fields['category_id']) {
      $update['category_id'] = false;
    }
    else {
      $update['category_id'] = $new_fields['category_id'];
    }

    // compare the title fields
    if ($old_fields['title'] == $new_fields['title']) {
      $update['title'] = false;
    }
    else {
      $update['title'] = $new_fields['title'];
    }

    // compare the gallery name fields
    if ($old_fields['gallery_name'] == $new_fields['gallery_name']) {
      $update['gallery_name'] = false;
    }
    else {
      $update['gallery_name'] = $new_fields['gallery_name'];
    }

    // compare the display fields
    if ($old_fields['display'] == $new_fields['display']) {
      $update['display'] = false;
    }
    else {
      $update['display'] = $new_fields['display'];
    }

    // compare the art_per_page fields
    if ($old_fields['art_per_page'] == $new_fields['art_per_page']) {
      $update['art_per_page'] = false;
    }
    else {
      $update['art_per_page'] = $new_fields['art_per_page'];
    }

    // compare the art_per_row fields
    if ($old_fields['art_per_row'] == $new_fields['art_per_row']) {
      $update['art_per_row'] = false;
    }
    else {
      $update['art_per_row'] = $new_fields['art_per_row'];
    }

    // compare the thumbnail_max fields
    if ($old_fields['thumbnail_max'] == $new_fields['thumbnail_max']) {
      $update['thumbnail_max'] = false;
    }
    else {
      $update['thumbnail_max'] = $new_fields['thumbnail_max'];
    }

    // compare the gallery_icon fields
    if ($old_fields['gallery_icon'] == $new_fields['gallery_icon']) {
      $update['gallery_icon'] = false;
    }
    else {
      $update['gallery_icon'] = $new_fields['gallery_icon'];
    }

    // compare the priority fields
    if ($old_fields['priority'] == $new_fields['priority']) {
      $update['priority'] = false;
    }
    elseif ($old_fields['priority'] != $new_fields['priority']) {
      $update['priority'] = $new_fields['priority'];
    }

    // compare the rss_channel fields
    if ($old_fields['rss_channel'] == $new_fields['rss_channel']) {
      $update['rss_channel'] = false;
    }
    elseif ($old_fields['rss_channel'] != $new_fields['rss_channel']) {
      $update['rss_channel'] = $new_fields['rss_channel'];
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

// update the database with data for new gallery
function create_gallery($now) {
  global $site, $home_dir;
  // we need a new meta_data_id, so enter meta_data first
  $query = "INSERT INTO meta_data (meta_data_id, 
                                   title,
                                   display,
                                   priority,
                                   rss_feed,
                                   description,
                                   keywords,
                                   pub_date,
                                   last_updated)
            VALUES ('',
                    '$_SESSION[title]',
                    '$_SESSION[display]',
                    '$_SESSION[priority]',
                    'disable',
                    '$_SESSION[description]',
                    '$_SESSION[keywords]',
                    '$now',
                    '$now')";

  $result = mysql_query($query);
  if ($result) {
    // get the newly created meta id
    $query = "SELECT meta_data_id FROM meta_data WHERE title = '$_SESSION[title]'";
    $result = mysql_query($query);
    $meta_data_id = mysql_result($result, 0, 'meta_data_id');

    // insert new gallery details into database
    $query = "INSERT INTO gallery (gallery_id,
                                   category_id,
                                   gallery_name,
                                   art_per_page,
                                   art_per_row,
                                   thumbnail_max,
                                   rss_channel,
                                   gallery_icon,
                                   meta_data_id)
              VALUES ('',
                      '$_SESSION[category_id]',
                      '$_SESSION[gallery_name]',
                      '$_SESSION[art_per_page]',
                      '$_SESSION[art_per_row]',
                      '$_SESSION[thumbnail_max]',
                      '$_SESSION[rss_channel]',
                      '$_SESSION[gallery_icon]',
                      '$meta_data_id')";

    $results = mysql_query($query);
  }
  if ($results) {
    // now we have both gallery and meta_data id's we need to
    // create a url for the gallery page and update the meta_data with it
    $query = "SELECT gallery_id FROM gallery WHERE meta_data_id = $meta_data_id";
    $result = mysql_query($query);
    $gallery_id = mysql_result($result, 0, 'gallery_id');

    // add link to the meta_data table
    $link = $site['url'] . '/' . $home_dir . '/gallery.php?content=gallery&amp;id=' . $gallery_id;
    $query = "UPDATE meta_data SET link = '$link' WHERE meta_data_id = $meta_data_id";
    $result = mysql_query($query);

    return true;
  }
  else {
    return false;
  }
}

?>