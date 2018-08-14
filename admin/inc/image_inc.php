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

function resize_image($img_type, $maximum=null) {
  global $home_dir, $row;
  // $img_type may be: 'gallery_icon', 'about_image' or 'thumbnail'
  switch ($img_type) {
    case 'gallery_icon':
        $url   = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $home_dir . '/images/gallery_icons/';
        $image = $row['gallery_icon'];
        $alt   = $row['gallery_name'];
      break;
    case 'picture':
        $url   = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $home_dir . '/images/';
        $image = $row['picture'];
        $alt   = $row['title'];
      break;
    case 'thumbnail':
        $url   = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $home_dir . '/images/original_art/thumbnails/';
        $image = $row['thumbnail'];
        $alt   = $row['title'];
      break;
    case 'Image':
        $url   = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $home_dir . '/images/original_art/thumbnails/';
        $image = $row['Image'];
        $alt   = $row['title'];
      break;
    case 'artwork':
        $url   = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $home_dir . '/images/original_art/artwork/';
        $image = $row['path'];
        $alt   = $row['caption'];
      break;
  }

  if ($image) {
    if ($maximum != null) {
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
        $thumbnail = '<img src="' . $image_url . '" width="' . $thumbnail_width . '" height="' . $thumbnail_height . '" alt="' . $alt . '" title="' . $alt . '" border="0">';
      }
    }
    else {
      $thumbnail = '<img src="' . $url . $image . '" alt="' . $alt . '" title="' . $alt . '" border="0">';
    }
  }
  else {
    return false;
  }
  return $thumbnail;
}

// stefan dot wehowsky at profilschmiede dot de
/* resizeToFile resizes a picture and writes it to the harddisk
 * 
 *   $sourcefile = the filename of the picture that is going to be resized
 *   $dest_x     = X-Size of the target picture in pixels
 *   $dest_y     = Y-Size of the target picture in pixels
 *   $targetfile = The name under which the resized picture will be stored
 *   $jpegqual   = The Compression-Rate that is to be used
 */

function resizeToFile ($sourcefile, $dest_x, $dest_y, $targetfile, $jpegqual) {
  /* Get the dimensions of the source picture */
  $picsize = getimagesize("$sourcefile");
  $source_x  = $picsize[0];
  $source_y  = $picsize[1];
  $source_id = imageCreateFromJPEG("$sourcefile");

  /* Create a new image object (not neccessarily true colour) */
  $target_id = imagecreatetruecolor($dest_x, $dest_y);

  /* Resize the original picture and copy it into the just created image
     object. Because of the lack of space I had to wrap the parameters to
     several lines. I recommend putting them in one line in order keep your
     code clean and readable */
  $target_pic = imagecopyresampled($target_id, $source_id, 0, 0, 0, 0, $dest_x, $dest_y, $source_x, $source_y);

  /* Create a jpeg with the quality of "$jpegqual" out of the image object "$target_pic".
     This will be saved as $targetfile */
  imagejpeg($target_id, "$targetfile", $jpegqual);

  return true;
}

// office at 4point-webdesign dot com
// Here's a funtion i used to resize and save images uploaded by the user, you can 
// either create thumbnails or other images.

//Main "feature" is that the width and height stay relativ to each other.

// imgcomp is the quality, i turned it around so now its from 0 -best to 100 -most compressed.

// For gif version just change the functions names.

function resampimagejpg($forcedwidth, $forcedheight, $sourcefile, $destfile, $imgcomp) {
  $g_imgcomp = 100 - $imgcomp;
  $g_srcfile = $sourcefile;
  $g_dstfile = $destfile;
  $g_fw = $forcedwidth;
  $g_fh = $forcedheight;

  if (file_exists($g_srcfile)) {
    $g_is = @getimagesize($g_srcfile);
    if (($g_is[0] - $g_fw) >= ($g_is[1] - $g_fh)) {
      $g_iw = $g_fw;
      $g_ih = ($g_fw / $g_is[0]) * $g_is[1];
    }
    else {
      $g_ih = $g_fh;
      $g_iw = ($g_ih / $g_is[1]) * $g_is[0];
    }

    $img_src = imagecreatefromjpeg($g_srcfile);
    $img_dst = imagecreate($g_iw,$g_ih);
    imagecopyresampled($img_dst, $img_src, 0, 0, 0, 0, $g_iw, $g_ih, $g_is[0], $g_is[1]);
    imagejpeg($img_dst, $g_dstfile, $g_imgcomp);
    imagedestroy($img_dst);
    return true;
  }
  else {
    return false;
  }
}

?>