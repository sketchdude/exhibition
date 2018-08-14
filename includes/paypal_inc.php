<?php

// eXhibition - A PHP/MySQL Art Publishing System
// copyright (c) 2007 sketchdude

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

// hash_call: Function to perform the API call to PayPal using API signature
// method_name is name of API  method.
// nvp_str is nvp string.
// returns an associative array containing the response from the server.
function hash_call($method_name, $nvp_str, $paypalapi_id) {
  global $nvp_header, $paypal_urls;

  // get api credentials from mysql
  $query = "SELECT
              api_username,
              api_password,
              api_signature,
              api_endpoint,
              use_proxy,
              proxy_host,
              proxy_port,
              paypal_url,
              version
            FROM
              paypalapi
            WHERE
              paypalapi_id = $paypalapi_id";

  $result = mysql_query($query);

  if (!@mysql_num_rows($result) < 1) {
    $api_username  = mysql_result($result, 0, 'api_username');
    $api_password  = mysql_result($result, 0, 'api_password');
    $api_signature = mysql_result($result, 0, 'api_signature');
    $api_endpoint  = mysql_result($result, 0, 'api_endpoint');
    $use_proxy     = mysql_result($result, 0, 'use_proxy');
    $proxy_host    = mysql_result($result, 0, 'proxy_host');
    $proxy_port    = mysql_result($result, 0, 'proxy_port');
    $paypal_url    = mysql_result($result, 0, 'paypal_url');
    $version       = mysql_result($result, 0, 'version');

    if ($paypal_url == 'sandbox') {
      $paypal_url = $paypal_urls['sandbox'];
    }
    else {
      $paypal_url = $paypal_urls['live'];
    }

    // setting the curl parameters
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_endpoint);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);

    // turning off the server and peer verification(TrustManager Concept)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_POST, 1);

    if ($use_proxy == 'TRUE') {
      curl_setopt($ch, CURLOPT_PROXY, $proxy_host . ':' . $proxy_port);
    }

    // nvp_request for submitting to server
    $nvpreq = 'METHOD=' . urlencode($method_name) . '&VERSION=' . urlencode($version) . '&PWD=' . urlencode($api_password) . '&USER=' . urlencode($api_username) . '&SIGNATURE=' . urlencode($api_signature) . $nvp_str;

    // setting the nvpreq as POST FIELD to curl
    curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

    // getting response from server
    $response = curl_exec($ch);

    //convrting nvp_response to an associative array
    $nvp_res_array = deformat_nvp($response);
    $nvp_req_array = deformat_nvp($nvpreq);
    $_SESSION['nvp_req_array'] = $nvp_req_array;

    if (curl_errno($ch)) {
      // display error message here (See APIError.php)
    }
    else {
      curl_close($ch);
    }

    return $nvp_res_array;
  }
  else {
    return false;
  }
}

// This function will take NVPString and convert it to an Associative Array and it will
// decode the response. It is usefull to search for a particular key and displaying arrays.
// nvpstr is NVPString.
// nvpArray is Associative Array.
function deformat_nvp($nvpstr) {
  $intial = 0;
  $nvp_array = array();

  while (strlen($nvpstr)) {
    // postion of Key
    $keypos = strpos($nvpstr, '=');

    // position of value
    $valuepos = strpos($nvpstr, '&') ? strpos($nvpstr, '&') : strlen($nvpstr);

    // getting the Key and Value values and storing in a Associative Array
    $keyval = substr($nvpstr, $intial, $keypos);
    $valval = substr($nvpstr, $keypos + 1, $valuepos - $keypos - 1);

    //decoding the respose
    $nvp_array[urldecode($keyval)] = urldecode($valval);
    $nvpstr = substr($nvpstr, $valuepos + 1, strlen($nvpstr));
  }
  return $nvp_array
}

?>