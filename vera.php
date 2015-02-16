<?php
﻿// MIT License (MIT)
// 
// Copyright (c) 2015 Wade Brainerd
// 
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
// 
// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.
// 
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
// SOFTWARE.
// 

function vera_curl( $url, $headers )
{
    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_USERAGENT      => "php-vera", // who am i
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT        => 120,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
        CURLOPT_HTTPHEADER     => $headers, // extra HTTP headers
        //CURLOPT_SSL_VERIFYPEER => false     // Disabled SSL Cert checks
    );

    var_dump( $url );

    $ch = curl_init( $url );
    curl_setopt_array( $ch, $options );

    $content = curl_exec( $ch );

    if ( curl_errno( $ch ) ) {
        die( curl_error( $ch ) );
    }
    
    curl_close( $ch );

    return $content;
}

function vera_auth( $user, $password )
{
    $password_seed = 'oZ7QE6LcLJp6fiWzdqZc';

    $sha1_password = sha1( $user . $password . $password_seed );

    $auth_server   = 'https://vera-us-oem-autha.mios.com';
    $auth_url      = "{$auth_server}/autha/auth/username/{$user}?SHA1Password={$sha1_password}&PK_Oem=1";

    $auth_content  = vera_curl( $auth_url, [] );
    $auth          = json_decode( $auth_content, true );

    return $auth;
}

function vera_identity( $auth )
{
    $identity_content = base64_decode( $auth['Identity'] );

    $identity         = json_decode( $identity_content, true );

    return $identity;
}

function vera_session( $auth, $server )
{
    $identity      = $auth['Identity'];
    $identity_sig  = $auth['IdentitySignature'];

    $session_url   = "https://{$server}/info/session/token";

    $headers = [
        "MMSAuth: {$identity}",
        "MMSAuthSig: {$identity_sig}"
    ];

    $session       = vera_curl( $session_url, $headers );

    return $session;
}

function vera_devices( $auth, $identity, $session )
{
    $server_account = $auth['Server_Account'];
    $pk_account     = $identity['PK_Account'];

    $devices_url    = "https://{$server_account}/account/account/account/{$pk_account}/devices";

    $headers = [
        "MMSSession: {$session}"
    ];

    $devices_json   = vera_curl( $devices_url, $headers );
    $devices        = json_decode( $devices_json, true );

    return $devices;
}

function vera_device( $device, $device_session )
{
    $server_device   = $device['Server_Device'];
    $pk_device       = $device['PK_Device'];

    $info_url        = "https://{$server_device}/device/device/device/{$pk_device}";

    $headers = [
        "MMSSession: {$device_session}"
    ];

    $info_json       = vera_curl( $info_url, $headers );
    $info            = json_decode( $info_json, true );

    return $info;
}

function vera_request( $device, $relay_session, $request )
{
    $server_relay    = $device['Server_Relay'];
    $pk_device       = $device['PK_Device'];

    $request_url     = "https://{$server_relay}/relay/relay/relay/device/{$pk_device}/port_3480/{$request}";

    $headers = [
        "MMSSession: {$relay_session}"
    ];

    $result          = vera_curl( $request_url, $headers );

    return $result;
}
?>

