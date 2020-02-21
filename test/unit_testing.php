<?php

/*
Uncomment this to run it as external library:
*/
require __DIR__.'/../vendor/autoload.php';
// --------

/*
Uncomment this to run it with source code:
$source_dir = __DIR__.'/../src/paybook/';
require $source_dir.'Paybook.php';
require $source_dir.'Account.php';
require $source_dir.'Attachment.php';
require $source_dir.'Catalogues.php';
require $source_dir.'Credentials.php';
require $source_dir.'Session.php';
require $source_dir.'Transaction.php';
require $source_dir.'User.php';
*/
// --------

$INDENT = '   ';
$SITE_ORGANIZATIONS_NAME_BY_ID = [];
$SITES_NAME_BY_ID = [];
$TEST_USERNAME = 'test';
$TEST_PASSWORD = 'test';
$INDENT = '   ';
$PAYBOOK_API_KEY = 'YOUR_API_KEY';
$USERNAME = 'PHP_LIBRARY_TEST_USER';

function _print($message, $indents = 0)
{
    global $INDENT;
    $indent = '';
    for ($i = 0; $i < $indents; ++$i) {
        $indent = $indent.$INDENT;
    }//End of for
    print_r($indent.$message.PHP_EOL);
}//End of _print

$step = 0;

function print_step($step_message)
{
    global $step;

    ++$step;

    _print(strval($step).'. '.$step_message);
}//End of print_step

function print_catalogue($catalogue, $catalogue_name)
{
    _print($catalogue_name.': '.strval(count($catalogue)), 2);
    foreach ($catalogue as $index => $item) {
        _print(($index + 1).'. '.$item->name, 3);
    }//End of foreach
}//End of print_catalogue

function print_accounts($accounts)
{
    foreach ($accounts as $index => $account) {
        _print(($index + 1).'. '.$account->name, 2);
    }//End of foreach
}//End of print_accounts

function print_credentials($credential_list)
{
    global $SITES_NAME_BY_ID;
    global $SITE_ORGANIZATIONS_NAME_BY_ID;
    _print('Credentials: '.strval(count($credential_list)), 2);
    foreach ($credential_list as $index => $credentials) {
        $id_site = $credentials->id_site;
        $id_site_organization = $credentials->id_site_organization;
        $site_name = $SITES_NAME_BY_ID[$id_site];
        $site_organization_name = $SITE_ORGANIZATIONS_NAME_BY_ID[$id_site_organization];
        _print(($index + 1).'. '.$site_organization_name.' - '.$site_name.' - '.$credentials->id_credential, 3);
    }//End of foreach
}//End of print_credentials

function get_test_site($test_sites, $test_site_name)
{
    // Could be: "Normal", "Token", "Error", "Token & captcha" or "Multiple Image"
    foreach ($test_sites as $index => $test_site) {
        if ($test_site->name == $test_site_name) {
            return $test_site;
        }//End of if
    }//End of foreach
}//End of get_test_site

function set_catalogues_by_id($sites, $site_organizations)
{
    global $SITES_NAME_BY_ID;
    global $SITE_ORGANIZATIONS_NAME_BY_ID;
    foreach ($sites as $index => $site) {
        if (!array_key_exists($site->id_site, $SITES_NAME_BY_ID)) {
            $SITES_NAME_BY_ID[$site->id_site] = $site->name;
        }//End of if
    }//End of if
    foreach ($site_organizations as $index => $site_organization) {
        if (!array_key_exists($site_organization->id_site_organization, $SITE_ORGANIZATIONS_NAME_BY_ID)) {
            $SITE_ORGANIZATIONS_NAME_BY_ID[$site_organization->id_site_organization] = $site_organization->name;
        }//End of if
    }//End of if
}//End of set_catalogues_by_id

function print_status($status)
{
    $status_str = ' -> ';
    foreach ($status as $index => $each_status) {
        $code = $each_status['code'];
        $status_str = $status_str.$code.', ';
    }//End of foreach
    $status_str = substr($status_str, 0, strlen($status_str) - 2);
    _print($status_str, 3);
}//End of print_status

function wait_for_status($credentials, $session, $status_code)
{
    $wait = false;
    $got_status = null;
    _print('Polling for status '.strval($status_code), 2);
    while (!$wait) {
        $status = $credentials->get_status($session);
        print_status($status);
        foreach ($status as $index => $each_status) {
            $code = $each_status['code'];
            if ($code == $status_code) {
                $wait = true;
                $got_status = $each_status;
            }//End of for
        }//End of foreach
        sleep(3);
    }//End of while 
    return $got_status;
}//End of wait_for_status

