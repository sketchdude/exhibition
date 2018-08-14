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
  $errors         = null;
  $update_message = null;
  $info           = null;
  $item           = 'Category';
  $id_type        = 'category_id';

  // the quit button is clicked
  if (isset($_POST['quit']) && $_POST['quit'] == 'Quit') {
    unset($_POST);
    unset($errors);
    unset($_SESSION['action']);
    unset($_SESSION['num_checkbox']);
    unset($_SESSION['checkbox']);
    unset($_SESSION['orderby']);

    // unset any remaining session variables
    unset($_SESSION['category_id']);
    unset($_SESSION['cat_name']);
    unset($_SESSION['cat_type']);
    unset($_SESSION['sidebar']);
    unset($_SESSION['rss_channel']);
    unset($_SESSION['meta_data_id']);
    unset($_SESSION['title']);
    unset($_SESSION['description']);
    unset($_SESSION['keywords']);
    unset($_SESSION['display']);
    unset($_SESSION['rss_feed']);
    unset($_SESSION['priority']);
    unset($_SESSION['link']);
    unset($_SESSION['pub_date']);
    unset($_SESSION['last_updated']);
  }
  // the add button is clicked:
  if (isset($_POST['add']) && $_POST['add'] == 'Add' || isset($_SESSION['action']) && $_SESSION['action'] == 'category_add') {
    // load session vars
    $_SESSION['action'] = 'category_add';

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
        $info = 'Adding new category...';
        $page_title = 'Add Category - eXhibition';
        $display_select = display_selectbox();
        $sidebar_selectbox = sidebar_selectbox();
        $ctype_select = ctype_select();

        // activate the template
        include_once($admin_tpl . '/header' . $admin_tplext);
        include_once($admin_tpl . '/category_add_form' . $admin_tplext);
        include_once($admin_tpl . '/footer' . $admin_tplext);
        break;
      case 'Save':
        // set message vars
        $message = 'Logged in as: ' . $_SESSION['administrator'];
        $page_title = 'Add Category - eXhibition';

        // load the session variable from post:
        if (isset($_POST['cat_name'])) {
          $_SESSION['cat_name'] = gp_filter($_POST['cat_name']);
        }

        if (isset($_POST['cat_type'])) {
          $_SESSION['cat_type'] = gp_filter($_POST['cat_type']);
        }

        if (isset($_POST['sidebar'])) {
          $_SESSION['sidebar'] = gp_filter($_POST['sidebar']);
        }

        if (isset($_POST['rss_channel'])) {
          $_SESSION['rss_channel'] = gp_filter($_POST['rss_channel']);
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

        // Run validation routines on cat_name
        validate_category();

        if (empty($errors)) {
          // create the category
          if(create_category()) {
            $info = '<p>New category successfully created!</p>';
          }
          else {
            $info = '<p>Could not create this category. Please try again later.</p>';
          }
          // activate the confirmation template
          include_once($admin_tpl . '/header' . $admin_tplext);
          include_once($admin_tpl . '/message' . $admin_tplext);
          include_once($admin_tpl . '/footer' . $admin_tplext);
        }
        else {
          $info = '<p>Errors exist: Edit details for new category.</p>';
          // activate the edit form
          include_once($admin_tpl . '/header' . $admin_tplext);
          include_once($admin_tpl . '/category_edit_form' . $admin_tplext);
          include_once($admin_tpl . '/footer' . $admin_tplext);
        }
        break;
    }
  }
  // the view button is clicked:
  elseif (isset($_POST['view']) && $_POST['view'] == 'View' || isset($_SESSION['action']) && $_SESSION['action'] == 'category_view') {
    if (!empty($_POST['checkbox'])) {
      // load the session vars
      $_SESSION['action']       = 'category_view';
      $_SESSION['num_checkbox'] = count($_POST['checkbox']);
      $_SESSION['checkbox']     = $_POST['checkbox'];
    }
    elseif (empty($_SESSION['checkbox'])) {
      // if no box is selected when an action is requested, do nothing.
      header('location: ' . $_SERVER['PHP_SELF']);
    }

    // set message vars
    $page_title = 'Adminstration Category Details - eXhibition';
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
      $num_post_message = '1 category selected';
    }
    else {
      $num_post_message = $_SESSION['num_checkbox'] . ' categories selected';
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
                c.category_id,
                c.cat_name,
                c.cat_type,
                c.sidebar,
                c.rss_channel,
                m.title,
                m.description,
                m.keywords,
                m.display,
                m.priority,
                m.link,
                m.pub_date,
                m.last_updated
              FROM
                category c,
                meta_data m
              WHERE
                c.category_id IN ($in)
              AND
                c.meta_data_id = m.meta_data_id
              LIMIT 
                $offset, $max_results";

    // get the category details
    $result = mysql_query($query);
    echo mysql_error();

    if (!mysql_num_rows($result)) {
      // no categories found
      $info = 'No categories found!';
      include_once($admin_tpl . '/header' . $admin_tplext);
      include_once($admin_tpl . '/message' . $admin_tplext);
      include_once($admin_tpl . '/footer' . $admin_tplext);
      exit();
    }
    else {
      // categories found
      $details = '';
      while ($row = mysql_fetch_assoc($result)) {
        // format the category details
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
  elseif (isset($_POST['edit']) && $_POST['edit'] == 'Edit' || isset($_SESSION['action']) && $_SESSION['action'] == 'category_edit') {
    if (!empty($_POST['checkbox'])) {
      // load the session vars
      $_SESSION['action']       = 'category_edit';
      $_SESSION['num_checkbox'] = count($_POST['checkbox']);
      $_SESSION['checkbox']     = $_POST['checkbox'];
    }
    elseif (empty($_SESSION['checkbox'])) {
      // if no box is selected when an action is requested, do nothing.
      header('location: ' . $_SERVER['PHP_SELF']);
    }

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
        $page_title = 'Administration Edit Categories - eXhibition';
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
          $num_post_message = '1 category selected';
        }
        else {
          $num_post_message = $_SESSION['num_checkbox'] . ' categories selected';
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
                    c.category_id,
                    c.cat_name,
                    c.sidebar,
                    c.rss_channel,
                    m.title,
                    m.description,
                    m.keywords,
                    m.display,
                    m.priority,
                    m.link,
                    m.pub_date,
                    m.last_updated
                  FROM
                    category c,
                    meta_data m
                  WHERE
                    c.category_id IN ($in)
                  AND
                    c.meta_data_id = m.meta_data_id
                  LIMIT 
                    $offset, $max_results";

        // get the category details
        $result = mysql_query($query);
        if (!mysql_num_rows($result)) {
          // no categories found
          $info = '<p>No categories found!</p>';
          include_once($admin_tpl . '/message' . $admin_tplext);
          exit();
        }
        else {
          // categories found
          while ($row = mysql_fetch_assoc($result)) {
            if (strlen($row['parent']) < 1) {
              $row['parent'] = 'None';
            }

            // load data into session variables
            $_SESSION['category_id'] = $row['category_id'];
            $_SESSION['cat_name']    = $row['cat_name'];
            $_SESSION['sidebar']     = $row['sidebar'];
            $_SESSION['rss_channel'] = $row['rss_channel'];
            $_SESSION['title']       = $row['title'];
            $_SESSION['description'] = $row['description'];
            $_SESSION['keywords']    = $row['keywords'];
            $_SESSION['display']     = $row['display'];
            $_SESSION['priority']    = $row['priority'];
            $_SESSION['link']        = $row['link'];
          }

          if (isset($_SESSION['display'])) {
            $display_select = display_selectbox($_SESSION['display']);
          }

          if (isset($_SESSION['sidebar'])) {
            $sidebar_select = sidebar_selectbox($_SESSION['sidebar']);
          }

          if (isset($_SESSION['rss_channel'])) {
            if ($_SESSION['rss_channel'] == 'yes') {
              $checked = ' checked';
            }
            else {
              $checked = null;
            }
          }

          // construct pagination links
          include_once('inc/paginate_inc.php');
          paginate_child($page, $total_pages);

          // activate the edit form
          include_once($admin_tpl . '/header' . $admin_tplext);
          include_once($admin_tpl . '/category_edit_form' . $admin_tplext);
          include_once($admin_tpl . '/footer' . $admin_tplext);
        }

        break;
      case 'Save':
        // load the session variable from post:
        if (isset($_POST['cat_name'])) {
          $_SESSION['cat_name'] = gp_filter($_POST['cat_name']);
        }

        if (isset($_POST['sidebar'])) {
          $_SESSION['sidebar'] = gp_filter($_POST['sidebar']);
        }

        if (isset($_POST['rss_channel'])) {
          if ($_POST['rss_channel'] == 'yes') {
            $_SESSION['rss_channel'] = 'yes';
          }
          else {
            $_SESSION['rss_channel'] = 'no';
          }
        }
        else {
          $_SESSION['rss_channel'] = 'no';
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

        // run validation routines on the session variable
        validate_category();

        if (empty($errors)) {
          // Saving data...
          // run a function to create a query string of changed fields
          $query_string = category_fields_diff();

          // main update query
          $update = "UPDATE
                       category
                     SET
                       $query_string 
                     WHERE
                       category_id = $_SESSION[category_id]";

          // if the query string shows changes, run the update
          if ($query_string) {
            $result = mysql_query($update);
            $info = 'Changes saved.';
          }
          else {
            $info = 'No changes made.';
          }

          // unset session variables
          unset($_SESSION['cat_name']);
          unset($_SESSION['sidebar']);
          unset($_SESSION['rss_channel']);
          unset($_SESSION['title']);
          unset($_SESSION['description']);
          unset($_SESSION['keywords']);
          unset($_SESSION['display']);
          unset($_SESSION['priority']);

          // show a confirmation page
          $page_title = 'Adminstration Edit Category - eXhibition';
          $message = 'Logged in as: ' . $_SESSION['administrator'];
          include_once($admin_tpl . '/header' . $admin_tplext);
          include_once($admin_tpl . '/message' . $admin_tplext);
          include_once($admin_tpl . '/footer' . $admin_tplext);
        }
        else {
          // errors exist
          $page_title = 'Errors Exist: Adminstration Edit Category - eXhibition';
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
            $num_post_message = '1 category selected';
          }
          else {
            $num_post_message = $_SESSION['num_checkbox'] . ' categories selected';
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
                      c.category_id,
                      c.cat_name,
                      c.sidebar,
                      c.rss_channel,
                      m.title,
                      m.description,
                      m.keywords,
                      m.display,
                      m.priority
                    FROM
                      category c,
                      meta_data m
                    WHERE
                      c.category_id IN ($in)
                    AND
                      c.category_id = $_SESSION[category_id]
                    AND
                      c.meta_data_id = m.meta_data_id
                    LIMIT 
                      $offset, $max_results";

          // get the category details
          $result = mysql_query($query);
          if (!mysql_num_rows($result)) {
            // no categories found
            $info = 'No categories found!';
            include_once($admin_tpl . '/message' . $admin_tplext);
            exit();
          }
          else {
            // categories found
            while ($row = mysql_fetch_assoc($result)) {
              $_SESSION['category_id'] = $row['category_id'];
              $_SESSION['cat_name']    = $row['cat_name'];
              $_SESSION['sidebar']     = $row['sidebar'];
              $_SESSION['rss_channel'] = $row['rss_channel'];
              $_SESSION['title']       = $row['title'];
              $_SESSION['description'] = $row['description'];
              $_SESSION['keywords']    = $row['keywords'];
              $_SESSION['display']     = $row['display'];
              $_SESSION['priority']    = $row['priority'];
            }
          }

          if (isset($_SESSION['display'])) {
            $display_select = display_selectbox($_SESSION['display']);
          }

          if (isset($_SESSION['sidebar'])) {
            $sidebar_select = sidebar_selectbox($_SESSION['sidebar']);
          }

          if (isset($_SESSION['rss_channel'])) {
            if ($_SESSION['rss_channel'] == 'yes') {
              $checked = ' checked';
            }
            else {
              $checked = null;
            }
          }

          // paginate
          include_once('inc/paginate_inc.php');
          $pagination_row = paginate_child($page, $total_pages);

          include_once($admin_tpl . '/header' . $admin_tplext);
          include_once($admin_tpl . '/category_edit_form' . $admin_tplext);
          include_once($admin_tpl . '/footer' . $admin_tplext);
        }
        break;
    }
  }
  // the delete button is clicked:
  elseif (isset($_POST['delete']) && $_POST['delete'] == 'Delete' || isset($_SESSION['action']) && $_SESSION['action'] == 'category_delete') {
    if (!empty($_POST['checkbox'])) {
      // load the session vars
      $_SESSION['action']       = 'category_delete';
      $_SESSION['num_checkbox'] = count($_POST['checkbox']);
      $_SESSION['checkbox']     = $_POST['checkbox'];
    }
    elseif (empty($_SESSION['checkbox'])) {
      // if no box is selected when an action is requested, do nothing.
      header('location: ' . $_SERVER['PHP_SELF']);
    }

    // set message vars
    $page_title = 'Category Delete - eXhibition';
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
          $num_post_message = '1 category selected';
        }
        else {
          $num_post_message = $_SESSION['num_checkbox'] . ' categories selected';
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
                    c.category_id,
                    c.cat_name,
                    c.sidebar,
                    c.rss_channel,
                    m.title,
                    m.description,
                    m.keywords,
                    m.display,
                    m.priority
                  FROM
                    category c,
                    meta_data m
                  WHERE
                    c.category_id IN ($in)
                  AND
                    c.meta_data_id = m.meta_data_id
                  LIMIT 
                    $offset, $max_results";

        $result = mysql_query($query);

        if (!mysql_num_rows($result)) {
          // no categories found
          $info = 'No categories found!';
          include_once($admin_tpl . '/header' . $admin_tplext);
          include_once($admin_tpl . '/message' . $admin_tplext);
          include_once($admin_tpl . '/footer' . $admin_tplext);
          exit();
        }
        else {
          // categories found
          $details = '';
          while ($row = mysql_fetch_assoc($result)) {
            // format the category details
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
        // delete this row from the database
        $category_id = $_SESSION['checkbox'][0];

        $query = "DELETE FROM 
                    category, meta_data
                  USING
                    category, meta_data
                  WHERE
                    category.category_id = '$category_id'
                  AND
                    category.meta_data_id = meta_data.meta_data_id";

        if (!$result = mysql_query($query)) {
          $info = '<p>Not deleted! Please try again later.</p>';
        }
        else {
          $info = '<p>Item deleted!</p>';
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
    // List view: build a list of existing categories
    $page_title = 'Categories - eXhibition';
    $message = 'Logged in as: ' . $_SESSION['administrator'];

    // count the database rows
    $query = "SELECT count(*) FROM category";
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
      $default_fieldname = 'category_id';
    }

    // initialize sort vars
    $orderby             = 'category_id DESC';  // appended to sql query
    $orderby_category_id = 'category_id';       // appended to <th> link
    $orderby_cat_name    = 'cat_name';          // appended to <th> link
    $orderby_cat_type    = 'cat_type';          // appended to <th> link
    $orderby_priority    = 'priority';          // appended to <th> link
    $orderby_display     = 'display';           // appended to <th> link
    $orderby_sidebar     = 'sidebar';           // appended to <th> link
    $paginate_str        = 'category_id';       // appended to pagination link
    $sortpic_up          = '<img src="img/sort_up.gif" width="13" height="11" alt="Sort by ' . $default_fieldname . '" title="Sort by ' . $default_fieldname . '">';
    $sortpic_dn          = '<img src="img/sort_dn.gif" width="13" height="11" alt="Sort by ' . substr($default_fieldname, 0, -5) . ' descending" title="Sort by ' . substr($default_fieldname, 0, -5) . ' descending">';
    $sortpic             = $sortpic_up;         // arrow for column heading - may point up or down
    if (!isset($_GET['sort'])) {
      $_GET['sort'] = 'category_id';
    }
    // maintain sort vars when column headings are clicked
    switch (gp_filter($_GET['sort'])) {
      case 'category_id':
      default:
        $orderby             = 'category_id ASC';
        $orderby_category_id = 'category_id_desc';
        $orderby_cat_name    = 'cat_name';
        $paginate_str        = 'category_id';
        $sortpic             = $sortpic_up;
        break;
      case 'category_id_desc':
        $orderby             = 'category_id DESC';
        $orderby_category_id = 'category_id';
        $paginate_str        = 'category_id_desc';
        $sortpic             = $sortpic_dn;
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
      case 'cat_type':
        $orderby          = 'cat_type ASC';
        $orderby_cat_type = 'cat_type_desc';
        $paginate_str     = 'cat_type';
        $sortpic          = $sortpic_up;
        break;
      case 'cat_type_desc':
        $orderby          = 'cat_type DESC';
        $orderby_cat_type = 'cat_type';
        $paginate_str     = 'cat_type_desc';
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
      case 'sidebar':
        $orderby         = 'sidebar ASC';
        $orderby_sidebar = 'sidebar_desc';
        $paginate_str    = 'sidebar';
        $sortpic         = $sortpic_up;
        break;
      case 'sidebar_desc':
        $orderby         = 'sidebar DESC';
        $orderby_sidebar = 'sidebar';
        $paginate_str    = 'sidebar_desc';
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

    $category_id_th = $sub1 . $orderby_category_id . $sub2 . '">Category ID</a>';
    if ($paginate_str == 'category_id' || $paginate_str == 'category_id_desc') {
      $category_id_th .= $sortpic;
    }
    $category_id_th .= "\n";

    $cat_name_th = $sub1 . $orderby_cat_name . $sub2 . '">Category Name</a>';
    if ($paginate_str == 'cat_name' || $paginate_str == 'cat_name_desc') {
      $cat_name_th .= $sortpic;
    }
    $cat_name_th .= "\n";

    $cat_type_th = $sub1 . $orderby_cat_type . $sub2 . '">Type</a>';
    if ($paginate_str == 'cat_type' || $paginate_str == 'cat_type_desc') {
      $cat_type_th .= $sortpic;
    }
    $cat_type_th .= "\n";

    $priority_th = $sub1 . $orderby_priority . $sub2 . '">Priority Number</a>';
    if ($paginate_str == 'priority' || $paginate_str == 'priority_desc') {
      $priority_th .= $sortpic;
    }
    $priority_th .= "\n";

    $display_th = $sub1 . $orderby_display . $sub2 . '">Display</a>';
    if ($paginate_str == 'display' || $paginate_str == 'display_desc') {
      $display_th .= $sortpic;
    }
    $display_th .= "\n";

    $sidebar_th = $sub1 . $orderby_sidebar . $sub2 . '">Side Bar</a>';
    if ($paginate_str == 'sidebar' || $paginate_str == 'sidebar_desc') {
      $sidebar_th .= $sortpic;
    }
    $sidebar_th .= "\n";

    // list query: get a summary list of existing categories
    $query = "SELECT
                c.category_id,
                c.cat_name,
                c.cat_type,
                m.priority,
                m.display,
                c.sidebar
              FROM 
                category c,
                meta_data m
              WHERE
                c.meta_data_id = m.meta_data_id
              ORDER BY
                $orderby
              LIMIT
                $offset, $max_results";

    $result = mysql_query($query);

    if (!$result) {
      // no categories exist
      $data_rows = '    <tr>' . "\n" .
                   '        <td colspan="7" align="center" height="35" bgcolor="#eeeeee">' . "\n" .
                   '            <p>No categories found! No results!</p>' . "\n" .
                   '        </td>' . "\n" .
                   '    </tr>' . "\n";
    }
    else {
      if (mysql_num_rows($result) < 1) {
        // no categories exist
        $data_rows = '    <tr>' . "\n" .
                     '        <td colspan="7" align="center" height="35" bgcolor="#eeeeee">' . "\n" .
                     '            <p>No categories found!</p>' . "\n" .
                     '        </td>' . "\n" .
                     '    </tr>' . "\n";
      }
      else {
        // categories exist
        $data_rows = '';
        $i = 0;
        while ($row = mysql_fetch_array($result)) {
          $category_id = $row['category_id'];
          $cat_name    = $row['cat_name'];
          $cat_type    = $row['cat_type'];
          $priority    = $row['priority'];
          $display     = $row['display'];
          $sidebar     = $row['sidebar'];

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
          '                    <input type="checkbox" name="checkbox[]" value="' . $category_id . '"' . $checked[$i] . '>' . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="#eeeeee" align="center" valign="middle">' . "\n" .
          '                     ' . $category_id . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="#eeeeee" align="center" valign="middle">' . "\n" .
          '                    ' . $cat_name . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="#eeeeee" align="center" valign="middle">' . "\n" .
          '                     ' . $cat_type . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="#eeeeee" align="center" valign="middle">' . "\n" .
          '                     ' . $priority . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="#eeeeee" align="center" valign="middle">' . "\n" .
          '                     ' . $display . "\n" .
          '                </td>' . "\n" .
          '                <td bgcolor="#eeeeee" align="center" valign="middle">' . "\n" .
          '                     ' . $sidebar . "\n" .
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
    include_once($admin_tpl . '/category_list' . $admin_tplext);
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