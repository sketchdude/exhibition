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

$output = array();

function write_cart() {
  // this function writes a link to the shopping cart
  $order = $_SESSION['order'];

  if (!$order) {
    $link = '<a href="order.php">My Order</a> (0) Items.';
  }
  else {
    // Parse the cart session variable
    $items = explode(',', $order);
    $s = (count($items) > 1) ? 's' : '';
    $link = '<a href="order.php">My Order</a> (' . count($items) . ') Item' . $s;
  }

  return $link;
}

function show_google_cart($site) {
  $cart = $_SESSION['cart'];

  $shop_display = '<table class="order_form" align="center" width="756" cellpadding="6" cellspacing="0" border="0">' . "\n" .
                  '    <tr>' . "\n" .
                  '        <td colspan="4" bgcolor="#ffffff" height="50" valign="top">' . "\n" .
                  '            <img src="images/artwork_order_form.jpg" height="45" width="320" alt="Artwork Order Form" title="Artwork Order Form">' . "\n" .
                  '        </td>' . "\n" .
                  '    </tr>' . "\n" .
                  '    <tr>' . "\n" .
                  '        <td colspan="4" bgcolor="#ffffff">' . "\n" .
                  '            <img src="images/secure_checkout.jpg" height="24" width="154" alt="Secure checkout" title="Secure checkout">' . "\n" .
                  '        </td>' . "\n" .
                  '    </tr>' . "\n" .
                  '    <tr>' . "\n" .
                  '        <td class="blue_border" colspan="4" bgcolor="#d6deff" height="39">' . "\n" .
                  '            <p>Order Details - ' . $site['business_address'] . '</p>' . "\n" .
                  '        </td>' . "\n" .
                  '    </tr>' . "\n" .
                  '    <tr>' . "\n" .
                  '        <td class="heading_line" align="center" bgcolor="#ffffff" height="35">' . "\n" .
                  '            <font color="#000000"><b>X</b></font>' . "\n" .
                  '        </td>' . "\n" .
                  '        <td class="heading_line" align="center" bgcolor="#ffffff" height="35">' . "\n" .
                  '            <font color="#000000"><b>Qty</b></font>' . "\n" .
                  '        </td>' . "\n" .
                  '        <td class="heading_line" bgcolor="#ffffff" height="35">' . "\n" .
                  '            <font color="#000000"><b>Item</b></font>' . "\n" .
                  '        </td>' . "\n" .
                  '        <td class="heading_line" bgcolor="#ffffff" height="35">' . "\n" .
                  '            <p align="right"><font color="#000000"><b>Price</b></font></p>' . "\n" .
                  '        </td>' . "\n" .
                  '    </tr>' . "\n";

  // get merchant settings
  $query = "SELECT
              g.google_merchant_id,
              g.google_merchant_key,
              g.google_server_type,
              g.security_protocol
            FROM
              admin a,
              google_settings g
            WHERE
              g.google_settings_id = a.google_settings_id";
  $result = mysql_query($query);
  if (!$result || mysql_num_rows($result) < 1) {
    // do something
    $shop_display .= '<tr><td class="heading_line" bgcolor="#ffffff" colspan="4">Your order is empty.</td></tr>' . "\n";
    return $shop_display;
  }
  else {
    // google settings are detected
    $merchant_id       = mysql_result($result, 0, 'google_merchant_id');
    $merchant_key      = mysql_result($result, 0, 'google_merchant_key');
    $server_type       = mysql_result($result, 0, 'google_server_type');
    $security_protocol = mysql_result($result, 0, 'security_protocol');

    // $google_address points the html form to the correct server on google
    if ($server_type == 'sandbox') {
        $google_address = $security_protocol . '://sandbox.google.com/checkout/api/checkout/v2/checkoutForm/Merchant/' . $merchant_id;
    }
    else {
      $google_address = 'https://checkout.google.com/api/checkout/v2/checkoutForm/Merchant/' . $merchant_id;
    }
  }

  if ($cart) {
      $items = explode(',', $cart);
      $contents = array();
      foreach($items as $item) {
        $contents[$item] = (isset($contents[$item])) ? $contents[$item] + 1 : 1;
      }
      $output[] = '<form method="POST" action="' . $google_address . '" accept-charset="utf-8"/>' . "\n";
      $output[] = $shop_display;

      $i = 1;
      foreach($contents as $id => $qty) {
        $query = "SELECT * FROM artwork a, meta_data m WHERE a.artwork_id = '$id' AND a.meta_data_id = m.meta_data_id";
        $result = mysql_query($query);
        $row = mysql_fetch_array($result);
        extract($row);
        $item_name = $title;
        $item_tag = '_' . $i;
        $ship_price = ($shipping + $handling);
        $output[] = '    <tr>' . "\n";
        $output[] = '        <td class="heading_line" align="center" bgcolor="#ffffff" height="35">' . "\n";
        $output[] = '            <a href="shop_cart.php?action=delete&id=' . $id . '" class="r"><b>X</b></a>' . "\n";
        $output[] = '        </td>' . "\n";
        $output[] = '        <td align="center" class="heading_line" bgcolor="#ffffff" height="35">' . "\n";
        $output[] = '            ' . $qty_instock . "\n";
        $output[] = '        </td>';
        $output[] = '        <td class="heading_line" bgcolor="#ffffff" height="35">' . "\n";
        $output[] = '            <b>' . $item_name . '</b> - ' . $description . "\n";
        $output[] = '        </td>';
        $output[] = '        <td class="heading_line" bgcolor="#ffffff" height="35">' . "\n";
        $output[] = '            <p align="right"><span class="cash_amounts">' . $site['currency'] . $price . '</span></p>' . "\n";
        $output[] = '        </td>' . "\n";
        $total += $price * $qty;
        $output[] = '    </tr>' . "\n";
        $output[] = '<input type="hidden" name="item_name' . $item_tag . '" value="' . $item_name . '"/>' . "\n";
        $output[] = '<input type="hidden" name="item_description' . $item_tag . '" value="' . $description . '"/>' . "\n";
        $output[] = '<input type="hidden" name="item_quantity' . $item_tag . '" value="' . $qty_instock . '"/>' . "\n";
        $output[] = '<input type="hidden" name="item_price' . $item_tag . '" value="' . $price . '"/>' . "\n";
        $output[] = '<input type="hidden" name="item_currency' . $item_tag . '" value="USD"/>' . "\n";
        $output[] = '<input type="hidden" name="ship_method_name' . $item_tag . '" value="UPS Air"/>' . "\n";
        $output[] = '<input type="hidden" name="ship_method_price' . $item_tag . '" value="19.99"/>' . "\n";
        $output[] = '<input type="hidden" name="ship_method_us_area' . $item_tag . '" value="FULL_50_STATES"/>' . "\n";
        $i++;
      }
      $output[] = '<input type="hidden" name="tax_rate" value="7.00"/>' . "\n";
      $output[] = '<input type="hidden" name="tax_us_state" value="IN"/>' . "\n";
      $output[] = '<input type="hidden" name="_charset_"/>' . "\n";
      $output[] = '    <tr>' . "\n";
      $output[] = '        <td class="way_line" colspan="4" bgcolor="#ffffff" height="35">' . "\n";
      $output[] = '            <p align="right">' . "\n";
      $output[] = '                Shipping & Handling' . "\n";
      $output[] = '                <span class="cash_amounts">$36.00</span>' . "\n";
      $output[] = '            </p>' . "\n";
      $output[] = '        </td>' . "\n";
      $output[] = '    </tr>' . "\n";
      $output[] = '    <tr>' . "\n";
      $output[] = '        <td colspan="4" bgcolor="#ffffff" height="35">' . "\n";
      $output[] = '            <p align="right">Tax - &nbsp;&nbsp;&nbsp;&nbsp; <span class="cash_amounts">$0.00</span></p>' . "\n";
      $output[] = '        </td>' . "\n";
      $output[] = '    </tr>' . "\n";
      $output[] = '    <tr>' . "\n";
      $output[] = '        <td colspan="4" bgcolor="#ebebeb" height="39">' . "\n";
      $output[] = '            <p align="right">Total: <span class="cash_amounts">' . $site['currency'] . $total . '</span></p>' . "\n";
      $output[] = '        </td>' . "\n";
      $output[] = '    </tr>' . "\n";
      $output[] = '    <tr>' . "\n";
      $output[] = '        <td colspan="4" bgcolor="#ffffff" height="75">' . "\n";
      $output[] = '            <p align="right"><input type="image" name="Google Checkout" alt="Fast checkout through Google" src="http://checkout.google.com/buttons/checkout.gif?merchant_id=' . $merchant_id . '&w=180&h=46&style=white&variant=text&loc=en_US" height="46" width="180"/></p>' . "\n";
      $output[] = '        </td>' . "\n";
      $output[] = '    </tr>' . "\n";
      $output[] = '</table>' . "\n";
      $output[] = '</form>';
  }
  else {
      $output[] = $shop_display;
      $output[] = '    <tr>' . "\n";
      $output[] = '        <td colspan="4" bgcolor="#ffffff" height="35">' . "\n";
      $output[] = '            <p>You have not ordered anything.</p>' . "\n";
      $output[] = '        </td>' . "\n";
      $output[] = '    </tr>' . "\n";
  }
  return join('', $output);
}

