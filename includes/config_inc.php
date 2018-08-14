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

session_cache_limiter('must-revalidate');

session_start();

// mysql settings
$dbname     = '';
$dbhost     = 'localhost';
$dbusername = '';
$dbuserpass = '';

// site settings:
$site['url']              = 'http://mysite.com';
$site['name']             = 'exhibition';
$site['title']            = 'eXhibition - A PHP/MySQL Art Publishing System';
$site['description']      = 'eXhibition is a simple open source content management site tailored to the visual arts community. Create and organize virtual galleries. List works of art for sale or exhibition. Publish informational pages on any subject.';
$site['keywords']         = 'visual art, content management, art work, art sale, exhibition, php';
$site['logo']             = 'exhibition.jpg';
$site['splash']           = 'splash.jpg';
$site['blurb']            = 'Welcome to my web site!';
$site['copyright']        = 'Powered by eXhibition &copy; ' . date("Y") . ' sketchdude';
$site['currency']         = '$';
$site['language']         = 'en-us';
$site['ecommerce']        = 'enable'; // set to 'disable' to turn off the shopping cart
$site['rss']              = 'enable'; // set to 'disable' to hide the rss feed icon
$site['business_address'] = 'mysite.com (907) 349-1892 400 S Colado Road Summerville Arizona 66291';
$site['timezone']         = 'GMT';
$site['owner']            = 'billfold';
// enable payment options
$site['paypal_payment_option'] = 'disable';
$site['google_payment_option'] = 'disable';
$site['mail_payment_option']   = 'disable';

// artwork settings: show or hide
// if set to show, these will be displayed on
// the detail page of the artwork.
$artwork['artist']      = 'show';
$artwork['subject']     = 'show';
$artwork['style']       = 'show';
$artwork['medium']      = 'show';
$artwork['size']        = 'show';
$artwork['status']      = 'show';
$artwork['price']       = 'show';
$artwork['shipping']    = 'show';
$artwork['handling']    = 'show';
$artwork['qty_instock'] = 'show';

// gallery settings: show or hide
// if set to show, these will be displayed along with 
// the artworks thumbnail in the gallery.
$gallery['title']   = 'show';
$gallery['artist']  = 'hide';
$gallery['medium']  = 'hide';
$gallery['size']    = 'hide';
$gallery['style']   = 'hide';
$gallery['subject'] = 'hide';
$gallery['price']   = 'hide';
$gallery['status']  = 'hide';

// turn on/off gallery icons
$gallery_icon_display = true;
//$gallery_icon_display = false;

// turn on/off breadcrumb trail of links
$breadcrumb_display = true;
//$breadcrumb_display = false;

// set a main template folder
$template_folder = 'sketch';

// set a file extension type for your main templates: '.tpl', '.html', '.htm', '.php' or whatever
$template_ext = '.html';

// set a name for the web directory
$home_dir = 'exhibition';

// set a name for the administration directory
$admin_dir = 'admin';

// set admin template folder name
$admin_tpl = 'htm';

// set admin file extension type for your templates: '.tpl', '.html', '.htm', '.php' or whatever
$admin_tplext = '.html';

// set include paths to ../includes and ../admin/inc
ini_set('include_path', '.:../:./includes:../' . $admin_dir . '/inc');

function dbconnect($dbname) {
  global $dbconnect, $dbhost, $dbusername, $dbuserpass;
  
  if (!$dbconnect) {
    $dbconnect = mysql_connect($dbhost, $dbusername, $dbuserpass);
  }
  if (!$dbconnect) {
    return 0;
  }
  elseif (!mysql_select_db($dbname)) {
    return 0;
  }
  else {
    return $dbconnect;
  }
}

// connect to the database
dbconnect($dbname);

$phpversion = phpversion();

// PayPal urls
$paypal_urls['sandbox'] = 'https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=';
$paypal_urls['live'] = 'https://www.paypal.com/webscr&cmd=_express-checkout&token=';

?>
