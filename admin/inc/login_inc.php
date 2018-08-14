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

function admin_login($username, $password) {
  $is_valid = false;
  $password = md5($password);

  // validate the username and password
  $query = "SELECT admin_name FROM admin WHERE admin_name = '$username' AND admin_pass = '$password'";
  $result = mysql_query($query);
  if (!mysql_num_rows($result) < 1) {
    $is_valid = true;
  }
  else {
    $is_valid = false;
  }

  if ($is_valid) {
    // proceed with login
    session_register('authorized', 'administrator');
    $_SESSION['authorized'] = 'admin';

    $row = mysql_fetch_assoc($result);
    $_SESSION['administrator'] = $row['admin_name'];

    header('location: index.php');
    exit;
  }
}

function admin_logout() {
  session_unregister('authorized');
  session_unregister('administrator');

  session_destroy();

  unset($_SESSION);

  header('location: gallery.php');
  exit();
}

?>