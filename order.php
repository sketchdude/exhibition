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
require_once('includes/navigate_inc.php');
require_once('includes/order_inc.php');
require_once('includes/time_inc.php');
require_once('includes/validate_inc.php');
require_once('includes/image_inc.php');

// get action from $_POST if needed
if (!empty($_POST['action']) && $_POST['action'] == 'contact') {
  $action = 'contact';
}
elseif (!empty($_POST['action']) && $_POST['action'] == 'options') {
  $action = 'options';
}
elseif (!empty($_POST['action']) && $_POST['action'] == 'order') {
  $action = 'order';
}
elseif (!empty($_POST['action']) && $_POST['action'] == 'confirm') {
  $action = 'confirm';
}
else {
  $action = null;
}

if (isset($site['ecommerce']) && $site['ecommerce'] == 'enable') {
  // order contents are stored as a comma separated list in a session named order
  $order = $_SESSION['order'];

  $show_cart_link = enable_ecommerce($site);
  $sidebar = print_sidebar();
  $galleries = print_gallery();
  $rss_icon = show_rss_icon($site);

  if (isset($action) && $action == 'contact') {
    // has the quit button been clicked?
    if (isset($_POST['quit']) && $_POST['quit'] == 'Cancel') {
      // rerun this script without any action
      unset($action);
      header('location: order.php');
      exit();
    }
    else {
      // this section retrieves contact data from the customer
      if (isset($_POST['contact_action']) && $_POST['contact_action'] == 'set_contact') {
        // write the contact data to the database
        $errors = array();
        $date_entered = get_datetime();

        // validate mysql fields coming from post
        if (!empty($_POST['first_name'])) {
          $first_name = gp_filter($_POST['first_name']);
          $first_name = clean_string($first_name, 35);
          length('first_name', $first_name, 1, 35);
        }
        else {
          // first_name is a required field
          $errors['first_name']['required'] = '<p class="errors">First Name is a required field.</p>';
        }

        if (!empty($_POST['last_name'])) {
          $last_name = gp_filter($_POST['last_name']);
          $last_name = clean_string($last_name, 35);
          length('last_name', $last_name, 1, 35);
        }
        else {
          // last_name is a required field
          $errors['last_name']['required'] = '<p class="errors">Last Name is a required field.</p>';
        }

        if (!empty($_POST['address1'])) {
          $address1 = gp_filter($_POST['address1']);
          $address1 = clean_string($address1, 65);
          length('address1', $address1, 1, 65);
        }
        else {
          // address1 is a required field
          $errors['address1']['required'] = '<p class="errors">Address 1 is a required field.</p>';
        }

        if (!empty($_POST['address2'])) {
          // optional field
          $address2 = gp_filter($_POST['address2']);
          $address2 = clean_string($address2, 65);
        }

        if (!empty($_POST['city'])) {
          $city = gp_filter($_POST['city']);
          $city = clean_string($city, 40);
          length('city', $city, 1, 40);
        }
        else {
          // city is a required field
          $errors['city']['required'] = '<p class="errors">City is a required field.</p>';
        }

        $state = gp_filter($_POST['state']);
        $state = clean_string($state, 2);

        if (!empty($_POST['post_code'])) {
          $post_code = gp_filter($_POST['post_code']);
          $post_code = clean_string($post_code, 25);
          length('post_code', $post_code, 1, 25);
        }
        else {
          // post_code is a required field
          $errors['post_code']['required'] = '<p class="errors">Postal Code is a required field.</p>';
        }

        $country = gp_filter($_POST['country']);
        $country = clean_string($country, 3);

        if (!empty($_POST['phone'])) {
          // optional field
          $phone = gp_filter($_POST['phone']);
          $phone = clean_string($phone, 16);
        }

        if (!empty($_POST['email'])) {
          $email = gp_filter($_POST['email']);
          $email = clean_string($email, 55);
          format_email($email);
        }
        else {
          // email is a required field
          $errors['email']['required'] = '<p class="errors">E Mail is a required field.</p>';
        }

        if (!empty($_POST['ship_address'])) {
          $ship_address = gp_filter($_POST['ship_address']);
          $ship_address = clean_string($ship_address, 65);
        }
        else {
          // ship_address is the same as address1 if left empty
          $ship_address = $address1;
          // if the address is the same, assume the state and country are also the same
          $ship_state = $state;
          $ship_country = $country;
        }

        if (!empty($_POST['ship_address2'])) {
          $ship_address2 = gp_filter($_POST['ship_address2']);
          $ship_address2 = clean_string($ship_address2, 65);
        }
        else {
          // ship_address2 is the same as address2 if left empty
          $ship_address2 = $address2;
        }

        if (!empty($_POST['ship_city'])) {
          $ship_city = gp_filter($_POST['ship_city']);
          $ship_city = clean_string($ship_city, 40);
        }
        else {
          // ship_city is the same as city if left empty
          $ship_city = $city;
        }

        // deciding whether to get country and state from $_POST
        if ($city != $ship_city) {
          // get state and country from $_POST
          $ship_state = gp_filter($_POST['ship_state']);
          $ship_country = gp_filter($_POST['ship_country']);
          foreach($states as $key => $value) {
            if ($ship_state == $value) {
              $ship_state = $value;
            }
            else {
              $ship_state = '01';
            }
          }
          foreach($countries as $key => $value) {
            if ($ship_country == $value) {
              $ship_country = $value;
            }
            else {
              $ship_country = '225';
            }
          }
        }

        if (!empty($_POST['ship_post_code'])) {
          $ship_post_code = gp_filter($_POST['ship_post_code']);
          $ship_post_code = clean_string($ship_post_code, 25);
        }
        else {
          // ship_post_code is the same as post_code if left empty
          $ship_post_code = $post_code;
        }

        if (!empty($_POST['bill_address'])) {
          $bill_address = gp_filter($_POST['bill_address']);
          $bill_address = clean_string($bill_address, 65);
        }
        else {
          // bill_address is the same as address1 if left empty
          $bill_address = $address1;
          // if the address is the same, assume the state and country are also the same
          $bill_state = $state;
          $bill_country = $country;
        }

        if (!empty($_POST['bill_address2'])) {
          $bill_address2 = gp_filter($_POST['bill_address2']);
          $bill_address2 = clean_string($bill_address2, 65);
        }
        else {
          // bill_address2 is the same as address2 if left empty
          $bill_address2 = $address2;
        }

        if (!empty($_POST['bill_city'])) {
          $bill_city = gp_filter($_POST['bill_city']);
          $bill_city = clean_string($bill_city, 40);
        }
        else {
          // bill_city is the same as city if left empty
          $bill_city = $city;
        }

        // deciding whether to get country and state from $_POST
        if ($city != $bill_city) {
          // get state and country from $_POST
          $bill_state = gp_filter($_POST['bill_state']);
          $bill_country = gp_filter($_POST['bill_country']);
          foreach($states as $key => $value) {
            if ($bill_state == $value) {
              $bill_state = $value;
            }
            else {
              $bill_state = '01';
            }
          }
          foreach($countries as $key => $value) {
            if ($bill_country == $value) {
              $bill_country = $value;
            }
            else {
              $bill_country = '225';
            }
          }
        }

        if (!empty($_POST['bill_post_code'])) {
          $bill_post_code = gp_filter($_POST['bill_post_code']);
          $bill_post_code = clean_string($bill_post_code, 25);
        }
        else {
          // bill_post_code is the same as post_code if left empty
          $bill_post_code = $post_code;
        }

        if (empty($errors)) {
          $error_message = 'No errors';
          // update mysql
          $query = "INSERT INTO
                      customer (first_name,
                                last_name,
                                address1,
                                address2,
                                city,
                                state,
                                post_code,
                                country,
                                phone,
                                email,
                                bill_address,
                                bill_address2,
                                bill_city,
                                bill_state,
                                bill_post_code,
                                bill_country,
                                ship_address,
                                ship_address2,
                                ship_city,
                                ship_state,
                                ship_post_code,
                                ship_country,
                                date_entered)
                    VALUES ('$first_name',
                            '$last_name',
                            '$address1',
                            '$address2',
                            '$city',
                            '$state',
                            '$post_code',
                            '$country',
                            '$phone',
                            '$email',
                            '$bill_address',
                            '$bill_address2',
                            '$bill_city',
                            '$bill_state',
                            '$bill_post_code',
                            '$bill_country',
                            '$ship_address',
                            '$ship_address2',
                            '$ship_city',
                            '$ship_state',
                            '$ship_post_code',
                            '$ship_country',
                            '$date_entered')";
          //$result = mysql_query($query) or die(mysql_error());
          // upon successful update, show a success page
          $contents = '                        <table align="center" width="100%" cellpadding="6" cellspacing="0" border="0">' . "\n" .
                      '                            <tr>' . "\n" .
                      '                                <td align="center" bgcolor="#ffffff" height="35">' . "\n" .
                      '                                    <p>Client Details Entered Successfully</p>' . "\n" .
                      '                                </td>' . "\n" .
                      '                            </tr>' . "\n" .
                      '                            <tr>' . "\n" .
                      '                                <td align="center" bgcolor="#ffffff" height="35">' . "\n" .
                      '                                    <p>' . $error_message . '</p>' . "\n" .
                      '                                </td>' . "\n" .
                      '                            </tr>' . "\n" .
                      '                            <tr>' . "\n" .
                      '                                <td align="right" bgcolor="#d6deff" height="75">' . "\n" .
                      '                                    <input type="submit" id="viewinput" name="check_out" value="Continue">' . "\n" .
                      '                                </td>' . "\n" .
                      '                            </tr>' . "\n" .
                      '                            <tr>' . "\n" .
                      '                                <td align="right" bgcolor="#d6deff" height="75">' . "\n" .
                      '                                    <p>Or</p>' . "\n" .
                      '                                    <input type="submit" id="deleteinput" name="quit" value="Cancel">' . "\n" .
                      '                                    <input type="hidden" name="action" value="options">' . "\n" .
                      '                                </td>' . "\n" .
                      '                            </tr>' . "\n" .
                      '                        </table>' . "\n";
          $content = shopping_cart($site, $contents);
          include_once('templates/' . $template_folder . '/header' . $template_ext);
          include_once('templates/' . $template_folder . '/body' . $template_ext);
          include_once('templates/' . $template_folder . '/footer' . $template_ext);
          exit();
        }
        else {
          // Errors exist: display input form with errors
          $eaddress_form = '            <table border="0" width="100%" cellpadding="0" cellspacing="0">' . "\n" .
                           '                <tr>' . "\n" .
                           '                    <th colspan="2" height="30">' . "\n" .
                           '                        <p align="center">Client Details:</p>' . "\n" .
                           '                    </th>' . "\n" .
                           '                </tr>' . "\n" .
                           '                <tr>' . "\n" .
                           '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        First Name: &nbsp;' . "\n" .
                           '                    </td>' . "\n" .
                           '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n";
          if (!empty($errors['first_name']['length'])) {
            $eaddress_form .= '                        ' . $errors['first_name']['length'] . "\n";
          }
          if (!empty($errors['first_name']['required'])) {
            $eaddress_form .= '                        ' . $errors['first_name']['required'] . "\n";
          }
          $eaddress_form .= '                        <input type="text" name="first_name" value="' . $first_name . '" size="35" maxlength="35">' . "\n" .
                           '                    </td>' . "\n" .
                           '                </tr>' . "\n" .
                           '                <tr>' . "\n" .
                           '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        Last Name: &nbsp;' . "\n" .
                           '                    </td>' . "\n" .
                           '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n";
          if (!empty($errors['last_name']['length'])) {
            $eaddress_form .= '                        ' . $errors['last_name']['length'] . "\n";
          }
          if (!empty($errors['last_name']['required'])) {
            $eaddress_form .= '                        ' . $errors['last_name']['required'] . "\n";
          }
          $eaddress_form .= '                        <input type="text" name="last_name" value="' . $last_name . '" size="35" maxlength="35">' . "\n" .
                           '                    </td>' . "\n" .
                           '                </tr>' . "\n" .
                           '                <tr>' . "\n" .
                           '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        Address 1: &nbsp;' . "\n" .
                           '                    </td>' . "\n" .
                           '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n";
          if (!empty($errors['address1']['length'])) {
            $eaddress_form .= '                        ' . $errors['address1']['length'] . "\n";
          }
          if (!empty($errors['address1']['required'])) {
            $eaddress_form .= '                        ' . $errors['address1']['required'] . "\n";
          }
          $eaddress_form .= '                        <input type="text" name="address1" value="' . $address1 . '" size="65" maxlength="65">' . "\n" .
                           '                    </td>' . "\n" .
                           '                </tr>' . "\n" .
                           '                <tr>' . "\n" .
                           '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        Address 2: &nbsp;' . "\n" .
                           '                    </td>' . "\n" .
                           '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        <input type="text" name="address2" value="' . $address2 . '" size="65" maxlength="65">' . "\n" .
                           '                    </td>' . "\n" .
                           '                </tr>' . "\n" .
                           '                <tr>' . "\n" .
                           '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        City: &nbsp;' . "\n" .
                           '                    </td>' . "\n" .
                           '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n";
          if (!empty($errors['city']['length'])) {
            $eaddress_form .= '                        ' . $errors['city']['length'] . "\n";
          }
          if (!empty($errors['city']['required'])) {
            $eaddress_form .= '                        ' . $errors['city']['required'] . "\n";
          }
          $eaddress_form .= '                        <input type="text" name="city" value="" size="40" maxlength="40">' . "\n" .
                           '                    </td>' . "\n" .
                           '                </tr>' . "\n" .
                           '                <tr>' . "\n" .
                           '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        State: &nbsp;' . "\n" .
                           '                    </td>' . "\n" .
                           '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        ' . state_options('state', $state) . "\n" .
                           '                    </td>' . "\n" .
                           '                </tr>' . "\n" .
                           '                <tr>' . "\n" .
                           '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        Postal Code: &nbsp;' . "\n" .
                           '                    </td>' . "\n" .
                           '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n";
          if (!empty($errors['post_code']['length'])) {
            $eaddress_form .= '                        ' . $errors['post_code']['length'] . "\n";
          }
          if (!empty($errors['post_code']['required'])) {
            $eaddress_form .= '                        ' . $errors['post_code']['required'] . "\n";
          }
          $eaddress_form .= '                        <input type="text" name="post_code" value="" size="25" maxlength="25">' . "\n" .
                           '                    </td>' . "\n" .
                           '                </tr>' . "\n" .
                           '                <tr>' . "\n" .
                           '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        Country: &nbsp;' . "\n" .
                           '                    </td>' . "\n" .
                           '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        ' . country_options() . "\n" .
                           '                    </td>' . "\n" .
                           '                </tr>' . "\n" .
                           '                <tr>' . "\n" .
                           '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        Phone: &nbsp;' . "\n" .
                           '                    </td>' . "\n" .
                           '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        <input type="text" name="phone" value="' . $phone . '" size="16" maxlength="16">' . "\n" .
                           '                    </td>' . "\n" .
                           '                </tr>' . "\n" .
                           '                <tr>' . "\n" .
                           '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        E-mail: &nbsp;' . "\n" .
                           '                    </td>' . "\n" .
                           '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n";
          if (!empty($errors['email'])) {
            $eaddress_form .= '                        ' . $errors['email'] . "\n";
          }
          if (!empty($errors['email']['required'])) {
            $eaddress_form .= '                        ' . $errors['email']['required'] . "\n";
          }
          $eaddress_form .= '                        <input type="text" name="email" value="" size="55" maxlength="55">' . "\n" .
                           '                    </td>' . "\n" .
                           '                </tr>' . "\n" .
                           '                <tr>' . "\n" .
                           '                    <th colspan="2" height="30">' . "\n" .
                           '                        <p align="center">Shipping Address:</p>' . "\n" .
                           '                    </th>' . "\n" .
                           '                </tr>' . "\n" .
                           '                <tr>' . "\n" .
                           '                    <td colspan="2" height="30" bgcolor="#ffffff">' . "\n" .
                           '                        <p align="center">Only fill out this information if the shipping address is different from the one above. If not, leave this section blank.</p>' . "\n" .
                           '                    </td>' . "\n" .
                           '                </tr>' . "\n" .
                           '                <tr>' . "\n" .
                           '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        Shipping Address 1: &nbsp;' . "\n" .
                           '                    </td>' . "\n" .
                           '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        <input type="text" name="ship_address" value="' . $ship_address . '" size="65" maxlength="65">' . "\n" .
                           '                    </td>' . "\n" .
                           '                </tr>' . "\n" .
                           '                <tr>' . "\n" .
                           '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        Shipping Address 2: &nbsp;' . "\n" .
                           '                    </td>' . "\n" .
                           '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        <input type="text" name="ship_address2" value="' . $ship_address2 . '" size="65" maxlength="65">' . "\n" .
                           '                    </td>' . "\n" .
                           '                </tr>' . "\n" .
                           '                <tr>' . "\n" .
                           '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        Shipping City: &nbsp;' . "\n" .
                           '                    </td>' . "\n" .
                           '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        <input type="text" name="ship_city" value="' . $ship_city . '" size="40" maxlength="40">' . "\n" .
                           '                    </td>' . "\n" .
                           '                </tr>' . "\n" .
                           '                <tr>' . "\n" .
                           '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        Shipping State: &nbsp;' . "\n" .
                           '                    </td>' . "\n" .
                           '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        ' . state_options('ship_state', $ship_state) . "\n" .
                           '                    </td>' . "\n" .
                           '                </tr>' . "\n" .
                           '                <tr>' . "\n" .
                           '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        Shipping Postal Code: &nbsp;' . "\n" .
                           '                    </td>' . "\n" .
                           '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        <input type="text" name="ship_post_code" value="' . $ship_post_code . '" size="25" maxlength="25">' . "\n" .
                           '                    </td>' . "\n" .
                           '                </tr>' . "\n" .
                           '                <tr>' . "\n" .
                           '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        Shipping Country: &nbsp;' . "\n" .
                           '                    </td>' . "\n" .
                           '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        ' . country_options('ship_country') . "\n" .
                           '                    </td>' . "\n" .
                           '                </tr>' . "\n" .
                           '                <tr>' . "\n" .
                           '                    <th colspan="2" height="30">' . "\n" .
                           '                        <p align="center">Billing Address:</p>' . "\n" .
                           '                    </th>' . "\n" .
                           '                </tr>' . "\n" .
                           '                <tr>' . "\n" .
                           '                    <td colspan="2" height="30" bgcolor="#ffffff">' . "\n" .
                           '                        <p>The billing address should be the address that appears on your credit or bank card. Only fill out this information if the billing address is different from the first address entered. If not, leave this section blank.</p>' . "\n" .
                           '                    </td>' . "\n" .
                           '                </tr>' . "\n" .
                           '                <tr>' . "\n" .
                           '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        Billing Address 1: &nbsp;' . "\n" .
                           '                    </td>' . "\n" .
                           '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        <input type="text" name="bill_address" value="' . $bill_address . '" size="65" maxlength="65">' . "\n" .
                           '                    </td>' . "\n" .
                           '                </tr>' . "\n" .
                           '                <tr>' . "\n" .
                           '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        Billing Address 2: &nbsp;' . "\n" .
                           '                    </td>' . "\n" .
                           '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        <input type="text" name="bill_address2" value="' . $bill_address2 . '" size="65" maxlength="65">' . "\n" .
                           '                    </td>' . "\n" .
                           '                </tr>' . "\n" .
                           '                <tr>' . "\n" .
                           '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        Billing City: &nbsp;' . "\n" .
                           '                    </td>' . "\n" .
                           '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        <input type="text" name="bill_city" value="' . $bill_city . '" size="40" maxlength="40">' . "\n" .
                           '                    </td>' . "\n" .
                           '                </tr>' . "\n" .
                           '                <tr>' . "\n" .
                           '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        Billing State: &nbsp;' . "\n" .
                           '                    </td>' . "\n" .
                           '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        ' . state_options('bill_state', $bill_state) . "\n" .
                           '                    </td>' . "\n" .
                           '                </tr>' . "\n" .
                           '                <tr>' . "\n" .
                           '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        Billing Postal Code: &nbsp;' . "\n" .
                           '                    </td>' . "\n" .
                           '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        <input type="text" name="bill_post_code" value="' . $bill_post_code . '" size="25" maxlength="25">' . "\n" .
                           '                    </td>' . "\n" .
                           '                </tr>' . "\n" .
                           '                <tr>' . "\n" .
                           '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        Billing Country: &nbsp;' . "\n" .
                           '                    </td>' . "\n" .
                           '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                           '                        ' . country_options('bill_country') . "\n" .
                           '                    </td>' . "\n" .
                           '                </tr>' . "\n" .
                           '                <tr>' . "\n" .
                           '                    <td colspan="2" align="center" bgcolor="#ffffff">' . "\n" .
                           '                        <table width="100%" cellpadding="0" cellspacing="0" border="0">' . "\n" .
                           '                            <tr>' . "\n" .
                           '                                <td align="center" valign="middle" width="50%" height="75">' . "\n" .
                           '                                    <p id="border"><input type="submit" id="addinput" name="contact_action" value="Add"></p>' . "\n" .
                           '                                </td>' . "\n" .
                           '                                <td align="center" valign="middle" width="50%" height="75">' . "\n" .
                           '                                    <p id="border"><input type="submit" id="deleteinput" name="quit" value="Cancel"></p>' . "\n" .
                           '                                </td>' . "\n" .
                           '                            </tr>' . "\n" .
                           '                            <tr>' . "\n" .
                           '                                <td align="center" valign="middle" width="50%" height="25">' . "\n" .
                           '                                    Add Shipping details' . "\n" .
                           '                                </td>' . "\n" .
                           '                                <td align="center" valign="middle" width="50%" height="25">' . "\n" .
                           '                                    Cancel' . "\n" .
                           '                                </td>' . "\n" .
                           '                            </tr>' . "\n" .
                           '                        </table>' . "\n" .
                           '                        <input type="hidden" name="action" value="contact">' . "\n" .
                           '                        <input type="hidden" name="contact_action" value="set_contact">' . "\n" .
                           '                    </td>' . "\n" .
                           '                </tr>' . "\n" .
                           '            </table>' . "\n";
          $content = shopping_cart($site, $eaddress_form);
          include_once('templates/' . $template_folder . '/header' . $template_ext);
          include_once('templates/' . $template_folder . '/body' . $template_ext);
          include_once('templates/' . $template_folder . '/footer' . $template_ext);
          unset($errors);
          exit();
        }
      }
      else {
        // display a form for entering client shipping data
        $contents = $address_form;
        $content = shopping_cart($site, $contents);
        include_once('templates/' . $template_folder . '/header' . $template_ext);
        include_once('templates/' . $template_folder . '/body' . $template_ext);
        include_once('templates/' . $template_folder . '/footer' . $template_ext);
        exit();
      }
    }
  }
  elseif (isset($action) && $action == 'options') {
    // has the quit button been clicked?
    if (isset($_POST['quit']) && $_POST['quit'] == 'Cancel') {
      // rerun this script without any action
      unset($action);
      header('location: order.php');
      exit();
    }
    else {
      // display the specified payment option(s)
      if (isset($site['paypal_payment_option']) && $site['paypal_payment_option'] == 'enable') {
        $paypal_po = '                                        <tr>' . "\n" .
                     '                                            <td>' . "\n" .
                     '                                                <input type="radio" name="pay_option" value="paypal"> &nbsp;PayPal Express Checkout' . "\n" .
                     '                                            </td>' . "\n" .
                     '                                        </tr>' . "\n";
      }
      else {
        $paypal_po = null;
      }

      if (isset($site['google_payment_option']) && $site['google_payment_option'] == 'enable') {
        $google_po = '                                        <tr>' . "\n" .
                     '                                            <td>' . "\n" .
                     '                                                <input type="radio" name="pay_option" value="google"> &nbsp;Google Payments' . "\n" .
                     '                                            </td>' . "\n" .
                     '                                        </tr>' . "\n";
      }
      else {
        $google_po = null;
      }

      if (isset($site['mail_payment_option']) && $site['mail_payment_option'] == 'enable') {
        $mail_po = '                                        <tr>' . "\n" .
                   '                                            <td>' . "\n" .
                   '                                                <input type="radio" name="pay_option" value="mail"> &nbsp;Mail Check or Money Order' . "\n" .
                   '                                            </td>' . "\n" .
                   '                                        </tr>' . "\n";
      }
      else {
        $mail_po = null;
      }

      $po = $paypal_po . $google_po . $mail_po;

      $contents = '                        <table align="center" width="100%" cellpadding="6" cellspacing="0" border="0">' . "\n" .
                  '                            <tr>' . "\n" .
                  '                                <td align="center" bgcolor="#ffffff" height="35">' . "\n" .
                  '                                    <p>Choose a payment option and continue</p>' . "\n" .
                  '                                </td>' . "\n" .
                  '                            </tr>' . "\n" .
                  '                            <tr>' . "\n" .
                  '                                <td align="center" bgcolor="#ffffff">' . "\n" .
                  '                                    <table border="1">' . "\n";

      $contents .= $po . '                                    </table>' . "\n" .
                         '                                </td>' . "\n" .
                         '                            </tr>' . "\n" .
                         '                            <tr>' . "\n" .
                         '                                <td align="right" bgcolor="#d6deff" height="75">' . "\n" .
                         '                                    <input type="submit" id="viewinput" name="check_out" value="Continue">' . "\n" .
                         '                                </td>' . "\n" .
                         '                            </tr>' . "\n" .
                         '                            <tr>' . "\n" .
                         '                                <td align="right" bgcolor="#d6deff" height="75">' . "\n" .
                         '                                    <p>Or</p>' . "\n" .
                         '                                    <input type="submit" id="deleteinput" name="quit" value="Cancel">' . "\n" .
                         '                                    <input type="hidden" name="action" value="order">' . "\n" .
                         '                                </td>' . "\n" .
                         '                            </tr>' . "\n" .
                         '                        </table>' . "\n";

        $content = shopping_cart($site, $contents);
        include_once('templates/' . $template_folder . '/header' . $template_ext);
        include_once('templates/' . $template_folder . '/body' . $template_ext);
        include_once('templates/' . $template_folder . '/footer' . $template_ext);
        exit();
    }
  }
  elseif (isset($action) && $action == 'order') {
    // this section displays a review of the order, sends payment and creates an order in mysql
    if (isset($_POST['quit']) && $_POST['quit'] == 'Cancel') {
      // rerun this script without any action
      unset($action);
      header('location: order.php');
      exit();
    }
    else {
      if (isset($order_action) && $order_action == 'send') {
        // send the payment
        // create an order in mysql
      }
      else {
        // display a review of the order
        $contents = '                        <table align="center" width="100%" cellpadding="6" cellspacing="0" border="0">' . "\n" .
                    '                            <tr>' . "\n" .
                    '                                <td align="center" bgcolor="#ffffff" height="35">' . "\n" .
                    '                                    <p>Order Review</p>' . "\n" .
                    '                                </td>' . "\n" .
                    '                            </tr>' . "\n" .
                    '                            <tr>' . "\n" .
                    '                                <td align="center" bgcolor="#ffffff" height="35">' . "\n" .
                    '                                    <p>' . $_POST['pay_option'] . '</p>' . "\n" .
                    '                                </td>' . "\n" .
                    '                            </tr>' . "\n" .
                    '                            <tr>' . "\n" .
                    '                                <td align="right" bgcolor="#d6deff" height="75">' . "\n" .
                    '                                    <input type="submit" id="viewinput" name="check_out" value="Make Payment">' . "\n" .
                    '                                </td>' . "\n" .
                    '                            </tr>' . "\n" .
                    '                            <tr>' . "\n" .
                    '                                <td align="right" bgcolor="#d6deff" height="75">' . "\n" .
                    '                                    <p>Or</p>' . "\n" .
                    '                                    <input type="submit" id="deleteinput" name="quit" value="Cancel">' . "\n" .
                    '                                    <input type="hidden" name="action" value="confirm">' . "\n" .
                    '                                </td>' . "\n" .
                    '                            </tr>' . "\n" .
                    '                        </table>' . "\n";
        $content = shopping_cart($site, $contents);
        include_once('templates/' . $template_folder . '/header' . $template_ext);
        include_once('templates/' . $template_folder . '/body' . $template_ext);
        include_once('templates/' . $template_folder . '/footer' . $template_ext);
        exit();
      }
    }
  }
  elseif (isset($action) && $action == 'confirm') {
    // display confirmation that the customer has paid or cancelled
    // has the quit button been clicked?
    if (isset($_POST['quit']) && $_POST['quit'] == 'Cancel') {
      // rerun this script without any action
      unset($action);
      header('location: order.php');
      exit();
    }
    else {
      // payment successful so display order information and confirmation
      $contents = '                        <table align="center" width="100%" cellpadding="6" cellspacing="0" border="0">' . "\n" .
                  '                            <tr>' . "\n" .
                  '                                <td align="center" bgcolor="#ffffff" height="35">' . "\n" .
                  '                                    <h1>Payment Successful!</h1>' . "\n" .
                  '                                </td>' . "\n" .
                  '                            </tr>' . "\n" .
                  '                        </table>' . "\n";
      $content = shopping_cart($site, $contents);
      include_once('templates/' . $template_folder . '/header' . $template_ext);
      include_once('templates/' . $template_folder . '/body' . $template_ext);
      include_once('templates/' . $template_folder . '/footer' . $template_ext);
      exit();
    }
  }
  else {
    // display the shopping cart
    if (isset($_GET['artwork_id'])) {
      $artwork_id = gp_filter($_GET['artwork_id']);
    }

    if (isset($_GET['act']) && $_GET['act'] == 'add') {
      if ($order) {
        $order .= ',' . $artwork_id;
      }
      else {
        $order = $artwork_id;
      }
    }
    elseif (isset($_GET['act']) && $_GET['act'] == 'delete') {
      if ($order) {
        $items = explode(',', $order);
        $neworder = '';
        foreach ($items as $item) {
          if ($artwork_id != $item) {
            if ($neworder != '') {
              $neworder .= ',' . $item;
            }
            else {
              $neworder = $item;
            }
          }
        }
        $order = $neworder;
      }
    }
    else {
      unset($_GET['act']);
    }

    $_SESSION['order'] = $order;
    $show_cart_link = enable_ecommerce($site);
    $contents = '                            <tr>' . "\n" .
                '                                <td class="heading_line" align="center" bgcolor="#ffffff" height="35">' . "\n" .
                '                                    <font color="#000000"><b>X</b></font>' . "\n" .
                '                                </td>' . "\n" .
                '                                <td class="heading_line" align="center" bgcolor="#ffffff" height="35">' . "\n" .
                '                                    <font color="#000000"><b>Qty</b></font>' . "\n" .
                '                                </td>' . "\n" .
                '                                <td class="heading_line" bgcolor="#ffffff" height="35">' . "\n" .
                '                                    <font color="#000000"><b>Item</b></font>' . "\n" .
                '                                </td>' . "\n" .
                '                                <td class="heading_line" bgcolor="#ffffff" height="35">' . "\n" .
                '                                    <p align="right"><font color="#000000"><b>Price</b></font></p>' . "\n" .
                '                                </td>' . "\n" .
                '                            </tr>' . "\n";

    if ($order) {
      // get order items from DB
      $in = rtrim($order, ', ');
      $query = "SELECT
                  a.artwork_id,
                  a.thumbnail,
                  a.price,
                  a.shipping,
                  a.handling,
                  a.qty_instock,
                  m.title,
                  m.description
                FROM
                  artwork a,
                  meta_data m
                WHERE
                  a.artwork_id IN ($in)
                AND
                  a.meta_data_id = m.meta_data_id
                AND
                  a.type = 'sale'
                AND
                  m.display = 'show'";
      $result = mysql_query($query) or die(mysql_error());
      if ($result) {
        while ($row = mysql_fetch_array($result)) {
          $thumbnail = thumbnail($row['thumbnail'], $row['title'], '100');
          $amount = $row['price'] + $row['shippng'] + $row['handling'];
          $contents .= '                            <tr>' . "\n" .
                       '                                <td class="heading_line" align="center" bgcolor="#ffffff">' . "\n" .
                       '                                    <a href="order.php?act=delete&artwork_id=' . $row['artwork_id'] . '" class="r"><b>X</b></a>' . "\n" .
                       '                                </td>' . "\n" .
                       '                                <td class="heading_line" align="center" bgcolor="#ffffff">' . "\n" .
                       '                                    ' . $row['qty_instock'] . "\n" .
                       '                                </td>' . "\n" .
                       '                                <td class="heading_line" bgcolor="#ffffff">' . "\n" .
                       '                                    ' . $thumbnail . $row['title'] . ' - ' . $row['description'] . "\n" .
                       '                                </td>' . "\n" .
                       '                                <td class="heading_line" bgcolor="#ffffff">' . "\n" .
                       '                                    <p align="right"><span class="cash_amounts">' . $site['currency'] . $amount . '</span></p>' . "\n" .
                       '                                </td>' . "\n" .
                       '                            </tr>' . "\n";
        }
      }
      else {
        // order should have items but does not
        $contents .= '                            <tr>' . "\n" .
                     '                                <td colspan="4" align="center" bgcolor="#ffffff">' . "\n" .
                     '                                    <p>Your order cannot be displayed.</p>' . "\n" .
                     '                                </td>' . "\n" .
                     '                            </tr>' . "\n";
      }        
    }
    else {
      // order is empty
      $contents .= '                            <tr>' . "\n" .
                   '                                <td colspan="4" align="center" bgcolor="#ffffff">' . "\n" .
                   '                                    <p>Your order is empty.</p>' . "\n" .
                   '                                </td>' . "\n" .
                   '                            </tr>' . "\n";
    }

    $contents .= '                            <tr>' . "\n" .
                 '                                <td class="blue_border" colspan="4" align="right" bgcolor="#d6deff" height="75">' . "\n" .
                 '                                    <input type="submit" id="viewinput" name="check_out" value="Check Out">' . "\n" .
                 '                                    <input type="hidden" name="action" value="contact">' . "\n" .
                 '                                </td>' . "\n" .
                 '                            </tr>' . "\n";

    $content = shopping_cart($site, $contents);
    include_once('templates/' . $template_folder . '/header' . $template_ext);
    include_once('templates/' . $template_folder . '/body' . $template_ext);
    include_once('templates/' . $template_folder . '/footer' . $template_ext);
    exit();
  }
}
else {
  header('location: index.php');
  exit();
}

?>