function shopping_cart($site, $contents) {
  $cart = $_SESSION['cart'];

  $shop_display = '                        <form method="post" action="' . $_SERVER['PHP_SELF'] . '" enctype="multipart/form-data">' . "\n" . 
                  '                        <table align="center" width="100%" cellpadding="6" cellspacing="0" border="0">' . "\n" .
                  '                            <tr>' . "\n" .
                  '                                <td colspan="4" bgcolor="#ffffff" height="50" valign="top">' . "\n" .
                  '                                    <img src="images/artwork_order_form.jpg" height="45" width="320" alt="Artwork Order Form" title="Artwork Order Form">' . "\n" .
                  '                                </td>' . "\n" .
                  '                            </tr>' . "\n" .
                  '                            <tr>' . "\n" .
                  '                                <td colspan="4" bgcolor="#ffffff">' . "\n" .
                  '                                    <img src="images/secure_checkout.jpg" height="24" width="154" alt="Secure checkout" title="Secure checkout">' . "\n" .
                  '                                </td>' . "\n" .
                  '                            </tr>' . "\n" .
                  '                            <tr>' . "\n" .
                  '                                <td class="blue_border" colspan="4" bgcolor="#d6deff" height="39">' . "\n" .
                  '                                    <p>Order Details - ' . $site['business_address'] . '</p>' . "\n" .
                  '                                </td>' . "\n" .
                  '                            </tr>' . "\n" . $contents .
                  '                        </table>' . "\n" .
                  '                        </form>' . "\n";

  return $shop_display;
}

