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

// creates html form fields to inter a year, month and day
function date_completed($year=null, $month=null, $day=null) {
  $year = 'Year: <input type="text" name="year_completed" size="4" maxlength="4">' . "\n";

  $month = array('Unknown'   => '00',
                 'January'   => '01',
                 'February'  => '02',
                 'March'     => '03',
                 'April'     => '04',
                 'May'       => '05',
                 'June'      => '06',
                 'July'      => '07',
                 'August'    => '08',
                 'September' => '09',
                 'October'   => '10',
                 'November'  => '11',
                 'December'  => '12');

  $month_input = 'Month: <select name="month_completed">' . "\n";
  foreach ($month AS $key => $value) {
    $month_input .= '    <option value="' . $value . '">' . $key . '</option>' . "\n";
  }
  $month_input .= '</select>' . "\n";

  $day = array('00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12',
               '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25',
               '26', '27', '28', '29', '30', '31');

  $day_input = 'Day: <select name="day_completed">' . "\n";
  foreach ($day AS $value) {
    $day_input .= '    <option value="' . $value . '">' . $value . '</option>' . "\n";
  }
  $day_input .= '</select>' . "\n";

  $selectbox = $year . $month_input . $day_input;

  return $selectbox;
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

// creates an html select box for choosing a listing type
function type_selectbox($type=null) {
  switch ($type) {
    case 'sale':
    default:
      $sel_sale = ' checked="checked"';
      $sel_show = null;
      break;
    case 'exhibit':
      $sel_sale = null;
      $sel_show = ' checked="checked"';
      break;
  }
  $select_box = '    <input type="radio" name="type" value="sale"' . $sel_sale . '>For Sale' . "\n" .
                '    <input type="radio" name="type" value="exhibit"' . $sel_show . '>Exhibit Only' . "\n" ;
  return $select_box;
}

// creates an html select box for choosing a listing status
function status_selectbox($status=null) {
  switch ($status) {
    case 'available':
    default:
      $sel_avai = ' checked="checked"';
      $sel_pend = null;
      $sel_sold = null;
      break;
    case 'pending':
      $sel_avai = null;
      $sel_pend = ' checked="checked"';
      $sel_sold = null;
      break;
    case 'sold':
      $sel_avai = null;
      $sel_pend = null;
      $sel_sold = ' checked="checked"';
      break;
  }
  $select_box = '    <input type="radio" name="status" value="available"' . $sel_avai . '>Available' . "\n" .
                '    <input type="radio" name="status" value="pending"' . $sel_pend . '>Pending</option>' . "\n" .
                '    <input type="radio" name="status" value="sold"' . $sel_sold . '>Sold</option>' . "\n";
  return $select_box;
}

function artist_selectbox($artist_id=null) {
  $query = "SELECT artist_id, name FROM artist";
  $result = mysql_query($query);
  $select_box = '<select name="artist_id">' . "\n";
  if ($result) {
    while ($row = mysql_fetch_assoc($result)) {
      if ($row['artist_id'] == $artist_id) {
        $selected = ' selected';
      }
      else {
        $selected = null;
      }
      $select_box .= '<option value="' . $row['artist_id'] . '"' . $selected . '>' . $row['name'] . '</option>' . "\n";
    }
  }
  else {
    $select_box .= '<option value="5">Unknown</option>' . "\n";
  }
  $select_box .= '</select>' . "\n";
  return $select_box;
}

// creates an html select box for choosing a parent gallery
function gallery_selectbox($gallery_id=null) {
  global $row;
  $query = "SELECT gallery_id, gallery_name FROM gallery";
  $result = mysql_query($query);
  $select_box = '<select name="gallery_id">' . "\n";
  if ($result) {
    while ($row = mysql_fetch_assoc($result)) {
      // remember the current gallery selection
      if ($row['gallery_id'] == $gallery_id) {
        $selected = ' selected';
      }
      else {
        $selected = null;
      }
      $select_box .= '<option value="' . $row['gallery_id'] . '"' . $selected . '>' . $row['gallery_name'] . '</option>' . "\n";
    }
  }
  else {
    $select_box .= '<option value="0">No Galleries Exist</option>' . "\n";
  }
  $select_box .= '</select>' . "\n";
  return $select_box;
}

function create_artwork($now) {
  // we need a new meta_data_id, so enter meta_data first
  $query = "INSERT INTO meta_data (meta_data_id, 
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
    // get the newly created meta id
    $query = "SELECT meta_data_id FROM meta_data WHERE title = '$_SESSION[title]'";
    $result = mysql_query($query);
    $meta_data_id = mysql_result($result, 0, 'meta_data_id');

    // insert new artwork details into database
    $query = "INSERT INTO artwork (artwork_id,
                                   thumbnail,
                                   type,
                                   artist,
                                   medium,
                                   size,
                                   style,
                                   subject,
                                   price,
                                   sale_amount,
                                   sale_date,
                                   date_completed,
                                   paypal_button,
                                   google_button,
                                   status,
                                   comments,
                                   gallery_id,
                                   meta_data_id)
              VALUES ('',
                      '$_SESSION[thumbnail]',
                      '$_SESSION[type]',
                      '$_SESSION[artist]',
                      '$_SESSION[medium]',
                      '$_SESSION[size]',
                      '$_SESSION[style]',
                      '$_SESSION[subject]',
                      '$_SESSION[price]',
                      '0.00',
                      '0000-00-00 00:00:00',
                      '$_SESSION[date_completed]',
                      '$_SESSION[paypal_button]',
                      '$_SESSION[google_button]',
                      '$_SESSION[status]',
                      '$_SESSION[comments]',
                      '$_SESSION[gallery_id]',
                      '$meta_data_id')";

    $results = mysql_query($query);
  }
  if ($results) {
    // now we have both artwork and meta_data id's we need to
    // create a url for the artwork page and update the meta_data with it
    $query = "SELECT artwork_id FROM artwork WHERE meta_data_id = $meta_data_id";
    $result = mysql_query($query);
    $artwork_id = mysql_result($result, 0, 'artwork_id');

    // add link to the meta_data table
    $link = $site['url'] . '/' . $home_dir . '/artwork.php?content=artwork&amp;id=' . $artwork_id;
    $query = "UPDATE meta_data SET link = '$link' WHERE meta_data_id = $meta_data_id";
    $result = mysql_query($query);

    return $artwork_id;
  }
  else {
    return false;
  }
}

