<?php

// eXhibition - A PHP/MySQL Art Publishing System
// copyright (c) 2009 sketchdude

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


require_once('includes/config_inc.php');
require_once('includes/validate_inc.php');
require_once('includes/image_inc.php');

// This builds an rss feed of artworks for the whole site
function build_feed() {
  global $site;
  include_once('includes/xml-processing/gc_xmlbuilder.php');
  $xml = new gc_XmlBuilder;
  $rss_version = array('version'    => '2.0',
                       'xmlns:atom' => 'http://www.w3.org/2005/Atom');
  $atom = array('href' => 'http://sketchdude.com/exhibition/rss.php',
                'rel'  => 'self',
                'type' => 'application/rss+xml');
  $generator = 'eXhibition - A PHP/MySQL Art Publishing System';

  // is it a valid xml object
  if ((is_object($xml) == false) || (sizeof($xml) <= 0)) {
    return false;
  }

  // get the site title and description
  $query = "SELECT title, description FROM meta_data WHERE meta_type = 'site'";
  $result = mysql_query($query);
  if (mysql_num_rows($result) > 0) {
    $title = mysql_result($result, 0, 'title');
    $description = mysql_result($result, 0, 'description');
  }
  else {
    $title = $site['title'];
    $description = $site['description'];
  }

  // start building RSS output
  $xml->Push('rss', $rss_version);
    $xml->Push('channel');
      $xml->Element('language', $site['language']);
      $xml->Element('generator', $generator);
      $xml->Element('copyright', $site['owner']);
      $xml->Element('link', $site['url']);
      $xml->Element('title', $title);
      $xml->Element('description', $description);
      $xml->EmptyElement('atom:link', $atom);

      // get rss artwork items
      $query = "SELECT
                  a.artwork_id,
                  a.thumbnail,
                  m.title,
                  m.description,
                  m.link,
                  m.pub_date,
                  m.last_updated
                FROM
                  artwork a,
                  meta_data m
                WHERE
                  m.rss_feed = 'enable'
                AND
                  a.meta_data_id = m.meta_data_id
                ORDER BY
                  m.last_updated DESC";

      $result = mysql_query($query);
      while ($row = mysql_fetch_array($result)) {
        $str2_date = strtotime($row['pub_date']);
        $pub_date = date('D, d M Y h:i:s', $str2_date) . ' ' . $site['timezone'];

        $thumbnail = thumbnail($row['thumbnail'], $row['title'], '175');

        $description = '<p><a href="' . $row['link'] . '">' . $thumbnail . '</a></p><p>' . $row['description'] . '</p>';

        $xml->Push('item');
          $xml->Element('title', $row['title']);
          $xml->Element('link', $row['link']);
          $xml->Element('guid', $row['link']);
          $xml->Element('pubDate', $pub_date);
          $xml->Element('description', $description);
        $xml->Pop('item');
      }
    $xml->Pop('channel');
  $xml->Pop('rss');

  if (is_object($xml)) {
    $xml = $xml->GetXML();
    return $xml;
  }
}

if (isset($site['rss']) && $site['rss'] == 'enable') {
  // set the file's content type and character set
  header('Content-Type: text/xml;charset=utf-8');
  $rss_feed = build_feed();
  echo $rss_feed;
}
else {
  header('location: index.php');
  exit();
}

?>