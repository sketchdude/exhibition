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
  $item           = 'Gallery';
  $id_type        = 'gallery_id';

  // the quit button is clicked
  if (isset($_POST['quit']) && $_POST['quit'] == 'Quit') {
    unset($_POST);
    unset($errors);
    unset($_SESSION['action']);
    unset($_SESSION['num_checkbox']);
    unset($_SESSION['checkbox']);
    unset($_SESSION['orderby']);

    // unset any remaining session variables
    unset($_SESSION['gallery_id']);
    unset($_SESSION['category_id']);
    unset($_SESSION['gallery_name']);
    unset($_SESSION['new_gallery_icon']);
    unset($_SESSION['current_gallery_icon']);
    unset($_SESSION['gallery_icon']);
    unset($_SESSION['art_per_page']);
    unset($_SESSION['art_per_row']);
    unset($_SESSION['thumbnail_max']);
    unset($_SESSION['meta_data_id']);
    unset($_SESSION['display']);
    unset($_SESSION['title']);
    unset($_SESSION['description']);
    unset($_SESSION['keywords']);
    unset($_SESSION['rss_channel']);
    unset($_SESSION['priority']);
    unset($_SESSION['link']);
    unset($_SESSION['pub_date']);
    unset($_SESSION['last_updated']);
  }

  // the add button is clicked:
  if (isset($_POST['add']) && $_POST['add'] == 'Add' || isset($_SESSION['action']) && $_SESSION['action'] == 'gallery_add') {
    // load session vars
    $_SESSION['action'] = 'gallery_add';

    // $task may be: form, or Save
    if (isset($_POST['task'])) {
      $task = gp_filter($_POST['task']);
    }
    else {
      // the default task is 'form'
      $task = 'form';
    }

    switch ($task) {
      case 'form':
      default:
        // set message vars
        $message = 'Logged in as: ' . $_SESSION['administrator'];
        $info = 'Adding new gallery...';
        $page_title = 'Adding a New Gallery -Administration- eXhibition';

        // activate the add new gallery form
        include_once('inc/gallery_inc.php');
        $gallery_cat_selectbox = gallery_cat_selectbox();
        $display_select        = display_selectbox();
        $rss_channel_selectbox    = rss_select('no');
        include_once($admin_tpl . '/header' . $admin_tplext);    
        include_once($admin_tpl . '/gallery_add_form' . $admin_tplext);
        include_once($admin_tpl . '/footer' . $admin_tplext);
        break;
      case 'Save':
        // set message vars
        $message = 'Logged in as: ' . $_SESSION['administrator'];
        $page_title = 'Adminstration Add Gallery - eXhibition';

        // load the session variables from post:
        if (isset($_POST['category_id'])) {
          $_SESSION['category_id'] = gp_filter($_POST['category_id']);
        }

        if (isset($_POST['title'])) {
          $_SESSION['title'] = gp_filter($_POST['title']);
        }

        if (isset($_POST['gallery_name'])) {
          $_SESSION['gallery_name'] = gp_filter($_POST['gallery_name']);
        }

        if (isset($_POST['display'])) {
          $_SESSION['display'] = gp_filter($_POST['display']);
        }

        if (isset($_POST['art_per_page'])) {
          $_SESSION['art_per_page'] = gp_filter($_POST['art_per_page']);
        }

        if (isset($_POST['art_per_row'])) {
          $_SESSION['art_per_row'] = gp_filter($_POST['art_per_row']);
        }

        if (isset($_POST['thumbnail_max'])) {
          $_SESSION['thumbnail_max'] = gp_filter($_POST['thumbnail_max']);
        }

        if (isset($_POST['new_gallery_icon'])) {
          $_SESSION['new_gallery_icon'] = gp_filter($_POST['new_gallery_icon']);
        }

        if (isset($_POST['gallery_icon'])) {
          $_SESSION['gallery_icon'] = gp_filter($_POST['gallery_icon']);
        }

        if (isset($_POST['priority'])) {
          $_SESSION['priority'] = gp_filter($_POST['priority']);
        }

        if (isset($_POST['rss_channel'])) {
          $_SESSION['rss_channel'] = gp_filter($_POST['rss_channel']);
        }

        if (isset($_POST['description'])) {
          $_SESSION['description'] = gp_filter($_POST['description']);
        }

        if (isset($_POST['keywords'])) {
          $_SESSION['keywords'] = gp_filter($_POST['keywords']);
        }

        if (!empty($_SESSION['new_gallery_icon']) && $_SESSION['new_gallery_icon'] == 'upload') {
          // go ahead and upload the image now
          include_once('inc/files_inc.php');
          if (upload($_FILES) && empty($errors)) {
            // $new_icon is returned by the upload function.
            $_SESSION['gallery_icon'] = $new_icon;
          }
          else {
            die("Upload Failed");
          }
          $_SESSION['thumbnail_image'] = '<img src="http://' . $_SERVER['HTTP_HOST'] . '/' . $home_dir . '/images/gallery_icons/' . $_SESSION['gallery_icon'] . '" alt="' . $_SESSION['gallery_name'] . '" title="' . $_SESSION['gallery_name'] . '"><p>(' . $_SESSION['gallery_icon'] . ')</p>';
        }
        elseif ($_SESSION['new_gallery_icon'] == 'none') {
          $_SESSION['thumbnail_image'] = 'none';
        }

        // Run validation routines on session variables
        validate_gallery();

        if (empty($errors)) {
          // create the gallery
          include_once('inc/gallery_inc.php');
          $now = get_datetime();
          if(create_gallery($now)) {
            $info = '<h4>New gallery successfully created!</h4>';
          }
          else {
            $info = 'Could not create this gallery. Please try again later.';
          }
          // activate the confirmation template
          include_once($admin_tpl . '/header' . $admin_tplext);
          include_once($admin_tpl . '/message' . $admin_tplext);
          include_once($admin_tpl . '/footer' . $admin_tplext);
        }
        else {
          $info = 'Errors exist: Edit details for new gallery.';
          // activate the edit form
          include_once('inc/gallery_inc.php');
          $gallery_cat_selectbox = gallery_cat_selectbox($_SESSION['category_id']);
          $display_select        = display_selectbox($_SESSION['display']);
          $rss_channel_selectbox    = rss_select($_SESSION['rss_channel']);
          include_once($admin_tpl . '/header' . $admin_tplext);
          include_once($admin_tpl . '/gallery_edit_form' . $admin_tplext);
          include_once($admin_tpl . '/footer' . $admin_tplext);
        }
        break;
    }
  }
  // the view button is clicked:
  elseif (isset($_POST['view']) && $_POST['view'] == 'View' || isset($_SESSION['action']) && $_SESSION['action'] == 'gallery_view') {
    if (!empty($_POST['checkbox'])) {
      // load the session vars
      $_SESSION['action']       = 'gallery_view';
      $_SESSION['num_checkbox'] = count($_POST['checkbox']);
      $_SESSION['checkbox']     = $_POST['checkbox'];
    }
    elseif (empty($_SESSION['checkbox'])) {
      // if no box is selected when an action is requested, do nothing.
      header('location: gallery.php');
    }

    // set message vars
    $page_title = 'View Gallery Details - eXhibition';
    $message = 'Logged in as: ' . $_SESSION['administrator'];

    // set a number for pagination
    if (!isset($_GET['page'])) {
      $page = 1;
    }
    else {
      $page = gp_filter($_GET['page']);
    }

    // print out the number of checkbox selections 
    if ($_SESSION['num_checkbox'] == 1) {
      $num_post_message = '1 gallery selected';
    }
    else {
      $num_post_message = $_SESSION['num_checkbox'] . ' galleries selected';
    }

    // set maximum number of rows per page
    $max_results = 1;
    // calculate the page offset
    $offset = (($page * $max_results) - $max_results);
    // calculate total pages
    $total_pages = ceil($_SESSION['num_checkbox'] / $max_results);

    // format the checkbox selections for the sql query
    $in = '';
    foreach ($_SESSION['checkbox'] as $key => $value) {
      $in .= $value . ', ';
    }
    $in = rtrim($in, ', ');

    // main query
    $query = "SELECT
                g.gallery_id,
                g.category_id,
                g.gallery_name,
                m.priority,
                g.gallery_icon,
                m.display,
                g.art_per_page,
                g.art_per_row,
                g.thumbnail_max,
                g.meta_data_id,
                m.title,
                m.description,
                m.keywords,
                g.rss_channel,
                m.link,
                m.pub_date,
                m.last_updated
              FROM gallery g, meta_data m WHERE g.gallery_id IN ($in)
              AND g.meta_data_id = m.meta_data_id
              LIMIT 
                $offset, $max_results";

    // get the gallery details
    $result = mysql_query($query);
    if (!mysql_num_rows($result)) {
      // no galleries found
      $info = '<h4>No galleries found!</h4>';
      include_once($admin_tpl . '/header' . $admin_tplext);
      include_once($admin_tpl . '/message' . $admin_tplext);
      include_once($admin_tpl . '/footer' . $admin_tplext);
      exit();
    }
    else {
      // galleries found
      $details = '';
      while ($row = mysql_fetch_assoc($result)) {
        if ($row['parent_id'] == 0) {
          $row['parent_id'] = 'None';
        }
        // show the gallery image
        include_once('inc/image_inc.php');
        $row['gallery_icon'] = resize_image('gallery_icon', 75);

        // format the gallery details
        foreach ($row as $label => $field) {
          $details .= 
          '    <tr>' . "\n" .
          '        <td bgcolor="#147a14" class="label" align="right" height="25">' . "\n" .
          '            ' . $label . "\n" .
          '        </td>' . "\n" .
          '        <td bgcolor="#147a14" class="field" align="left" height="25">' . "\n" .
          '            ' . $field . "\n" .
          '        </td>' . "\n" .
          '    </tr>' . "\n";
        }
      }
    }

    // construct pagination links
    include_once('inc/paginate_inc.php');
    paginate_child($page, $total_pages);

    // activate the template
    include_once($admin_tpl . '/header' . $admin_tplext);
    include_once($admin_tpl . '/view' . $admin_tplext);
    include_once($admin_tpl . '/footer' . $admin_tplext);
  }
  // the edit button is clicked:
  elseif (isset($_POST['edit']) && $_POST['edit'] == 'Edit' || isset($_SESSION['action']) && $_SESSION['action'] == 'gallery_edit') {
    if (!empty($_POST['checkbox'])) {
      // load the session vars
      $_SESSION['action']       = 'gallery_edit';
      $_SESSION['num_checkbox'] = count($_POST['checkbox']);
      $_SESSION['checkbox']     = $_POST['checkbox'];
    }
    elseif (empty($_SESSION['checkbox'])) {
      // if no box is selected when an action is requested, do nothing.
      header('location: gallery.php');
    }

    // $task may be: form or save
    if (isset($_POST['task'])) {
      $task = gp_filter($_POST['task']);
    }
    else {
      // the default task is 'form'
      $task = 'form';
    }

    switch ($task) {
      case 'form':
      default:
        // set message vars
        $page_title = 'Administration Edit Gallery - eXhibition';
        $message = 'Logged in as: ' . $_SESSION['administrator'];

        // set a number for pagination
        if (!isset($_GET['page'])) {
          $page = 1;
        }
        else {
          $page = gp_filter($_GET['page']);
        }

        // set a message 
        if ($_SESSION['num_checkbox'] == 1) {
          $num_post_message = '1 gallery selected';
        }
        else {
          $num_post_message = $_SESSION['num_checkbox'] . ' galleries selected';
        }

        // set maximum number of rows per page
        $max_results = 1;
        // calculate the page offset
        $offset = (($page * $max_results) - $max_results);
        // calculate total pages
        $total_pages = ceil($_SESSION['num_checkbox'] / $max_results);

        // format the checkbox selections for sql
        $in = '';
        foreach ($_SESSION['checkbox'] as $key => $value) {
          $in .= $value . ', ';
        }
        $in = rtrim($in, ', ');

        // main query
        $query = "SELECT
                    g.gallery_id,
                    g.category_id,
                    g.gallery_name,
                    g.gallery_icon,
                    m.display,
                    m.priority,
                    g.rss_channel,
                    g.art_per_page,
                    g.art_per_row,
                    g.thumbnail_max,
                    g.meta_data_id,
                    m.title,
                    m.description,
                    m.keywords
                  FROM gallery g, meta_data m WHERE g.gallery_id IN ($in)
                  AND g.meta_data_id = m.meta_data_id
                  LIMIT 
                    $offset, $max_results";

        // get the gallery details
        $result = mysql_query($query);
        if (!mysql_num_rows($result)) {
          // no galleries found
          $info = '<h4>No galleries found!</h4>';
          include_once($admin_tpl . '/header' . $admin_tplext);
          include_once($admin_tpl . '/message' . $admin_tplext);
          include_once($admin_tpl . '/footer' . $admin_tplext);
          exit();
        }
        else {
          // galleries found
          while ($row = mysql_fetch_assoc($result)) {
            // load data into session variables
            $_SESSION['gallery_id']   = $row['gallery_id'];
            $_SESSION['category_id']  = $row['category_id'];
            $_SESSION['gallery_name'] = $row['gallery_name'];
            $_SESSION['parent_id']    = $row['parent_id'];
            if (isset($row['gallery_icon'])) {
              $_SESSION['current_gallery_icon'] = $row['gallery_icon'];
            }
            $_SESSION['display']       = $row['display'];
            $_SESSION['priority']      = $row['priority'];
            $_SESSION['rss_channel']      = $row['rss_channel'];
            $_SESSION['art_per_page']  = $row['art_per_page'];
            $_SESSION['art_per_row']   = $row['art_per_row'];
            $_SESSION['thumbnail_max'] = $row['thumbnail_max'];
            $_SESSION['meta_data_id']  = $row['meta_data_id'];
            $_SESSION['title']         = $row['title'];
            $_SESSION['description']   = $row['description'];
            $_SESSION['keywords']      = $row['keywords'];

            // format the gallery_icon
            include_once('inc/image_inc.php');
            $gallery_icon = resize_image('gallery_icon', '75');
          }

          include_once('inc/gallery_inc.php');
          $rss_select = rss_select($_SESSION['rss_channel']);
          $gallery_cat_select = gallery_cat_selectbox($_SESSION['category_id']);
          $display_select = display_selectbox($_SESSION['display']);

          // construct pagination links
          include_once('inc/paginate_inc.php');
          paginate_child($page, $total_pages);

          // activate the edit form
          include_once($admin_tpl . '/header' . $admin_tplext);
          include_once($admin_tpl . '/gallery_edit_form' . $admin_tplext);
          include_once($admin_tpl . '/footer' . $admin_tplext);
        }
        break;

      case 'Save':
        // load the session variables from post:
        if (isset($_POST['category_id'])) {
          $_SESSION['category_id'] = gp_filter($_POST['category_id']);
        }

        if (isset($_POST['title'])) {
          $_SESSION['title'] = gp_filter($_POST['title']);
        }

        if (isset($_POST['gallery_name'])) {
          $_SESSION['gallery_name'] = gp_filter($_POST['gallery_name']);
        }

        if (isset($_POST['display'])) {
          $_SESSION['display'] = gp_filter($_POST['display']);
        }

        if (isset($_POST['art_per_page'])) {
          $_SESSION['art_per_page'] = gp_filter($_POST['art_per_page']);
        }

        if (isset($_POST['art_per_row'])) {
          $_SESSION['art_per_row'] = gp_filter($_POST['art_per_row']);
        }

        if (isset($_POST['thumbnail_max'])) {
          $_SESSION['thumbnail_max'] = gp_filter($_POST['thumbnail_max']);
        }

        if (isset($_POST['new_gallery_icon'])) {
          $_SESSION['new_gallery_icon'] = gp_filter($_POST['new_gallery_icon']);
        }

        if (isset($_POST['gallery_icon'])) {
          $_SESSION['gallery_icon'] = gp_filter($_POST['gallery_icon']);
        }

        if (isset($_POST['priority'])) {
          $_SESSION['priority'] = gp_filter($_POST['priority']);
        }

        if (isset($_POST['rss_channel'])) {
          $_SESSION['rss_channel'] = gp_filter($_POST['rss_channel']);
        }

        if (isset($_POST['description'])) {
          $_SESSION['description'] = gp_filter($_POST['description']);
        }

        if (isset($_POST['keywords'])) {
          $_SESSION['keywords'] = gp_filter($_POST['keywords']);
        }

        // run validation routines on the session variables
        validate_gallery();

        if (empty($errors)) {
          // Saving data...
          // perform appropriate image related actions depending on the radio selection
          if (!empty($_SESSION['new_gallery_icon']) && $_SESSION['new_gallery_icon'] == 'upload') {
            include_once('inc/files_inc.php');
            if (upload($_FILES) && empty($errors)) {
            // $new_icon is returned by the upload function.
              $_SESSION['gallery_icon'] = $new_icon;
            }
            else {
              die("Upload Failed");
            }
          }
          elseif (!empty($_SESSION['new_gallery_icon']) && $_SESSION['new_gallery_icon'] == 'delete') {
            // find the old image
            $query = "SELECT
                        gallery_icon AS old_gallery_icon
                      FROM
                        gallery
                      WHERE
                        gallery_id = $_SESSION[gallery_id]";

            if ($result = mysql_query($query)) {
              $old_gallery_icon = mysql_result($result, 0, 'old_gallery_icon');

              // get the path to the old_gallery_icon
              $old_gallery_icon = $_SERVER['DOCUMENT_ROOT'] . '/' . $home_dir . '/images/gallery_icons/' . $old_gallery_icon;

              unlink($old_gallery_icon) or die('unable to delete file');
              $_SESSION['gallery_icon'] = null;
            }
            else {
              $_SESSION['gallery_icon'] = null;
            }
          }
          elseif (!empty($_SESSION['new_gallery_icon']) && $_SESSION['new_gallery_icon'] == 'current') {
            $_SESSION['gallery_icon'] = $_SESSION['current_gallery_icon'];
          }

          // run a function to create a query string of changed fields
          include_once('inc/gallery_inc.php');
          $query_string = gallery_fields_diff();

          // main update query
          $update = "UPDATE
                       gallery g,
                       meta_data m
                     SET
                       $query_string 
                     WHERE
                       g.gallery_id = $_SESSION[gallery_id]
                     AND
                       g.meta_data_id = m.meta_data_id";

          // if the query string shows changes, run the update
          if ($query_string) {
            $result = mysql_query($update);
            $info = '<h4>Changes saved.</h4>' . "\n";
          }
          else {
            $info = '<h4>No changes made.</h4>' . "\n";
          }

          // unset session variables
          unset($_SESSION['gallery_id']);
          unset($_SESSION['category_id']);
          unset($_SESSION['title']);
          unset($_SESSION['gallery_name']);
          unset($_SESSION['display']);
          unset($_SESSION['art_per_page']);
          unset($_SESSION['art_per_row']);
          unset($_SESSION['thumbnail_max']);
          unset($_SESSION['parent_id']);
          unset($_SESSION['new_gallery_icon']);
          unset($_SESSION['current_gallery_icon']);
          unset($_SESSION['gallery_icon']);
          unset($_SESSION['priority']);
          unset($_SESSION['rss_channel']);
          unset($_SESSION['meta_data_id']);
          unset($_SESSION['description']);
          unset($_SESSION['keywords']);

          // show a confirmation page
          $page_title = 'Adminstration Edit Gallery - eXhibition';
          $message = 'Logged in as: ' . $_SESSION['administrator'];
          include_once($admin_tpl . '/header' . $admin_tplext);
          include_once($admin_tpl . '/message' . $admin_tplext);
          include_once($admin_tpl . '/footer' . $admin_tplext);
        }
        else {
          // errors exist
          $page_title = 'Errors Exist: Adminstration Edit Gallery - eXhibition';
          $message = 'Logged in as: ' . $_SESSION['administrator'];

          // set a number for pagination
          if (!isset($_GET['page'])) {
            $page = 1;
          }
          else {
            $page = gp_filter($_GET['page']);
          }

          // set a message 
          if ($_SESSION['num_checkbox'] == 1) {
            $num_post_message = '1 gallery selected';
          }
          else {
            $num_post_message = $_SESSION['num_checkbox'] . ' galleries selected';
          }

          // set maximum number of rows per page
          $max_results = 1;
          // calculate the page offset
          $offset = (($page * $max_results) - $max_results);
          // calculate total pages
          $total_pages = ceil($_SESSION['num_checkbox'] / $max_results);

          // format the checkbox selections for sql
          $in = '';
          foreach ($_SESSION['checkbox'] as $key => $value) {
            $in .= $value . ', ';
          }
          $in = rtrim($in, ', ');

          // paginate query
          $query = "SELECT
                      gallery_id,
                      gallery_name,
                      gallery_icon
                    FROM gallery WHERE gallery_id IN ($in)
                    AND
                      gallery_id = $_SESSION[gallery_id]
                    LIMIT 
                      $offset, $max_results";

          // get the gallery details
          $result = mysql_query($query);
          if (!mysql_num_rows($result)) {
            // no galleries found
            $info = '<h4>No galleries found!</h4>';
            include_once($admin_tpl . '/header' . $admin_tplext);
            include_once($admin_tpl . '/message' . $admin_tplext);
            include_once($admin_tpl . '/footer' . $admin_tplext);
            exit();
          }
          else {
            // galleries found
            while ($row = mysql_fetch_assoc($result)) {
              // format the gallery_icon
              include_once('inc/image_inc.php');
              $gallery_icon = resize_image('gallery_icon', '75');
            }
          }

          // paginate
          include_once('inc/paginate_inc.php');
          $pagination_row = paginate_child($page, $total_pages);

          // activate the edit form
          include_once('inc/gallery_inc.php');
          $rss_select = rss_select($_SESSION['rss_channel']);
          $gallery_cat_select = gallery_cat_selectbox($_SESSION['category_id']);
          $display_select = display_selectbox($_SESSION['display']);
          include_once($admin_tpl . '/header' . $admin_tplext);
          include_once($admin_tpl . '/gallery_edit_form' . $admin_tplext);
          include_once($admin_tpl . '/footer' . $admin_tplext);
        }
        break;
    }
  }
  // the delete button is clicked:
  elseif (isset($_POST['delete']) && $_POST['delete'] == 'Delete' || isset($_SESSION['action']) && $_SESSION['action'] == 'gallery_delete') {
    if (!empty($_POST['checkbox'])) {
      // load the session vars
      $_SESSION['action']       = 'gallery_delete';
      $_SESSION['num_checkbox'] = count($_POST['checkbox']);
      $_SESSION['checkbox']     = $_POST['checkbox'];
    }
    elseif (empty($_SESSION['checkbox'])) {
      // if no box is selected when an action is requested, do nothing.
      header('location: gallery.php');
      exit();
    }

    // set message vars
    $page_title = 'Adminstration Gallery Delete - eXhibition';
    $message = 'Logged in as: ' . $_SESSION['administrator'];

    if (!isset($_POST['task'])) {
      $_POST['task'] = 'request';
    }

    // $task may be request or Delete
    switch (gp_filter($_POST['task'])) {
      default:
      case 'request':
        // set a number for pagination
        if (!isset($_GET['page'])) {
          $page = 1;
        }
        else {
          $page = gp_filter($_GET['page']);
        }

        // set a message 
        if ($_SESSION['num_checkbox'] == 1) {
          $num_post_message = '1 gallery selected';
        }
        else {
          $num_post_message = $_SESSION['num_checkbox'] . ' galleries selected';
        }

        // set maximum number of rows per page
        $max_results = 1;
        // calculate the page offset
        $offset = (($page * $max_results) - $max_results);
        // calculate total pages
        $total_pages = ceil($_SESSION['num_checkbox'] / $max_results);

        // format the checkbox selections for sql
        $in = '';
        foreach ($_SESSION['checkbox'] as $key => $value) {
          $in .= $value . ', ';
        }
        $in = rtrim($in, ', ');

        // main query
        $query = "SELECT
                    m.title,
                    g.gallery_name,
                    g.gallery_icon,
                    m.description
                  FROM gallery g, meta_data m WHERE g.gallery_id IN ($in)
                  AND g.meta_data_id = m.meta_data_id
                  LIMIT 
                    $offset, $max_results";

        $result = mysql_query($query);

        if (!mysql_num_rows($result)) {
          // no galleries found
          $info = '<h4>No galleries found!</h4>';
          include_once($admin_tpl . '/header' . $admin_tplext);
          include_once($admin_tpl . '/message' . $admin_tplext);
          include_once($admin_tpl . '/footer' . $admin_tplext);
          exit();
        }
        else {
          // galleries found
          $details = '';
          while ($row = mysql_fetch_assoc($result)) {
            $gallery_name = $row['gallery_name'];
            // format the gallery_icon
            include_once('inc/image_inc.php');
            $row['gallery_icon'] = resize_image('gallery_icon', '75');
            $details .= '    <tr>' . "\n" .
                        '        <th colspan="2" bgcolor="#ff8d74" height="30">' . "\n" .
                        '            <p align="center">' . $row['gallery_name'] . '</p>' . "\n" .
                        '        </th>' . "\n" .
                        '    </tr>' . "\n";

            // format the gallery details
            foreach ($row as $label => $field) {
              if ($label != 'gallery_name') {
                $details .= '    <tr>' . "\n" .
                            '        <td colspan="2" align="center" bgcolor="#eeeeee" height="40">' . "\n" .
                            '            ' . $field . "\n" .
                            '        </td>' . "\n" .
                            '    </tr>' . "\n";
              }
            }
          }
        }

        // construct pagination links
        include_once('inc/paginate_inc.php');
        paginate_child($page, $total_pages);

        // activate the template
        include_once($admin_tpl . '/header' . $admin_tplext);
        include_once($admin_tpl . '/delete' . $admin_tplext);
        include_once($admin_tpl . '/footer' . $admin_tplext);

        break;
      case 'Delete':
        // delete this row from the database
        $gallery_id = $_SESSION['checkbox'][0];

        $query = "DELETE FROM 
                    gallery, meta_data
                  USING
                    gallery, meta_data
                  WHERE
                    gallery.gallery_id = '$gallery_id'
                  AND
                    gallery.meta_data_id = meta_data.meta_data_id";

        if (!$result = mysql_query($query)) {
          $info = '<h4>Not deleted! Please try again later.</h4>' . "\n";
        }
        else {
          $info = '<h4>Item deleted!</h4>' . "\n";
        }

        // show the confirm delete template
        include_once($admin_tpl . '/header' . $admin_tplext);
        include_once($admin_tpl . '/message' . $admin_tplext);
        include_once($admin_tpl . '/footer' . $admin_tplext);
        break;
    }
  }
  // no button was clicked:
  else {
    // List view: build a summary list of existing galleries

    // set message vars
    $page_title = 'Administrate Galleries - eXhibition';
    $message = 'Logged in as: ' . $_SESSION['administrator'];

    // count the database rows
    $query = "SELECT count(*) FROM gallery";
    $result = mysql_query($query);
    if (!$result) {
      $num_rows = 0;
    }
    else {
      $num_rows = mysql_fetch_row($result);
    }

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
      $default_fieldname = 'gallery_id';
    }

    // initialize sort vars
    $orderby               = 'gallery_id DESC'; // appended to sql query
    $sortpic               = null;              // arrow for column heading - may point up or down
    $orderby_gallery_id    = 'gallery_id';      // appended to column heading link
    $orderby_title         = 'title';           // appended to column heading link
    $orderby_gallery_icon  = 'gallery_icon';    // appended to column heading link
    $orderby_gallery_name  = 'gallery_name';    // appended to column heading link
    $orderby_display       = 'display';         // appended to column heading link
    $orderby_priority      = 'priority';        // appended to column heading link
    $paginate_str          = 'gallery_id';      // appended to pagination link
    $sortpic_up            = '<img src="img/sort_up.gif" width="13" height="11" alt="Sort by ' . $default_fieldname . '" title="Sort by ' . $default_fieldname . '">';
    $sortpic_dn            = '<img src="img/sort_dn.gif" width="13" height="11" alt="Sort by ' . substr($default_fieldname, 0, -5) . ' descending" title="Sort by ' . substr($default_fieldname, 0, -5) . ' descending">';
    if (!isset($_GET['sort'])) {
      $_GET['sort'] = 'gallery_id';
    }

    // maintain sort vars when column headings are clicked
    switch (gp_filter($_GET['sort'])) {
      case 'gallery_id':
      default:
        $orderby               = 'gallery_id ASC';
        $orderby_gallery_id    = 'gallery_id_desc';
        $orderby_gallery_name  = 'gallery_name';
        $orderby_gallery_icon = 'gallery_icon';
        $orderby_display       = 'display';
        $orderby_meta_data_id  = 'meta_data_id';
        $paginate_str          = 'gallery_id';
        $sortpic               = $sortpic_up;
        break;
      case 'gallery_id_desc':
        $orderby            = 'gallery_id DESC';
        $orderby_gallery_id = 'gallery_id';
        $paginate_str       = 'gallery_id_desc';
        $sortpic            = $sortpic_dn;
        break;
      case 'title':
        $orderby       = 'title ASC';
        $orderby_title = 'title_desc';
        $paginate_str  = 'title';
        $sortpic       = $sortpic_up;
        break;
      case 'title_desc':
        $orderby       = 'title DESC';
        $orderby_title = 'title';
        $paginate_str  = 'title_desc';
        $sortpic       = $sortpic_dn;
        break;
      case 'gallery_icon':
        $orderby               = 'gallery_icon ASC';
        $orderby_gallery_icon = 'gallery_icon_desc';
        $paginate_str          = 'gallery_icon';
        $sortpic               = $sortpic_up;
        break;
      case 'gallery_icon_desc':
        $orderby               = 'gallery_icon DESC';
        $orderby_gallery_icon = 'gallery_icon';
        $paginate_str          = 'gallery_icon_desc';
        $sortpic               = $sortpic_dn;
        break;
      case 'gallery_name':
        $orderby              = 'gallery_name ASC';
        $orderby_gallery_name = 'gallery_name_desc';
        $paginate_str         = 'gallery_name';
        $sortpic              = $sortpic_up;
        break;
      case 'gallery_name_desc':
        $orderby              = 'gallery_name DESC';
        $orderby_gallery_name = 'gallery_name';
        $paginate_str         = 'gallery_name_desc';
        $sortpic              = $sortpic_dn;
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
      case 'priority':
        $orderby          = 'priority ASC';
        $orderby_priority = 'priority_desc';
        $paginate_str     = 'priority';
        $sortpic          = $sortpic_up;
        break;
      case 'priority_desc':
        $orderby          = 'priority DESC';
        $orderby_priority = 'priority';
        $paginate_str     = 'priority_desc';
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

    $gallery_id_th = $sub1 . $orderby_gallery_id . $sub2 . '">Gallery ID</a>';
    if ($paginate_str == 'gallery_id' || $paginate_str == 'gallery_id_desc') {
      $gallery_id_th .= $sortpic;
    }
    $gallery_id_th .= "\n";

    $title_th = $sub1 . $orderby_title . $sub2 . '">Title</a>';
    if ($paginate_str == 'title' || $paginate_str == 'title_desc') {
      $title_th .= $sortpic;
    }
    $title_th .= "\n";

    $gallery_icon_th = $sub1 . $orderby_gallery_icon . $sub2 . '">Icon</a>';
    if ($paginate_str == 'gallery_icon' || $paginate_str == 'gallery_icon_desc') {
      $gallery_icon_th .= $sortpic;
    }
    $gallery_icon_th .= "\n";

    $gallery_name_th = $sub1 . $orderby_gallery_name . $sub2 . '">Name</a>';
    if ($paginate_str == 'gallery_name' || $paginate_str == 'gallery_name_desc') {
      $gallery_name_th .= $sortpic;
    }
    $gallery_name_th .= "\n";

    $display_th = $sub1 . $orderby_display . $sub2 . '">Display</a>';
    if ($paginate_str == 'display' || $paginate_str == 'display_desc') {
      $display_th .= $sortpic;
    }
    $display_th .= "\n";

    $priority_th = $sub1 . $orderby_priority . $sub2 . '">Priority</a>';
    if ($paginate_str == 'priority' || $paginate_str == 'priority_desc') {
      $priority_th .= $sortpic;
    }
    $priority_th .= "\n";

    // list query: get a summary list of existing galleries
    $query = "SELECT
                g.gallery_id,
                m.title,
                g.gallery_icon,
                g.gallery_name,
                m.display,
                m.priority 
              FROM 
                gallery g,
                meta_data m
              WHERE
                g.meta_data_id = m.meta_data_id
              ORDER BY
                $orderby
              LIMIT
                $offset, $max_results";

    // get data rows from mysql
    $result = mysql_query($query);

    if (!$result) {
      // no galleries exist
      $data_rows = '    <tr>' . "\n" .
                   '        <td colspan="7" align="center" height="35" bgcolor="#eeeeee">' . "\n" .
                   '            No galleries found!' . "\n" .
                   '        </td>' . "\n" .
                   '    </tr>' . "\n";
    }
    else {
      if (mysql_num_rows($result) < 1) {
        // no galleries exist
        $data_rows = '    <tr>' . "\n" .
                     '        <td colspan="7" align="center" height="35" bgcolor="#eeeeee">' . "\n" .
                     '            No galleries found!' . "\n" .
                     '        </td>' . "\n" .
                     '    </tr>' . "\n";
      }
      else {
        // galleries exist
        $color1 = '#e5e5e5';
        $color2 = '#eeeeee';
        $data_rows = '';
        $i = 0;
        while ($row = mysql_fetch_array($result)) {
          $gallery_id   = $row['gallery_id'];
          $title        = $row['title'];
          $gallery_name = $row['gallery_name'];
          $display      = $row['display'];
          $priority     = $row['priority'];

          // print out the gallery icon with a maximum size of 75 pixels
          include_once('inc/image_inc.php');
          $gallery_icon = resize_image('gallery_icon', 75);

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
          '                    <input type="checkbox" name="checkbox[]" value="' . $gallery_id . '"' . $checked[$i] . '>' . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="' . $row_color . '" align="center" valign="middle">' . "\n" .
          '                     ' . $gallery_id . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="' . $row_color . '" align="center" valign="middle">' . "\n" .
          '                    ' . $gallery_name . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="' . $row_color . '" width="79" height="79" align="center" valign="middle">' . "\n" .
          '                    ' . $gallery_icon . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="' . $row_color . '" align="center" valign="middle">' . "\n" .
          '                     ' . $title . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="' . $row_color . '" align="center" valign="middle">' . "\n" .
          '                    ' . $priority . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="' . $row_color . '" align="center" valign="middle">' . "\n" .
          '                    ' . $display . "\n" .
          '                </td>' . "\n" .
          '            </tr>' . "\n";
          $i++;
        }
      }
    }
    // construct pagination links
    include_once('inc/paginate_inc.php');
    paginate_list($page, $total_pages);

    // activate the gallery_list template
    include_once($admin_tpl . '/header' . $admin_tplext);
    include_once($admin_tpl . '/gallery_list' . $admin_tplext);
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