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
require_once('../includes/config_inc.php');
require_once('inc/validate_inc.php');

// activate the login script
require_once('inc/login_inc.php');

// look for a valid login session
if (isset($_SESSION['administrator']) && $_SESSION['authorized'] == 'admin') {
  // check to see if someone is logging out
  if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    admin_logout();
  }
  // user is successfully logged in
  $message = 'Logged in as: ' . $_SESSION['administrator'];
  $info = 'Help Section.';
  $page_title = 'Help - eXhibition';

  include_once($admin_tpl . '/header' . $admin_tplext);
  include_once($admin_tpl . '/menu' . $admin_tplext);

?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#dddddd">
    <tr>
        <th colspan="2" align="center" valign="middle" height="35" bgcolor="#777bb4">
            <span class="list_heading">Help</span>
        </th>
    </tr>
    <tr>
        <td colspan="2" align="center" valign="middle" height="30" bgcolor="#a3a6d0">
            <b><a name="topics">Topics:</a></b>
        </td>
    </tr>
    <tr>
        <td width="50%" valign="middle" height="200" bgcolor="#fefefe">
        <ul>
            <li><a href="<?php echo $_SERVER['PHP_SELF'] ?>#exhibition">eXhibition</a></li>
            <li><a href="<?php echo $_SERVER['PHP_SELF'] ?>#simple_config">Simple configuration for non-commercial display of artwork</a></li>
            <li><a href="<?php echo $_SERVER['PHP_SELF'] ?>#full_config">Full configuration for commercial display of artwork</a></li>
            <li><a href="<?php echo $_SERVER['PHP_SELF'] ?>#galleries">Galleries</a></li>
            <li><a href="<?php echo $_SERVER['PHP_SELF'] ?>#add_gallery">Add a gallery</a></li>
            <li><a href="<?php echo $_SERVER['PHP_SELF'] ?>#edit_gallery">Make changes to a gallery</a></li>
            <li><a href="<?php echo $_SERVER['PHP_SELF'] ?>#delete_gallery">Delete a gallery</a></li>
            <li><a href="<?php echo $_SERVER['PHP_SELF'] ?>#gallery_icons">Gallery Icons</a></li>
            <li><a href="<?php echo $_SERVER['PHP_SELF'] ?>#artworks">Artworks</a></li>
            <li><a href="<?php echo $_SERVER['PHP_SELF'] ?>#add_artwork">Add an artwork</a></li>
            <li><a href="<?php echo $_SERVER['PHP_SELF'] ?>#edit_artwork">Make changes to an artwork</a></li>
            <li><a href="<?php echo $_SERVER['PHP_SELF'] ?>#delete_artwork">Delete an artwork</a></li>
        </ul>
        </td>
        <td width="50%" valign="middle" height="200" bgcolor="#fefefe">
        <ul>
            <li><a href="<?php echo $_SERVER['PHP_SELF'] ?>#categories">Categories</a></li>
            <li><a href="<?php echo $_SERVER['PHP_SELF'] ?>#add_category">Add a category</a></li>
            <li><a href="<?php echo $_SERVER['PHP_SELF'] ?>#edit_category">Make changes to a category</a></li>
            <li><a href="<?php echo $_SERVER['PHP_SELF'] ?>#delete_category">Delete a category</a></li>
            <li><a href="<?php echo $_SERVER['PHP_SELF'] ?>#pages">Pages</a></li>
            <li><a href="<?php echo $_SERVER['PHP_SELF'] ?>#add_page">Add a page</a></li>
            <li><a href="<?php echo $_SERVER['PHP_SELF'] ?>#edit_page">Make changes to a page</a></li>
            <li><a href="<?php echo $_SERVER['PHP_SELF'] ?>#delete_page">Delete a page</a></li>
            <li><a href="<?php echo $_SERVER['PHP_SELF'] ?>#comments">Comments</a></li>
            <li><a href="<?php echo $_SERVER['PHP_SELF'] ?>#rss_feeds">RSS Feeds</a></li>
            <li><a href="<?php echo $_SERVER['PHP_SELF'] ?>#templates">Templates</a></li>
            <li><a href="<?php echo $_SERVER['PHP_SELF'] ?>#paypal">Accepting PayPal</a></li>
            <li><a href="<?php echo $_SERVER['PHP_SELF'] ?>#bugs">Report Bugs</a></li>
            <li><a href="<?php echo $_SERVER['PHP_SELF'] ?>#contribute">Contribute Code</a></li>
        </ul>
        </td>
    </tr>
    <tr>
        <td colspan="2" bgcolor="#ececec">
            <p><a name="exhibition"><h4>eXhibition</h4></a></p>
            <p>eXhibition is a simple open source content management system tailored to the online publishing needs of artists, dealers and small art galleries. This system consists of a front end, which is visible to the public and an administrative back end which is private.</p>
            <p><a href="<?php echo $_SERVER['PHP_SELF'] ?>#topics">Back</a></p>

            <p><a name="simple_config"><h4>Simple configuration for non-commercial display of artwork</h4></a></p>
            <p>eXhibition can be configured to display artwork with only images and a minimum of informational fields. To choose which fields to display on your site, open the file config_inc.php in your favorite text editor and reset the variables.</p>
            <p><a href="<?php echo $_SERVER['PHP_SELF'] ?>#topics">Back</a></p>

            <p><a name="full_config"><h4>Full configuration for commercial display of artwork</h4></a></p>
            <p>To display artwork with ecommerce enabled and all available informational fields,</p>
            <p><a href="<?php echo $_SERVER['PHP_SELF'] ?>#topics">Back</a></p>
        <hr>
            <p><a name="galleries"><h4>Galleries</h4></a></p>
            <p>Use galleries to organize artworks.</p>
            <p><a href="<?php echo $_SERVER['PHP_SELF'] ?>#topics">Back</a></p>

            <p><a name="add_gallery"><h4>Add a gallery</h4></a></p>
            <p>To create a new gallery, select "Galleries" fom the administration menu (upper left), click the ADD button, fill in the information and click the SAVE button.</p>
            <p><a href="<?php echo $_SERVER['PHP_SELF'] ?>#topics">Back</a></p>

            <p><a name="edit_gallery"><h4>Make changes to a gallery</h4></a>To edit a gallery, click "Galleries" on the administration menu (upper left), select it by clicking the check box, click the edit button, change the information and click the SAVE button.</p>
            <p><a href="<?php echo $_SERVER['PHP_SELF'] ?>#topics">Back</a></p>

            <P><a name="delete_gallery"><h4>Delete a gallery</h4></a>To delete a gallery, click "Galleries" on the administration menu (upper left), select it by clicking the check box, click the delete button, and click the confirm delete button.</p>
            <p><a href="<?php echo $_SERVER['PHP_SELF'] ?>#topics">Back</a></p>

            <p><a name="gallery_icons"><h4>Gallery Icons</h4></a></p>
            <p>Gallery icons allow you to represent some or all of your galleries with an image. You can upload a gallery icon when you create a gallery, or else you can add an icon later by selecting the gallery and using the edit button. Remove or replace an icon by selecting a gallery and using the edit button.</p>
            <p>If enabled, top level gallery icons will be displayed by default on the home page under the splash image. To disable gallery icons on the home page, use a text editor to open config_inc.php and set the gallery_icon_display variable on line 67 to false.</p>
            <p><a href="<?php echo $_SERVER['PHP_SELF'] ?>#topics">Back</a></p>
        <hr>
            <p><a name="artworks"><h4>Artworks</h4></a></p>
            <p>Artwork displays pictures and text for a work of art.</p>
            <p><a href="<?php echo $_SERVER['PHP_SELF'] ?>#topics">Back</a></p>

            <p><a name="add_artwork"><h4>Add an artwork</h4></a></p>
            <p>To add a work of art, click "Artwork" on the administration menu (upper left), click the ADD button, fill in the information and click the SAVE button.</p>
            <p><a href="<?php echo $_SERVER['PHP_SELF'] ?>#topics">Back</a></p>

            <p><a name="edit_artwork"><h4>Make changes to an artwork</h4></a></p>
            <p>To make changes to an artwork, click "Artwork" on the administration menu (upper left), select it by clicking the check box, click the edit button, change the information and click the SAVE button.</p>
            <p><a href="<?php echo $_SERVER['PHP_SELF'] ?>#topics">Back</a></p>

            <p><a name="delete_artwork"><h4>Delete an artwork</h4></a></p>
            <p>To delete an artwork, click "Artwork" on the administration menu (upper left), select it by clicking the check box, click the delete button, and click the confirm delete button.</p>
            <p><a href="<?php echo $_SERVER['PHP_SELF'] ?>#topics">Back</a></p>
        <hr>
            <p><a name="categories"><h4>Categories</h4></a></p>
            <p>Use categories to organize Pages or Galleries.</p>
            <p><a href="<?php echo $_SERVER['PHP_SELF'] ?>#topics">Back</a></p>

            <p><a name="add_category"><h4>Add a category</h4></a></p>
            <p>To add a category, click "Categories" on the administration menu (upper left), click the ADD button, fill in the information and click the SAVE button.</p>
            <p><a href="<?php echo $_SERVER['PHP_SELF'] ?>#topics">Back</a></p>

            <p><a name="edit_category"><h4>Make changes to a category</h4></a></p>
            <p>To make changes to a category, click "Categories" on the administration menu (upper left), select it by clicking the check box, click the edit button, change the information and click the SAVE button.</p>
            <p><a href="<?php echo $_SERVER['PHP_SELF'] ?>#topics">Back</a></p>

            <p><a name="delete_category"><h4>Delete a category</h4></a></p>
            <p>To delete a category, click "Categories" on the administration menu (upper left), select it by clicking the check box, click the delete button, and click the confirm delete button.</p>
            <p><a href="<?php echo $_SERVER['PHP_SELF'] ?>#topics">Back</a></p>
        <hr>
            <p><a name="pages"><h4>Pages</h4></a></p>
            <p>You can create a custom page on any subject.</p>
            <p><a href="<?php echo $_SERVER['PHP_SELF'] ?>#topics">Back</a></p>

            <p><a name="add_page"><h4>Add a page</h4></a></p>
            <p>To create a page, click "Pages" on the administration menu (upper left), click the ADD button, fill in the information and click the SAVE button.</p>
            <p><a href="<?php echo $_SERVER['PHP_SELF'] ?>#topics">Back</a></p>

            <p><a name="edit_page"><h4>Make changes to a page</h4></a></p>
            <p>To make changes to a page, click "Pages" on the administration menu (upper left), select it by clicking the check box, click the edit button, change the information and click the SAVE button.</p>
            <p><a href="<?php echo $_SERVER['PHP_SELF'] ?>#topics">Back</a></p>

            <p><a name="delete_page"><h4>Delete a page</h4></a></p>
            <p>To delete a page, click "Pages" on the administration menu (upper left), select it by clicking the check box, click the delete button, and click the confirm delete button.</p>
            <p><a href="<?php echo $_SERVER['PHP_SELF'] ?>#topics">Back</a></p>
        <hr>
            <p><a name="comments"><h4>Comments</h4></a></p>
            <p>Review and reply to comments by clicking "Comments" on the administration menu. You can delete comments too.</p>
            <p><a href="<?php echo $_SERVER['PHP_SELF'] ?>#topics">Back</a></p>

            <p><a name="rss_feeds"><h4>RSS Feeds</h4></a></p>
            <p>Enable RSS Feed for any gallery, artwork or page by choosing the rss enable option when you create, or later on you can edit the item to enable rss. The feed handler will automatically choose the ten most recent rss enabled items to display for your feed. eXhibition uses RSS version 2.0.</p>
            <p><a href="<?php echo $_SERVER['PHP_SELF'] ?>#topics">Back</a></p>

            <p><a name="templates"><h4>Templates</h4></a></p>
            <p>Use templates to design your own look and feel for the site.</p>
            <p><a href="<?php echo $_SERVER['PHP_SELF'] ?>#topics">Back</a></p>

            <p><a name="paypal"><h4>Accepting PayPal</h4></a></p>
            <p>Enable PayPal payments in "Settings" on the administration menu, adding your API username, password and signature. You'll have to log into your PayPal account to retrieve that. You can add multiple accounts, and choose which one to use for each artwork you have for sale. Test payments by using the sandbox account, which is preloaded.</p>
            <p><a href="<?php echo $_SERVER['PHP_SELF'] ?>#topics">Back</a></p>

            <p><a name="bugs"><h4>Report Bugs</h4></a></p>
            <p>Report bugs by email. Contact: sketchdude@gmail.com</p>
            <p><a href="<?php echo $_SERVER['PHP_SELF'] ?>#topics">Back</a></p>

            <p><a name="contribute"><h4>Contribute Code</h4></a></p>
            <p>Send bug fixes and hacks by email to sketchdude@gmail.com.</p>
            <p><a href="<?php echo $_SERVER['PHP_SELF'] ?>#topics">Back</a></p>
        </td>
    </tr>
</table>

<?php

  include_once($admin_tpl . '/footer' . $admin_tplext);
}
else {
  // user needs to login
  if (!empty($_POST['action']) && $_POST['action'] == 'login') {
    admin_login(gp_filter($_POST['username']), gp_filter($_POST['password']));
  }

  $message    = 'Please Log In';
  $info       = 'Administration Login - eXhibition';
  $page_title = 'Administration Login - eXhibition';
  $text       = '<p align="center">Welcome to the eXhibition administrative back-end.</p><p align="center">This area is password protected, so you will need to login to access this part of your site.</p><p>&nbsp;</p>';
  // show the login form
  include_once($admin_tpl . '/header_dead' . $admin_tplext);
  include_once($admin_tpl . '/login' . $admin_tplext);
  include_once($admin_tpl . '/footer' . $admin_tplext);
  exit();
}

?>