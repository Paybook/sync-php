<?php

require __DIR__.'/vendor/autoload.php';

/*
Change this values:
*/
$YOUR_API_KEY = 'YOUR_API_KEY';
$RFC = 'YOUR_RFC';
$CIEC = 'YOUR_CIEC';
$USERNAME = 'MY_USER';

$SAT_SITE = 'CIEC';// Keep this value

function _print($message, $indents = 0)
{
    print_r($message.PHP_EOL);
}//End of _print

function print_status($status)
{
    $status_str = ' -> ';
    foreach ($status as $index => $each_status) {
        $code = $each_status['code'];
        $status_str = $status_str.$code.', ';
    }//End of foreach
    $status_str = substr($status_str, 0, strlen($status_str) - 2);
    _print($status_str);
}//End of print_status

try {
    paybook\Paybook::init($YOUR_API_KEY);
    $my_users = paybook\User::get();
    $user = null;
    foreach ($my_users as $index => $my_user) {
        if ($my_user->name == $USERNAME) {
            _print('User '.$USERNAME.' already exists');
            $user = $my_user;
        }//End of if
    }//End of foreach
    if ($user == null) {
        _print('Creating user '.$USERNAME);
        $user = new paybook\User($USERNAME);
    }//End of if
    $session = new paybook\Session($user);
    _print('Token: '.$session->token);
    $session_verified = $session->verify();
    _print('Session verfied: '.strval($session_verified));
    $sites = paybook\Catalogues::get_sites($session);
    $sat_site = null;
    foreach ($sites as $index => $site) {
        if ($site->name == $SAT_SITE) {
            $sat_site = $site;
        }//End of if
    }//End of foreach
    _print('SAT site: '.$sat_site->id_site.' '.$sat_site->name);
    $credentials_params = [
        'rfc' => $RFC,
        'password' => $CIEC,
    ];//End of credentials_params
    $sat_credentials = new paybook\Credentials($session, null, $sat_site->id_site, $credentials_params);
    _print('Credentials username: '.$sat_credentials->username);
    $sat_sync_completed = false;
    while (!$sat_sync_completed) {
        sleep(5);
        $status = $sat_credentials->get_status($session);
        print_status($status);
        foreach ($status as $index => $each_status) {
            $code = $each_status['code'];
            if ($code >= 200 && $code <= 205) {
                $sat_sync_completed = true;
            } elseif ($code >= 400 && $code <= 405) {
                _print('There was an error with your credentials with code: '.strval($code).'.');
                _print('Please check your credentials and run this script again'.PHP_EOL.PHP_EOL);
                exit();
            }//End of if
        }//End of foreach
    }//End of while 
    $options = [
        'id_credential' => $sat_credentials->id_credential,
    ];//End of $options
    $sat_transactions = paybook\Transaction::get($session, null, $options);
    _print('SAT transactions: '.strval(count($sat_transactions)));
    $sat_attachments = paybook\Attachment::get($session, null, null, null, $options);
    _print('SAT attachments: '.strval(count($sat_attachments)));
    if (count($sat_attachments) > 0) {
        _print('Getting a SAT attachment');
        $url = $sat_attachments[0]->url;
        $id_attachment = substr($url, 1, strlen($url));
        $attachment = paybook\Attachment::get($session, null, $id_attachment);
        print_r($attachment);
    }//End of if
} catch (paybook\Error $e) {
    _print('Paybook error: ');
    _print($e->get_code().' '.$e->get_message(), 1);
}//End of try
