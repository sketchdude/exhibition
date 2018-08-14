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
require_once('includes/config_inc.php');

// This ensures ecommerce is enabled
if (isset($site['ecommerce']) && $site['ecommerce'] == 'enable') {
  include_once('includes/time_inc.php');
  include_once('includes/navigate_inc.php');
  include_once('includes/validate_inc.php');
  include_once('includes/header_inc.php');

  if (isset($_POST['submit']) && $_POST['submit'] == 'add_name') {
    // A name is submitted to the list
    // Send out a confirmation number by email
    // email confirmation
  }
  else { 
    // Nothing submitted so show default
    $text = '<p>Add your name & email to my waiting list to recieve a direct offer to purchase my next work at the current amount.</p>' . "\n" .
            '<p>If you are willing to pay more, you may be able to move up on the list and recieve an offer sooner.</p>' . "\n";
    // Count valid names on the list

    $content = $text;

  }

  // activate the header template
  include_once('templates/' . $template_folder . '/header' . $template_ext);

  // load the sidebar & gallery_menu
  $sidebar      = print_sidebar();
  $galleries = print_gallery();
  $rss_feed = find_rss_feed($meta_data);

  // activate the waiting list template
  include_once('templates/' . $template_folder . '/waiting_list' . $template_ext);

  // activate the footer template
  include_once('templates/' . $template_folder . '/footer' . $template_ext);
}
else {
  header('location: index.php');
  exit();
}

?>