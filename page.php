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
require_once('includes/config_inc.php');
require_once('includes/order_inc.php');

// velidate browser requests for content
require_once('includes/validate_inc.php');
if (isset($_GET['content'])) {
  $_GET['content'] = gp_filter($_GET['content']);
}

if (isset($_GET['id'])) {
  $_GET['id'] = gp_filter($_GET['id']);
  $page_id = $_GET['id'];
}

// Main query
$query ="SELECT
           p.category_id,
           p.name,
           p.text,
           p.comments,
           m.title,
           m.keywords,
           m.display,
           m.rss_feed,
           m.last_updated
         FROM
           page p,
           meta_data m
         WHERE
           p.page_id = '$page_id'
         AND
           p.meta_data_id = m.meta_data_id";

if (!$result = mysql_query($query)) {
  // no page exists
  header('location: index.php');
  exit();
}
else {
  if (mysql_num_rows($result) < 1) {
    // still no page
    header('location: index.php');
    exit();
  }
  else {
    // page exists
    $category_id  = mysql_result($result, 0, 'category_id');
    $name         = mysql_result($result, 0, 'name');
    $text         = mysql_result($result, 0, 'text');
    $comments     = mysql_result($result, 0, 'comments');
    $title        = mysql_result($result, 0, 'title');
    $keywords     = mysql_result($result, 0, 'keywords');
    $display      = mysql_result($result, 0, 'display');
    $rss_feed     = mysql_result($result, 0, 'rss_feed');
    $last_updated = mysql_result($result, 0, 'last_updated');

    if ($display == 'show') {
      // format the last_updated date
      include_once('includes/time_inc.php');
      $long_date = date_format_long($last_updated);

      // begin breadcrumb trail of links.
      // can be turned off globally by setting $breadcrumb_display to false 
      // in includes/config_inc.php
      // turn them off locally by uncommenting the line below:
      //$breadcrumb_display = false;
      $breadcrumb = null;
      if ($breadcrumb_display) {
        // set a character or string to separate the breadcrumb links.
        $separator = ' > ';

        $query = "SELECT
                    c.cat_name,
                    c.category_id,
                    p.name
                  FROM
                    category c,
                    page p,
                    meta_data m
                  WHERE
                    p.page_id = '$page_id' 
                  AND
                    c.category_id = p.category_id
                  AND
                    m.display = 'show'";

        $result = mysql_query($query);

        $cat_name    = mysql_result($result, 0, 'cat_name');
        $category_id = mysql_result($result, 0, 'category_id');
        $page_name   = mysql_result($result, 0, 'name');

        $sub1 = '<a href="index.php">Home</a>';
        $sub2 = '<a href="category.php?id=' . $category_id . '">' . $cat_name . '</a>';

        $breadcrumb = $sub1 . $separator . $cat_name . $separator . $page_name;
      }

      // get meta data for the page header
      include_once('includes/header_inc.php');
      if (!isset($_GET['content'])) {
        $_GET['content'] = 'home';
      }
      if (!isset($_GET['id'])) {
        $_GET['id'] = null;
      }
      $meta_data = load_meta_data($_GET['content'], $page_id);

      // rss
      switch ($rss_feed) {
        case 'disable':
        default:
          $rss = null;
          break;
        case 'enable':
          // find out if the current category is specified as an rss channel
          $query = "SELECT rss_channel FROM category WHERE category_id = '$category_id'";
          $result = mysql_query($query);
          $rss = '<img src="images/feed-icon.gif">';
          break;
      }

      // activate the header template
      include_once('templates/' . $template_folder . '/header' . $template_ext);

      // activate the sidebar &gallery_menu
      include_once('includes/navigate_inc.php');
      $sidebar = print_sidebar();
      $galleries = print_gallery();
      $rss_icon = show_rss_icon($site);
      $show_cart_link = enable_ecommerce($site);

      include_once('templates/' . $template_folder . '/page' . $template_ext);

      // activate the footer template
      include_once('templates/' . $template_folder . '/footer' . $template_ext);
    }
    else {
      // hidden page - abort
      header('location: index.php');
      exit();
    }
  }
}

?>