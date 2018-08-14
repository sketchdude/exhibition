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

// this shopping cart is based on a tutorial script at: 
// http://www.thewatchmakerproject.com/journal/276/building-a-simple-php-shopping-cart

// start the session and connect to the database
require_once('includes/config_inc.php');
require_once('includes/shop_cart_inc.php');

// This ensures ecommerce is enabled
if (isset($site['ecommerce']) && $site['ecommerce'] == 'enable') {
  // include code for google payments
  include_once('includes/googlecart.php');
  include_once('includes/googleitem.php');
  include_once('includes/googleshipping.php');
  include_once('includes/googletax.php');
  include_once('includes/xml-processing/gc_xmlbuilder.php');

  $breadcrumb = null;

  // validate browser requests for content
  include_once('includes/validate_inc.php');
  if (isset($_GET['content']) || isset($_GET['id'])) {
    $_GET['content'] = gp_filter($_GET['content']);
    $_GET['id']      = gp_filter($_GET['id']);
  }
  else {
    $_GET['content'] = null;
    $_GET['id']      = null;
  }

  // get meta data for the page header
  include_once('includes/header_inc.php');
  if (!isset($_GET['content'])) {
    $_GET['content'] = 'cart';
  }
  if (!isset($_GET['id'])) {
    $_GET['id'] = null;
  }
  $meta_data = load_meta_data('cart', $_GET['id']);

  // content of the cart is stored as a comma separated list in a session named cart
  $cart = $_SESSION['cart'];

  if (isset($_GET['action'])) {
    $action = $_GET['action'];
  }
  else {
    $action = null;
  }

  switch ($action) {
    case 'add':
      if ($cart) {
        $cart .= ',' . $_GET['id'];
      }
      else {
        $cart = $_GET['id'];
      }
      break;
    case 'delete':
      if ($cart) {
        $items = explode(',', $cart);
        $newcart = '';
        foreach ($items as $item) {
          if ($_GET['id'] != $item) {
            if ($newcart != '') {
              $newcart .= ',' . $item;
            }
            else {
              $newcart = $item;
            }
          }
        }
        $cart = $newcart;
      }
      break;
    case 'update':
      if ($cart) {
        $newcart = '';
        foreach ($_POST as $key => $value) {
          if (stristr($key, 'qty')) {
            $id = str_replace('qty', '', $key);
            $items = ($newcart != '') ? explode(',', $newcart) : explode(',', $cart);
            $newcart = '';
            foreach ($items as $item) {
              if ($id != $item) {
                if ($newcart != '') {
                  $newcart .= ',' . $item;
                }
                else {
                  $newcart = $item;
                }
              }
            }
            for ($i = 1; $i <= $value; $i++) {
              if ($newcart != '') {
                $newcart .= ',' . $id;
              }
              else {
                $newcart = $id;
              }
            }
          }
        }
      }
      $cart = $newcart;
      break;
  }

  $_SESSION['cart'] = $cart;
  $cart_link = write_cart();
//  $shopping_cart = show_google_cart($site);
  $contents = '    <tr>' . "\n" .
              '        <td colspan="4" bgcolor="#ffffff" height="35">' . "\n" .
              '            <p>You have not ordered anything.</p>' . "\n" .
              '        </td>' . "\n" .
              '    </tr>' . "\n";
  $shopping_cart = shopping_cart($site, $contents);

  // activate the header template
  include_once('templates/' . $template_folder . '/header' . $template_ext);

  // activate the sidebar & gallery_menu
  include_once('includes/navigate_inc.php');
  $sidebar = print_sidebar();
  $galleries = print_gallery();
  $show_cart_link = enable_ecommerce($site);
  $rss_icon = show_rss_icon($site);

  $breadcrumb = '<b><a href="index.php">Home</a> > My Order</b>';

  $content = $shopping_cart . "\n";

  include_once('templates/' . $template_folder . '/body' . $template_ext);

  // activate the footer template
  include_once('templates/' . $template_folder . '/footer' . $template_ext);

}
else {
  header('location: index.php');
  exit();
}

?>