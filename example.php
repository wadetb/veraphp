<?php
include "vera.php";

$user          = 'change_this_to_your_user';
$password      = 'change_this_to_your_password';

$auth = vera_auth( $user, $password );
$identity = vera_identity( $auth );

$device_list_session = vera_session( $auth, $auth['Server_Account'] );
$device_list = vera_devices( $auth, $identity, $device_list_session );

$device_entry = $device_list['Devices'][0];

$device_session = vera_session( $auth, $device_entry['Server_Device'] );
$device = vera_device( $device_entry, $device_session );

$relay_session = vera_session( $auth, $device['Server_Relay'] );
$result = vera_request( $device, $relay_session, "data_request?id=alive" );

var_dump( $result );
?>
