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
require_once('inc/category_inc.php');
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
  $errors = null;
  $info = null;
  $update_message = null;
  $item           = 'Page';
  $id_type        = 'page_id';

  // the quit button is clicked
  if (isset($_POST['quit']) && $_POST['quit'] == 'Quit') {
    unset($_POST);
    unset($errors);
    unset($_SESSION['action']);
    unset($_SESSION['num_checkbox']);
    unset($_SESSION['checkbox']);
    unset($_SESSION['orderby']);

    // unset any remaining session variables
    unset($_SESSION['page_id']);
    unset($_SESSION['category_id']);
    unset($_SESSION['priority']);
    unset($_SESSION['add_cat']);
    unset($_SESSION['cat_name']);
    unset($_SESSION['name']);
    unset($_SESSION['display']);
    unset($_SESSION['comments']);
    unset($_SESSION['text']);
    unset($_SESSION['meta_data_id']);
    unset($_SESSION['title']);
    unset($_SESSION['description']);
    unset($_SESSION['keywords']);
    unset($_SESSION['preview']);
  }

  // the add button is clicked:
  if (isset($_POST['add']) && $_POST['add'] == 'Add' || isset($_SESSION['action']) && $_SESSION['action'] == 'page_add') {
    // load session vars
    $_SESSION['action'] = 'page_add';

    // $task may be: form or Save
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
        $info = 'Adding new page...';
        $page_title = 'Adminstration Add Page - eXhibition';

        // activate the add new page page form
        $id = null;
        $where = "WHERE cat_type='page'";
        $category_select = category_selectbox($id, $where);
        $display_select  = display_selectbox();
        $comment_select  = comment_selectbox();
        $rss_feed_select = rss_select('disable');
        include_once($admin_tpl . '/header' . $admin_tplext);
        include_once($admin_tpl . '/page_add_form' . $admin_tplext);
        include_once($admin_tpl . '/footer' . $admin_tplext);
        break;
      case 'Save':
        // set message vars
        $message = 'Logged in as: ' . $_SESSION['administrator'];
        $page_title = 'Adminstration Add page Page - eXhibition';

        // load the session variables from post:
        if (isset($_POST['category_id'])) {
          $_SESSION['category_id'] = gp_filter($_POST['category_id']);
        }

        if (isset($_POST['name'])) {
          $_SESSION['name'] = gp_filter($_POST['name']);
        }

        if (isset($_POST['text'])) {
          $_SESSION['text'] = $_POST['text'];
        }

        if (isset($_POST['comments'])) {
          $_SESSION['comments'] = gp_filter($_POST['comments']);
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

        if (isset($_POST['rss_feed'])) {
          $_SESSION['rss_feed'] = $_POST['rss_feed'];
        }

        if (isset($_POST['priority'])) {
          $_SESSION['priority'] = gp_filter($_POST['priority']);
        }

        // Run validation routines on session variables
        validate_page();

        if (empty($errors)) {
          // create the page page
          if(create_page()) {
            $info = '<p>Page successfully created!</p>';
          }
          else {
            $info = '<p>Could not create new page!</p>';
          }

          // activate the confirmation template
          include_once($admin_tpl . '/header' . $admin_tplext);
          include_once($admin_tpl . '/message' . $admin_tplext);
          include_once($admin_tpl . '/footer' . $admin_tplext);
        }
        else {
          $info = 'Errors exist: Edit details for new page page.';
          // activate the edit form
          $display_select = display_selectbox($_SESSION['display']);
          $comment_select = comment_selectbox($_SESSION['comment_display']);
          include_once($admin_tpl . '/header' . $admin_tplext);
          include_once($admin_tpl . '/page_edit_form' . $admin_tplext);
          include_once($admin_tpl . '/footer' . $admin_tplext);
        }
        break;
    }
  }
  // the view button is clicked:
  elseif (isset($_POST['view']) && $_POST['view'] == 'View' || isset($_SESSION['action']) && $_SESSION['action'] == 'page_view') {
    if (!empty($_POST['checkbox'])) {
      // load the session vars
      $_SESSION['action']       = 'page_view';
      $_SESSION['num_checkbox'] = count($_POST['checkbox']);
      $_SESSION['checkbox']     = $_POST['checkbox'];
    }
    elseif (empty($_SESSION['checkbox'])) {
      // if no box is selected when an action is requested, do nothing.
      header('location: page.php');
    }

    // set message vars
    $page_title = 'Adminstration page Page Details - eXhibition';
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
      $num_post_message = '1 page page selected';
    }
    else {
      $num_post_message = $_SESSION['num_checkbox'] . ' page pages selected';
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
                a.page_id,
                c.cat_name as category,
                a.name,
                a.text,
                a.comments,
                m.title,
                m.description,
                m.keywords,
                m.display,
                m.rss_feed,
                m.priority,
                m.link,
                m.pub_date,
                m.last_updated
              FROM
                page a,
                category c,
                meta_data m
              WHERE
                a.page_id IN ($in)
              AND
                a.meta_data_id = m.meta_data_id
              AND
                a.category_id = c.category_id
              LIMIT 
                $offset, $max_results";

    // get the page page details
    $result = mysql_query($query);
    if (!mysql_num_rows($result)) {
      // no pages found
      $info = '<p>No pages found!</p>';
      include_once($admin_tpl . '/header' . $admin_tplext);
      include_once($admin_tpl . '/message' . $admin_tplext);
      include_once($admin_tpl . '/footer' . $admin_tplext);
      exit();
    }
    else {
      // pages found
      $details = '';
      while ($row = mysql_fetch_assoc($result)) {
        // format the page page details
        foreach ($row as $label => $field) {
          $details .= 
          '    <tr>' . "\n";
          if ($label == 'text') {
            // the text field belongs in it's own table row
            // so the preview can use 100 percent of the page width
            $details .=
            '        <td bgcolor="#147a14" class="label" align="right" height="25">' . "\n" .
            '            ' . $label . "\n" .
            '        </td>' . "\n" .
            '        <td bgcolor="#147a14" class="field" align="left" height="25">' . "\n" .
            '            &nbsp;' . "\n" .
            '        </td>' . "\n" .
            '    </tr>' . "\n" .
            '    <tr>' . "\n" .
            '        <td bgcolor="#147a14" class="field" colspan="2"height="35">' . "\n" .
            '            <p>' . $field . '</p>' . "\n" .
            '        </td>' . "\n";
          }
          else {
            $details .=
            '        <td bgcolor="#147a14" class="label" align="right" height="25">' . "\n" .
            '            ' . $label . "\n" .
            '        </td>' . "\n" .
            '        <td bgcolor="#147a14" class="field" align="left" height="25">' . "\n" .
            '            ' . $field . "\n" .
            '        </td>' . "\n";
          }
          $details .=
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
  elseif (isset($_POST['edit']) && $_POST['edit'] == 'Edit' || isset($_SESSION['action']) && $_SESSION['action'] == 'page_edit') {
    if (!empty($_POST['checkbox'])) {
      // load the session vars
      $_SESSION['action']       = 'page_edit';
      $_SESSION['num_checkbox'] = count($_POST['checkbox']);
      $_SESSION['checkbox']     = $_POST['checkbox'];
    }
    elseif (empty($_SESSION['checkbox'])) {
      // if no box is selected when an action is requested, do nothing.
      header('location: page.php');
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
        $page_title = 'Adminstration Edit Page - eXhibition';
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
          $num_post_message = '1 page selected'; 
        }
        else {
          $num_post_message = $_SESSION['num_checkbox'] . ' pages selected';
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

    $query = "SELECT
                a.page_id,
                c.cat_name,
                a.name,
                a.text,
                a.comments,
                m.title,
                m.description,
                m.keywords,
                m.display,
                m.rss_feed,
                m.priority
              FROM
                page a,
                category c,
                meta_data m
              WHERE
                a.page_id IN ($in)
              AND
                a.meta_data_id = m.meta_data_id
              AND
                a.category_id = c.category_id
              LIMIT 
                $offset, $max_results";

        // get the page page details
        $result = mysql_query($query);
        if (!mysql_num_rows($result)) {
          // no pages found
          $info = '<p>No pages found!</p>';
          include_once($admin_tpl . '/header' . $admin_tplext);
          include_once($admin_tpl . '/message' . $admin_tplext);
          include_once($admin_tpl . '/footer' . $admin_tplext);
          exit();
        }
        else {
          // pagees found
          while ($row = mysql_fetch_assoc($result)) {
            // load data into session vars
            $_SESSION['page_id']     = $row['page_id'];
            $_SESSION['category_id'] = $row['category_id'];
            $_SESSION['cat_name']    = $row['cat_name'];
            $_SESSION['name']        = $row['name'];
            $_SESSION['text']        = $row['text'];
            $_SESSION['comments']    = $row['comments'];
            $_SESSION['title']       = $row['title'];
            $_SESSION['description'] = $row['description'];
            $_SESSION['keywords']    = $row['keywords'];
            $_SESSION['display']     = $row['display'];
            $_SESSION['rss_feed']    = $row['rss_feed'];
            $_SESSION['priority']    = $row['priority'];
          }
        }

        $where = "WHERE cat_type = 'page'";
        $category_select = category_selectbox($_SESSION['category_id'], $where);
        $display_select = display_selectbox($_SESSION['display']);
        $comment_select = comment_selectbox($_SESSION['comment_display']);
        $rss_select = rss_select($_SESSION['rss_feed']);

        // construct pagination links
        include_once('inc/paginate_inc.php');
        paginate_child($page, $total_pages);

        // activate the edit form template
        include_once($admin_tpl . '/header' . $admin_tplext);
        include_once($admin_tpl . '/page_edit_form' . $admin_tplext);
        include_once($admin_tpl . '/footer' . $admin_tplext);

        break;

      case 'Save':
        // load the session variables from post:
        if (isset($_POST['category_id'])) {
          $_SESSION['category_id'] = gp_filter($_POST['category_id']);
        }

        if (isset($_POST['name'])) {
          $_SESSION['name'] = gp_filter($_POST['name']);
        }

        if (isset($_POST['text'])) {
          $_SESSION['text'] = $_POST['text'];
        }

        if (isset($_POST['comments'])) {
          $_SESSION['comments'] = gp_filter($_POST['comments']);
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

        if (isset($_POST['rss_feed'])) {
          $_SESSION['rss_feed'] = gp_filter($_POST['rss_feed']);
        }

        if (isset($_POST['priority'])) {
          $_SESSION['priority'] = gp_filter($_POST['priority']);
        }

        // run validation routines on the session variabless
        validate_page();

        if (empty($errors)) {
          // Saving data...
          // run a function to create a query string of changed fields
          $query_string = page_fields_diff();

          // main update query
          $update = "UPDATE
                       page a,
                       meta_data m
                     SET
                       $query_string 
                     WHERE
                       a.page_id = $_SESSION[page_id]
                     AND
                       a.meta_data_id = m.meta_data_id";

          // if the query string shows changes, run the update
          if ($query_string) {
            $result = mysql_query($update);
            $info = '<p>Changes saved.</p>';
          }
          else {
            $info = '<p>No changes made.</p>';
          }

          // unset session variables
          unset($_SESSION['page_id']);
          unset($_SESSION['category_id']);
          unset($_SESSION['name']);
          unset($_SESSION['text']);
          unset($_SESSION['comments']);
          unset($_SESSION['title']);
          unset($_SESSION['description']);
          unset($_SESSION['keywords']);
          unset($_SESSION['display']);
          unset($_SESSION['rss_feed']);
          unset($_SESSION['priority']);

          // show a confirmation page
          $page_title = 'Adminstration Edit page Page - eXhibition';
          $message = 'Logged in as: ' . $_SESSION['administrator'];
          include_once($admin_tpl . '/header' . $admin_tplext);
          include_once($admin_tpl . '/message' . $admin_tplext);
          include_once($admin_tpl . '/footer' . $admin_tplext);
        }
        else {
          // errors exist
          $page_title = 'Errors Exist: Adminstration Edit page Page - eXhibition';
          $message = 'Logged in as: ' . $_SESSION['administrator'];

          if (get_magic_quotes_gpc()) {
            $_SESSION['text'] = stripslashes($_SESSION['text']);
          }

          // set message vars
          $page_title = 'Adminstration Edit page Page - eXhibition';
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
            $num_post_message = '1 page page selected';
          }
          else {
            $num_post_message = $_SESSION['num_checkbox'] . ' page pages selected';
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

          $query = "SELECT
                  a.page_id,
                  c.cat_name,
                  a.name,
                  a.text,
                  a.comments,
                  m.title,
                  m.description,
                  m.keywords,
                  m.display,
                  m.rss_feed,
                  m.priority
                FROM
                  page a,
                  category c,
                  meta_data m
                WHERE
                  a.page_id IN ($in)
                AND
                  a.meta_data_id = m.meta_data_id
                AND
                  a.category_id = c.category_id
                LIMIT 
                  $offset, $max_results";

          // get the page page details
          $result = mysql_query($query);
          if (!mysql_num_rows($result)) {
            // no pages found
            $info = '<p>No pages found!</p>';
            include_once($admin_tpl . '/header' . $admin_tplext);
            include_once($admin_tpl . '/message' . $admin_tplext);
            include_once($admin_tpl . '/footer' . $admin_tplext);
            exit();
          }
          else {
            // pagees found
            while ($row = mysql_fetch_assoc($result)) {
              // load data into session vars
              $_SESSION['page_id']     = $row['page_id'];
              $_SESSION['category_id'] = $row['category_id'];
              $_SESSION['cat_name']    = $row['cat_name'];
              $_SESSION['name']        = $row['name'];
              $_SESSION['text']        = $row['text'];
              $_SESSION['comments']    = $row['comments'];
              $_SESSION['title']       = $row['title'];
              $_SESSION['description'] = $row['description'];
              $_SESSION['keywords']    = $row['keywords'];
              $_SESSION['display']     = $row['display'];
              $_SESSION['rss_feed']    = $row['rss_feed'];
              $_SESSION['priority']    = $row['priority'];
            }
          }

          $where = "WHERE cat_type = 'page'";
          $category_select = category_selectbox($_SESSION['category_id'], $where);
          $display_select = display_selectbox($_SESSION['display']);
          $comment_select = comment_selectbox($_SESSION['comment_display']);
          $rss_select = rss_select($_SESSION['rss_feed']);

          // construct pagination links
          include_once('inc/paginate_inc.php');
          paginate_child($page, $total_pages);

          // activate the edit form template
          include_once($admin_tpl . '/header' . $admin_tplext);
          include_once($admin_tpl . '/page_edit_form' . $admin_tplext);
          include_once($admin_tpl . '/footer' . $admin_tplext);
        }
        break;
    }
  }
  // the delete button is clicked:
  elseif (isset($_POST['delete']) && $_POST['delete'] == 'Delete' || isset($_SESSION['action']) && $_SESSION['action'] == 'page_delete') {
    if (!empty($_POST['checkbox'])) {
      // load the session vars
      $_SESSION['action']       = 'page_delete';
      $_SESSION['num_checkbox'] = count($_POST['checkbox']);
      $_SESSION['checkbox']     = $_POST['checkbox'];
    }
    elseif (empty($_SESSION['checkbox'])) {
      // if no box is selected when an action is requested, do nothing.
      header('location: page.php');
      exit();
    }

    // set message vars
    $page_title = 'Adminstration page Page Delete - eXhibition';
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
          $num_post_message = '1 page page selected';
        }
        else {
          $num_post_message = $_SESSION['num_checkbox'] . ' page pages selected';
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
                    a.page_id,
                    a.category_id,
                    a.name,
                    m.display,
                    a.text,
                    a.meta_data_id,
                    m.title,
                    m.description,
                    m.keywords
                  FROM page a, meta_data m WHERE a.page_id IN ($in)
                  AND a.meta_data_id = m.meta_data_id
                  LIMIT 
                    $offset, $max_results";

        // get the page page details
        if ($result = mysql_query($query, $dbconnect)) {
          $details = '';
          while ($row = mysql_fetch_assoc($result)) {
            // format the page page details
            foreach ($row as $label => $field) {
              $details .= 
              '    <tr>' . "\n" .
              '        <td bgcolor="#eeeeee">' . "\n" .
              '            ' . $label . "\n" .
              '        </td>' . "\n" .
              '        <td bgcolor="#eeeeee">' . "\n" .
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
        // get the category for this page
        $page_id = $_SESSION['checkbox'][0];
        $query = "SELECT category_id FROM page WHERE page_id = '$page_id'";
        $result = mysql_query($query);
        $category_id = mysql_result($result, 0, 'category_id');

        // delete this row from the database
        $query = "DELETE FROM 
                    page, meta_data
                  USING
                    page, meta_data
                  WHERE
                    page.page_id = '$page_id'
                  AND
                    page.meta_data_id = meta_data.meta_data_id";

        if ($result = mysql_query($query, $dbconnect)) {
          $info = '<p>Item deleted!</p>';
        }
        else {
          $info = '<p>Not deleted! Please try again later.</p>';
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
    // List view: build a summary list of existing page pages

    // set message vars
    $page_title = 'Adminstration Pages - eXhibition';
    $message = 'Logged in as: ' . $_SESSION['administrator'];

    // count the database rows
    $query = "SELECT count(*) FROM page";
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
    $setmax_results = 6;

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
      $default_fieldname = 'page_id';
    }

    // initialize sort vars
    $orderby          = 'page_id DESC';  // appended to sql query
    $sortpic          = null;            // arrow for column heading - points up or down
    $orderby_page_id  = 'page_id';       // appended to column heading link
    $orderby_cat_name = 'cat_name';      // appended to column heading link
    $orderby_priority = 'priority';      // appended to column heading link
    $orderby_name     = 'name';          // appended to column heading link
    $orderby_display  = 'display';       // appended to column heading link
    $orderby_comments = 'comments';      // appended to column heading link
    $paginate_str     = 'page_id';       // appended to pagination link

    $sortpic_up = '<img src="img/sort_up.gif" width="13" height="11" alt="Sort by '
                  . $default_fieldname .
                  '" title="Sort by ' . $default_fieldname . '">';
    $sortpic_dn = '<img src="img/sort_dn.gif" width="13" height="11" alt="Sort by '
                  . substr($default_fieldname, 0, -5) .
                  ' descending" title="Sort by ' 
                  . substr($default_fieldname, 0, -5) . ' descending">';

    if (!isset($_GET['sort'])) {
      $_GET['sort'] = 'page_id';
    }

    // maintain sort vars when column headings are clicked
    switch (gp_filter($_GET['sort'])) {
      case 'page_id':
      default:
        $orderby             = 'page_id ASC';
        $orderby_page_id    = 'page_id_desc';
        $orderby_title       = 'title';
        $orderby_headline    = 'headline';
        $orderby_page_image = 'page_image';
        $orderby_display     = 'display';
        $paginate_str        = 'page_id';
        $sortpic             = $sortpic_up;
        break;
      case 'page_id_desc':
        $orderby          = 'page_id DESC';
        $orderby_page_id = 'page_id';
        $paginate_str     = 'page_id_desc';
        $sortpic          = $sortpic_dn;
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
      case 'comments':
        $orderby          = 'comments ASC';
        $orderby_comments = 'comments_desc';
        $paginate_str     = 'comments';
        $sortpic          = $sortpic_up;
        break;
      case 'comments_desc':
        $orderby          = 'comments DESC';
        $orderby_comments = 'comment_display';
        $paginate_str     = 'comments_desc';
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

    $page_id_th = $sub1 . $orderby_page_id . $sub2 . '">Page ID</a>';
    if ($paginate_str == 'page_id' || $paginate_str == 'page_id_desc') {
      $page_id_th .= $sortpic;
    }
    $page_id_th .= "\n";

    $cat_name_th = $sub1 . $orderby_cat_name . $sub2 . '">Category</a>';
    if ($paginate_str == 'cat_name' || $paginate_str == 'cat_name_desc') {
      $cat_name_th .= $sortpic;
    }
    $cat_name_th .= "\n";

    $priority_th = $sub1 . $orderby_priority . $sub2 . '">Priority</a>';
    if ($paginate_str == 'priority' || $paginate_str == 'priority_desc') {
      $priority_th .= $sortpic;
    }
    $priority_th .= "\n";

    $name_th = $sub1 . $orderby_name . $sub2 . '">Name</a>';
    if ($paginate_str == 'name' || $paginate_str == 'name_desc') {
      $name_th .= $sortpic;
    }
    $name_th .= "\n";

    $display_th = $sub1 . $orderby_display . $sub2 . '">Display</a>';
    if ($paginate_str == 'display' || $paginate_str == 'display_desc') {
      $display_th .= $sortpic;
    }
    $display_th .= "\n";

    $comments_th = $sub1 . $orderby_comments . $sub2 . '">Comments</a>';
    if ($paginate_str == 'comments' || $paginate_str == 'comments_desc') {
      $comments_th .= $sortpic;
    }
    $comments_th .= "\n";

    // list query: get a summary list of existing pages
    $query = "SELECT
                a.page_id,
                c.cat_name,
                m.priority,
                a.name,
                m.display,
                a.comments
              FROM 
                page a,
                category c,
                meta_data m
              WHERE
                a.meta_data_id = m.meta_data_id
              AND
                a.category_id = c.category_id
              ORDER BY
                $orderby
              LIMIT
                $offset, $max_results";

    // get data rows from mysql
    $result = mysql_query($query);
    if (!$result) {
      // no pages exist
      $data_rows = '    <tr>' . "\n" .
                   '        <td colspan="7" align="center" height="35" bgcolor="#eeeeee">' . "\n" .
                   '            No pages found!' . "\n" .
                   '        </td>' . "\n" .
                   '    </tr>' . "\n";
    }
    else {
      if (mysql_num_rows($result) < 1) {
        // no pages exist
        $data_rows = '    <tr>' . "\n" .
                     '        <td colspan="7" align="center" height="35" bgcolor="#eeeeee">' . "\n" .
                     '            No pages found!' . "\n" .
                     '        </td>' . "\n" .
                     '    </tr>' . "\n";
      }
      else {
        // pages exist
        $data_rows = '';
        $i = 0;
        while ($row = mysql_fetch_array($result)) {
          $page_id  = $row['page_id'];
          $name     = $row['name'];
          $priority = $row['priority'];
          $cat_name = $row['cat_name'];
          $display  = $row['display'];
          $comments = $row['comments'];

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
          '                <td bgcolor="#eeeeee" align="center" valign="middle" height="25">' . "\n" .
          '                    <input type="checkbox" name="checkbox[]" value="' . $page_id . '"' . $checked[$i] . '>' . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="#eeeeee" align="center" valign="middle">' . "\n" .
          '                     ' . $page_id . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="#eeeeee" align="center" valign="middle">' . "\n" .
          '                    ' . $name . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="#eeeeee" align="center" valign="middle">' . "\n" .
          '                    ' . $priority . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="#eeeeee" align="center" valign="middle">' . "\n" .
          '                    ' . $cat_name . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="#eeeeee" align="center" valign="middle">' . "\n" .
          '                    ' . $display . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="#eeeeee" align="center" valign="middle">' . "\n" .
          '                    ' . $comments . "\n" .
          '                </td>' . "\n" .
          '            </tr>' . "\n";
          $i++;
        }
      }
    }

    // construct pagination links
    include_once('inc/paginate_inc.php');
    paginate_list($page, $total_pages);

    // activate the page pages_list template
    include_once($admin_tpl . '/header' . $admin_tplext);
    include_once($admin_tpl . '/page_list' . $admin_tplext);
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