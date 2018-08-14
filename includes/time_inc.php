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


// formats current time for inclusion in database datetime fields
function get_datetime() {
  // 0000-00-00 00:00:00
  $now = date("Y-m-d H:i:s");

  return $now;
}

// formats a long date ex.  Friday Dec 14, 2007 4:57am
function date_format_long($timestamp) {
  $time = strtotime($timestamp);
  $long_date = date('l M d, Y g:ia ', $time);

  return $long_date;
}

?>