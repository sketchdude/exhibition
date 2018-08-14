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

// returns a resized clickable thumbnail wrapped in a table data cell
function gallery_icon($maximum, $thumbnail, $artwork_id, $title) {
  global $home_dir;

  $dim   = $maximum + 45;
  $sub1  = '<td align="center" valign="middle" width="' . $dim . '" height="' . $dim . '" bgcolor="#bbbbbb"><p id="thumbnail_text">' . $title . '</p><a href="artwork.php?content=artwork&amp;id=' . $artwork_id . '">';
  $sub2  = '</a></td>';
  $url   = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $home_dir . '/images/original_art/thumbnails/';
  $image = $thumbnail;
  $alt   = $title;

  if ($image) {
    $image_url = $url . $image;
    $full_size = @getimagesize($image_url);
    $width  = $full_size[0];
    $height = $full_size[1];
    if ($width > 0 && $height > 0) {
      $x_ratio = $maximum / $width;
      $y_ratio = $maximum / $height;
      if (($width <= $maximum) && ($height <= $maximum)) {
        $thumbnail_width  = $width;
        $thumbnail_height = $height;
      }
      elseif (($x_ratio * $height) < $maximum) {
        $thumbnail_height = ceil($x_ratio * $height);
        $thumbnail_width  = $maximum;
      }
      else {
        $thumbnail_width  = ceil($y_ratio * $width);
        $thumbnail_height = $maximum;
      }
      $thumbnail = $sub1 . '<img src="' . $image_url . '" width="' . $thumbnail_width . '" height="' . $thumbnail_height . '" alt="' . $alt . '" title="' . $alt . '" border="0">' . $sub2;
    }
    else {
      return false;
    }
  }
  else {
    return false;
  }
  return $thumbnail;
}

function print_image() {
  global $home_dir, $images, $site;
  $image_url = $site['url'] . '/' . $home_dir . '/images/original_art/artwork/' . $images['path'];

  $full_size = getimagesize($image_url);
  $width  = $full_size[0];
  $height = $full_size[1];

  $alt   = $images['caption'];
  $image_tag = '<img src="' . $image_url . '" width="' . $width. '" height="' . $height . '" alt="' . $alt . '" title="' . $alt . '" border="0">';
  return $image_tag;
}

function thumbnail($thumbnail, $title, $maximum) {
  global $home_dir, $site;
  $url   = $site['url'] . '/' . $home_dir . '/images/original_art/thumbnails/';
  $image = $thumbnail;
  $alt   = $title;

  if ($image) {
    $image_url = $url . $image;
    $full_size = getimagesize($image_url);
    $width  = $full_size[0];
    $height = $full_size[1];
    if ($width > 0 && $height > 0) {
      $x_ratio = $maximum / $width;
      $y_ratio = $maximum / $height;
      if (($width <= $maximum) && ($height <= $maximum)) {
        $thumbnail_width  = $width;
        $thumbnail_height = $height;
      }
      elseif (($x_ratio * $height) < $maximum) {
        $thumbnail_height = ceil($x_ratio * $height);
        $thumbnail_width  = $maximum;
      }
      else {
        $thumbnail_width  = ceil($y_ratio * $width);
        $thumbnail_height = $maximum;
      }
      $thumbnail = '<img id="image" src="' . $image_url . '" width="' . $thumbnail_width . '" height="' . $thumbnail_height . '" alt="' . $alt . '" title="' . $alt . '" border="0">' . "\n";
    }
    else {
      return false;
    }
  }
  else {
    return false;
  }
  return $thumbnail;
}

?>