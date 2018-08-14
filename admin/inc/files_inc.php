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

function upload($_FILES) {
  global $errors, $home_dir, $new_icon;

  // determine the file type and set an extension
  switch ($_FILES['gallery_icon']['type']) {
    case 'image/pjpeg':
    case 'image/jpeg':
      $ext = '.jpeg';
      break;
    case 'image/pjpg':
    case 'image/jpg':
      $ext = '.jpg';
      break;
    case 'image/gif':
      $ext = '.gif';
      break;
    default:
      $errors['gallery_icon']['filetype'] = '<p class="errors">File error: Image must be jpg or gif!</p>';
      return $errors;
  }
  // get a unique filename:
  $img_name = md5(uniqid(time())) . $ext;
  $location = $_SERVER['DOCUMENT_ROOT'] . '/' . $home_dir . '/images/gallery_icons/' . $img_name;

  // move the file to a folder in the image directory
  if (move_uploaded_file($_FILES['gallery_icon']['tmp_name'], $location)) {
    // save the new filename for mysql
    $new_icon = $img_name;
    return $new_icon;
  }
  else {
    $errors['gallery_icon']['upload'] = '<p class="errors">Upload error: Unable to upload gallery image.</p>';
    return $errors;
  } 
}

function upload_product($_FILES) {
  global $errors, $home_dir, $new_image;

  // determine the file type and set an extension
  switch ($_FILES['picture']['type']) {
    case 'image/pjpeg':
    case 'image/jpeg':
      $ext = '.jpeg';
      break;
    case 'image/pjpg':
    case 'image/jpg':
      $ext = '.jpg';
      break;
    case 'image/gif':
      $ext = '.gif';
      break;
    default:
      $errors['picture']['filetype'] = '<p class="errors">File error: Image must be jpg or gif!</p>';
      return $errors;
  }
  // get a unique filename:
  $img_name = md5(uniqid(time())) . $ext;
  $location = $_SERVER['DOCUMENT_ROOT'] . '/' . $home_dir . '/images/' . $img_name;

  // move the file to a folder in the image directory
  if (move_uploaded_file($_FILES['picture']['tmp_name'], $location)) {
    // save the new filename for mysql
    $new_image = $img_name;
    return $new_image;
  }
  else {
    $errors['picture']['upload'] = '<p class="errors">Upload error: Unable to upload gallery image.</p>';
    return $errors;
  }
}

function upload_thumbnail($_FILES) {
  global $errors, $home_dir, $new_image;

  // determine the file type and set an extension
  switch ($_FILES['thumbnail']['type']) {
    case 'image/pjpeg':
    case 'image/jpeg':
      $ext = '.jpeg';
      break;
    case 'image/pjpg':
    case 'image/jpg':
      $ext = '.jpg';
      break;
    case 'image/gif':
      $ext = '.gif';
      break;
    default:
      $errors['thumbnail']['filetype'] = '<p class="errors">File error: Image must be jpg or gif!</p>';
      return $errors;
  }
  // use the original filename:
  $img_name = $_FILES['thumbnail']['name'];
  $location = $_SERVER['DOCUMENT_ROOT'] . '/' . $home_dir . '/images/original_art/thumbnails/' . $img_name;

  // move the file to a folder in the image directory
  if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $location)) {
    // save the new filename for mysql
    $new_image = $img_name;
    return $new_image;
  }
  else {
    $errors['thumbnail']['upload'] = '<p class="errors">Upload error: Unable to upload gallery image.</p>';
    return $errors;
  } 
}

function upload_artwork($_FILES) {
  global $errors, $home_dir, $new_image;

  // determine the file type and set an extension
  switch ($_FILES['artimage']['type']) {
    case 'image/pjpeg':
    case 'image/jpeg':
      $ext = '.jpeg';
      break;
    case 'image/pjpg':
    case 'image/jpg':
      $ext = '.jpg';
      break;
    case 'image/gif':
      $ext = '.gif';
      break;
    default:
      $errors['artimage']['filetype'] = '<p class="errors">File error: Image must be jpg or gif!</p>';
      return $errors;
  }
  // use the original filename:
  $img_name = $_FILES['artimage']['name'];
  $location = $_SERVER['DOCUMENT_ROOT'] . '/' . $home_dir . '/images/original_art/artwork/' . $img_name;

  // move the file to a folder in the image directory
  if (move_uploaded_file($_FILES['artimage']['tmp_name'], $location)) {
    // save the new file name for mysql
    $new_image = $img_name;
    return $new_image;
  }
  else {
    $errors['artimage']['upload'] = '<p class="errors">Upload error: Image upload failed. Please check permissions on your upload directories!</p>';
    return $errors;
  } 
}

?>