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
require_once('includes/config_inc.php');
require_once('includes/order_inc.php');
require_once('includes/navigate_inc.php');
require_once('includes/validate_inc.php');
require_once('includes/header_inc.php');

$breadcrumb = null;

// validate browser requests for content
if (isset($_GET['content']) || isset($_GET['id'])) {
  $_GET['content'] = gp_filter($_GET['content']);
  $_GET['id']      = gp_filter($_GET['id']);
}
else {
  $_GET['content'] = null;
  $_GET['id']      = null;
}

// get meta data for the page header
if (!isset($_GET['content'])) {
  $_GET['content'] = 'home';
}
if (!isset($_GET['id'])) {
  $_GET['id'] = null;
}
$meta_data = load_meta_data('home', $_GET['id']);

// Sidebar Contruction
$sidebar = print_sidebar();
$galleries = print_gallery();
$rss_icon = show_rss_icon($site);
$show_cart_link = enable_ecommerce($site);

// testing html wrappers
$type = 'gallery';
$html = '';
$cat_name = 'My Big Fat Test';

$query = "select gallery_name AS name, gallery_id AS id from gallery";
$result = mysql_query($query) or die(mysql_error());

while ($row = mysql_fetch_array($result)) {
  $html .= catwrapitem($row, $type);
}

//$cat_test = catwrap($cat_name, $html, $type);

// main content for the front page
$content = '<h3>' . $site['blurb'] . '</h3>' . "\n" .
           '<img src="' . $site['url'] . '/' . $home_dir . '/images/' . $site['splash'] . '" alt="' . $site['title'] . '" title="' . $site['title'] . '">' . "\n";

// activate the templates
require_once('templates/' . $template_folder . '/header' . $template_ext);
require_once('templates/' . $template_folder . '/body' . $template_ext);
require_once('templates/' . $template_folder . '/footer' . $template_ext);

?>