// This array associates country names with a 3 digit numeric code for MySQL storage.
$countries = array(
  '225' => 'United States of America',
  '001' => 'Afghanistan',
  '002' => 'Albania',
  '003' => 'Algeria',
  '004' => 'American Samoa',
  '005' => 'Andorra',
  '006' => 'Angola',
  '007' => 'Anguilla',
  '008' => 'Antarctica',
  '009' => 'Antigua and Barbuda',
  '010' => 'Argentina',
  '011' => 'Armenia',
  '012' => 'Aruba',
  '013' => 'Australia',
  '014' => 'Austria',
  '015' => 'Azerbaijan',
  '016' => 'Bahamas',
  '017' => 'Bahrain',
  '018' => 'Bangladesh',
  '019' => 'Barbados',
  '020' => 'Belarus',
  '021' => 'Belgium',
  '022' => 'Belize',
  '023' => 'Benin',
  '024' => 'Bermuda',
  '025' => 'Bhutan',
  '026' => 'Bolivia',
  '027' => 'Bosnia and Herzegovina',
  '028' => 'Botswana',
  '029' => 'Bouvet Island',
  '030' => 'Brazil',
  '031' => 'British Indian Ocean Territory',
  '032' => 'Brunei Darussalam',
  '033' => 'Bulgaria',
  '034' => 'Burkina Faso',
  '035' => 'Burundi',
  '036' => 'Cambodia',
  '037' => 'Cameroon',
  '038' => 'Canada',
  '039' => 'Cape Verde',
  '040' => 'Cayman Islands',
  '041' => 'Central African Republic',
  '042' => 'Chad',
  '043' => 'Chile',
  '044' => 'China',
  '045' => 'Christmas Island',
  '046' => 'Cocos (Keeling) Islands',
  '047' => 'Colombia',
  '048' => 'Comoros',
  '049' => 'Congo',
  '050' => 'The Democratic Republic of the Congo',
  '051' => 'Cook Islands',
  '052' => 'Costa Rica',
  '053' => 'Cote D Ivoire',
  '054' => 'Croatia',
  '055' => 'Cuba',
  '056' => 'Cyprus',
  '057' => 'Czech Republic',
  '058' => 'Denmark',
  '059' => 'Djibouti',
  '060' => 'Dominica',
  '061' => 'Dominican Republic',
  '062' => 'East Timor',
  '063' => 'Ecuador',
  '064' => 'Egypt',
  '065' => 'El Salvador',
  '066' => 'Equatorial Guinea',
  '067' => 'Eritrea',
  '068' => 'Estonia',
  '069' => 'Ethiopia',
  '070' => 'Falkland Islands (Malvinas)',
  '071' => 'Faroe Islands',
  '072' => 'Fiji',
  '073' => 'Finland',
  '074' => 'France',
  '075' => 'French Guiana',
  '076' => 'French Polynesia',
  '077' => 'French Southern Territories',
  '078' => 'Gabon',
  '079' => 'Gambia',
  '080' => 'Georgia',
  '081' => 'Germany',
  '082' => 'Ghana',
  '083' => 'Gibraltar',
  '084' => 'Greece',
  '085' => 'Greenland',
  '086' => 'Grenada',
  '087' => 'Guadeloupe',
  '088' => 'Guam',
  '089' => 'Guatemala',
  '090' => 'Guinea',
  '091' => 'Guinea-Bissau',
  '092' => 'Guyana',
  '093' => 'Haiti',
  '094' => 'Heard Island and Mcdonald Islands',
  '095' => 'Holy See (Vatican City State)',
  '096' => 'Honduras',
  '097' => 'Hong Kong',
  '098' => 'Hungary',
  '099' => 'Iceland',
  '100' => 'India',
  '101' => 'Indonesia',
  '102' => 'Islamic Republic of Iran',
  '103' => 'Iraq',
  '104' => 'Ireland',
  '105' => 'Israel',
  '106' => 'Italy',
  '107' => 'Jamaica',
  '108' => 'Japan',
  '109' => 'Jordan',
  '110' => 'Kazakstan',
  '111' => 'Kenya',
  '112' => 'Kiribati',
  '113' => 'Democratic Peoples Republic of Korea',
  '114' => 'Republic of Korea',
  '115' => 'Kuwait',
  '116' => 'Kyrgyzstan',
  '117' => 'Lao Peoples Democratic Republic',
  '118' => 'Latvia',
  '119' => 'Lebanon',
  '120' => 'Lesotho',
  '121' => 'Liberia',
  '122' => 'Libyan Arab Jamahiriya',
  '123' => 'Liechtenstein',
  '124' => 'Lithuania',
  '125' => 'Luxembourg',
  '126' => 'Macau',
  '127' => 'Macedonia',
  '128' => 'Madagascar',
  '129' => 'Malawi',
  '130' => 'Malaysia',
  '131' => 'Maldives',
  '132' => 'Mali',
  '133' => 'Malta',
  '134' => 'Marshall Islands',
  '135' => 'Martinique',
  '136' => 'Mauritania',
  '137' => 'Mauritius',
  '138' => 'Mayotte',
  '139' => 'Mexico',
  '140' => 'Federated States of Micronesia',
  '141' => 'Republic of Moldova',
  '142' => 'Monaco',
  '143' => 'Mongolia',
  '144' => 'Montserrat',
  '145' => 'Morocco',
  '146' => 'Mozambique',
  '147' => 'Myanmar',
  '148' => 'Namibia',
  '149' => 'Nauru',
  '150' => 'Nepal',
  '151' => 'Netherlands',
  '152' => 'Netherlands Antilles',
  '153' => 'New Caledonia',
  '154' => 'New Zealand',
  '155' => 'Nicaragua',
  '156' => 'Niger',
  '157' => 'Nigeria',
  '158' => 'Niue',
  '159' => 'Norfolk Island',
  '160' => 'Northern Mariana Islands',
  '161' => 'Norway',
  '162' => 'Oman',
  '163' => 'Pakistan',
  '164' => 'Palau',
  '165' => 'Palestine',
  '166' => 'Panama',
  '167' => 'Papua New Guinea',
  '168' => 'Paraguay',
  '169' => 'Peru',
  '170' => 'Philippines',
  '171' => 'Pitcairn',
  '172' => 'Poland',
  '173' => 'Portugal',
  '174' => 'Puerto Rico',
  '175' => 'Qatar',
  '176' => 'Reunion',
  '177' => 'Romania',
  '178' => 'Russian Federation',
  '179' => 'Rwanda',
  '180' => 'Saint Helena',
  '181' => 'Saint Kitts and Nevis',
  '182' => 'Saint Lucia',
  '183' => 'Saint Pierre and Miquelon',
  '184' => 'Saint Vincent and the Grenadines',
  '185' => 'Samoa',
  '186' => 'San Marino',
  '187' => 'Sao Tome and Principe',
  '188' => 'Saudi Arabia',
  '189' => 'Senegal',
  '190' => 'Seychelles',
  '191' => 'Sierra Leone',
  '192' => 'Singapore',
  '193' => 'Slovakia',
  '194' => 'Slovenia',
  '195' => 'Solomon Islands',
  '196' => 'Somalia',
  '197' => 'South Africa',
  '198' => 'South Georgia and the South Sandwich Islands', // 198
  '199' => 'Spain',
  '200' => 'Sri Lanka',
  '201' => 'Sudan',
  '202' => 'Suriname',
  '203' => 'Svalbard and Jan Mayen',
  '204' => 'Swaziland',
  '205' => 'Sweden',
  '206' => 'Switzerland',
  '207' => 'Syrian Arab Republic',
  '208' => 'Taiwan',
  '209' => 'Tajikistan',
  '210' => 'United Republic of Tanzania',
  '211' => 'Thailand',
  '212' => 'Togo',
  '213' => 'Tokelau',
  '214' => 'Tonga',
  '215' => 'Trinidad and Tobago',
  '216' => 'Tunisia',
  '217' => 'Turkey',
  '218' => 'Turkmenistan',
  '219' => 'Turks and Caicos Islands',
  '220' => 'Tuvalu',
  '221' => 'Uganda',
  '222' => 'Ukraine',
  '223' => 'United Arab Emirates',
  '224' => 'United Kingdom',
  '226' => 'United States Minor Outlying Islands',
  '227' => 'Uruguay',
  '228' => 'Uzbekistan',
  '229' => 'Vanuatu',
  '230' => 'Venezuela',
  '231' => 'Viet Nam',
  '232' => 'Virgin Islands, British',
  '233' => 'Virgin Islands, US',
  '234' => 'Wallis And Futuna',
  '235' => 'Western Sahara',
  '236' => 'Yemen',
  '237' => 'Yugoslavia',
  '238' => 'Zambia',
  '239' => 'Zimbabwe'
);