function artwork_fields_diff() {
  // get the old fields from mysql
  $query = "SELECT
              a.gallery_id,
              m.title,
              a.thumbnail,
              m.display,
              a.type,
              a.date_completed,
              a.status,
              a.qty_instock,
              a.price,
              a.shipping,
              a.handling,
              m.priority,
              a.comments,
              m.rss_feed,
              a.artist_id,
              a.medium,
              a.size,
              a.style,
              a.subject,
              m.description,
              m.keywords
            FROM
              artwork a,
              meta_data m
            WHERE
              a.artwork_id = $_SESSION[artwork_id]
            AND
              a.meta_data_id = m.meta_data_id";
  $result = mysql_query($query);
  // load all current/new fields into an array
  $new_fields = array('gallery_id'     => $_SESSION['gallery_id'],
                      'title'          => $_SESSION['title'],
                      'thumbnail'      => $_SESSION['thumbnail'],
                      'display'        => $_SESSION['display'],
                      'type'           => $_SESSION['type'],
                      'date_completed' => $_SESSION['date_completed'],
                      'status'         => $_SESSION['status'],
                      'qty_instock'    => $_SESSION['qty_instock'],
                      'price'          => $_SESSION['price'],
                      'shipping'       => $_SESSION['shipping'],
                      'handling'       => $_SESSION['handling'],
                      'priority'       => $_SESSION['priority'],
                      'comments'       => $_SESSION['comments'],
                      'rss_feed'       => $_SESSION['rss_feed'],
                      'artist_id'      => $_SESSION['artist_id'],
                      'medium'         => $_SESSION['medium'],
                      'size'           => $_SESSION['size'],
                      'style'          => $_SESSION['style'],
                      'subject'        => $_SESSION['subject'],
                      'description'    => $_SESSION['description'],
                      'keywords'       => $_SESSION['keywords']);

  if ($old_fields = mysql_fetch_assoc($result)) {
    // compare the gallery_id fields
    if ($old_fields['gallery_id'] == $new_fields['gallery_id']) {
      $update['gallery_id'] = false;
    }
    else {
      $update['gallery_id'] = $new_fields['gallery_id'];
    }

    // compare the title fields
    if ($old_fields['title'] == $new_fields['title']) {
      $update['title'] = false;
    }
    else {
      $update['title'] = $new_fields['title'];
    }

    // compare the thumbnail fields
    if ($old_fields['thumbnail'] == $new_fields['thumbnail']) {
      $update['thumbnail'] = false;
    }
    else {
      $update['thumbnail'] = $new_fields['thumbnail'];
    }

    // compare the display fields
    if ($old_fields['display'] == $new_fields['display']) {
      $update['display'] = false;
    }
    else {
      $update['display'] = $new_fields['display'];
    }

    // compare the type fields
    if ($old_fields['type'] == $new_fields['type']) {
      $update['type'] = false;
    }
    else {
      $update['type'] = $new_fields['type'];
    }

    // compare the date_completed fields
    if ($old_fields['date_completed'] == $new_fields['date_completed']) {
      $update['date_completed'] = false;
    }
    else {
      $update['date_completed'] = $new_fields['date_completed'];
    }

    // compare the status fields
    if ($old_fields['status'] == $new_fields['status']) {
      $update['status'] = false;
    }
    else {
      $update['status'] = $new_fields['status'];
    }

    // compare the qty_instock fields
    if ($old_fields['qty_instock'] == $new_fields['qty_instock']) {
      $update['qty_instock'] = false;
    }
    else {
      $update['qty_instock'] = $new_fields['qty_instock'];
    }

    // compare the price fields
    if ($old_fields['price'] == $new_fields['price']) {
      $update['price'] = false;
    }
    else {
      $update['price'] = $new_fields['price'];
    }

    // compare the shipping fields
    if ($old_fields['shipping'] == $new_fields['shipping']) {
      $update['shipping'] = false;
    }
    else {
      $update['shipping'] = $new_fields['shipping'];
    }

    // compare the handling fields
    if ($old_fields['handling'] == $new_fields['handling']) {
      $update['handling'] = false;
    }
    else {
      $update['handling'] = $new_fields['handling'];
    }

    // compare the priority fields
    if ($old_fields['priority'] == $new_fields['priority']) {
      $update['priority'] = false;
    }
    else {
      $update['priority'] = $new_fields['priority'];
    }

    // compare the comments fields
    if ($old_fields['comments'] == $new_fields['comments']) {
      $update['comments'] = false;
    }
    else {
      $update['comments'] = $new_fields['comments'];
    }

    // compare the rss_feed fields
    if ($old_fields['rss_feed'] == $new_fields['rss_feed']) {
      $update['rss_feed'] = false;
    }
    else {
      $update['rss_feed'] = $new_fields['rss_feed'];
    }

    // compare the artist fields
    if ($old_fields['artist_id'] == $new_fields['artist_id']) {
      $update['artist_id'] = false;
    }
    else {
      $update['artist_id'] = $new_fields['artist_id'];
    }

    // compare the medium fields
    if ($old_fields['medium'] == $new_fields['medium']) {
      $update['medium'] = false;
    }
    else {
      $update['medium'] = $new_fields['medium'];
    }

    // compare the size fields
    if ($old_fields['size'] == $new_fields['size']) {
      $update['size'] = false;
    }
    else {
      $update['size'] = $new_fields['size'];
    }

    // compare the style fields
    if ($old_fields['style'] == $new_fields['style']) {
      $update['style'] = false;
    }
    else {
      $update['style'] = $new_fields['style'];
    }

    // compare the subject fields
    if ($old_fields['subject'] == $new_fields['subject']) {
      $update['subject'] = false;
    }
    else {
      $update['subject'] = $new_fields['subject'];
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

?>