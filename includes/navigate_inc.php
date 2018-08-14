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

// returns the current page number for an artwork_id
function get_page($artwork_id) {
  $id_map = array();
  $current_page = false;

  // find the gallery for this artwork
  $query = "SELECT a.gallery_id 
            FROM artwork a, meta_data m 
            WHERE a.artwork_id = '$artwork_id'
            AND a.meta_data_id = m.meta_data_id 
            AND m.display = 'show'";
  $result = mysql_query($query) or die(mysql_error());
  $gallery_id = mysql_result($result, 0, 'gallery_id');

  // get all the artworks in the gallery
  $query = "SELECT a.artwork_id 
            FROM artwork a, meta_data m 
            WHERE a.gallery_id = '$gallery_id'
            AND a.meta_data_id = m.meta_data_id 
            AND m.display = 'show'";
  $result = mysql_query($query) or die(mysql_error());

  $page_counter = 1;
  while ($row = mysql_fetch_assoc($result)) {
    foreach ($row as $key => $value) {
      // associate the id numbers with correct page numbers
      $id_map[$page_counter] = $value;
      $page_counter++;
    }
  }

  foreach ($id_map as $key => $value) {
    if ($value == $artwork_id) {
      $current_page = $key;
    }
  }

  return $current_page;
}

// returns pagination links for artworks
function artwork_pages($page, $artwork_id, $gallery_id) {
  // initialize vars
  $total_pages    = null;
  $format         = null;
  $pagenumber     = null;
  $sub1           = '&nbsp;<a class="paginate" href="artwork.php?id=';
  $sub2           = '&amp;page=';
  $sub3           = '">';
  $sub4           = '</a>&nbsp;';
  $left           = '';
  $middle         = '';
  $right          = '';
  $pagination_row = '';
  $id_map         = array();

  // count the artworks in the current gallery
  $query = "SELECT
              COUNT(a.artwork_id) AS num_rows
            FROM
              artwork a,
              meta_data m
            WHERE a.gallery_id = '$gallery_id'
            AND a.meta_data_id = m.meta_data_id
            AND m.display = 'show'";
  $result = mysql_query($query);
  $num_rows = mysql_result($result, 0, 'num_rows');

  // use one page for each artwork
  $total_pages = $num_rows;

  // get all the artwork_id's
  $query = "SELECT
              a.artwork_id
            FROM
              artwork a,
              meta_data m
            WHERE a.gallery_id = '$gallery_id'
            AND a.meta_data_id = m.meta_data_id
            AND m.display = 'show'";
  $result = mysql_query($query);

  // begin formatting links
  $page_counter = 1;
  while ($row = mysql_fetch_assoc($result)) {
    foreach ($row as $key => $value) {
      // associate the id numbers with correct page numbers
      $id_map[$page_counter] = $value;

      // format the middle part of $pagination_row
      if ($page == $page_counter) {
        $pagenumber = '&nbsp;' . $page_counter . '&nbsp;';
        $middle .= $pagenumber;
      }
      else {
        $middle .= $sub1 . $id_map[$page_counter] . $sub2 . $page_counter . $sub3 . $page_counter . $sub4;
      }
      $page_counter++;
    }
  }

  // format the left part of $pagination_row
  if ($page > 1) {
    $prev_page = $page - 1;
    foreach ($id_map as $key => $value) {
      if ($key == $prev_page) {
        $prev_id = $value;
      }
    }
    // This string prints out: "<<Prev "
    $format = '&lt;&lt;Prev&nbsp;';
    $left .= $sub1 . $prev_id . $sub2 . $prev_page . $sub3 . $format . $sub4;
  }
  else {
    $left .= '&lt;&lt;Prev&nbsp;';
  }

  // format the right part of $pagination_row
  if ($page < $total_pages) {
    $next_page = $page + 1;
    foreach ($id_map as $key => $value) {
      if ($key == $next_page) {
        $next_id = $value;
      }
    }
    // this string prints out: " Next>>"
    $format = '&nbsp;Next&gt;&gt;';
    $right .= $sub1 . $next_id . $sub2 . $next_page . $sub3 . $format . $sub4;
  }
  else {
    $right .= '&nbsp;Next&gt;&gt;';
  }

  $pagination_row = $left . $middle . $right;
  return $pagination_row;
}

