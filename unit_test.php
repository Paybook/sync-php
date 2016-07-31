<?php

require_once realpath(__DIR__.DIRECTORY_SEPARATOR.'paybook').DIRECTORY_SEPARATOR.'SDK.php';

use paybook as pb;

function _print($message)
{
    print_r($message.PHP_EOL);
}//End of _print

function print_an_item($items)
{
    foreach ($items as $index => $item) {
        print_r($item);
        break;
    }//End of foreach
}//End of print_an_item

try {
    $API_KEY = '7167a4a04f660f9131bafc949e8ca0fe';

    pb\Paybook::init($API_KEY, $print_calls = true, $log = true);

    // $USERNAME = 'php1';
    // $users = pb\User::get();
    // _print('Users: '.strval(count($users)));

    // $new_user = new pb\User($name = $USERNAME);

    // print_r($new_user);

    // $users = pb\User::get();
    // _print('Users: '.strval(count($users)));

    // foreach ($users as $index => $user) {
    //     $id_user = $user->id_user;
    //     $name_user = $user->name;
    //     if ($name_user == $USERNAME) {
    //         $user_deleted = pb\User::delete($id_user);
    //         _print('User '.$name_user.' deleted '.strval($user_deleted));
    //     }//End of if
    // }//End of foreach

    // _print('After deleting users');
    $users = pb\User::get();
    _print('Users: '.strval(count($users)));

    $user = $users[0];

    _print('');
    _print('SESSSIONS: ');
    _print('');
    _print('Creating session ... ');
    $session = new pb\Session($user);
    $token = $session->token;
    _print('Token: '.$token);
    _print('Verifiying session ... ');
    $session_verified = $session->verify();
    _print('Session verified: '.strval($session_verified));
    _print('Deleting session ... ');
    $session_deleted = pb\Session::delete($token);
    _print('Session deleted: '.strval($session_deleted));
    _print('Verifiying deleted session ... ');
    try {
        $session_verified = $session->verify();
    } catch (pb\Error $e) {
        $code = $e->get_array()['code'];
        if ($code == 401) {
            _print('Session deleted verified: '.strval($code));
        }//End of if
    }//End of try
    _print('Creating a session again ... ');
    $session = new pb\Session($user);
    $token = $session->token;
    _print('Token: '.$token);
    // _print('');
    // _print('ACCOUNTS: ');
    // _print('');
    // $accounts = pb\Account::get($session);
    // _print('Accounts: '.strval(count($accounts)));
    // _print('');
    // _print('TRANSACTIONS: ');
    // _print('');
    // $transactions_count = pb\Transaction::get_count($session);
    // _print('Transactions count: '.strval($transactions_count));
    // $transactions = pb\Transaction::get($session);
    // _print('Transactions: '.strval(count($transactions)));
    _print('');
    _print('CATALOGES: ');
    _print('');
    $items = pb\Catalogues::get_account_types($session);
    _print('Account types: '.strval(count($items)));
    $items = pb\Catalogues::get_attachment_types($session);
    _print('Attachment types: '.strval(count($items)));
    $items = pb\Catalogues::get_countries($session);
    _print('Countries: '.strval(count($items)));
    $items = pb\Catalogues::get_sites($session);
    _print('Sites: '.strval(count($items)));
    $items = pb\Catalogues::get_site_organizations($session);
    _print('Site organizations: '.strval(count($items)));
} catch (pb\Error $e) {
    _print('Paybook Error: '.$e->get_array()['message'].' '.strval($e->get_array()['code']));
}//End of try
