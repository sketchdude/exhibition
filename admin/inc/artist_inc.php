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

// compare elements of 2 arrays and return the difference
function artist_fields_diff() {

  // get the old fields from mysql
  $query = "SELECT
              name,
              birth_date,
              death_date,
              location
            FROM
              artist
            WHERE
              artist_id = $_SESSION[artist_id]";

  $result = mysql_query($query);

  // load all current/new fields into an array
  $new_fields = array('name'       => $_SESSION['name'],
                      'birth_date' => $_SESSION['birth_date'],
                      'death_date' => $_SESSION['death_date'],
                      'location'   => $_SESSION['location']);

  if ($old_fields = mysql_fetch_assoc($result)) {
    // compare the name fields
    if ($old_fields['name'] == $new_fields['name']) {
      $update['name'] = false;
    }
    else {
      $update['name'] = $new_fields['name'];
    }

    // compare the birth_date fields
    if ($old_fields['birth_date'] == $new_fields['birth_date']) {
      $update['birth_date'] = false;
    }
    else {
      $update['birth_date'] = $new_fields['birth_date'];
    }

    // compare the death_date fields
    if ($old_fields['death_date'] == $new_fields['death_date']) {
      $update['death_date'] = false;
    }
    else {
      $update['death_date'] = $new_fields['death_date'];
    }

    // compare the location fields
    if ($old_fields['location'] == $new_fields['location']) {
      $update['location'] = false;
    }
    else {
      $update['location'] = $new_fields['location'];
    }
  }

  // get rid of the used arrays
  unset($old_fields);
  unset($new_fields);

  if (empty($update)) {
    return false;
  }
  else {
    $query_string = '';
    foreach ($update as $new => $entry) {
      if (!empty($entry)) {
        $query_string .= ", $new = " . "'" . $entry . "'";
      }
    }
    $query_string = substr($query_string, 1);
    // $update is no longer needed
    unset($update);

    if (empty($query_string)) {
      return false;
    }
    else {
      return $query_string;
    }
  }
}

?>