// returns pagination links for a gallery
function gallery_pages($page, $gallery_id, $art_per_page) {

  // initialize vars
  $total_pages = null;
  $format = null;
  $pagenumber = null;
  $sub1 = ' <a class="paginate" href="gallery.php?id=' . $gallery_id . '&amp;page=';
  $sub2 = '">';
  $sub3 = '</a> ';
  $pagination_row = '';

  // count the pages
  $query = "SELECT 
              COUNT(a.artwork_id) AS num_rows
            FROM
              artwork a,
              meta_data m
            WHERE
              a.gallery_id = '$gallery_id'
            AND
              a.meta_data_id = m.meta_data_id
            AND
              m.display = 'show'";

  $result = mysql_query($query);
  $num_rows = mysql_result($result, 0, 'num_rows');

  // calculate total pages needed
  $total_pages = ceil($num_rows / $art_per_page);

  // begin formating links
  if ($page > 1) {
    $prev = $page - 1;
    $format = ' &lt;&lt;Prev ';
    $pagination_row .= $sub1 . $prev . $sub2 . $format . $sub3;
  }
  else {
    $pagination_row .= ' &lt;&lt;Prev ';
  }

  for ($i = 1; $i <= $total_pages; $i++) {
    if ($page == $i) {
      $pagenumber = ' ' . $i . ' ';
      $pagination_row .= $pagenumber;
    }
    else {
      $pagination_row .= $sub1 . $i . $sub2 . $i . $sub3;
    }
  }

  if ($page < $total_pages) {
    $next = $page + 1;
    $format = ' Next&gt;&gt; ';
    $pagination_row .= $sub1 . $next . $sub2 . $format . $sub3;
  }
  else {
    $pagination_row .= ' Next&gt;&gt; ';
  }

  return $pagination_row;
}

function show_rss_icon($site) {
  global $home_dir;
  switch ($site['rss']) {
    case 'disable':
      $feed_icon = null;
      break;
    case 'enable':
      $feed_icon = '            <table border="0" class="sidebar" cellpadding="4" cellspacing="0" width="152">' . "\n" .
                   '                <tr>' . "\n" .
                   '                    <th class="sidebar">' . "\n" .
                   '                        RSS Feed' . "\n" .
                   '                    </th>' . "\n" .
                   '                </tr>' . "\n" .
                   '                <tr>' . "\n" .
                   '                    <td class="sidebar">' . "\n" . 
                   '                        <p align="center"><a href="' . $site['url'] . '/' . $home_dir . '/rss.php"><img src="images/feed-icon.gif" width="16" height="16" border="0" alt="RSS feed available" title="RSS feed available"></a></p>' . "\n" .
                   '                    </td>' . "\n" .
                   '                </tr>' . "\n" .
                   '            </table>' . "\n";
      break;
  }
  return $feed_icon;
}

function find_rss_feed($meta_data) {
  global $meta_data, $site, $home_dir;
  if (!empty($meta_data['rss_feed']) && $meta_data['rss_feed'] == 'enable') {
    $feed = '            <table border="0" class="sidebar" cellpadding="4" cellspacing="0" width="152">' . "\n" .
            '                <tr>' . "\n" .
            '                    <th class="sidebar">' . "\n" .
            '                        RSS Feed' . "\n" .
            '                    </th>' . "\n" .
            '                </tr>' . "\n" .
            '                <tr>' . "\n" .
            '                    <td class="sidebar">' . "\n" . 
            '                        <p align="center"><a href="' . $site['url'] . '/' . $home_dir . '/rss.php?content=channel&amp;id=1"><img src="images/feed-icon.gif" width="16" height="16" border="0" alt="RSS feed available" title="RSS feed available"></a></p>' . "\n" .
            '                    </td>' . "\n" .
            '                </tr>' . "\n" .
            '            </table>' . "\n";
  }
  else {
    $feed = null;
  }
  return $feed;
}

function enable_ecommerce($site) {
  switch ($site['ecommerce']) {
    case 'disable':
      $show_cart_link = null;
      break;
    case 'enable':
      $cart_link = write_cart();
      $show_cart_link = '            <table border="0" class="sidebar" cellpadding="4" cellspacing="0" width="152">' . "\n" .
                        '                <tr>' . "\n" .
                        '                    <th class="sidebar">' . "\n" .
                        '                        Purchase' . "\n" .
                        '                    </th>' . "\n" .
                        '                </tr>' . "\n" .
                        '                <tr>' . "\n" .
                        '                    <td class="sidebar">' . "\n" .
                        '                        ' . $cart_link . "\n" .
                        '                    </td>' . "\n" .
                        '                </tr>' . "\n" .
                        '            </table>' . "\n";
      break;
  }
  return $show_cart_link;
}

function catwrapitem($item, $type) {
  // html wrapper for category items
  $html =  '                <tr>' . "\n" .
           '                    <td class="sidebar">' . "\n" .
           '                        <a href="' . $type . '.php?id=' . $item['id'] . '">' . $item['name'] . '</a>' . "\n" .
           '                    </td>' . "\n" .
           '                </tr>' . "\n";

  return $html;
}

function catwrap($cat_name, $html, $type) {
  // html wrapper for an entire category
  $catwrap = '            <table border="0" class="sidebar" cellpadding="4" cellspacing="0" width="152">' . "\n" .
             '                <tr>' . "\n" .
             '                    <th class="sidebar">' . "\n" .
             '                        ' . $cat_name . "\n" .
             '                    </th>' . "\n" .
             '                </tr>' . "\n" . $html .
             '            </table>' . "\n";

  return $catwrap;
}

