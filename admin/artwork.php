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
  $item           = 'Artwork';
  $id_type        = 'artwork_id';

  // the quit button is clicked
  if (isset($_POST['quit']) && $_POST['quit'] == 'Quit') {
    unset($_POST);
    unset($errors);
    unset($_SESSION['action']);
    unset($_SESSION['num_checkbox']);
    unset($_SESSION['checkbox']);
    unset($_SESSION['orderby']);

    // unset any remaining session variables
    unset($_SESSION['artwork_id']);
    unset($_SESSION['thumbnail']);
    unset($_SESSION['new_thumbnail']);
    unset($_SESSION['current_thumbnail']);
    unset($_SESSION['view_thumbnail']);
    unset($_SESSION['type']);
    unset($_SESSION['artist']);
    unset($_SESSION['artist_id']);
    unset($_SESSION['medium']);
    unset($_SESSION['size']);
    unset($_SESSION['style']);
    unset($_SESSION['subject']);
    unset($_SESSION['price']);
    unset($_SESSION['shipping']);
    unset($_SESSION['handling']);
    unset($_SESSION['sale_amount']);
    unset($_SESSION['sale_date']);
    unset($_SESSION['date_completed']);
    unset($_SESSION['year_completed']);
    unset($_SESSION['month_completed']);
    unset($_SESSION['day_completed']);
    unset($_SESSION['status']);
    unset($_SESSION['qty_instock']);
    unset($_SESSION['comments']);
    unset($_SESSION['gallery_id']);
    unset($_SESSION['gallery']);

    unset($_SESSION['meta_data_id']);
    unset($_SESSION['meta_type']);
    unset($_SESSION['content_id']);
    unset($_SESSION['title']);
    unset($_SESSION['description']);
    unset($_SESSION['keywords']);
    unset($_SESSION['display']);
    unset($_SESSION['rss_feed']);
    unset($_SESSION['priority']);
    unset($_SESSION['link']);
    unset($_SESSION['pub_date']);
    unset($_SESSION['last_updated']);

    unset($_SESSION['image_id']);
    unset($_SESSION['path']);
    unset($_SESSION['caption']);
  }

  // the add button is clicked:
  if (isset($_POST['add']) && $_POST['add'] == 'Add' || isset($_SESSION['action']) && $_SESSION['action'] == 'artwork_add') {
    // load session vars
    $_SESSION['action'] = 'artwork_add';

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
        $info = 'Adding new artwork...';
        $page_title = 'Adminstration Add Art Work - eXhibition';

        // activate the add new artwork form
        include_once('inc/artwork_inc.php');
        $rss_feed_selectbox    = rss_select('disable');
        $display_select = display_selectbox();
        $comment_select = comment_selectbox();
        $type_select    = type_selectbox();
        $status_select  = status_selectbox();
        $gallery_select = gallery_selectbox();
        $date_complete_select = date_completed();
        $artist_select = artist_selectbox();    
        include_once($admin_tpl . '/header' . $admin_tplext);
        include_once($admin_tpl . '/artwork_add_form' . $admin_tplext);
        include_once($admin_tpl . '/footer' . $admin_tplext);
        break;
      case 'Save':
        // the add artwork form is submitted
        // set message vars
        $message = 'Logged in as: ' . $_SESSION['administrator'];
        $page_title = 'Adminstration Add Art Work - eXhibition';

        // load the session variables from post:
        if (isset($_POST['gallery_id'])) {
          $_SESSION['gallery_id'] = gp_filter($_POST['gallery_id']);
        }

        if (isset($_POST['meta_type'])) {
          $_SESSION['meta_type'] = gp_filter($_POST['meta_type']);
        }

        if (isset($_POST['content_id'])) {
          $_SESSION['content_id'] = gp_filter($_POST['content_id']);
        }

        if (isset($_POST['title'])) {
          $_SESSION['title'] = gp_filter($_POST['title']);
        }

        if (isset($_POST['new_thumbnail'])) {
          $_SESSION['new_thumbnail'] = gp_filter($_POST['new_thumbnail']);
        }

        if (isset($_POST['thumbnail'])) {
          $_SESSION['current_thumbnail'] = gp_filter($_POST['thumbnail']);
        }

        if (isset($_POST['display'])) {
          $_SESSION['display'] = gp_filter($_POST['display']);
        }

        if (isset($_POST['type'])) {
          $_SESSION['type'] = gp_filter($_POST['type']);
        }

        if (isset($_POST['year_completed'])) {
          $_SESSION['year_completed'] = gp_filter($_POST['year_completed']);
        }

        if (isset($_POST['month_completed'])) {
          $_SESSION['month_completed'] = gp_filter($_POST['month_completed']);
        }

        if (isset($_POST['day_completed'])) {
          $_SESSION['day_completed'] = gp_filter($_POST['day_completed']);
        }

        if (isset($_POST['status'])) {
          $_SESSION['status'] = gp_filter($_POST['status']);
        }

        if (isset($_POST['qty_instock'])) {
          $_SESSION['qty_instock'] = gp_filter($_POST['qty_instock']);
        }

        if (isset($_POST['price'])) {
          $_SESSION['price'] = gp_filter($_POST['price']);
        }

        if (isset($_POST['shipping'])) {
          $_SESSION['shipping']  = $_POST['shipping'];
        }

        if (isset($_POST['handling'])) {
          $_SESSION['handling']  = $_POST['handling'];
        }

        if (isset($_POST['priority'])) {
          $_SESSION['priority'] = gp_filter($_POST['priority']);
        }

        if (isset($_POST['comments'])) {
          $_SESSION['comments'] = gp_filter($_POST['comments']);
        }

        if (isset($_POST['rss_feed'])) {
          $_SESSION['rss_feed'] = gp_filter($_POST['rss_feed']);
        }

        if (isset($_POST['artist_id'])) {
          $_SESSION['artist_id'] = gp_filter($_POST['artist_id']);
        }

        if (isset($_POST['medium'])) {
          $_SESSION['medium'] = gp_filter($_POST['medium']);
        }

        if (isset($_POST['size'])) {
          $_SESSION['size'] = gp_filter($_POST['size']);
        }

        if (isset($_POST['style'])) {
          $_SESSION['style'] = gp_filter($_POST['style']);
        }

        if (isset($_POST['subject'])) {
          $_SESSION['subject'] = gp_filter($_POST['subject']);
        }

        if (isset($_POST['description'])) {
          $_SESSION['description'] = gp_filter($_POST['description']);
        }

        if (isset($_POST['keywords'])) {
          $_SESSION['keywords'] = gp_filter($_POST['keywords']);
        }

        // new_thumbnail may be 'upload' or 'current'
        if (!empty($_SESSION['new_thumbnail']) && $_SESSION['new_thumbnail'] == 'upload') {
          // go ahead and upload the image now
          include_once('inc/files_inc.php');
          if (upload_thumbnail($_FILES) && empty($errors)) {
            $_SESSION['thumbnail'] = $new_image;
          }
          else {
            die("Upload Failed");
          }
          $_SESSION['view_thumbnail'] = '<img src="http://' . $_SERVER['HTTP_HOST'] . '/' . $home_dir . '/images/original_art/thumbnails/' . $_SESSION['thumbnail'] . '" alt="' . $_SESSION['title'] . '" title="' . $_SESSION['title'] . '"><p>(' . $_SESSION['thumbnail'] . ')</p>';
        }
        elseif ($_SESSION['new_thumbnail'] == 'current') {
          $_SESSION['thumbnail'] = $_SESSION['current_thumbnail'];
        }

        // Run validation routines on session variables
        validate_artwork();

        if (empty($errors)) {
          // creating a new artwork listing
          $_SESSION['date_completed'] = $_SESSION['year_completed'] . '-' . $_SESSION['month_completed'] . '-' . $_SESSION['day_completed'];
          include_once('../includes/time_inc.php');
          $now = get_datetime();
          // insert meta_data from $_SESSION
          $query ="INSERT INTO 
                     meta_data (meta_data_id,
                                meta_type, 
                                title,
                                description,
                                keywords,
                                display,
                                rss_feed,
                                priority,
                                pub_date,
                                last_updated)
                     VALUES ('',
                             'artwork',
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
            // retrieve meta_data_id
            $query = "SELECT LAST_INSERT_ID() AS meta_data_id FROM meta_data";
            $result = mysql_query($query);
            $meta_data_id = mysql_result($result, 0, 'meta_data_id');
            if ($result) {
              // insert artwork details
              $query = "INSERT INTO 
                          artwork(
                            artwork_id,
                            thumbnail,
                            type,
                            artist_id,
                            medium,
                            size,
                            style,
                            subject,
                            price,
                            shipping,
                            handling,
                            sale_amount,
                            sale_date,
                            date_completed,
                            status,
                            qty_instock,
                            comments,
                            gallery_id,
                            meta_data_id)
                        VALUES(
                          '',
                          '$_SESSION[thumbnail]',
                          '$_SESSION[type]',
                          '$_SESSION[artist_id]',
                          '$_SESSION[medium]',
                          '$_SESSION[size]',
                          '$_SESSION[style]',
                          '$_SESSION[subject]',
                          '$_SESSION[price]',
                          '$_SESSION[shipping]',
                          '$_SESSION[handling]',
                          '0.00',
                          '0000-00-00 00:00:00',
                          '$_SESSION[date_completed]',
                          '$_SESSION[status]',
                          '$_SESSION[qty_instock]',
                          '$_SESSION[comments]',
                          '$_SESSION[gallery_id]',
                          '$meta_data_id')";
              $result = mysql_query($query);
              if ($result) {
                // retrieve artwork_id
                $query = "SELECT LAST_INSERT_ID() AS artwork_id FROM artwork";
                $result = mysql_query($query);
                $artwork_id = mysql_result($result, 0, 'artwork_id');
                if ($result) {
                  // cat together a page link from art_id
                  $link = $site['url'] . '/' . $home_dir . '/artwork.php?id=' . $artwork_id;
                  // update meta_data row with link
                  $query = "UPDATE meta_data SET link = '$link' WHERE meta_data_id = $meta_data_id";
                  $result = mysql_query($query);
                  if ($result) {
                    // show confirmation
                    $info = '<h4>New artwork listing successfully created!</h4>' . "\n" .
                            '<p>Upload another image for this artwork?</p>' . "\n" .
                            '<input type="hidden" name="MAX_FILE_SIZE" value="1000000">' . "\n" .
                            '<input type="hidden" name="continue_image" value="upload">' . "\n" .
                            '<input type="file" name="artimage"><br>' . "\n" .
                            'Optional image caption: <input type="text" name="caption" size="55" maxlength="255">' . "\n" . 
                            '<input type="hidden" name="artwork_id" value="' . $artwork_id . '">' . "\n" .
                            '<input type="submit" name="task" value="Save Image">' . "\n";

                  }
                }
              }
            }
          }

          // activate the confirmation template
          include_once($admin_tpl . '/header' . $admin_tplext);
          include_once($admin_tpl . '/message' . $admin_tplext);
          include_once($admin_tpl . '/footer' . $admin_tplext);
        }
        else {
          $info = 'Errors exist: Edit details for new artwork.';
          // activate the edit form
          include_once('inc/artwork_inc.php');
          $display_select = display_selectbox($_SESSION['display']);
          $artist_select  = artist_selectbox($_SESSION['artist_id']);
          $comment_select = comment_selectbox($_SESSION['comment_display']);
          $type_select    = type_selectbox($_SESSION['type']);
          $status_select  = status_selectbox($_SESSION['status']);
          $gallery_select = gallery_selectbox($_SESSION['gallery']);
          include_once($admin_tpl . '/header' . $admin_tplext);
          include_once($admin_tpl . '/artwork_edit_form' . $admin_tplext);
          include_once($admin_tpl . '/footer' . $admin_tplext);
        }
        break;
      case 'Save Image':
        // An extra image is added to a saved artwork
        if (!empty($_POST['continue_image']) && $_POST['continue_image'] == 'upload') {
          $artwork_id = gp_filter($_POST['artwork_id']);
          if (!empty($_POST['caption'])) {
            $caption = gp_filter($_POST['caption']);
          }
          else {
            $caption = null;
          }
          // upload the new image
          include_once('inc/files_inc.php');
          if (upload_artwork($_FILES) && empty($errors)) {
            $newart_image = $new_image;
            // update the image table
            $query = "INSERT INTO image (image_id, artwork_id, path, caption) VALUES ('', $artwork_id, '$newart_image', '$caption')";
            $result = mysql_query($query) or die(mysql_error());
            // show another upload button
            $info = '<h4>New image successfully uploaded!</h4>' . "\n" .
                    '<p>Upload another image for this artwork?</p>' . "\n" .
                    '<input type="hidden" name="MAX_FILE_SIZE" value="1000000">' . "\n" .
                    '<input type="hidden" name="continue_image" value="upload">' . "\n" .
                    '<input type="file" name="artimage"><br>' . "\n" .
                    'Optional image caption: <input type="text" name="caption" size="55" maxlength="255">' . "\n" . 
                    '<input type="hidden" name="artwork_id" value="' . $artwork_id . '">' . "\n" .
                    '<input type="submit" name="task" value="Save Image">' . "\n";
          }
          else {
            if (!empty($errors)) {
              if (isset($errors['artimage']['filetype'])) {
                $info = $errors['artimage']['filetype'] . "\n";
              }
              elseif (isset($errors['artimage']['upload'])) {
                $info = $errors['artimage']['upload'] . "\n";
              }
            }
            else {
              die("Upload Failed");
            }
          }
          // activate the confirmation template
          include_once($admin_tpl . '/header' . $admin_tplext);
          include_once($admin_tpl . '/message' . $admin_tplext);
          include_once($admin_tpl . '/footer' . $admin_tplext);
        }
        break;
    }
  }

  // the view button is clicked:
  elseif (isset($_POST['view']) && $_POST['view'] == 'View' || isset($_SESSION['action']) && $_SESSION['action'] == 'artwork_view') {
    if (!empty($_POST['checkbox'])) {
      // load the session vars
      $_SESSION['action']       = 'artwork_view';
      $_SESSION['num_checkbox'] = count($_POST['checkbox']);
      $_SESSION['checkbox']     = $_POST['checkbox'];
    }
    elseif (empty($_SESSION['checkbox'])) {
      // if no box is selected when an action is requested, do nothing.
      header('location: ' . $_SERVER['PHP_SELF']);
    }

    // set message vars
    $page_title = 'Adminstration Art Work Details - eXhibition';
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
      $num_post_message = '1 artwork selected';
    }
    else {
      $num_post_message = $_SESSION['num_checkbox'] . ' artworks selected';
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
                a.artwork_id AS 'Artwork ID',
                g.gallery_name AS 'Gallery',
                m.title AS 'Title',
                a.thumbnail AS 'Image',
                m.display AS 'Display',
                a.type AS 'Type',
                a.date_completed AS 'Date Completed',
                a.status AS 'Status',
                a.price AS 'Price',
                a.shipping AS 'Shipping',
                a.handling AS 'Handling',
                a.qty_instock AS 'Quantity',
                m.priority AS 'Priority',
                m.link AS 'Link',
                a.comments AS 'Comments',
                m.rss_feed AS 'RSS Feed',
                x.name AS 'Artist',
                a.medium AS 'Medium',
                a.size AS 'Dimensions',
                a.style AS 'Style',
                a.subject AS 'Subject',
                m.description AS 'Description',
                m.keywords AS 'Key Words'
              FROM 
                artwork a,
                artist x,
                gallery g,
                meta_data m
              WHERE
                a.artwork_id IN ($in)
              AND a.meta_data_id = m.meta_data_id
              AND a.gallery_id = g.gallery_id
              AND a.artist_id = x.artist_id
              LIMIT 
                $offset, $max_results";

    // get the artwork details
    if ($result = mysql_query($query)) {
      $details = '';
      while ($row = mysql_fetch_assoc($result)) {
        // format the thumbnail
        include_once('inc/image_inc.php');
        $row['Image'] = resize_image('Image', 200);

        // format the artwork details
        foreach ($row as $label => $field) {
          if ($label == 'Artwork ID') {
            $artwork_id = $field;
          }
          $details .= '    <tr>' . "\n" .
                      '        <td align="right" class="label" bgcolor="#147a14" height="30">' . "\n" .
                      '            ' . $label . "\n" .
                      '        </td>' . "\n" .
                      '        <td class="field" bgcolor="#147a14" height="30">' . "\n" .
                      '            ' . $field . "\n" .
                      '        </td>' . "\n";
          $details .= '    </tr>' . "\n";
        }
      }
    }

    // get extra images
    $query = "SELECT path, caption FROM image WHERE artwork_id = $artwork_id";

    if ($result = mysql_query($query)) {
      while ($row = mysql_fetch_assoc($result)) {
        $row['path'] = resize_image('artwork');
        $details .= 
                    '    <tr>' . "\n" .
                    '        <td height="30" align="center" colspan="2" class="label" bgcolor="#147a14">' . "\n" .
                    '             Image:' . "\n" .
                    '        </td>' . "\n" .
                    '    </tr>' . "\n" .
                    '    <tr>' . "\n" .
                    '        <td align="center" colspan="2" class="label" bgcolor="#147a14">' . "\n" .
                    '            ' . $row['path'] . "\n" .
                    '        </td>' . "\n" .
                    '    </tr>' . "\n";

        if (!empty($row['caption'])) {
          $details .= '    <tr>' . "\n" .
                      '        <td align="center" colspan="2" class="field" bgcolor="#147a14" height="30">' . "\n" .
                      '            ' . $row['caption'] . "\n" .
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
  elseif (isset($_POST['edit']) && $_POST['edit'] == 'Edit' || isset($_SESSION['action']) && $_SESSION['action'] == 'artwork_edit') {
    if (!empty($_POST['checkbox'])) {
      // load the session vars
      $_SESSION['action']       = 'artwork_edit';
      $_SESSION['num_checkbox'] = count($_POST['checkbox']);
      $_SESSION['checkbox']     = $_POST['checkbox'];
    }
    elseif (empty($_SESSION['checkbox'])) {
      // if no box is selected when an action is requested, do nothing.
      header('location: ' . $_SERVER['PHP_SELF']);
    }

    // $task may be: form, Save or Add Image
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
        $page_title = 'Administration Edit Art Work - eXhibition';
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
          $num_post_message = '1 artwork selected';
        }
        else {
          $num_post_message = $_SESSION['num_checkbox'] . ' artworks selected';
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
                    a.artwork_id,
                    a.type,
                    a.thumbnail,
                    a.artist_id,
                    x.name as artist,
                    a.medium,
                    a.size,
                    a.style,
                    a.subject,
                    a.qty_instock,
                    a.price,
                    a.shipping,
                    a.handling,
                    a.status,
                    a.comments,
                    a.gallery_id,
                    m.title,
                    m.display,
                    m.description,
                    m.keywords,
                    m.rss_feed,
                    m.priority
                  FROM
                    artwork a,
                    artist x,
                    meta_data m
                  WHERE
                    a.artwork_id IN ($in)
                  AND
                    a.artist_id = x.artist_id
                  AND
                    a.meta_data_id = m.meta_data_id
                  LIMIT 
                    $offset, $max_results";

        if ($result = mysql_query($query)) {
         while ($row = mysql_fetch_array($result)) {
            // load data into session vars
            $_SESSION['artwork_id']  = $row['artwork_id'];
            $_SESSION['display']     = $row['display'];
            $_SESSION['comments']    = $row['comments'];
            $_SESSION['type']        = $row['type'];
            $_SESSION['thumbnail']   = $row['thumbnail'];
            $_SESSION['artist_id']   = $row['artist_id'];
            $_SESSION['artist']      = $row['artist'];
            $_SESSION['medium']      = $row['medium'];
            $_SESSION['size']        = $row['size'];
            $_SESSION['style']       = $row['style'];
            $_SESSION['subject']     = $row['subject'];
            $_SESSION['qty_instock'] = $row['qty_instock'];
            $_SESSION['price']       = $row['price'];
            $_SESSION['shipping']    = $row['shipping'];
            $_SESSION['handling']    = $row['handling'];
            $_SESSION['status']      = $row['status'];
            $_SESSION['comments']    = $row['comments'];
            $_SESSION['gallery_id']  = $row['gallery_id'];
            $_SESSION['title']       = $row['title'];
            $_SESSION['description'] = $row['description'];
            $_SESSION['keywords']    = $row['keywords'];
            $_SESSION['rss_feed']    = $row['rss_feed'];
            $_SESSION['priority']    = $row['priority'];

            include_once('inc/image_inc.php');
            $current_thumbnail = resize_image('thumbnail', '150');

            // prepare template variables for activation
            include_once('inc/artwork_inc.php');
            $artist_select  = artist_selectbox($_SESSION['artist_id']);
            $display_select = display_selectbox($_SESSION['display']);
            $comment_select = comment_selectbox($_SESSION['comments']);
            $type_select    = type_selectbox($_SESSION['type']);
            $status_select  = status_selectbox($_SESSION['status']);
            $gallery_select = gallery_selectbox($_SESSION['gallery_id']);
            $rss_select     = rss_select($_SESSION['rss_feed']);
          }

          // get any other images
          $query = "SELECT image_id, path, caption FROM image WHERE artwork_id = '$_SESSION[artwork_id]'";
          $result = mysql_query($query);
          if ($result) {
            if (!mysql_num_rows($result)) {
              // no images found
              $image_rows = null;
            }
            else {
              // images found so format them
              $image_rows = '';
              while ($row = mysql_fetch_array($result)) {
                include_once('inc/image_inc.php');
                $image = resize_image('artwork', '150');
                $image_rows .= '<tr>' . "\n" .
                               '    <td align="right" valign="middle" bgcolor="#eeeeee" height="160">' . "\n" .
                               '        Delete: <input type="checkbox" name="delete_image[]" value="' . $row['image_id'] . '"> &nbsp;' . "\n" .
                               '    </td>' . "\n" .
                               '    <td align="left" valign="middle" bgcolor="#eeeeee" height="160">' . "\n" .
                               '        ' . $image . '<br>' . $row['path'] . "\n" .
                               '    </td>' . "\n" .
                               '</tr>' . "\n" .
                               '<tr>' . "\n" .
                               '    <td align="right" bgcolor="#eeeeee" height="40">' . "\n" .
                               '        Caption for "' . $row['path'] . '" &nbsp;' . "\n" .
                               '    </td>' . "\n" .
                               '    <td align="left" bgcolor="#eeeeee" height="40">' . "\n" .
                               '        <input type="text" name="caption" value="' . $row['caption'] . '" size="75" maxlength="255">' . "\n" .
                               '    </td>' . "\n" .
                               '</tr>' . "\n";
              }
            }
          }
          else {
            // no images found
            $image_rows = null;
          } 
        }

        // construct pagination links
        include_once('inc/paginate_inc.php');
        paginate_child($page, $total_pages);


        // activate the edit form template
        include_once($admin_tpl . '/header' . $admin_tplext);
        include_once($admin_tpl . '/artwork_edit_form' . $admin_tplext);
        include_once($admin_tpl . '/footer' . $admin_tplext);

        // get rid of the session vars (except $_SESSION[artwork_id] which is still needed)
        unset($_SESSION['display']);
        unset($_SESSION['comments']);
        unset($_SESSION['type']);
        unset($_SESSION['thumbnail']);
        unset($_SESSION['current_thumbnail']);
        unset($_SESSION['artist']);
        unset($_SESSION['artist_id']);
        unset($_SESSION['medium']);
        unset($_SESSION['size']);
        unset($_SESSION['style']);
        unset($_SESSION['subject']);
        unset($_SESSION['qty_instock']);
        unset($_SESSION['price']);
        unset($_SESSION['shipping']);
        unset($_SESSION['handling']);
        unset($_SESSION['status']);
        unset($_SESSION['gallery_id']);
        unset($_SESSION['title']);
        unset($_SESSION['description']);
        unset($_SESSION['keywords']);
        unset($_SESSION['rss_feed']);
        unset($_SESSION['priority']);

        break;
      case 'Save':
        // load the session variables from post:
        if (isset($_POST['display'])) {
          $_SESSION['display'] = gp_filter($_POST['display']);
        }

        if (isset($_POST['comments'])) {
          $_SESSION['comments'] = gp_filter($_POST['comments']);
        }

        if (isset($_POST['rss_feed'])) {
          $_SESSION['rss_feed'] = gp_filter($_POST['rss_feed']);
        }

        if (isset($_POST['type'])) {
          $_SESSION['type'] = gp_filter($_POST['type']);
        }

        if (isset($_POST['new_thumbnail'])) {
          $_SESSION['new_thumbnail'] = gp_filter($_POST['new_thumbnail']);
        }

        if (isset($_POST['thumbnail'])) {
          $_SESSION['current_thumbnail'] = gp_filter($_POST['thumbnail']);
        }

        if (isset($_POST['artist_id'])) {
          $_SESSION['artist_id'] = gp_filter($_POST['artist_id']);
        }

        if (isset($_POST['medium'])) {
          $_SESSION['medium'] = gp_filter($_POST['medium']);
        }

        if (isset($_POST['size'])) {
          $_SESSION['size'] = gp_filter($_POST['size']);
        }

        if (isset($_POST['style'])) {
          $_SESSION['style'] = gp_filter($_POST['style']);
        }

        if (isset($_POST['subject'])) {
          $_SESSION['subject'] = gp_filter($_POST['subject']);
        }

        if (isset($_POST['qty_instock'])) {
          $_SESSION['qty_instock'] = gp_filter($_POST['qty_instock']);
        }

        if (isset($_POST['price'])) {
          $_SESSION['price'] = gp_filter($_POST['price']);
        }

        if (isset($_POST['shipping'])) {
          $_SESSION['shipping']  = $_POST['shipping'];
        }

        if (isset($_POST['handling'])) {
          $_SESSION['handling']  = $_POST['handling'];
        }

        if (isset($_POST['status'])) {
          $_SESSION['status'] = gp_filter($_POST['status']);
        }

        if (isset($_POST['gallery_id'])) {
          $_SESSION['gallery_id'] = gp_filter($_POST['gallery_id']);
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

        // run validation routines on the session variabless
        validate_artwork();

        if (empty($errors)) {
          // Saving data...
          // perform appropriate image related actions depending on the radio selection
          if (!empty($_SESSION['new_thumbnail']) && $_SESSION['new_thumbnail'] == 'upload') {
            include_once('inc/files_inc.php');
            if (upload_thumbnail($_FILES) && empty($errors)) {
              $_SESSION['thumbnail'] = $new_image;
            }
            else {
              die("Upload Failed");
            }
          }
          elseif (!empty($_SESSION['new_thumbnail']) && $_SESSION['new_thumbnail'] == 'delete') {
            // find the old image
            $query = "SELECT
                        thumbnail AS old_thumbnail
                      FROM
                        artwork
                      WHERE
                        artwork_id = $_SESSION[artwork_id]";

            if ($result = mysql_query($query)) {
              $old_thumbnail = mysql_result($result, 0, 'old_thumbnail');

              // get the path to the old_thumbnail
              $old_thumbnail = $_SERVER['DOCUMENT_ROOT'] . '/' . $home_dir . '/images/original_art/thumbnails/' . $old_thumbnail;

              unlink($old_thumbnail) or die('unable to delete file');
              $_SESSION['thumbnail'] = null;
            }
            else {
              $_SESSION['thumbnail'] = null;
            }
          }
          elseif (!empty($_SESSION['new_thumbnail']) && $_SESSION['new_thumbnail'] == 'current') {
            $_SESSION['thumbnail'] = $_SESSION['current_thumbnail'];
          }

          // run a function to create a query string of changed fields
          include_once('inc/artwork_inc.php');
          $query_string = artwork_fields_diff();

          // main update query
          $update = "UPDATE
                       artwork a,
                       meta_data m
                     SET
                       $query_string 
                     WHERE
                       a.artwork_id = $_SESSION[artwork_id]
                     AND
                       a.meta_data_id = m.meta_data_id";

          // if the query string shows changes, run the update
          if ($query_string) {
            $result = mysql_query($update);
            $info = 'Changes saved.';
          }
          else {
            $info = 'No changes made.';
          }

          // unset session variables
          unset($_SESSION['artwork_id']);
          unset($_SESSION['display']);
          unset($_SESSION['comments']);
          unset($_SESSION['type']);
          unset($_SESSION['new_thumbnail']);
          unset($_SESSION['current_thumbnail']);
          unset($_SESSION['thumbnail']);
          unset($_SESSION['artist_id']);
          unset($_SESSION['medium']);
          unset($_SESSION['size']);
          unset($_SESSION['style']);
          unset($_SESSION['subject']);
          unset($_SESSION['qty_instock']);
          unset($_SESSION['price']);
          unset($_SESSION['shipping']);
          unset($_SESSION['handling']);
          unset($_SESSION['status']);
          unset($_SESSION['gallery_id']);
          unset($_SESSION['title']);
          unset($_SESSION['description']);
          unset($_SESSION['keywords']);

          // show a confirmation page
          $page_title = 'Adminstration Edit Art Work - eXhibition';
          $message = 'Logged in as: ' . $_SESSION['administrator'];
          include_once($admin_tpl . '/header' . $admin_tplext);
          include_once($admin_tpl . '/message' . $admin_tplext);
          include_once($admin_tpl . '/footer' . $admin_tplext);
        }
        else {
          // errors exist
          $page_title = 'Errors Exist: Adminstration Edit Art Work - eXhibition';
          $message = 'Logged in as: ' . $_SESSION['administrator'];

          // activate the edit form
          include_once('inc/artwork_inc.php');
          $display_select = display_selectbox($_SESSION['display']);
          $comment_select = comment_selectbox($_SESSION['comments']);
          $type_select    = type_selectbox($_SESSION['type']);
          $status_select  = status_selectbox($_SESSION['status']);
          $gallery_select = gallery_selectbox($_SESSION['gallery']);
          include_once($admin_tpl . '/artwork_edit_form' . $admin_tplext);
        }
        break;
      case 'Add Image':
        break;
    }
  }
  // the delete button is clicked:
  elseif (isset($_POST['delete']) && $_POST['delete'] == 'Delete' || isset($_SESSION['action']) && $_SESSION['action'] == 'artwork_delete') {
    if (!empty($_POST['checkbox'])) {
      // load the session vars
      $_SESSION['action']       = 'artwork_delete';
      $_SESSION['num_checkbox'] = count($_POST['checkbox']);
      $_SESSION['checkbox']     = $_POST['checkbox'];
    }
    elseif (empty($_SESSION['checkbox'])) {
      // if no box is selected when an action is requested, do nothing.
      header('location: ' . $_SERVER['PHP_SELF']);
    }

    // set message vars
    $page_title = 'Adminstration Art Work Delete - eXhibition';
    $message = 'Logged in as: ' . $_SESSION['administrator'];
    if (!isset($_POST['task'])) {
      $_POST['task'] = 'request';
    }

    // $task may be request or Confirm Delete
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
          $num_post_message = '1 artwork selected';
        }
        else {
          $num_post_message = $_SESSION['num_checkbox'] . ' artworks selected';
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
                    a.thumbnail,
                    m.description
                  FROM artwork a, meta_data m WHERE a.artwork_id IN ($in)
                  AND a.meta_data_id = m.meta_data_id
                  LIMIT 
                    $offset, $max_results";

        // get the artwork details
        if ($result = mysql_query($query)) {
          $details = '';
          while ($row = mysql_fetch_assoc($result)) {
            // format the thumbnail
            include_once('inc/image_inc.php');
            $row['thumbnail'] = resize_image('thumbnail', '150');
            $details .= '    <tr>' . "\n" .
                        '        <th colspan="2" bgcolor="#ff8d74" height="30">' . "\n" .
                        '            <p align="center">' . $row['title'] . '</p>' . "\n" .
                        '        </th>' . "\n" .
                        '    </tr>' . "\n";

            // format the artwork details
            foreach ($row as $label => $field) {
              if ($label == 'thumbnail') {
                $height = '175';
              }
              else {
                $height = '40';
              }
              if ($label != 'title') {
                $details .= '    <tr>' . "\n" .
                            '        <td colspan="2" align="center" bgcolor="#eeeeee" height="' . $height . '">' . "\n" .
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
      case 'Confirm Delete':
        // delete this row from the database
        $artwork_id = $_SESSION['checkbox'][0];

        $query = "DELETE FROM 
                    artwork, meta_data
                  USING
                    artwork, meta_data
                  WHERE
                    artwork.artwork_id = '$artwork_id'
                  AND
                    artwork.meta_data_id = meta_data.meta_data_id";

        if ($result = mysql_query($query)) {
          $info = '<h4>Item deleted!</h4>' . "\n";
        }
        else {
          $info = '<h4>Not deleted! Please try again later.</h4>' . "\n";
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
    // List view: build a summary list of existing artworks

    // set message vars
    $page_title = 'Adminstration Art Works - eXhibition';
    $message = 'Logged in as: ' . $_SESSION['administrator'];

    // count the database rows
    $query = "SELECT count(*) FROM artwork";
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
      $default_fieldname = 'artwork_id';
    }

    // initialize sort vars
    $orderby                 = 'artwork_id DESC';  // appended to sql query
    $orderby_artwork_id      = 'artwork_id';       // appended to column heading link
    $orderby_display         = 'display';          // appended to column heading link
    $orderby_type            = 'type';             // appended to column heading link
    $orderby_thumbnail       = 'thumbnail';        // appended to column heading link
    $orderby_artist          = 'artist';           // appended to column heading link
    $orderby_price           = 'price';            // appended to column heading link
    $orderby_status          = 'status';           // appended to column heading link
    $orderby_gallery         = 'gallery';          // appended to column heading link
    $paginate_str            = 'artwork_id';       // appended to pagination link
    $sortpic_up = '<img src="img/sort_up.gif" width="13" height="11" alt="Sort by ' . $default_fieldname . '" title="Sort by ' . $default_fieldname . '">';
    $sortpic_dn = '<img src="img/sort_dn.gif" width="13" height="11" alt="Sort by ' . substr($default_fieldname, 0, -5) . ' descending" title="Sort by ' . substr($default_fieldname, 0, -5) . ' descending">';
    $sortpic    = $sortpic_up;  // arrow for column heading - may point up or down
    if (!isset($_GET['sort'])) {
      $_GET['sort'] = 'artwork_id';
    }

    // maintain sort vars when column headings are clicked
    switch (gp_filter($_GET['sort'])) {
      case 'artwork_id':
      default:
        $orderby                 = 'artwork_id ASC';
        $orderby_artwork_id      = 'artwork_id_desc';
        $orderby_display         = 'display';
        $orderby_type            = 'type';
        $orderby_thumbnail       = 'thumbnail';
        $orderby_artist          = 'artist';
        $orderby_price           = 'price';
        $orderby_status          = 'status';
        $orderby_gallery         = 'gallery';
        $paginate_str            = 'artwork_id';
        $sortpic                 = $sortpic_up;
        break;
      case 'artwork_id_desc':
        $orderby            = 'artwork_id DESC';
        $orderby_artwork_id = 'artwork_id';
        $paginate_str       = 'artwork_id_desc';
        $sortpic            = $sortpic_dn;
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
      case 'type':
        $orderby      = 'type ASC';
        $orderby_type = 'type_desc';
        $paginate_str = 'type';
        $sortpic      = $sortpic_up;
        break;
      case 'type_desc':
        $orderby      = 'type DESC';
        $orderby_type = 'type';
        $paginate_str = 'type_desc';
        $sortpic      = $sortpic_dn;
        break;
      case 'thumbnail':
        $orderby           = 'thumbnail ASC';
        $orderby_thumbnail = 'thumbnail_desc';
        $paginate_str      = 'thumbnail';
        $sortpic           = $sortpic_up;
        break;
      case 'thumbnail_desc':
        $orderby           = 'thumbnail DESC';
        $orderby_thumbnail = 'thumbnail';
        $paginate_str      = 'thumbnail_desc';
        $sortpic           = $sortpic_dn;
        break;
      case 'artist':
        $orderby        = 'artist ASC';
        $orderby_artist = 'artist_desc';
        $paginate_str   = 'artist';
        $sortpic        = $sortpic_up;
        break;
      case 'artist_desc':
        $orderby        = 'artist DESC';
        $orderby_artist = 'artist';
        $paginate_str   = 'artist_desc';
        $sortpic        = $sortpic_dn;
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
      case 'status':
        $orderby        = 'status ASC';
        $orderby_status = 'status_desc';
        $paginate_str   = 'status';
        $sortpic        = $sortpic_up;
        break;
      case 'status_desc':
        $orderby        = 'status DESC';
        $orderby_status = 'status';
        $paginate_str   = 'status_desc';
        $sortpic        = $sortpic_dn;
        break;
      case 'gallery':
        $orderby         = 'gallery_name ASC';
        $orderby_gallery = 'gallery_desc';
        $paginate_str    = 'gallery';
        $sortpic         = $sortpic_up;
        break;
      case 'gallery_desc':
        $orderby         = 'gallery_name DESC';
        $orderby_gallery = 'gallery';
        $paginate_str    = 'gallery_desc';
        $sortpic         = $sortpic_dn;
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

    $artwork_id_th = $sub1 . $orderby_artwork_id . $sub2 . '">Art Work ID</a>';
    if ($paginate_str == 'artwork_id' || $paginate_str == 'artwork_id_desc') {
      $artwork_id_th .= $sortpic;
    }
    $artwork_id_th .= "\n";

    $display_th = $sub1 . $orderby_display . $sub2 . '">Display</a>';
    if ($paginate_str == 'display' || $paginate_str == 'display_desc') {
      $display_th .= $sortpic;
    }
    $display_th .= "\n";

    $type_th = $sub1 . $orderby_type . $sub2 . '">Type</a>';
    if ($paginate_str == 'type' || $paginate_str == 'type_desc') {
      $type_th .= $sortpic;
    }
    $type_th .= "\n";

    $thumbnail_th = $sub1 . $orderby_thumbnail . $sub2 . '">Thumbnail</a>';
    if ($paginate_str == 'thumbnail' || $paginate_str == 'thumbnail_desc') {
      $thumbnail_th .= $sortpic;
    }
    $thumbnail_th .= "\n";

    $artist_th = $sub1 . $orderby_artist . $sub2 . '">Artist</a>';
    if ($paginate_str == 'artist' || $paginate_str == 'artist_desc') {
      $artist_th .= $sortpic;
    }
    $artist_th .= "\n";

    $price_th = $sub1 . $orderby_price . $sub2 . '">Price</a>';
    if ($paginate_str == 'price' || $paginate_str == 'price_desc') {
      $price_th .= $sortpic;
    }
    $price_th .= "\n";

    $status_th = $sub1 . $orderby_status . $sub2 . '">Status</a>';
    if ($paginate_str == 'status' || $paginate_str == 'status_desc') {
      $status_th .= $sortpic;
    }
    $status_th .= "\n";

    $gallery_th = $sub1 . $orderby_gallery . $sub2 . '">Gallery</a>';
    if ($paginate_str == 'gallery' || $paginate_str == 'gallery_desc') {
      $gallery_th .= $sortpic;
    }
    $gallery_th .= "\n";

    // list query: get a summary list of existing artworks
    $query = "SELECT
                a.artwork_id,
                a.thumbnail,
                m.display,
                a.type,
                x.name as artist,
                a.price,
                a.status,
                g.gallery_name,
                m.title
              FROM 
                artwork a,
                artist x,
                gallery g,
                meta_data m
              WHERE
                a.gallery_id = g.gallery_id
              AND
                a.artist_id = x.artist_id
              AND
                a.meta_data_id = m.meta_data_id
              ORDER BY
                $orderby
              LIMIT
                $offset, $max_results";

    // get data rows from mysql
    $result = mysql_query($query);
    if (!mysql_num_rows($result)) {
      // no artworks exist
      $data_rows = '    <tr>' . "\n" .
                   '        <td colspan="9" align="center" height="35" bgcolor="#eeeeee">' . "\n" .
                   '            No artworks found!' . "\n" .
                   '        </td>' . "\n" .
                   '    </tr>' . "\n";
    }
    else {
      if (mysql_num_rows($result) < 1) {
        // no artworks exist
        $data_rows = '    <tr>' . "\n" .
                     '        <td colspan="9" align="center" height="35" bgcolor="#eeeeee">' . "\n" .
                     '            No artworks found!' . "\n" .
                     '        </td>' . "\n" .
                     '    </tr>' . "\n";
      }
      else {
        // artworks exist
        $color1 = '#e5e5e5';
        $color2 = '#eeeeee';
        $data_rows = '';
        $i = 0;
        while ($row = mysql_fetch_array($result)) {
          $artwork_id      = $row['artwork_id'];
          $thumbnail       = $row['thumbnail'];
          $display         = $row['display'];
          $type            = $row['type'];
          $artist          = $row['artist'];
          $price           = $row['price'];
          $status          = $row['status'];
          $gallery         = $row['gallery_name'];
          $title           = $row['title'];

          // print out the thumbnail with a maximum size of 100 pixels
          include_once('inc/image_inc.php');
          $thumbnail = resize_image('thumbnail', 100);

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
          '                    <input type="checkbox" name="checkbox[]" value="' . $artwork_id . '"' . $checked[$i] . '>' . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="' . $row_color . '" align="center" valign="middle">' . "\n" .
          '                     ' . $artwork_id . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="' . $row_color . '" align="center" valign="middle">' . "\n" .
          '                    ' . $display . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="' . $row_color . '" align="center">' . "\n" .
          '                    ' . $type . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="' . $row_color . '" width="104" height="104" align="center">' . "\n" .
          '                    ' . $thumbnail . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="' . $row_color . '" align="center" valign="middle">' . "\n" .
          '                    ' . $artist . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="' . $row_color . '" align="center" valign="middle">' . "\n" .
          '                    ' . $price . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="' . $row_color . '" align="center" valign="middle">' . "\n" .
          '                    ' . $status . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="' . $row_color . '" align="center" valign="middle">' . "\n" .
          '                    ' . $gallery . "\n" .
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
    include_once($admin_tpl . '/artwork_list' . $admin_tplext);
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