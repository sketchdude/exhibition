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
require_once('includes/time_inc.php');
require_once('includes/navigate_inc.php');
require_once('includes/validate_inc.php');
require_once('includes/header_inc.php');
require_once('includes/order_inc.php');

$content    = gp_filter($_GET['content']);
$artwork_id = gp_filter($_GET['id']);

if (isset($_GET['page'])) {
  $page = gp_filter($_GET['page']);
}
else {
  $page = get_page($artwork_id);
}

$meta_data = load_meta_data($content, $artwork_id);

// type determines whether or not to show any sale info 
$query = "SELECT type FROM artwork WHERE artwork_id = '$artwork_id'";
$result = mysql_query($query);
$type = mysql_result($result, 0, 'type');

// $options is part of the database query.
// the option values are set in includes/config_inc.php
$options = '';
if (isset($artwork['artist']) && $artwork['artist'] == 'show') {
  $options .= 'a.artist_id,';
}
if (isset($artwork['medium']) && $artwork['medium'] == 'show') {
  $options .= 'a.medium,';
}
if (isset($artwork['size']) && $artwork['size'] == 'show') {
  $options .= 'a.size,';
}
if (isset($artwork['style']) && $artwork['style'] == 'show') {
  $options .= 'a.style,';
}
if (isset($artwork['subject']) && $artwork['subject'] == 'show') {
  $options .= 'a.subject,';
}
if (isset($artwork['price']) && $artwork['price'] == 'show') {
  if ($type == 'sale') {
    $options .= 'a.price,';
  }
}
if (isset($artwork['shipping']) && $artwork['shipping'] == 'show') {
  if ($type == 'sale') {
    $options .= 'a.shipping,';
  }
}
if (isset($artwork['handling']) && $artwork['handling'] == 'show') {
  if ($type == 'sale') {
    $options .= 'a.handling,';
  }
}
if (isset($artwork['status']) && $artwork['status'] == 'show') {
  if ($type == 'sale') {
    $options .= 'a.status,';
  }
}
if (isset($artwork['qty_instock']) && $artwork['qty_instock'] == 'show') {
  if ($type == 'sale') {
    $options .= 'a.qty_instock,';
  }
}

// first get any extra images for this artwork
$query = "SELECT path, caption FROM image WHERE artwork_id = '$artwork_id'";
$result = mysql_query($query);
if ($result) {
  include_once('includes/image_inc.php');
  $image_rows = '<table width="100%" border="0">' . "\n";
  while ($images = mysql_fetch_array($result)) {
    $image = print_image();
    $image_rows .= '    <tr>' . "\n" .
                   '        <td align="center">' . "\n" .
                   '            ' . $image . "\n" .
                   '        </td>' . "\n" .
                   '    </tr>' . "\n" .
                   '    <tr>' . "\n" .
                   '        <td height="50" align="center" valign="top">' . "\n" .
                   '            ' . $images['caption'] . "\n" .
                   '        </td>' . "\n" .
                   '    </tr>' . "\n";
  }
  $image_rows .= '</table>' . "\n";
}

if (!empty($image_rows)) {
  $extra_images = $image_rows;
}

// initialize the comments variable
$comments = 'disable';

// now get main artwork info
$query = "SELECT
            m.title,
            $options
            a.gallery_id,
            a.thumbnail,
            a.comments,
            x.name
          FROM
            meta_data m,
            artwork a,
            artist x
          WHERE
            a.artwork_id = '$artwork_id'
          AND
            a.artist_id = x.artist_id
          AND
            m.meta_data_id = a.meta_data_id
          AND
            m.display = 'show'";

