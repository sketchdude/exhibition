<?php

// eXhibition - A PHP/MySQL Art Publishing System
// copyright (c) 2005 sketchdude

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

// load meta_data into variables
function load_meta_data($content, $id=null) {
  // $site[] variables are set in config_inc.php. You should change the values there.
  global $site;
  switch($content) {
    default:
    case 'home':
      $query = "SELECT
                  title,
                  description,
                  keywords,
                  rss_feed
                FROM
                  meta_data
                WHERE
                  meta_type = 'site'";
      break;
    case 'cart':
      $query = "SELECT
                  title,
                  description,
                  keywords,
                  rss_feed
                FROM
                  meta_data
                WHERE
                  meta_type = 'site'";
      break;
    case 'pricelist':
      $query = "SELECT
                  title,
                  description,
                  keywords,
                  rss_feed
                FROM
                  meta_data
                WHERE
                  meta_type = 'site'";
      break;
    case 'category':
      $query = "SELECT
                  m.title,
                  m.description,
                  m.keywords,
                  m.display,
                  m.rss_feed,
                  m.priority,
                  m.pub_date,
                  m.last_updated 
                FROM
                  meta_data m, category c
                WHERE
                  c.category_id = '$id'
                AND
                  m.display = 'show'
                AND
                  m.meta_data_id = c.meta_data_id";
      break;
    case 'gallery':
      $query = "SELECT
                  m.title,
                  m.description,
                  m.keywords,
                  m.display,
                  m.rss_feed,
                  m.priority,
                  m.pub_date,
                  m.last_updated 
                FROM
                  meta_data m, gallery g
                WHERE
                  g.gallery_id = '$id'
                AND
                  m.display = 'show'
                AND
                  m.meta_data_id = g.meta_data_id";
      break;
    case 'artwork':
      $query = "SELECT
                  m.title,
                  m.description,
                  m.keywords,
                  m.display,
                  m.rss_feed,
                  m.priority,
                  m.pub_date,
                  m.last_updated 
                FROM
                  meta_data m, artwork a
                WHERE
                  a.artwork_id = '$id'
                AND
                  m.display = 'show'
                AND
                  m.meta_data_id = a.meta_data_id";
      break;
    case 'page':
      $query = "SELECT
                  m.title,
                  m.description,
                  m.keywords,
                  m.display,
                  m.rss_feed,
                  m.priority,
                  m.pub_date,
                  m.last_updated 
                FROM
                  meta_data m, page a
                WHERE
                  a.page_id = '$id'
                AND
                  m.display = 'show'
                AND
                  m.meta_data_id = a.meta_data_id";
      break;
  }
  if (isset($query)) {
    $result = mysql_query($query);
    if (!mysql_num_rows($result)) {
      $meta_data['title']       = $site['title'];
      $meta_data['description'] = $site['description'];
      $meta_data['keywords']    = $site['keywords'];
      $meta_data['rss_feed']    = 'disable';
    }
    else {
      $meta_data = mysql_fetch_array($result);
    }
  }
  return $meta_data;
}

?>