try {
    _print(PHP_EOL.'***** PAYBOOK PHP LIBRARY ***** ');
    _print(PHP_EOL.'***** UNIT TESTING SCRIPT ***** '.PHP_EOL);

    _print(PHP_EOL.' -> INIT '.PHP_EOL);

    print_step('Init library with incorrect API key');
    paybook\Paybook::init('this_is_an_incorrect_api_key');

    print_step('Performs a call to API [ User.get() ]');
    try {
        $users = paybook\User::get();
    } catch (paybook\Error $e) {
        if ($e->get_code() == 401) {
            _print($e->get_code().' '.$e->get_message(), 2);
        } else {
            throw $e;
        }//End of if
    }//End of try

    print_step('Init library with correct API key');
    paybook\Paybook::init($PAYBOOK_API_KEY, true);

    _print(PHP_EOL.' -> USERS ENDPOINTS '.PHP_EOL);

    print_step('Get user lists');
    $users = paybook\User::get();
    $users_count = count($users);

    if ($users_count == 0) {
        throw new Exception('Unit testing could not continue (API Key users == 0)', 1);
    }//End of if

    _print('Users: '.strval($users_count), 2);

    print_step('Creates a new user '.$USERNAME);
    $new_user = new paybook\User($USERNAME);
    _print('Id user: '.$new_user->id_user, 2);
    _print('Name:    '.$new_user->name, 2);

    print_step('Get user lists again (after creation)');
    $users = paybook\User::get();
    $users_count_after_creation = count($users);
    if ($users_count != ($users_count_after_creation - 1)) {
        throw new Exception('Error validating user creation', 1);
    }//End of if
    _print('Users: '.strval($users_count_after_creation), 2);

    print_step('Deletes the user created before');
    $user_deleted = paybook\User::delete($new_user->id_user);
    _print('User '.$new_user->name.' with id '.$new_user->id_user.' deleted '.strval($user_deleted), 2);

    print_step('Get user lists again (after deletion)');
    $users = paybook\User::get();
    $users_count_after_deletion = count($users);
    if ($users_count != $users_count_after_deletion) {
        throw new Exception('Error validating user deletion', 1);
    }//End of if
    _print('Users: '.strval($users_count_after_deletion), 2);

    print_step('Creates a new user '.$USERNAME.' (again)');
    $new_user = new paybook\User($USERNAME);
    _print('Id user: '.$new_user->id_user, 2);
    _print('Name:    '.$new_user->name, 2);

    print_step('Creates a new user using an id_user of a existing user)');
    $id_user = $new_user->id_user;
    $user = new paybook\User(null, $id_user);
    _print('Id user: '.$user->id_user, 2);
    _print('Name:    '.$user->name, 2);

    _print(PHP_EOL.' -> SESSION ENDPOINTS '.PHP_EOL);

    print_step('Creates a new session for '.$user->name);
    $session = new paybook\Session($user);
    _print('Token:    '.$session->token, 2);

    print_step('Verify session');
    $session_verified = $session->verify();
    _print('Session verfied: '.strval($session_verified), 2);

    print_step('Delete created session');
    $session_deleted = paybook\Session::delete($session->token);
    _print('Session deleted: '.strval($session_deleted), 2);

    print_step('Verify deleted session');
    try {
        $session_verified = $session->verify();
        _print('Session verfied: '.strval($session_verified), 2);
    } catch (paybook\Error $e) {
        _print($e->get_code().' '.$e->get_message(), 2);
    }//End of try

    print_step('Creates a new session again for '.$user->name);
    $session = new paybook\Session($user);
    _print('Token:    '.$session->token, 2);

    _print(PHP_EOL.' -> ACCOUNTS ENDPOINTS '.PHP_EOL);

    print_step('Getting user accounts');
    $accounts = paybook\Account::get($session);
    print_accounts($accounts);

    _print(PHP_EOL.' -> CATALOGUES ENDPOINTS '.PHP_EOL);

    print_step('(Production) Get account types');
    $account_types = paybook\Catalogues::get_account_types($session);
    print_catalogue($account_types, 'Account types');

    print_step('(Production) Get attachment types');
    $attachment_types = paybook\Catalogues::get_attachment_types($session);
    print_catalogue($attachment_types, 'Attachment types');

    print_step('(Production) Get countries');
    $countries = paybook\Catalogues::get_countries($session);
    print_catalogue($countries, 'Countries');

    print_step('(Production) Get sites');
    $sites = paybook\Catalogues::get_sites($session);
    print_catalogue($sites, 'Sites');

    print_step('(Production) Get site organizations');
    $site_organizations = paybook\Catalogues::get_site_organizations($session);
    print_catalogue($site_organizations, 'Site organizations');

    set_catalogues_by_id($sites, $site_organizations);

    print_step('(Test) Get sites');
    $test_sites = paybook\Catalogues::get_sites($session, null, null, true);
    print_catalogue($test_sites, 'Sites');

    print_step('(Test) Get site organizations');
    $test_site_organizations = paybook\Catalogues::get_site_organizations($session, null, null, true);
    print_catalogue($test_site_organizations, 'Site organizations');

    set_catalogues_by_id($test_sites, $test_site_organizations);

    print_step('Getting sites for testing credentials');
    $test_site_normal = get_test_site($test_sites, 'Normal');
    $test_site_token = get_test_site($test_sites, 'Token');
    _print('Test normal: '.$test_site_normal->name.' -> '.$test_site_normal->id_site, 2);
    _print('Test token: '.$test_site_token->name.' -> '.$test_site_token->id_site, 2);

    _print(PHP_EOL.' -> CREDENTIALS ENDPOINTS '.PHP_EOL);

    print_step('Getting user credentials');
    $credentials_list = paybook\Credentials::get($session);
    print_credentials($credentials_list);

    print_step('Create normal credentials');
    $normal_credentials_params = [
        'username' => $TEST_USERNAME,
        'password' => $TEST_PASSWORD,
    ];//End of normal_credentials_params
    $new_normal_credentials = new paybook\Credentials($session, null, $test_site_normal->id_site, $normal_credentials_params);
    _print('Id credential:    '.$new_normal_credentials->id_credential, 2);
    _print('Username:         '.$new_normal_credentials->username, 2);
    _print('Web Socket:       '.$new_normal_credentials->ws, 2);
    _print('Status:           '.$new_normal_credentials->status, 2);
    _print('TwoFA:            '.$new_normal_credentials->twofa, 2);

    print_step('Getting user credentials (again)');
    $credentials_list = paybook\Credentials::get($session);
    print_credentials($credentials_list);

    print_step('Deletes the user credentials created');
    $credentials_delted = paybook\Credentials::delete($session, null, $new_normal_credentials->id_credential);
    _print('Credentials deleted: '.strval($credentials_delted), 2);

    print_step('Getting user credentials (again)');
    $credentials_list = paybook\Credentials::get($session);
    print_credentials($credentials_list);

    _print('Create normal credentials again ... ');
    $normal_credentials_params = [
        'username' => $TEST_USERNAME,
        'password' => $TEST_PASSWORD,
    ];//End of normal_credentials_params
    $normal_credentials = new paybook\Credentials($session, null, $test_site_normal->id_site, $normal_credentials_params);
    _print('Id credential:    '.$normal_credentials->id_credential, 2);
    _print('Username:         '.$normal_credentials->username, 2);
    _print('Web Socket:       '.$normal_credentials->ws, 2);
    _print('Status:           '.$normal_credentials->status, 2);
    _print('TwoFA:            '.$normal_credentials->twofa, 2);

    print_step('Getting user credentials (again)');
    $credentials_list = paybook\Credentials::get($session);
    print_credentials($credentials_list);

    print_step('Waiting normal credentials to sync... ');
    $status_200 = wait_for_status($normal_credentials, $session, 200);
    _print('Normal credentials syncrhonized \\0/', 3);

    print_step('Create token credentials ... ');
    $token_credentials_params = [
        'username' => $TEST_USERNAME,
        'password' => $TEST_PASSWORD,
    ];//End of token_credentials_params
    $token_credentials = new paybook\Credentials($session, null, $test_site_token->id_site, $token_credentials_params);
    _print('Id credential:    '.$token_credentials->id_credential, 2);
    _print('Username:         '.$token_credentials->username, 2);
    _print('Web Socket:       '.$token_credentials->ws, 2);
    _print('Status:           '.$token_credentials->status, 2);
    _print('TwoFA:            '.$token_credentials->twofa, 2);

    print_step('Getting user credentials (again)');
    $credentials_list = paybook\Credentials::get($session);
    print_credentials($credentials_list);

    print_step('Waits for token request');
    $status_410 = wait_for_status($token_credentials, $session, 410);

    print_step('Displays token labels');
    $twofa = $status_410['twofa'][0];
    $label = $twofa['label'];
    _print($label.': '.$TEST_PASSWORD, 2);

    print_step('Sends twofa (token)');
    $token_credentials->set_twofa($session, null, $TEST_PASSWORD);

    print_step('Waits for sync to start');
    $status_102 = wait_for_status($token_credentials, $session, 102);

    print_step('Token accepted. Waiting token credentials to sync... ');
    $status_200 = wait_for_status($token_credentials, $session, 200);
    _print('Token credentials syncrhonized \\0/', 3);

    _print(PHP_EOL.' -> TRANSACTION ENDPOINTS '.PHP_EOL);

    print_step('Getting user transactions count');
    $transactions_count = paybook\Transaction::get_count($session);
    _print('Transactions count: '.strval($transactions_count), 2);

    print_step('Getting user transactions');
    $transactions = paybook\Transaction::get($session);
    _print('Transactions: '.strval(count($transactions)), 2);

    print_step('Getting user transactions count (normal credentials)');
    $options = [
        'id_credential' => $normal_credentials->id_credential,
    ];//End of options
    $transactions_count = paybook\Transaction::get_count($session, null, $options);
    _print('Transactions count: '.strval($transactions_count), 2);

    print_step('Getting user transactions (normal credentials)');
    $transactions = paybook\Transaction::get($session, null, $options);
    _print('Transactions: '.strval(count($transactions)), 2);

    print_step('Getting user transactions count (token credentials)');
    $options = [
        'id_credential' => $token_credentials->id_credential,
    ];//End of options
    $transactions_count = paybook\Transaction::get_count($session, null, $options);
    _print('Transactions count: '.strval($transactions_count), 2);

    print_step('Getting user transactions (token credentials)');
    $transactions = paybook\Transaction::get($session, null, $options);
    _print('Transactions: '.strval(count($transactions)), 2);

    _print(PHP_EOL.' -> ATTACHMENT ENDPOINTS '.PHP_EOL);

    print_step('Getting user attachments count');
    $attachments_count = paybook\Attachment::get_count($session);
    _print('Attachment count: '.strval($attachments_count), 2);

    print_step('Getting user attachments');
    $attachments = paybook\Attachment::get($session);
    _print('Attachments: '.strval(count($attachments)), 2);

    if ($attachments_count > 0) {
        print_step('Getting a user attachment');
        $url = $attachments[0]->url;
        $id_attachment = substr($url, 1, strlen($url));
        $attachment = paybook\Attachment::get($session, null, $id_attachment);
        _print('Attachment content substring: '.substr($attachment, 0, 8), 2);

        print_step('Getting a user attachment extra');
        $extra = paybook\Attachment::get($session, $id_user = null, $id_attachment = $id_attachment, $extra = true);
        _print('Extra attr mime: '.$extra['mime'], 2);
    }//End of if

    _print(PHP_EOL.' -> DELETING UNIT TESTING DATA '.PHP_EOL);

    print_step('Deletes the user created for unit testing');
    $user_deleted = paybook\User::delete($user->id_user);
    _print('User '.$user->name.' with id '.$user->id_user.' deleted '.strval($user_deleted), 2);

    print_step('Get user lists');
    $users = paybook\User::get();
    $users_final_count = count($users);
    if ($users_count == $users_final_count) {
        _print('Final users: '.strval($users_final_count), 2);
        _print('Everythings Ok. Unit testin data created was deleted.', 2);
    }//End of if

    _print(PHP_EOL.PHP_EOL);
    _print('Unit testing completed successfully with '.$step.' steps \\0/', 1);
    _print(PHP_EOL.PHP_EOL);
} catch (paybook\Error $e) {
    _print(PHP_EOL.PHP_EOL);
    _print('Unit testing uncompleted at step '.$step.' :(', 1);
    _print($e->get_code().' '.$e->get_message(), 1);
    _print(PHP_EOL.PHP_EOL);
}//End of try