// Associates US state or province names with a 2 digit numeric code for MySQL storage.
$states = array(
  '01' => 'Alabama',
  '02' => 'Alaska',
  '03' => 'Arizona',
  '04' => 'Arkansas',
  '05' => 'California',
  '06' => 'Colorado',
  '07' => 'Connecticut',
  '08' => 'Delaware',
  '09' => 'District of Columbia',
  '10' => 'Florida',
  '11' => 'Georgia',
  '12' => 'Hawaii',
  '13' => 'Idaho',
  '14' => 'Illinois',
  '15' => 'Indiana',
  '16' => 'Iowa',
  '17' => 'Kansas',
  '18' => 'Kentucky',
  '19' => 'Louisiana',
  '20' => 'Maine',
  '21' => 'Maryland',
  '22' => 'Massachusetts',
  '23' => 'Michigan',
  '24' => 'Minnesota',
  '25' => 'Mississippi',
  '26' => 'Missouri',
  '27' => 'Montana',
  '28' => 'Nebraska',
  '29' => 'Nevada',
  '30' => 'New Hampshire',
  '31' => 'New Jersey',
  '32' => 'New Mexico',
  '33' => 'New York',
  '34' => 'North Carolina',
  '35' => 'North Dakota',
  '36' => 'Ohio',
  '37' => 'Oklahoma',
  '38' => 'Oregon',
  '39' => 'Pennsylvania',
  '40' => 'Rhode Island',
  '41' => 'South Carolina',
  '42' => 'South Dakota',
  '43' => 'Tennessee',
  '44' => 'Texas',
  '45' => 'Utah',
  '46' => 'Vermont',
  '47' => 'Virginia',
  '48' => 'Washington',
  '49' => 'West Virginia',
  '50' => 'Wisconsin',
  '51' => 'Wyoming',
  '52' => 'Non-USA'
);

