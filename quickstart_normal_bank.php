<?php

require_once realpath(__DIR__.DIRECTORY_SEPARATOR.'paybook').DIRECTORY_SEPARATOR.'SDK.php';

/*
Change this values:
*/
$YOUR_API_KEY = 'YOUR_API_KEY';
$BANK_USERNAME = 'YOUR_BANK_USERNAME';
$BANK_PASSWORD = 'YOUR_BANK_PASSWORD';
$USERNAME = 'MY_USER';
// $BANK_SITE = 'SuperNET Particulares';//Normal bank site of paybook production catalogue
$BANK_SITE = 'Normal';//Normal bank site of paybook test catalogue

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
    // $sites = paybook\Catalogues::get_sites($session);
    $sites = paybook\Catalogues::get_sites($session, null, null, true);
    $bank_site = null;
    _print('Sites list:');
    foreach ($sites as $index => $site) {
        _print($site->name);
        if ($site->name == $BANK_SITE) {
            $bank_site = $site;
        }//End of if
    }//End of foreach
    _print('Bank site: '.$bank_site->id_site.' '.$bank_site->name);
    $credentials_params = [
        'username' => $BANK_USERNAME,
        'password' => $BANK_PASSWORD,
    ];//End of credentials_params
    _print('Creating credentials of '.$bank_site->name);
    $bank_credentials = new paybook\Credentials($session, null, $bank_site->id_site, $credentials_params);
    _print('Credentials username: '.$bank_credentials->username);
    $bank_sync_completed = false;
    while (!$bank_sync_completed) {
        sleep(5);
        $status = $bank_credentials->get_status($session);
        print_status($status);
        foreach ($status as $index => $each_status) {
            $code = $each_status['code'];
            if ($code >= 200 && $code <= 205) {
                $bank_sync_completed = true;
            } elseif ($code >= 400 && $code <= 411) {
                _print('There was an error with your credentials with code: '.strval($code).'.');
                _print('Check the code status in https://www.paybook.com/sync/docs'.PHP_EOL.PHP_EOL);
                exit();
            }//End of if
        }//End of foreach
    }//End of while 
    $options = [
        'id_credential' => $bank_credentials->id_credential,
    ];//End of $options
    $bank_transactions = paybook\Transaction::get($session, null, $options);
    _print('Bank transactions: '.strval(count($bank_transactions)));
    $bank_attachments = paybook\Attachment::get($session, null, null, $options);
    _print('Bank attachments: '.strval(count($bank_attachments)));
    if (count($bank_attachments) > 0) {
        _print('Getting a Bank attachment');
        $url = $bank_attachments[0]->url;
        $id_attachment = substr($url, 1, strlen($url));
        $attachment = paybook\Attachment::get($session, null, $id_attachment);
        print_r($attachment);
    }//End of if
} catch (paybook\Error $e) {
    _print('Paybook error: ');
    _print($e->get_code().' '.$e->get_message(), 1);
}//End of try