$result = mysql_query($query);
while ($row = mysql_fetch_array($result)) {
  $title       = $row['title'];
  $thumbnail   = $row['thumbnail'];
  if (isset($row['name'])) {
    $artist = $row['name'];
  }
  else {
    $artist = null;
  }
  if (isset($row['medium'])) {
    $medium = $row['medium'];
  }
  else {
    $medium = null;
  }
  if (isset($row['size'])) {
    $size = $row['size'];
  }
  else {
    $size = null;
  }
  if (isset($row['style'])) {
    $style = $row['style'];
  }
  else {
    $style = null;
  }
  if (isset($row['subject'])) {
    $subject = $row['subject'];
  }
  else {
    $subject = null;
  }
  if (isset($row['price'])) {
    $price = $row['price'];
  }
  else {
    $price = null;
  }
  if (isset($row['shipping'])) {
    $shipping = $row['shipping'];
  }
  else {
    $shipping = null;
  }
  if (isset($row['handling'])) {
    $handling = $row['handling'];
  }
  else {
    $handling = null;
  }
  if (isset($row['status'])) {
    $status = $row['status'];
  }
  else {
    $status = null;
  }
  $gallery_id  = $row['gallery_id'];
  $box = '';
  if (isset($artist) && (strlen($artist) > 0) || isset($medium) && (strlen($medium) > 0) || isset($size) && (strlen($size) > 0) || isset($style) && (strlen($style) > 0) || isset($subject) && (strlen($subject) > 0) || isset($price) && (strlen($price) > 0) || isset($status) && (strlen($status) > 0)) {
    $box .= '<table border="0" id="infobox" cellpadding="4" cellspacing="2">' . "\n" .
            '                                        <tr>' . "\n" .
            '                                            <th colspan="2">' . "\n" .
            '                                                ' . $title . "\n" .
            '                                            </th>' . "\n" .
            '                                        </tr>' . "\n";
    foreach ($artwork AS $key => $value) {
      if ($key == 'artist' && $value == 'show') {
        $box .= '                                        <tr>' . "\n" .
                '                                            <td align="right">' . "\n" .
                '                                                Artist: ' . "\n" .
                '                                            </td>' . "\n" .
                '                                            <td>' . "\n" .
                '                                                ' . $artist . "\n" .
                '                                            </td>' . "\n" .
                '                                        </tr>' . "\n";
      }
      if ($key == 'medium' && $value == 'show') {
        $box .= '                                        <tr>' . "\n" .
                '                                            <td align="right">' . "\n" .
                '                                                Medium: ' . "\n" .
                '                                            </td>' . "\n" .
                '                                            <td>' . "\n" .
                '                                                ' . $medium . "\n" .
                '                                            </td>' . "\n" .
                '                                        </tr>' . "\n";
      }
      if ($key == 'size' && $value == 'show') {
        $box .= '                                        <tr>' . "\n" .
                '                                            <td align="right">' . "\n" .
                '                                                Dimensions: ' . "\n" .
                '                                            </td>' . "\n" .
                '                                            <td>' . "\n" .
                '                                                ' . $size . "\n" .
                '                                            </td>' . "\n" .
                '                                        </tr>' . "\n";
      }
      if ($key == 'style' && $value == 'show') {
        $box .= '                                        <tr>' . "\n" .
                '                                            <td align="right">' . "\n" .
                '                                                Style: ' . "\n" .
                '                                            </td>' . "\n" .
                '                                            <td>' . "\n" .
                '                                                ' . $style . "\n" .
                '                                            </td>' . "\n" .
                '                                        </tr>' . "\n";
      }
      if ($key == 'subject' && $value == 'show') {
        $box .= '                                        <tr>' . "\n" .
                '                                            <td align="right">' . "\n" .
                '                                                Subject: ' . "\n" .
                '                                            </td>' . "\n" .
                '                                            <td>' . "\n" .
                '                                                ' . $subject . "\n" .
                '                                            </td>' . "\n" .
                '                                        </tr>' . "\n";
      }
      if ($key == 'status' && $value == 'show') {
        if ($type == 'sale') {
          $pricelist = null;
          switch ($status) {
            case 'available':
              $status_img = 'status_available.jpg';
              $blurb = 'Available for purchase';
              $shipping_handling = $shipping + $handling;
              $shipping_handling = number_format($shipping_handling, 2, '.', '');
              $total = $price + $shipping_handling;
              $total = number_format($total, 2, '.', '');
              $pricelist = '<a href="pricelist.php">View Price List</a>' . "\n";
              $purchase_button = '<a href="order.php?act=add&artwork_id=' . $artwork_id . '">Add to Order</a>' . "\n";
              break;
            case 'pending':
              $status_img = 'status_pending.jpg';
              $blurb = 'Pending payment acceptance';
              $shipping_handling = null;
              $total = null;
              $purchase_button = null;
              break;
            case 'sold':
              $status_img = 'status_sold.jpg';
              $blurb = 'Sold';
              $shipping_handling = null;
              $total = null;
              $pricelist = '<a href="pricelist.php">View Price List</a>' . "\n";
              $purchase_button = null;
              break;
          }

          $box .= '                                        <tr>' . "\n" .
                  '                                            <td align="right">' . "\n" .
                  '                                                Status: ' . "\n" .
                  '                                            </td>' . "\n" .
                  '                                            <td>' . "\n" .
                  '                                                <img src="images/' . $status_img . '"> &nbsp;' . $blurb . "\n" .
                  '                                            </td>' . "\n" .
                  '                                        </tr>' . "\n";
        }
      }
      if ($key == 'price' && $value == 'show') {
        if ($type == 'sale') {
          $box .= '                                        <tr>' . "\n" .
                  '                                            <td align="right">' . "\n" .
                  '                                                Price: ' . "\n" .
                  '                                            </td>' . "\n" .
                  '                                            <td>' . "\n" .
                  '                                                ' . $site['currency'] . $price . "\n" .
                  '                                            </td>' . "\n" .
                  '                                        </tr>' . "\n" .
                  '                                        <tr>' . "\n" .
                  '                                            <td align="right">' . "\n" .
                  '                                                Shipping & Handling: ' . "\n" .
                  '                                            </td>' . "\n" .
                  '                                            <td>' . "\n" .
                  '                                                ' . $site['currency'] . $shipping_handling . "\n" .
                  '                                            </td>' . "\n" .
                  '                                        </tr>' . "\n" .
                  '                                        <tr>' . "\n" .
                  '                                            <td align="right">' . "\n" .
                  '                                                Total: ' . "\n" .
                  '                                            </td>' . "\n" .
                  '                                            <td>' . "\n" .
                  '                                                ' . $site['currency'] . $total . "\n" .
                  '                                            </td>' . "\n" .
                  '                                        </tr>' . "\n" .
                  '                                        <tr>' . "\n" .
                  '                                            <td colspan="2" align="center">' . "\n" .
                  '                                                ' . $pricelist . "\n" .
                  '                                            </td>' . "\n" .
                  '                                        </tr>' . "\n" .
                  '                                        <tr>' . "\n" .
                  '                                            <td colspan="2" align="center">' . "\n" .
                  '                                                ' . $purchase_button . "\n" .
                  '                                            </td>' . "\n" .
                  '                                        </tr>' . "\n";
        }
      }
    }
    $box .= '                                    </table>' . "\n";
  }

  include_once('includes/image_inc.php');
  $thumbnail = thumbnail($thumbnail, $title, '400');

  // begin pagination
  $pagination = artwork_pages($page, $artwork_id, $gallery_id);

  // begin breadcrumb trail of links.
  // can be turned off globally by setting $breadcrumb_display to false 
  // in includes/config_inc.php
  // turn them off locally by uncommenting the line below:
  //$breadcrumb_display = false;
  $breadcrumb   = null;
  if ($breadcrumb_display) {
    // set a character or string to separate the breadcrumb links.
    // some chars work better than others. experiment.
    $separator = ' > ';

    $br_query     = "SELECT g.gallery_name, g.category_id FROM gallery g, meta_data m WHERE g.gallery_id = '$gallery_id' AND m.display = 'show'";
    $br_result    = mysql_query($br_query);
    $category_id    = mysql_result($br_result, 0, 'category_id');
    $gallery_name = mysql_result($br_result, 0, 'gallery_name');

    $breadcrumb = '<a href="index.php">Home</a>' . $separator . '<a href="gallery.php?content=gallery&amp;id=' . $gallery_id . '">' . $gallery_name . '</a>' . $separator . $title;

  }
  else {
    $breadcrumb = null;
  }

  // are comments enabled for this artwork?
  if ($row['comments'] == 'enable') {
    include_once('includes/comment_inc.php');
    $re_title = 'Re: ' . $title;
    if (strlen($re_title > 55)) {
    }
    // check to see if a comment is being posted now
    if (isset($_POST['comment']) && ($_POST['comment'] == 'Comment')) {
      $comment_fields = validate_comment();
      if (empty($errors)) {
        // add the comment now
        add_comment($comment_fields);
      }
    }

    // display comments & comment form
    $comment_display = comment_display('art', $artwork_id);
    $comment_form = comment_form($re_title);
  }
  else {
    $comment_display = null;
    $comment_form = null;
  }
}

// activate the header template
require_once('templates/' . $template_folder . '/header' . $template_ext);

// load the sidebar & gallery_menu
$sidebar        = print_sidebar();
$galleries   = print_gallery();
$rss_icon       = show_rss_icon($site);
$show_cart_link = enable_ecommerce($site);

// activate the artwork template
require_once('templates/' . $template_folder . '/artwork' . $template_ext);

// activate the footer template
require_once('templates/' . $template_folder . '/footer' . $template_ext);

?>