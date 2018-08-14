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
  $item           = 'Artist';
  $id_type        = 'artist_id';

  // the quit button is clicked
  if (isset($_POST['quit']) && $_POST['quit'] == 'Quit') {
    unset($_POST);
    unset($errors);
    unset($_SESSION['action']);
    unset($_SESSION['num_checkbox']);
    unset($_SESSION['checkbox']);
    unset($_SESSION['orderby']);

    // unset any remaining session variables
    unset($_SESSION['artist_id']);
    unset($_SESSION['name']);
    unset($_SESSION['birth_date']);
    unset($_SESSION['birth_year']);
    unset($_SESSION['birth_month']);
    unset($_SESSION['birth_day']);
    unset($_SESSION['death_date']);
    unset($_SESSION['death_year']);
    unset($_SESSION['death_month']);
    unset($_SESSION['death_day']);
    unset($_SESSION['location']);
  }
  // the add button is clicked:
  if (isset($_POST['add']) && $_POST['add'] == 'Add' || isset($_SESSION['action']) && $_SESSION['action'] == 'artist_add') {
    // load session vars
    $_SESSION['action'] = 'artist_add';

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
        $info = 'Adding new artist...';
        $page_title = 'Adding a New Artist -Administration- eXhibition';

        // activate the add new gallery form
        include_once('inc/artist_inc.php');
        include_once($admin_tpl . '/header' . $admin_tplext);    
        include_once($admin_tpl . '/artist_add_form' . $admin_tplext);
        include_once($admin_tpl . '/footer' . $admin_tplext);
        break;
      case 'Save':
        // set message vars
        $message = 'Logged in as: ' . $_SESSION['administrator'];
        $page_title = 'Adminstration Add Artist - eXhibition';

        // load the session variables from post:
        if (isset($_POST['name'])) {
          $_SESSION['name'] = gp_filter($_POST['name']);
        }

        if (isset($_POST['birth_year'])) {
          $_SESSION['birth_year'] = gp_filter($_POST['birth_year']);
        }

        if (isset($_POST['birth_month'])) {
          $_SESSION['birth_month'] = gp_filter($_POST['birth_month']);
        }

        if (isset($_POST['birth_day'])) {
          $_SESSION['birth_day'] = gp_filter($_POST['birth_day']);
        }

        if (isset($_POST['death_year'])) {
          $_SESSION['death_year'] = gp_filter($_POST['death_year']);
        }

        if (isset($_POST['death_month'])) {
          $_SESSION['death_month'] = gp_filter($_POST['death_month']);
        }

        if (isset($_POST['death_day'])) {
          $_SESSION['death_day'] = gp_filter($_POST['death_day']);
        }

        if (isset($_POST['location'])) {
          $_SESSION['location'] = gp_filter($_POST['location']);
        }

        // Run validation routines on session variables
        validate_artist();

        if (empty($errors)) {
          // add the artist
          $query = "INSERT INTO 
                      artist(name, 
                             birth_date, 
                             death_date, 
                             location) 
                    VALUES('$_SESSION[name]',
                           '$_SESSION[birth_date]',
                           '$_SESSION[death_date]',
                           '$_SESSION[location]')";

          $result = mysql_query($query);
          if ($result) {
            $info = '<h4>New artist successfully added!</h4>';
          }
          else {
            $info = 'Could not add this artist. Please try again later.';
          }
          // activate the confirmation template
          include_once($admin_tpl . '/header' . $admin_tplext);
          include_once($admin_tpl . '/message' . $admin_tplext);
          include_once($admin_tpl . '/footer' . $admin_tplext);
        }
        else {
          $info = 'Errors exist: Edit details for new artist.';
          // activate the edit form
          include_once('inc/artis_inc.php');
          include_once($admin_tpl . '/header' . $admin_tplext);
          include_once($admin_tpl . '/artist_edit_form' . $admin_tplext);
          include_once($admin_tpl . '/footer' . $admin_tplext);
        }
        break;
    }

  }
  // the view button is clicked:
  elseif (isset($_POST['view']) && $_POST['view'] == 'View' || isset($_SESSION['action']) && $_SESSION['action'] == 'artist_view') {
    if (!empty($_POST['checkbox'])) {
      // load the session vars
      $_SESSION['action']       = 'artist_view';
      $_SESSION['num_checkbox'] = count($_POST['checkbox']);
      $_SESSION['checkbox']     = $_POST['checkbox'];
    }
    elseif (empty($_SESSION['checkbox'])) {
      // if no box is selected when an action is requested, do nothing.
      header('location: artist.php');
    }
    // set message vars
    $page_title = 'View Artist Details - eXhibition';
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
      $num_post_message = '1 artist selected';
    }
    else {
      $num_post_message = $_SESSION['num_checkbox'] . ' artists selected';
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
                artist_id,
                name,
                birth_date,
                death_date,
                location
              FROM
                artist
              WHERE
                artist_id
              IN
                ($in)
              LIMIT 
                $offset, $max_results";
    // get the artist details
    $result = mysql_query($query);
    if (!mysql_num_rows($result)) {
      // no artists found
      $info = '<h4>No artists found!</h4>';
      include_once($admin_tpl . '/header' . $admin_tplext);
      include_once($admin_tpl . '/message' . $admin_tplext);
      include_once($admin_tpl . '/footer' . $admin_tplext);
      exit();
    }
    else {
      // artists found
      $details = '';
      while ($row = mysql_fetch_assoc($result)) {
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

      // construct pagination links
      include_once('inc/paginate_inc.php');
      paginate_child($page, $total_pages);

      // activate the template
      include_once($admin_tpl . '/header' . $admin_tplext);
      include_once($admin_tpl . '/view' . $admin_tplext);
      include_once($admin_tpl . '/footer' . $admin_tplext);
    }
  }
  // the edit button is clicked:
  elseif (isset($_POST['edit']) && $_POST['edit'] == 'Edit' || isset($_SESSION['action']) && $_SESSION['action'] == 'artist_edit') {
    if (!empty($_POST['checkbox'])) {
      // load the session vars
      $_SESSION['action']       = 'artist_edit';
      $_SESSION['num_checkbox'] = count($_POST['checkbox']);
      $_SESSION['checkbox']     = $_POST['checkbox'];
    }
    elseif (empty($_SESSION['checkbox'])) {
      // if no box is selected when an action is requested, do nothing.
      header('location: artist.php');
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
        $page_title = 'Administration Edit Artist - eXhibition';
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
          $num_post_message = '1 artist selected';
        }
        else {
          $num_post_message = $_SESSION['num_checkbox'] . ' artists selected';
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
                    artist_id,
                    name,
                    birth_date,
                    death_date,
                    location
                  FROM artist WHERE artist_id IN ($in)
                  LIMIT 
                    $offset, $max_results";

        // get the artist details
        $result = mysql_query($query);
        if (!mysql_num_rows($result)) {
          // no artists found
          $info = '<h4>No artists found!</h4>';
          include_once($admin_tpl . '/header' . $admin_tplext);
          include_once($admin_tpl . '/message' . $admin_tplext);
          include_once($admin_tpl . '/footer' . $admin_tplext);
          exit();
        }
        else {
          // artists found
          while ($row = mysql_fetch_assoc($result)) {
            // load data into session variables
            $_SESSION['artist_id']   = $row['artist_id'];
            $_SESSION['name']        = $row['name'];
            $_SESSION['birth_date']  = $row['birth_date'];
            $_SESSION['birth_year']  = substr($row['birth_date'], 0, -6);
            $_SESSION['birth_month'] = substr($row['birth_date'], 5, -3);
            $_SESSION['birth_day']   = substr($row['birth_date'], -2);
            $_SESSION['death_date']  = $row['death_date'];
            $_SESSION['death_year']  = substr($row['death_date'], 0, -6);
            $_SESSION['death_month'] = substr($row['death_date'], 5, -3);
            $_SESSION['death_day']   = substr($row['death_date'], -2);
            $_SESSION['location']    = $row['location'];
          }

          include_once('inc/artwork_inc.php');
          $artist_select = artist_selectbox($_SESSION['artist_id']);

          // construct pagination links
          include_once('inc/paginate_inc.php');
          paginate_child($page, $total_pages);

          // activate the edit form
          include_once($admin_tpl . '/header' . $admin_tplext);
          include_once($admin_tpl . '/artist_edit_form' . $admin_tplext);
          include_once($admin_tpl . '/footer' . $admin_tplext);
        }
        break;

      case 'Save':
        // load the session variables from post:
        if (isset($_POST['artist_id'])) {
          $_SESSION['artist_id'] = gp_filter($_POST['artist_id']);
        }

        if (isset($_POST['name'])) {
          $_SESSION['name'] = gp_filter($_POST['name']);
        }

        if (isset($_POST['birth_year'])) {
          $_SESSION['birth_year'] = gp_filter($_POST['birth_year']);
        }

        if (isset($_POST['birth_month'])) {
          $_SESSION['birth_month'] = gp_filter($_POST['birth_month']);
        }

        if (isset($_POST['birth_day'])) {
          $_SESSION['birth_day'] = gp_filter($_POST['birth_day']);
        }

        if (isset($_POST['death_year'])) {
          $_SESSION['death_year'] = gp_filter($_POST['death_year']);
        }

        if (isset($_POST['death_month'])) {
          $_SESSION['death_month'] = gp_filter($_POST['death_month']);
        }

        if (isset($_POST['death_day'])) {
          $_SESSION['death_day'] = gp_filter($_POST['death_day']);
        }

        if (isset($_POST['location'])) {
          $_SESSION['location'] = gp_filter($_POST['location']);
        }

        // run validation routines on the session variables
        validate_artist();

        if (empty($errors)) {
          // Saving data...
          // run a function to create a query string of changed fields
          include_once('inc/artist_inc.php');
          $query_string = artist_fields_diff();

          // main update query
          $update = "UPDATE artist
                     SET $query_string 
                     WHERE artist_id = $_SESSION[artist_id]";

          // if the query string shows changes, run the update
          if ($query_string) {
            $result = mysql_query($update);
            $info = '<h4>Changes saved.</h4>' . "\n";
          }
          else {
            $info = '<h4>No changes made.</h4>' . "\n";
          }

          // unset session variables
          unset($_SESSION['artist_id']);
          unset($_SESSION['name']);
          unset($_SESSION['birth_date']);
          unset($_SESSION['birth_year']);
          unset($_SESSION['birth_month']);
          unset($_SESSION['birth_day']);
          unset($_SESSION['death_date']);
          unset($_SESSION['death_year']);
          unset($_SESSION['death_month']);
          unset($_SESSION['death_day']);
          unset($_SESSION['location']);

          // show a confirmation page
          $page_title = 'Adminstration Edit Artist - eXhibition';
          $message = 'Logged in as: ' . $_SESSION['administrator'];
          include_once($admin_tpl . '/header' . $admin_tplext);
          include_once($admin_tpl . '/message' . $admin_tplext);
          include_once($admin_tpl . '/footer' . $admin_tplext);
        }
        else {
          // errors exist
          $page_title = 'Errors Exist: Adminstration Edit Artist - eXhibition';
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
            $num_post_message = '1 artist selected';
          }
          else {
            $num_post_message = $_SESSION['num_checkbox'] . ' artists selected';
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
                      artist_id,
                      name,
                      birth_date,
                      death_date,
                      location
                    FROM artist WHERE artist_id IN ($in)
                    AND
                      artist_id = $_SESSION[artist_id]
                    LIMIT 
                      $offset, $max_results";

          // get the artist details
          $result = mysql_query($query);
          if (!mysql_num_rows($result)) {
            // no artists found
            $info = '<h4>No artists found!</h4>';
            include_once($admin_tpl . '/header' . $admin_tplext);
            include_once($admin_tpl . '/message' . $admin_tplext);
            include_once($admin_tpl . '/footer' . $admin_tplext);
            exit();
          }
          else {
            // artists found
            while ($row = mysql_fetch_assoc($result)) {
              $_SESSION['artist_id']   = $row['artist_id'];
              $_SESSION['name']        = $row['name'];
              $_SESSION['birth_date']  = $row['birth_date'];
              $_SESSION['birth_year']  = substr($row['birth_date'], 0, -6);
              $_SESSION['birth_month'] = substr($row['birth_date'], 5, -3);
              $_SESSION['birth_day']   = substr($row['birth_date'], -2);
              $_SESSION['death_date']  = $row['death_date'];
              $_SESSION['death_year']  = substr($row['death_date'], 0, -6);
              $_SESSION['death_month'] = substr($row['death_date'], 5, -3);
              $_SESSION['death_day']   = substr($row['death_date'], -2);
              $_SESSION['location']    = $row['location'];
            }
          }
          // paginate
          include_once('inc/paginate_inc.php');
          $pagination_row = paginate_child($page, $total_pages);

          // activate the edit form
          include_once($admin_tpl . '/header' . $admin_tplext);
          include_once($admin_tpl . '/artist_edit_form' . $admin_tplext);
          include_once($admin_tpl . '/footer' . $admin_tplext);
        }
        break;
    }
  }
  // the delete button is clicked:
  elseif (isset($_POST['delete']) && $_POST['delete'] == 'Delete' || isset($_SESSION['action']) && $_SESSION['action'] == 'artist_delete') {
    if (!empty($_POST['checkbox'])) {
      // load the session vars
      $_SESSION['action']       = 'artist_delete';
      $_SESSION['num_checkbox'] = count($_POST['checkbox']);
      $_SESSION['checkbox']     = $_POST['checkbox'];
    }
    elseif (empty($_SESSION['checkbox'])) {
      // if no box is selected when an action is requested, do nothing.
      header('location: artist.php');
      exit();
    }

    // set message vars
    $page_title = 'Adminstration Artist Delete - eXhibition';
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
          $num_post_message = '1 artist selected';
        }
        else {
          $num_post_message = $_SESSION['num_checkbox'] . ' artists selected';
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
                    name,
                    birth_date,
                    location
                  FROM artist WHERE artist_id IN ($in)
                  LIMIT 
                    $offset, $max_results";

        $result = mysql_query($query);

        if (!mysql_num_rows($result)) {
          // no artists found
          $info = '<h4>No artists found!</h4>';
          include_once($admin_tpl . '/header' . $admin_tplext);
          include_once($admin_tpl . '/message' . $admin_tplext);
          include_once($admin_tpl . '/footer' . $admin_tplext);
          exit();
        }
        else {
          // artists found
          $details = '';
          while ($row = mysql_fetch_assoc($result)) {
            // format the gallery details
            foreach ($row as $label => $field) {
              $details .= '    <tr>' . "\n" .
                          '        <td colspan="2" align="center" bgcolor="#eeeeee" height="40">' . "\n" .
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
        include_once($admin_tpl . '/delete' . $admin_tplext);
        include_once($admin_tpl . '/footer' . $admin_tplext);

        break;
      case 'Delete':
        // delete this row from the database
        $artist_id = $_SESSION['checkbox'][0];

        $query = "DELETE FROM 
                    artist
                  WHERE
                    artist_id = '$artist_id'";

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
    // List view: build a summary list of existing artists

    // set message vars
    $page_title = 'Administrate Artists - eXhibition';
    $message = 'Logged in as: ' . $_SESSION['administrator'];

    // count the database rows
    $query = "SELECT count(*) FROM artist";
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
    $orderby            = 'artist_id DESC';  // appended to sql query
    $sortpic            = null;              // arrow for column heading - may point up or down
    $orderby_artist_id  = 'artist_id';       // appended to column heading link
    $orderby_name       = 'name';            // appended to column heading link
    $orderby_birth_date = 'birth_date';      // appended to column heading link
    $orderby_location   = 'location';        // appended to column heading link
    $paginate_str       = 'artist_id';       // appended to pagination link
    $sortpic_up         = '<img src="img/sort_up.gif" width="13" height="11" alt="Sort by ' . $default_fieldname . '" title="Sort by ' . $default_fieldname . '">';
    $sortpic_dn         = '<img src="img/sort_dn.gif" width="13" height="11" alt="Sort by ' . substr($default_fieldname, 0, -5) . ' descending" title="Sort by ' . substr($default_fieldname, 0, -5) . ' descending">';
    if (!isset($_GET['sort'])) {
      $_GET['sort'] = 'artist_id';
    }

    // maintain sort vars when column headings are clicked
    switch (gp_filter($_GET['sort'])) {
      case 'artist_id':
      default:
        $orderby            = 'artist_id ASC';
        $orderby_artist_id  = 'artist_id_desc';
        $orderby_name       = 'name';
        $orderby_birth_date = 'birth_date';
        $orderby_location   = 'location';
        $paginate_str       = 'artist_id';
        $sortpic            = $sortpic_up;
        break;
      case 'artist_id_desc':
        $orderby           = 'artist_id DESC';
        $orderby_artist_id = 'artist_id';
        $paginate_str      = 'artist_id_desc';
        $sortpic           = $sortpic_dn;
        break;
      case 'name':
        $orderby      = 'name ASC';
        $orderby_name = 'name_desc';
        $paginate_str = 'name';
        $sortpic      = $sortpic_up;
        break;
      case 'name_desc':
        $orderby      = 'name DESC';
        $orderby_name = 'name';
        $paginate_str = 'name_desc';
        $sortpic      = $sortpic_dn;
        break;
      case 'birth_date':
        $orderby            = 'birth_date ASC';
        $orderby_birth_date = 'birth_date_desc';
        $paginate_str       = 'birth_date';
        $sortpic            = $sortpic_up;
        break;
      case 'birth_date_desc':
        $orderby            = 'birth_date DESC';
        $orderby_birth_date = 'birth_date';
        $paginate_str       = 'birth_date_desc';
        $sortpic            = $sortpic_dn;
        break;
      case 'location':
        $orderby          = 'location ASC';
        $orderby_location = 'location_desc';
        $paginate_str     = 'location';
        $sortpic          = $sortpic_up;
        break;
      case 'location_desc':
        $orderby          = 'location DESC';
        $orderby_location = 'location';
        $paginate_str     = 'location_desc';
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

    $artist_id_th = $sub1 . $orderby_artist_id . $sub2 . '">Artist ID</a>';
    if ($paginate_str == 'artist_id' || $paginate_str == 'artist_id_desc') {
      $artist_id_th .= $sortpic;
    }
    $artist_id_th .= "\n";

    $name_th = $sub1 . $orderby_name . $sub2 . '">Name</a>';
    if ($paginate_str == 'name' || $paginate_str == 'name_desc') {
      $name_th .= $sortpic;
    }
    $name_th .= "\n";

    $birth_date_th = $sub1 . $orderby_birth_date . $sub2 . '">Date of Birth</a>';
    if ($paginate_str == 'birth_date' || $paginate_str == 'birth_date_desc') {
      $birth_date_th .= $sortpic;
    }
    $birth_date_th .= "\n";

    $location_th = $sub1 . $orderby_location . $sub2 . '">Location</a>';
    if ($paginate_str == 'location' || $paginate_str == 'location_desc') {
      $location_th .= $sortpic;
    }
    $location_th .= "\n";

    // list query: get a summary list of existing artists
    $query = "SELECT
                artist_id,
                name,
                birth_date,
                location 
              FROM 
                artist
              ORDER BY
                $orderby
              LIMIT
                $offset, $max_results";

    $result = mysql_query($query);

    if (!$result) {
      // no artists exist
      $data_rows = '    <tr>' . "\n" .
                   '        <td colspan="5" align="center" height="35" bgcolor="#eeeeee">' . "\n" .
                   '            No artists found!' . "\n" .
                   '        </td>' . "\n" .
                   '    </tr>' . "\n";
    }
    else {
      if (mysql_num_rows($result) < 1) {
        // no artists exist
        $data_rows = '    <tr>' . "\n" .
                     '        <td colspan="5" align="center" height="35" bgcolor="#eeeeee">' . "\n" .
                     '            No artists found!' . "\n" .
                     '        </td>' . "\n" .
                     '    </tr>' . "\n";
      }
      else {
        // artists exist
        $color1 = '#e5e5e5';
        $color2 = '#eeeeee';
        $data_rows = '';
        $i = 0;
        while ($row = mysql_fetch_array($result)) {
          $artist_id  = $row['artist_id'];
          $name       = $row['name'];
          $birth_date = $row['birth_date'];
          $location   = $row['location'];

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
          '                    <input type="checkbox" name="checkbox[]" value="' . $artist_id . '"' . $checked[$i] . '>' . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="' . $row_color . '" align="center" valign="middle">' . "\n" .
          '                     ' . $artist_id . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="' . $row_color . '" align="center" valign="middle">' . "\n" .
          '                    ' . $name . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="' . $row_color . '" width="79" height="79" align="center" valign="middle">' . "\n" .
          '                    ' . $birth_date . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="' . $row_color . '" align="center" valign="middle">' . "\n" .
          '                     ' . $location . "\n" .
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
    include_once($admin_tpl . '/artist_list' . $admin_tplext);
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