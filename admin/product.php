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
require_once('../includes/config_inc.php');
require_once('../includes/time_inc.php');
require_once('inc/validate_inc.php');

// activate the login script
require_once('inc/login_inc.php');

// look for a valid login session
if (isset($_SESSION['administrator']) && $_SESSION['authorized'] == 'admin') {
  // check to see if someone is logging out
  if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    admin_logout();
  }

  // user is successfully logged in
  $errors         = null;
  $update_message = null;
  $info           = null;
  $item           = 'Product';
  $id_type        = 'product_id';

  // the quit button is clicked
  // the quit button is clicked
  if (isset($_POST['quit']) && $_POST['quit'] == 'Quit') {
    unset($_POST);
    unset($errors);
    unset($_SESSION['action']);
    unset($_SESSION['num_checkbox']);
    unset($_SESSION['checkbox']);
    unset($_SESSION['orderby']);

    // unset any remaining session variables
    unset($_SESSION['product_id']);
    unset($_SESSION['category_id']);
    unset($_SESSION['product_name']);
    unset($_SESSION['new_picture']);
    unset($_SESSION['current_picture']);
    unset($_SESSION['picture']);
    unset($_SESSION['qty_instock']);
    unset($_SESSION['price']);
    unset($_SESSION['shipping']);
    unset($_SESSION['handling']);
    unset($_SESSION['meta_data_id']);
    unset($_SESSION['display']);
    unset($_SESSION['title']);
    unset($_SESSION['description']);
    unset($_SESSION['keywords']);
    unset($_SESSION['rss_feed']);
    unset($_SESSION['priority']);
    unset($_SESSION['link']);
    unset($_SESSION['pub_date']);
    unset($_SESSION['last_updated']);
  }
  // the add button is clicked:
  if (isset($_POST['add']) && $_POST['add'] == 'Add' || isset($_SESSION['action']) && $_SESSION['action'] == 'product_add') {
    // load session vars
    $_SESSION['action'] = 'product_add';

    // $task may be: form, Save or Save Image
    if (isset($_POST['task'])) {
      $task = gp_filter($_POST['task']);
    }
    else {
      // the default task is 'form'
      $task = 'form';
    }

    switch ($task) {
      case 'form':
      // the add artwork form is requested
      default:
        // set message vars
        $message = 'Logged in as: ' . $_SESSION['administrator'];
        $info = 'Adding new product...';
        $page_title = 'Adminstration Add Product - eXhibition';

        // activate the add new artwork form
        include_once('inc/product_inc.php');
        $rss_feed_selectbox    = rss_select('disable');
        $display_select = display_selectbox();
        $comment_select = comment_selectbox();
        $where = "WHERE cat_type = 'product'";
        $category_select  = category_selectbox($category_id=null, $where);
        include_once($admin_tpl . '/header' . $admin_tplext);
        include_once($admin_tpl . '/product_add_form' . $admin_tplext);
        include_once($admin_tpl . '/footer' . $admin_tplext);
        break;
      case 'Save':
        // the add product form is submitted
        // set message vars
        $message = 'Logged in as: ' . $_SESSION['administrator'];
        $page_title = 'Adminstration Add Product - eXhibition';

        // load the session variables from post:
        if (isset($_POST['category_id'])) {
          $_SESSION['category_id'] = gp_filter($_POST['category_id']);
        }

        if (isset($_POST['product_name'])) {
          $_SESSION['product_name'] = gp_filter($_POST['product_name']);
        }

        if (isset($_POST['new_picture'])) {
          $_SESSION['new_picture'] = gp_filter($_POST['new_picture']);
        }

        if (isset($_POST['picture'])) {
          $_SESSION['picture'] = gp_filter($_POST['picture']);
        }

        if (isset($_POST['qty_instock'])) {
          $_SESSION['qty_instock'] = gp_filter($_POST['qty_instock']);
        }

        if (isset($_POST['price'])) {
          $_SESSION['price'] = gp_filter($_POST['price']);
        }

        if (isset($_POST['shipping'])) {
          $_SESSION['shipping'] = gp_filter($_POST['shipping']);
        }

        if (isset($_POST['handling'])) {
          $_SESSION['handling'] = gp_filter($_POST['handling']);
        }

        if (isset($_POST['title'])) {
          $_SESSION['title'] = gp_filter($_POST['title']);
        }

        if (isset($_POST['description'])) {
          $_SESSION['description'] = gp_filter($_POST['description']);
        }

        if (isset($_POST['keywords'])) {
          $_SESSION['keywords'] = gp_filter($_POST['keywords']);
        }

        if (isset($_POST['display'])) {
          $_SESSION['display'] = gp_filter($_POST['display']);
        }

        if (isset($_POST['priority'])) {
          $_SESSION['priority'] = gp_filter($_POST['priority']);
        }

        // new_picture may be 'upload' or 'current'
        if (!empty($_SESSION['new_picture']) && $_SESSION['new_picture'] == 'upload') {
          // go ahead and upload the image now
          include_once('inc/files_inc.php');
          if (upload_product($_FILES) && empty($errors)) {
            $_SESSION['picture'] = $new_image;
          }
          else {
            die("Upload Failed");
          }
          $_SESSION['view_picture'] = '<img src="http://' . $_SERVER['HTTP_HOST'] . '/' . $home_dir . '/images/original_art/thumbnails/' . $_SESSION['thumbnail'] . '" alt="' . $_SESSION['title'] . '" title="' . $_SESSION['title'] . '"><p>(' . $_SESSION['thumbnail'] . ')</p>';
        }
        elseif ($_SESSION['new_picture'] == 'current') {
          $_SESSION['picture'] = $_SESSION['current_picture'];
        }

        // Run validation routines on session variables
        validate_product();

        if (empty($errors)) {
          // create the product
          include_once('../includes/time_inc.php');
          $now = get_datetime();
          // insert meta_data from $_SESSION
          $query ="INSERT INTO 
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
                             '',
                             '',
                             '$now',
                             '$now')";
          $result = mysql_query($query);
          if ($result) {
            // retrieve meta_data_id
            $query = "SELECT LAST_INSERT_ID() AS meta_data_id FROM meta_data";
            $result = mysql_query($query);
            $meta_data_id = mysql_result($result, 0, 'meta_data_id');
            if ($result) {
              // insert product details
              $query = "INSERT INTO 
                          product(
                            product_id,
                            category_id,
                            product_name,
                            picture,
                            qty_instock,
                            price,
                            shipping,
                            handling,
                            meta_data_id)
                        VALUES(
                          '',
                          '$_SESSION[category_id]',
                          '$_SESSION[product_name]',
                          '$_SESSION[picture]',
                          '$_SESSION[qty_instock]',
                          '$_SESSION[price]',
                          '$_SESSION[shipping]',
                          '$_SESSION[handling]',
                          '$meta_data_id')";
              $result = mysql_query($query);
              if ($result) {
                // show confirmation
                $info = '<h4>New product listing successfully created!</h4>' . "\n" .
                        '<p>Upload another image for this product?</p>' . "\n" .
                        '<input type="hidden" name="MAX_FILE_SIZE" value="1000000">' . "\n" .
                        '<input type="hidden" name="continue_image" value="upload">' . "\n" .
                        '<input type="file" name="productimage"><br>' . "\n" .
                        'Optional image caption: <input type="text" name="caption" size="55" maxlength="255">' . "\n" . 
                        '<input type="hidden" name="product_id" value="' . $product_id . '">' . "\n" .
                        '<input type="submit" name="task" value="Save Image">' . "\n";
              }
            }
          }
          // activate the confirmation template
          include_once($admin_tpl . '/header' . $admin_tplext);
          include_once($admin_tpl . '/message' . $admin_tplext);
          include_once($admin_tpl . '/footer' . $admin_tplext);
        }
        else {
          // errors exist, activate the edit form
        }

        break;
    }
  }
  // the view button is clicked:
  elseif (isset($_POST['view']) && $_POST['view'] == 'View' || isset($_SESSION['action']) && $_SESSION['action'] == 'product_view') {
  }
  // the edit button is clicked:
  elseif (isset($_POST['edit']) && $_POST['edit'] == 'Edit' || isset($_SESSION['action']) && $_SESSION['action'] == 'product_edit') {
  }
  // the delete button is clicked:
  elseif (isset($_POST['delete']) && $_POST['delete'] == 'Delete' || isset($_SESSION['action']) && $_SESSION['action'] == 'product_delete') {
  }
  // no button was clicked:
  else {
    // List view: build a summary list of existing galleries

    // set message vars
    $page_title = 'Adminstration Products - eXhibition';
    $message = 'Logged in as: ' . $_SESSION['administrator'];

    // count the database rows
    $query = "SELECT count(*) FROM product";
    $result = mysql_query($query);
    $num_rows = mysql_fetch_row($result);

    // set a number for pagination
    if (!isset($_GET['page'])) {
      $page = 1;
    }
    else {
      $page = gp_filter($_GET['page']);
    }

    // set maximum number of rows per page
    $setmax_results = 3;

    // check if the display all link is clicked
    if (isset($_GET['displayall']) && $_GET['displayall'] == 'normal') {
      $max_results = $setmax_results;
      $display_all = '&amp;displayall=normal';
    }
    elseif (isset($_GET['displayall']) && $_GET['displayall'] == 'all') {
      $max_results = $num_rows[0];
      $display_all = '&amp;displayall=all';
    }
    else {
      $_GET['displayall'] = 'normal';
      $max_results = $setmax_results;
      $display_all = null;
    }

    // this prevents the page from breaking if displayall is selected from page 2 or higher
    if (($page > 1) && ($_GET['displayall'] == 'all')) {
      $page = 1;
    }

    // calculate the page offset
    $offset = (($page * $max_results) - $max_results);
    // calculate total pages
    if (isset($num_rows[0]) && $num_rows[0] > 0) {
      $total_pages = ceil($num_rows[0] / $max_results);
    }
    else {
      $total_pages = 1;
    }

    // get a nice field name for the sort pic alt tag
    if (!empty($_GET['sort'])) {
      $default_fieldname = gp_filter($_GET['sort']);
    }
    else {
      $default_fieldname = 'product_id';
    }

    // initialize sort vars
    $orderby              = 'product_id DESC';  // appended to sql query
    $orderby_product_id   = 'product_id';       // appended to column heading link
    $orderby_product_name = 'product_name';     // appended to column heading link
    $orderby_picture      = 'picture';          // appended to column heading link
    $orderby_qty_instock  = 'qty_instock';      // appended to column heading link
    $orderby_price        = 'price';            // appended to column heading link
    $orderby_display      = 'display';          // appended to column heading link
    $orderby_cat_name     = 'cat_name';         // appended to column heading link
    $paginate_str         = 'product_id';       // appended to pagination link
    $sortpic_up = '<img src="img/sort_up.gif" width="13" height="11" alt="Sort by ' . $default_fieldname . '" title="Sort by ' . $default_fieldname . '">';
    $sortpic_dn = '<img src="img/sort_dn.gif" width="13" height="11" alt="Sort by ' . substr($default_fieldname, 0, -5) . ' descending" title="Sort by ' . substr($default_fieldname, 0, -5) . ' descending">';
    $sortpic    = $sortpic_up;  // arrow for column heading - may point up or down
    if (!isset($_GET['sort'])) {
      $_GET['sort'] = 'product_id';
    }

    // maintain sort vars when column headings are clicked
    switch (gp_filter($_GET['sort'])) {
      case 'product_id':
      default:
        $orderby              = 'product_id ASC';
        $orderby_product_id   = 'product_id_desc';
        $orderby_product_name = 'product_name';
        $orderby_picture      = 'picture';
        $orderby_qty_instock  = 'qty_instock';
        $orderby_price        = 'price';
        $orderby_display      = 'display';
        $orderby_cat_name     = 'cat_name';
        $paginate_str         = 'product_id';
        $sortpic              = $sortpic_up;
        break;
      case 'product_id_desc':
        $orderby            = 'product_id DESC';
        $orderby_product_id = 'product_id';
        $paginate_str       = 'product_id_desc';
        $sortpic            = $sortpic_dn;
        break;
      case 'product_name':
        $orderby             = 'product_name ASC';
        $orderby_product_name = 'product_name_desc';
        $paginate_str        = 'product_name';
        $sortpic             = $sortpic_up;
        break;
      case 'product_name_desc':
        $orderby              = 'product_name DESC';
        $orderby_product_name = 'product_name';
        $paginate_str         = 'product_name_desc';
        $sortpic              = $sortpic_dn;
        break;
      case 'picture':
        $orderby         = 'picture ASC';
        $orderby_picture = 'picture_desc';
        $paginate_str    = 'picture';
        $sortpic         = $sortpic_up;
        break;
      case 'picture_desc':
        $orderby         = 'picture DESC';
        $orderby_picture = 'picture';
        $paginate_str    = 'picture_desc';
        $sortpic         = $sortpic_dn;
        break;
      case 'qty_instock':
        $orderby             = 'qty_instock ASC';
        $orderby_qty_instock = 'qty_instock_desc';
        $paginate_str        = 'qty_instock';
        $sortpic             = $sortpic_up;
        break;
      case 'qty_instock_desc':
        $orderby             = 'qty_instock DESC';
        $orderby_qty_instock = 'qty_instock';
        $paginate_str        = 'qty_instock_desc';
        $sortpic             = $sortpic_dn;
        break;
      case 'price':
        $orderby       = 'price ASC';
        $orderby_price = 'price_desc';
        $paginate_str  = 'price';
        $sortpic       = $sortpic_up;
        break;
      case 'price_desc':
        $orderby       = 'price DESC';
        $orderby_price = 'price';
        $paginate_str  = 'price_desc';
        $sortpic       = $sortpic_dn;
        break;
      case 'display':
        $orderby         = 'display ASC';
        $orderby_display = 'display_desc';
        $paginate_str    = 'display';
        $sortpic         = $sortpic_up;
        break;
      case 'display_desc':
        $orderby         = 'display DESC';
        $orderby_display = 'display';
        $paginate_str    = 'display_desc';
        $sortpic         = $sortpic_dn;
        break;
      case 'cat_name':
        $orderby          = 'cat_name ASC';
        $orderby_cat_name = 'cat_name_desc';
        $paginate_str     = 'cat_name';
        $sortpic          = $sortpic_up;
        break;
      case 'cat_name_desc':
        $orderby          = 'cat_name DESC';
        $orderby_cat_name = 'cat_name';
        $paginate_str     = 'cat_name_desc';
        $sortpic          = $sortpic_dn;
        break;
    }

    if (isset($_GET['action'])) {
      if ($_GET['action'] == 'selectall') {
        $select_all = '&amp;action=selectall';
      }
      elseif ($_GET['action'] == 'unselectall') {
        $select_all = '&amp;action=unselectall';
      }
      else {
        $select_all = null;
      }
    }
    else {
      $select_all = null;
    }

    //construct dynamic hyperlinks for the html table headings
    $sub1 = '<a href="' . $_SERVER['PHP_SELF'] . '?sort=';
    $sub2 = '&amp;page=' . $page . $select_all . $display_all;

    $product_id_th = $sub1 . $orderby_product_id . $sub2 . '">Product ID</a>';
    if ($paginate_str == 'product_id' || $paginate_str == 'product_id_desc') {
      $product_id_th .= $sortpic;
    }
    $product_id_th .= "\n";

    $product_name_th = $sub1 . $orderby_product_name . $sub2 . '">Name</a>';
    if ($paginate_str == 'product_name' || $paginate_str == 'product_name_desc') {
      $product_name_th .= $sortpic;
    }
    $product_name_th .= "\n";

    $picture_th = $sub1 . $orderby_picture . $sub2 . '">Picture</a>';
    if ($paginate_str == 'picture' || $paginate_str == 'picture_desc') {
      $picture_th .= $sortpic;
    }
    $picture_th .= "\n";

    $qty_instock_th = $sub1 . $orderby_qty_instock . $sub2 . '">Quantity</a>';
    if ($paginate_str == 'qty_instock' || $paginate_str == 'qty_instock_desc') {
      $qty_instock_th .= $sortpic;
    }
    $qty_instock_th .= "\n";

    $price_th = $sub1 . $orderby_price . $sub2 . '">Price</a>';
    if ($paginate_str == 'price' || $paginate_str == 'price_desc') {
      $price_th .= $sortpic;
    }
    $price_th .= "\n";

    $display_th = $sub1 . $orderby_display . $sub2 . '">Display</a>';
    if ($paginate_str == 'display' || $paginate_str == 'display_desc') {
      $display_th .= $sortpic;
    }
    $display_th .= "\n";

    $cat_name_th = $sub1 . $orderby_cat_name . $sub2 . '">Category</a>';
    if ($paginate_str == 'cat_name' || $paginate_str == 'cat_name_desc') {
      $cat_name_th .= $sortpic;
    }
    $cat_name_th .= "\n";

    // list query: get a summary list of existing products
    $query = "SELECT
                p.product_id,
                p.product_name,
                p.picture,
                p.qty_instock,
                p.price,
                m.display,
                c.cat_name
              FROM 
                product p,
                category c,
                meta_data m
              WHERE
                p.meta_data_id = m.meta_data_id
              AND
                c.category_id = p.category_id
              ORDER BY
                $orderby
              LIMIT
                $offset, $max_results";

    // get data rows from mysql
    $result = mysql_query($query);
    if (!mysql_num_rows($result)) {
      // no products exist
      $data_rows = '    <tr>' . "\n" .
                   '        <td colspan="8" align="center" height="35" bgcolor="#eeeeee">' . "\n" .
                   '            No products found!' . "\n" .
                   '        </td>' . "\n" .
                   '    </tr>' . "\n";
    }
    else {
      if (mysql_num_rows($result) < 1) {
        // no artworks exist
        $data_rows = '    <tr>' . "\n" .
                     '        <td colspan="8" align="center" height="35" bgcolor="#eeeeee">' . "\n" .
                     '            No products found!' . "\n" .
                     '        </td>' . "\n" .
                     '    </tr>' . "\n";
      }
      else {
        // products exist
        $color1 = '#e5e5e5';
        $color2 = '#eeeeee';
        $data_rows = '';
        $i = 0;
        while ($row = mysql_fetch_array($result)) {
          $product_id   = $row['product_id'];
          $product_name = $row['product_name'];
          $picture      = $row['picture'];
          $qty_instock  = $row['qty_instock'];
          $price        = $row['price'];
          $display      = $row['display'];
          $cat_name     = $row['cat_name'];

          // print out the thumbnail with a maximum size of 100 pixels
          include_once('inc/image_inc.php');
          $picture = resize_image('picture', 100);

          // define a color for the table rows
          $row_color = ($i % 2) ? $color1 : $color2;

          // toggle select/unselect for checkboxes
          if (!isset($_GET['action'])) {
            $_GET['action'] = null;
          }
          switch (gp_filter($_GET['action'])) {
            case 'selectall':
              $checked[$i] = ' checked';
              $active_action = 'selectall';
              break;
            case 'unselectall':
            default:
              $active_action = 'unselectall';
              $checked[$i] = null;
              break;
          }

          // format the rows
          $data_rows .= 
          '            <tr>' . "\n" .
          '                <td bgcolor="' . $row_color . '" align="center" valign="middle">' . "\n" .
          '                    <input type="checkbox" name="checkbox[]" value="' . $product_id . '"' . $checked[$i] . '>' . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="' . $row_color . '" align="center" valign="middle">' . "\n" .
          '                     ' . $product_id . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="' . $row_color . '" align="center" valign="middle">' . "\n" .
          '                    ' . $product_name . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="' . $row_color . '" align="center">' . "\n" .
          '                    ' . $picture . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="' . $row_color . '" width="104" height="104" align="center">' . "\n" .
          '                    ' . $qty_instock . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="' . $row_color . '" align="center" valign="middle">' . "\n" .
          '                    ' . $price . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="' . $row_color . '" align="center" valign="middle">' . "\n" .
          '                    ' . $display . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="' . $row_color . '" align="center" valign="middle">' . "\n" .
          '                    ' . $cat_name . "\n" .
          '                </td>' . "\n" .
          '            </tr>' . "\n";
          $i++;
        }
      }
    }

    // construct pagination links
    include_once('inc/paginate_inc.php');
    paginate_list($page, $total_pages);

    // activate the artwork_list template
    include_once($admin_tpl . '/header' . $admin_tplext);
    include_once($admin_tpl . '/product_list' . $admin_tplext);
    include_once($admin_tpl . '/footer' . $admin_tplext);
  }
}
else {
  // user needs to login
  if (!empty($_POST['action']) && $_POST['action'] == 'login') {
    admin_login(gp_filter($_POST['username']), gp_filter($_POST['password']));
  }

  $message    = 'Please Log In';
  $info       = 'Administration Login - eXhibition';
  $page_title = 'Administration Login - eXhibition';
  $text       = '<p align="center">Welcome to the eXhibition administrative back-end.</p><p align="center">This area is password protected, so you will need to login to access this part of your site.</p><p>&nbsp;</p>';
  // show the login form
  include_once($admin_tpl . '/header_dead' . $admin_tplext);
  include_once($admin_tpl . '/login' . $admin_tplext);
  include_once($admin_tpl . '/footer' . $admin_tplext);
  exit();
}

?>