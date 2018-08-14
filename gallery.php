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

// start the session and connect to the database
require_once('includes/config_inc.php');
require_once('includes/order_inc.php');

// validate browser requests for content
require_once('includes/validate_inc.php');
if (isset($_GET['content'])) {
  $content = gp_filter($_GET['content']);
}
else {
  $content = 'gallery';
}
if (isset($_GET['id'])) {
  $gallery_id = gp_filter($_GET['id']);
}
else {
  $gallery_id = 1;
}
if (isset($_GET['page'])) {
  $page = gp_filter($_GET['page']);
}
else {
  $page = 1;
}

// get data for the page header
require_once('includes/header_inc.php');
$meta_data = load_meta_data($content, $gallery_id);

// get the gallery info
$query = "SELECT
            g.gallery_name,
            g.art_per_page,
            g.art_per_row,
            g.thumbnail_max
          FROM
            gallery g,
            meta_data m
          WHERE
            g.gallery_id = '$gallery_id'
          AND
            m.display = 'show'";

$result = mysql_query($query);
while ($row = mysql_fetch_array($result)) {
  $gallery_name  = $row['gallery_name'];
  $art_per_page  = $row['art_per_page'];
  $art_per_row   = $row['art_per_row'];
  $thumbnail_max = $row['thumbnail_max'];

  // begin pagination
  include_once('includes/navigate_inc.php');

  // calculate the page offset for mysql
  $offset = (($page * $art_per_page) - $art_per_page);

  // may need a mysql limit clause for selecting artworks
  if ($page > 0) {
    $limit = " LIMIT $offset, $art_per_page";
  }
  else {
    $limit = null;
  }

  // the gallery_pages() function loads all the page links into a variable
  $pagination = gallery_pages($page, $gallery_id, $art_per_page);

  // begin breadcrumb trail of links.
  // can be turned off globally by setting $breadcrumb_display to false 
  // in includes/config_inc.php
  // turn them off locally by uncommenting the line below:
  //$breadcrumb_display = false;
  $breadcrumb = null;
  if ($breadcrumb_display) {
    // set a character or string to separate the breadcrumb links.
    // some chars work better than others. experiment.
    $separator = ' > ';
    $breadcrumb = '<a href="index.php">Home</a>' . $separator . $gallery_name;
  }
  else {
    $breadcrumb = null;
  }

  // begin display of artworks
  $table = null;
  include_once('includes/image_inc.php');

  $query = "SELECT
              m.title,
              a.artwork_id,
              a.thumbnail
            FROM
              artwork a,
              meta_data m
            WHERE
              a.gallery_id = '$gallery_id'
            AND
              a.meta_data_id = m.meta_data_id
            AND
              m.display = 'show'
            $limit";

  $art_result = mysql_query($query) or die(mysql_error());
  if (!mysql_num_rows($art_result) < 1) {
    // artwork found
    $count = mysql_num_rows($art_result);
    $new_row = 0;
    $table = '<table bgcolor="#ffffff" border="0" cellpadding="13" cellspacing="5">' . "\n";
    $i = 0;
    while ($art_row = mysql_fetch_assoc($art_result)) {
      if ($i == 0 || $i == $new_row) {
        $table .= '    <tr>' . "\n";
        $new_row = $new_row + $art_per_row;
        $end = $new_row - 1;
        if ($end > $count || $end == $count) {
          $end = $count - 1;
        } 
      }
      // the gallery_icon function wraps thumbnails into <td> tags
      $table .= gallery_icon($thumbnail_max, $art_row['thumbnail'], $art_row['artwork_id'], $art_row['title']);
      //$table .= '<td><a href="artwork.php?id=' . $art_row['artwork_id'] . '">' . $art_row['title'] . '</a></td>' . "\n";
      if ($i == $end || $i > $end) {
        $table .= '    </tr>' . "\n";
      }
      $i++;
    }
    $table .= '</table>' . "\n";
  }
}

// activate the header template
require_once('templates/' . $template_folder . '/header' . $template_ext);

// load the sidebar & gallery_menu
$sidebar      = print_sidebar();
$galleries = print_gallery();
$rss_icon = show_rss_icon($site);
$show_cart_link = enable_ecommerce($site);

// activate the gallery template
require_once('templates/' . $template_folder . '/gallery' . $template_ext);

// activate the footer template
require_once('templates/' . $template_folder . '/footer' . $template_ext);

?>