// Prints out a drop-down list of countries. $country will appear selected.
function country_options($type=null) {

  global $country;
  global $countries;

  if (!isset($type)) {
    $type = 'country';
  }

  $options = '<select name="' . $type . '">' . "\n";
  foreach ($countries as $num => $name) {
    if ($country == $name || $country == $num) {
      $selected_country = " selected";
    }
    else {
      $selected_country = "";
    }

    $options .= '<option value="' . $num . '"' . $selected_country . '>' . $name . '</option>' . "\n";
  }
  $options .= '</select>' . "\n";

  return $options;
}

// country_codes() Pass it a numeric code and it returns the corresponding country name.
// Pass it a country name and it returns the numeric code.
function country_codes($country) {

  global $country;
  global $countries;

  foreach ($countries as $num => $name) {
    if ($country == $num || $country == $name) {
      $country_num = $num;
      $country_name = $name;
    }
  }
  if (strlen($country) > 3) {
    $country = $country_num;
  }
  else {
    $country = $country_name;
  }
  return $country;
}

// Prints out a drop-down list of states. $state will appear selected.
function state_options($type=null, $state=null) {

  global $state;
  global $states;

  if (!isset($type)) {
    $type = 'state';
  }

  $options = '<select name="' . $type . '">' . "\n";
  foreach ($states as $num => $name) {
    if ($state == $name || $state == $num) {
      $selected_state = " selected";
    }
    else {
      $selected_state = '';
    }
    $options .= '<option value="' . $num . '"' . $selected_state . '>' . $name . '</option>' . "\n";
  }
  $options .= '</select>' . "\n";

  return $options;
}