// formats an HTML table of category and page links
function print_sidebar($column=null) {
  if (!isset($column)) {
    $column = 'one';
  }
  // get the categories and pages
  $query = "SELECT
              c.cat_name,
              a.name,
              a.page_id AS id
            FROM
              category c,
              page a,
              meta_data m
            WHERE
              a.meta_data_id = m.meta_data_id
            AND
              m.display = 'show'
            AND
              c.category_id = a.category_id
            AND
              c.sidebar = '$column'
            ORDER BY
              c.category_id, 
              m.priority DESC";

  $result = mysql_query($query);

  if (!$result) {
    $sidebar = '            <table border="0" class="sidebar" cellpadding="4" cellspacing="0" width="152">' . "\n" .
               '                <tr>' . "\n" .
               '                    <th class="sidebar">' . "\n" .
               '                        Pages' . "\n" . 
               '                    </th>' . "\n" .
               '                </tr>' . "\n" .
               '                <tr>' . "\n" .
               '                    <td class="sidebar">' . "\n" .
               '                        No Pages Found' . "\n" .
               '                    </td>' . "\n" .
               '                </tr>' . "\n" .
               '            </table>' . "\n";
  }
  else {
    $previous = null;
    $sidebar = '            <table border="0" class="sidebar" cellpadding="4" cellspacing="0" width="152">' . "\n";

    while ($row = mysql_fetch_array($result)) {
      if ($row['cat_name'] != $previous) {
        $sidebar .= '                <tr>' . "\n" .
                    '                    <th class="sidebar">' . "\n" .
                    '                        ' . $row['cat_name'] . "\n" .
                    '                    </th>' . "\n" .
                    '                </tr>' . "\n";
        $previous = $row['cat_name'];
      }
      elseif ($row['cat_name'] == 'None') {
        $previous = $row['cat_name'];
      }
      $sidebar .= '                <tr>' . "\n" .
                  '                    <td class="sidebar">' . "\n" .
                  '                        <a href="page.php?content=page&id=' . $row['id'] . '">' . $row['name'] . '</a>' . "\n" .
                  '                    </td>' . "\n" .
                  '                </tr>' . "\n";
    }
    $sidebar .= '            </table>' . "\n";
  }

  return $sidebar;
}

function print_gallery($column=null) {
  if (!isset($column)) {
    $column = 'one';
  }

  // get the gallories and pages
  $query = "SELECT
              c.cat_name,
              g.gallery_name,
              g.gallery_id AS id
            FROM
              category c,
              gallery g,
              meta_data m
            WHERE
              g.meta_data_id = m.meta_data_id
            AND
              m.display = 'show'
            AND
              c.category_id = g.category_id
            AND
              c.sidebar = '$column'
            ORDER BY
              g.gallery_id,
              m.priority DESC";

  $result = mysql_query($query);

  if (!$result) {
    $galleries = '            <table border="0" class="sidebar" cellpadding="4" cellspacing="0" width="152">' . "\n" .
                 '                <tr>' . "\n" .
                 '                    <th class="sidebar">' . "\n" .
                 '                        Galleries' . "\n" . 
                 '                    </th>' . "\n" .
                 '                </tr>' . "\n" .
                 '                <tr>' . "\n" .
                 '                    <td class="sidebar">' . "\n" .
                 '                        No Galleries Found' . "\n" .
                 '                    </td>' . "\n" .
                 '                </tr>' . "\n" .
                 '            </table>' . "\n";
  }
  else {
    $previous = null;
    $galleries = '            <table border="0" class="sidebar" cellpadding="4" cellspacing="0" width="152">' . "\n";

    while ($row = mysql_fetch_array($result)) {
      if ($row['cat_name'] != $previous) {
        $galleries .= '                <tr>' . "\n" .
                      '                    <th class="sidebar">' . "\n" .
                      '                        ' . $row['cat_name'] . "\n" .
                      '                    </th>' . "\n" .
                      '                </tr>' . "\n";
        $previous = $row['cat_name'];
      }
      elseif ($row['cat_name'] == 'None') {
        $previous = $row['cat_name'];
      }
      $galleries .= '                <tr>' . "\n" .
                    '                    <td class="sidebar">' . "\n" .
                    '                        <a href="gallery.php?id=' . $row['id'] . '">' . $row['gallery_name'] . '</a>' . "\n" .
                    '                    </td>' . "\n" .
                    '                </tr>' . "\n";
    }
    $galleries .= '            </table>' . "\n";
  }

  return $galleries;
}

// This function is for compatibility with PHP 4.x's realpath()
// function.  In later versions of PHP, it needs to be called
// to do checks with some functions.  Older versions of PHP don't
// seem to need this, so we'll just return the original value.
// dougk_ff7 <October 5, 2002>
function exhibition_realpath($path) {
  global $ex_root_path, $php_ext;

  return (!@function_exists('realpath') || !@realpath($ex_root_path . 'includes/config_inc.' . $php_ext)) ? $path : @realpath($path);
}

?>