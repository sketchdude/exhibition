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

// this is for the parent scripts, which all contain a summary list of db rows
function paginate_list($page, $total_pages) {
  global $paginate_str, $prev, $prev_url, $active_action, $j, $next, $next_url;
  global $pagination_row, $page, $total_pages, $pageno, $pageno_url;

  $pagination_row = '';
  if ($page > 1) {
    $prev = ($page - 1);
    $prev_url = '<span class="paginate"><a href="' . $_SERVER['PHP_SELF'] . '?sort=' . $paginate_str . '&amp;page=' . $prev . '&amp;action=' . $active_action . '">&lt;&lt;PREV</a></span> ';
    $pagination_row .= $prev_url;
  }
  else {
    $pagination_row .= '<span class="nolink">&lt;&lt;PREV</span> ';
  }
  for ($j = 1; $j <= $total_pages; $j++) {
    if (($page) == $j) {
      $pageno = ' <span class="nolink">' . $j . '</span> ';
      $pagination_row .= $pageno;
    }
    else {
      $pageno_url = ' <span class="paginate"><a href="' . $_SERVER['PHP_SELF'] . '?sort=' . $paginate_str . '&amp;page=' . $j . '&amp;action=' . $active_action . '">' . $j . '</a></span> ';
      $pagination_row .= $pageno_url;
    }
  }
  if ($page < $total_pages) {
    $next = ($page + 1);
    $next_url = ' <span class="paginate"><a href="' . $_SERVER['PHP_SELF'] . '?sort=' . $paginate_str . '&amp;page=' . $next . '&amp;action=' . $active_action . '">NEXT&gt;&gt;</a></span>' . "\n";
    $pagination_row .= $next_url;
  }
  else {
    $pagination_row .= ' <span class="nolink">NEXT&gt;&gt;</span>';
  }
  return $pagination_row;
}

// this is for the child scripts, which are detailed views of the parent script summaries
function paginate_child($page, $total_pages) {
  global $prev, $prev_url, $j, $next, $next_url;
  global $pagination_row, $page, $total_pages, $pageno, $pageno_url;

  $pagination_row = '';
  if ($page > 1) {
    $prev = ($page - 1);
    $prev_url = '<span class="paginate"><a href="' . $_SERVER['PHP_SELF'] . '?page=' . $prev . '">&lt;&lt;PREV</a></span> ';
    $pagination_row .= $prev_url;
  }
  else {
    $pagination_row .= '<span class="nolink">&lt;&lt;PREV</span> ';
  }
  for ($j = 1; $j <= $total_pages; $j++) {
    if (($page) == $j) {
      $pageno = ' <span class="nolink">' . $j . '</span> ';
      $pagination_row .= $pageno;
    }
    else {
      $pageno_url = ' <span class="paginate"><a href="' . $_SERVER['PHP_SELF'] . '?page=' . $j . '">' . $j . '</a></span> ';
      $pagination_row .= $pageno_url;
    }
  }
  if ($page < $total_pages) {
    $next = ($page + 1);
    $next_url = ' <span class="paginate"><a href="' . $_SERVER['PHP_SELF'] . '?page=' . $next . '">NEXT&gt;&gt;</a></span>' . "\n";
    $pagination_row .= $next_url;
  }
  else {
    $pagination_row .= ' <span class="nolink">NEXT&gt;&gt;</span>';
  }
  return $pagination_row;
}

?>