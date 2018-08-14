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
require_once('inc/validate_inc.php');
require_once('../includes/time_inc.php');
require_once('inc/login_inc.php');
require_once('inc/image_inc.php');
// look for a valid login session
if (isset($_SESSION['administrator']) && $_SESSION['authorized'] == 'admin') {
  // check to see if someone is logging out
  if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    admin_logout();
  }

  // user is successfully logged in
  $errors     = null;
  $message    = 'Logged in as: ' . $_SESSION['administrator'];
  $page_title = 'Administration Main Page - eXhibition';
  $info       = 'Welcome!';
  $text       = null;

  $public_comments = '    <tr>' . "\n" .
                     '        <td colspan="4" align="center" height="35" bgcolor="#eeeeee">' . "\n" .
                     '            No comments found!' . "\n" .
                     '        </td>' . "\n" .
                     '    </tr>' . "\n";

  // count the database rows
  $query = "SELECT count(*) FROM comment";
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
  $setmax_results = 10;

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
    $default_fieldname = 'comment_id';
  }

  // initialize sort vars
  $orderby               = 'comment_id DESC'; // appended to sql query
  $sortpic               = null;              // arrow for column heading - may point up or down
  $orderby_comment_id    = 'comment_parent';  // appended to column heading link
  $orderby_author        = 'author';          // appended to column heading link
  $orderby_title         = 'title';           // appended to column heading link
  $orderby_pub_date      = 'pub_date';        // appended to column heading link
  $paginate_str          = 'comment_id';      // appended to pagination link
  $sortpic_up            = '<img src="img/sort_up.gif" width="13" height="11" alt="Sort by ' . $default_fieldname . '" title="Sort by ' . $default_fieldname . '">';
  $sortpic_dn            = '<img src="img/sort_dn.gif" width="13" height="11" alt="Sort by ' . substr($default_fieldname, 0, -5) . ' descending" title="Sort by ' . substr($default_fieldname, 0, -5) . ' descending">';
  if (!isset($_GET['sort'])) {
    $_GET['sort'] = 'comment_id';
  }

  // maintain sort vars when column headings are clicked
  switch (gp_filter($_GET['sort'])) {
    case 'comment_parent':
    default:
      $orderby            = 'comment_parent ASC';
      $orderby_comment_id = 'comment_parent_desc';
      $orderby_author     = 'author';
      $orderby_title      = 'title';
      $orderby_pub_date   = 'pub_date';
      $paginate_str       = 'comment_id';
      $sortpic            = $sortpic_up;
      break;
    case 'comment_parent_desc':
      $orderby            = 'comment_parent DESC';
      $orderby_comment_id = 'comment_parent';
      $paginate_str       = 'comment_parent_desc';
      $sortpic            = $sortpic_dn;
      break;
    case 'author':
      $orderby        = 'author ASC';
      $orderby_author = 'author_desc';
      $paginate_str   = 'author';
      $sortpic        = $sortpic_up;
      break;
    case 'author_desc':
      $orderby        = 'author DESC';
      $orderby_author = 'author';
      $paginate_str   = 'author_desc';
      $sortpic        = $sortpic_dn;
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
    case 'pub_date':
      $orderby          = 'pub_date ASC';
      $orderby_pub_date = 'pub_date_desc';
      $paginate_str     = 'pub_date';
      $sortpic          = $sortpic_up;
      break;
    case 'pub_date_desc':
      $orderby          = 'pub_date DESC';
      $orderby_pub_date = 'pub_date';
      $paginate_str     = 'pub_date_desc';
      $sortpic          = $sortpic_dn;
      break;
  }

  //construct dynamic hyperlinks for the html table headings
  $sub1 = '<a href="' . $_SERVER['PHP_SELF'] . '?sort=';
  $sub2 = '&amp;page=' . $page . $display_all;

  $comment_id_th = $sub1 . $orderby_comment_id . $sub2 . '">Content</a>';
  if ($paginate_str == 'comment_id' || $paginate_str == 'comment_id_desc') {
    $comment_id_th .= $sortpic;
  }
  $comment_id_th .= "\n";

  $author_th = $sub1 . $orderby_author . $sub2 . '">Author</a>';
  if ($paginate_str == 'author' || $paginate_str == 'author_desc') {
    $author_th .= $sortpic;
  }
  $author_th .= "\n";

  $title_th = $sub1 . $orderby_title . $sub2 . '">Title</a>';
  if ($paginate_str == 'title' || $paginate_str == 'title_desc') {
    $title_th .= $sortpic;
  }
  $title_th .= "\n";

  $pub_date_th = $sub1 . $orderby_pub_date . $sub2 . '">Date</a>';
  if ($paginate_str == 'pub_date' || $paginate_str == 'pub_date_desc') {
    $pub_date_th .= $sortpic;
  }
  $pub_date_th .= "\n";

  // get all public comments
  $query = "SELECT
              c.comment_id,
              c.comment_parent,
              c.parent_type,
              c.author,
              c.message,
              c.msg_type,
              a.thumbnail,
              p.name,
              m.title,
              m.pub_date 
            FROM
              comment c,
              artwork a,
              page p,
              meta_data m
            WHERE
              c.msg_type = 'public'
            AND
              c.meta_data_id = m.meta_data_id
            AND a.artwork_id = c.comment_parent OR p.page_id = c.comment_parent
            ORDER BY
              $orderby
            LIMIT
              $offset, $max_results";

  if ($result = mysql_query($query) or die(mysql_error())) {
    if (!mysql_num_rows($result)) {
      // no comments exist
      $data_rows = '    <tr>' . "\n" .
                   '        <td colspan="4" align="center" height="35" bgcolor="#eeeeee">' . "\n" .
                   '            No comments found!' . "\n" .
                   '        </td>' . "\n" .
                   '    </tr>' . "\n";
    }
    else {
      // comments found so format them and print them out
      $_SESSION['comment_parent'] = $row['comment_parent'];
      $public_comments = '';
      $color1 = '#e5e5e5';
      $color2 = '#eeeeee';
      $i = 0;
      while ($row = mysql_fetch_array($result)) {
        // make sure some kind of name & title appears for every post
        if (strlen($row['author']) < 1) {
          $row['author'] = 'Anonymous';
        }
        if (strlen($row['title']) < 1) {
          $row['title'] = 'No title entered';
        }

        // find out which content item this message is tied to
        if ($row['parent_type'] == 'art') {          
          $content = resize_image('thumbnail', '40');
        }
        elseif($row['parent_type'] == 'page') {
          $content = 'about id = ' . $row['comment_parent'];
        }
        elseif($row['parent_type'] == 'comment') {
          $content = 'comment id = ' . $row['comment_parent'];
        }

        // define a color for the table rows
        $row_color = ($i % 2) ? $color1 : $color2;

        $pub_date = date_format_long($row['pub_date']);
        $link = '<a href="message_detail.php?content=message&amp;type=public&amp;parent_type=' . $row['parent_type'] . '&amp;id=' . $row['comment_id'] . '">' . $row['title'] . '</a>';
        $public_comments .= '<tr>' . "\n" .
                            '<td bgcolor="' . $row_color . '" align="center" height="45">' . $content . '</td>' . "\n" .
                            '<td bgcolor="' . $row_color . '" align="center">' . $row['author'] . '</td>' . "\n" .
                            '<td bgcolor="' . $row_color . '"> &nbsp;' . $link . '</td>' . "\n" .
                            '<td bgcolor="' . $row_color . '"> &nbsp;' . $pub_date . '</td>' . "\n" .
                            '</tr>' . "\n";
        $i++;
      }
    }
  }

  // construct pagination links
  include_once('inc/paginate_inc.php');
  paginate_list($page, $total_pages);

  // activate the template
  include_once($admin_tpl . '/header' . $admin_tplext);
  include_once($admin_tpl . '/comments' . $admin_tplext);
  include_once($admin_tpl . '/footer' . $admin_tplext);

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