// state_codes() Pass it a number and it returns a name.
// Pass it a name and it returns a number.
function state_codes($state) {

  global $state;
  global $states;

  foreach ($states as $num => $name) {
    if ($state == $num || $state == $name) {
      $state_num = $num;
      $state_name = $name;
    }
  }
  if (strlen($state) > 2) {
    $state = $state_num;
  }
  else {
    $state = $state_name;
  }

  return $state;
}

// Contains the html form for adding customer mailing address
$address_form = '            <table border="0" width="100%" cellpadding="0" cellspacing="0">' . "\n" .
                '                <tr>' . "\n" .
                '                    <th colspan="2" height="30">' . "\n" .
                '                        <p align="center">Client Details:</p>' . "\n" .
                '                    </th>' . "\n" .
                '                </tr>' . "\n" .
                '                <tr>' . "\n" .
                '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        First Name: &nbsp;' . "\n" .
                '                    </td>' . "\n" .
                '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        <input type="text" name="first_name" value="" size="35" maxlength="35">' . "\n" .
                '                    </td>' . "\n" .
                '                </tr>' . "\n" .
                '                <tr>' . "\n" .
                '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        Last Name: &nbsp;' . "\n" .
                '                    </td>' . "\n" .
                '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        <input type="text" name="last_name" value="" size="35" maxlength="35">' . "\n" .
                '                    </td>' . "\n" .
                '                </tr>' . "\n" .
                '                <tr>' . "\n" .
                '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        Address 1: &nbsp;' . "\n" .
                '                    </td>' . "\n" .
                '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        <input type="text" name="address1" value="" size="65" maxlength="65">' . "\n" .
                '                    </td>' . "\n" .
                '                </tr>' . "\n" .
                '                <tr>' . "\n" .
                '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        Address 2: &nbsp;' . "\n" .
                '                    </td>' . "\n" .
                '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        <input type="text" name="address2" value="" size="65" maxlength="65">' . "\n" .
                '                    </td>' . "\n" .
                '                </tr>' . "\n" .
                '                <tr>' . "\n" .
                '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        City: &nbsp;' . "\n" .
                '                    </td>' . "\n" .
                '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        <input type="text" name="city" value="" size="40" maxlength="40">' . "\n" .
                '                    </td>' . "\n" .
                '                </tr>' . "\n" .
                '                <tr>' . "\n" .
                '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        State: &nbsp;' . "\n" .
                '                    </td>' . "\n" .
                '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        ' . state_options() . "\n" .
                '                    </td>' . "\n" .
                '                </tr>' . "\n" .
                '                <tr>' . "\n" .
                '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        Postal Code: &nbsp;' . "\n" .
                '                    </td>' . "\n" .
                '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        <input type="text" name="post_code" value="" size="25" maxlength="25">' . "\n" .
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
                '                        <input type="text" name="phone" value="" size="16" maxlength="16">' . "\n" .
                '                    </td>' . "\n" .
                '                </tr>' . "\n" .
                '                <tr>' . "\n" .
                '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        E-mail: &nbsp;' . "\n" .
                '                    </td>' . "\n" .
                '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        <input type="text" name="email" value="" size="55" maxlength="55">' . "\n" .
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
                '                        <input type="text" name="ship_address" value="" size="65" maxlength="65">' . "\n" .
                '                    </td>' . "\n" .
                '                </tr>' . "\n" .
                '                <tr>' . "\n" .
                '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        Shipping Address 2: &nbsp;' . "\n" .
                '                    </td>' . "\n" .
                '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        <input type="text" name="ship_address2" value="" size="65" maxlength="65">' . "\n" .
                '                    </td>' . "\n" .
                '                </tr>' . "\n" .
                '                <tr>' . "\n" .
                '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        Shipping City: &nbsp;' . "\n" .
                '                    </td>' . "\n" .
                '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        <input type="text" name="ship_city" value="" size="40" maxlength="40">' . "\n" .
                '                    </td>' . "\n" .
                '                </tr>' . "\n" .
                '                <tr>' . "\n" .
                '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        Shipping State: &nbsp;' . "\n" .
                '                    </td>' . "\n" .
                '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        ' . state_options('ship_state') . "\n" .
                '                    </td>' . "\n" .
                '                </tr>' . "\n" .
                '                <tr>' . "\n" .
                '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        Shipping Postal Code: &nbsp;' . "\n" .
                '                    </td>' . "\n" .
                '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        <input type="text" name="ship_post_code" value="" size="25" maxlength="25">' . "\n" .
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
                '                        <input type="text" name="bill_address" value="" size="65" maxlength="65">' . "\n" .
                '                    </td>' . "\n" .
                '                </tr>' . "\n" .
                '                <tr>' . "\n" .
                '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        Billing Address 2: &nbsp;' . "\n" .
                '                    </td>' . "\n" .
                '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        <input type="text" name="bill_address2" value="" size="65" maxlength="65">' . "\n" .
                '                    </td>' . "\n" .
                '                </tr>' . "\n" .
                '                <tr>' . "\n" .
                '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        Billing City: &nbsp;' . "\n" .
                '                    </td>' . "\n" .
                '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        <input type="text" name="bill_city" value="" size="40" maxlength="40">' . "\n" .
                '                    </td>' . "\n" .
                '                </tr>' . "\n" .
                '                <tr>' . "\n" .
                '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        Billing State: &nbsp;' . "\n" .
                '                    </td>' . "\n" .
                '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        ' . state_options('bill_state') . "\n" .
                '                    </td>' . "\n" .
                '                </tr>' . "\n" .
                '                <tr>' . "\n" .
                '                    <td align="right" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        Billing Postal Code: &nbsp;' . "\n" .
                '                    </td>' . "\n" .
                '                    <td align="left" valign="middle" bgcolor="#ffffff" height="40">' . "\n" .
                '                        <input type="text" name="bill_post_code" value="" size="25" maxlength="25">' . "\n" .
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

?>