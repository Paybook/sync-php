<?php

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
