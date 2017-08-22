<?php

$config_path = __DIR__.'/config/';

/*

phpunit --debug --bootstrap bootstrap.php tests/

Load testing config
*/
global $TESTING_CONFIG;
$TESTING_CONFIG = json_decode(file_get_contents($config_path.'env.json'), true);
if (is_null($TESTING_CONFIG)) {
    exit(PHP_EOL.'Invalid config.json, check JSON file syntax.'.PHP_EOL.PHP_EOL);
}

/*
Load Sync library
*/
if ($TESTING_CONFIG['src'] === true) {
    /*
    Loads library from src directory (pre-release):
    */
    $source_dir = __DIR__.'/../../src/paybook/';
    require $source_dir.'Paybook.php';
    require $source_dir.'Account.php';
    require $source_dir.'Attachment.php';
    require $source_dir.'Catalogues.php';
    require $source_dir.'Credentials.php';
    require $source_dir.'Session.php';
    require $source_dir.'Transaction.php';
    require $source_dir.'User.php';
    require $source_dir.'Taxpayer.php';
    require $source_dir.'Provider.php';
    require $source_dir.'Invoice.php';
} else {
    /*
    Loads library installed by Composer (released):
    */
    require __DIR__.'/vendor/autoload.php';
}//End of if

/*
Load API responses configuration
*/
$TESTING_CONFIG['responses'] = [];
$TESTING_CONFIG['responses']['accounts'] = json_decode(file_get_contents($config_path.'responses/accounts.json'), true);
$TESTING_CONFIG['responses']['users'] = json_decode(file_get_contents($config_path.'responses/users.json'), true);
$TESTING_CONFIG['responses']['sessions'] = json_decode(file_get_contents($config_path.'responses/sessions.json'), true);
$TESTING_CONFIG['responses']['catalogues']['account_types'] = json_decode(file_get_contents($config_path.'responses/catalogues/account_types.json'), true);
$TESTING_CONFIG['responses']['catalogues']['attachment_types'] = json_decode(file_get_contents($config_path.'responses/catalogues/attachment_types.json'), true);
$TESTING_CONFIG['responses']['catalogues']['countries'] = json_decode(file_get_contents($config_path.'responses/catalogues/countries.json'), true);
$TESTING_CONFIG['responses']['catalogues']['sites'] = json_decode(file_get_contents($config_path.'responses/catalogues/sites.json'), true);
$TESTING_CONFIG['responses']['catalogues']['credentials_structure'] = json_decode(file_get_contents($config_path.'responses/catalogues/credentials_structure.json'), true);
$TESTING_CONFIG['responses']['catalogues']['site_organizations'] = json_decode(file_get_contents($config_path.'responses/catalogues/site_organizations.json'), true);
$TESTING_CONFIG['responses']['credentials'] = json_decode(file_get_contents($config_path.'responses/credentials.json'), true);
$TESTING_CONFIG['responses']['transactions'] = json_decode(file_get_contents($config_path.'responses/transactions.json'), true);
$TESTING_CONFIG['responses']['attachments'] = json_decode(file_get_contents($config_path.'responses/attachments.json'), true);
$TESTING_CONFIG['responses']['taxpayers'] = json_decode(file_get_contents($config_path.'responses/taxpayers.json'), true);
$TESTING_CONFIG['responses']['providers'] = json_decode(file_get_contents($config_path.'responses/providers.json'), true);
$TESTING_CONFIG['responses']['invoices'] = json_decode(file_get_contents($config_path.'responses/invoices.json'), true);

/*
Check config API key and ENV are correct:
*/
global $ID_USER;

$ID_USER = $TESTING_CONFIG['id_user'];

paybook\Paybook::init(true, true);

/*
Check ID_USER exists:
*/

try {
    $users = paybook\User::get();
} catch (paybook\Error $e) {
    exit('Invalid config.json, ENV or API_KEY could be wrong.'.PHP_EOL.PHP_EOL);
}//End of try

$id_user_is_ok = false;
foreach ($users as $i => $user) {
    if ($user->id_user == $ID_USER) {
        $id_user_is_ok = true;
        break;
    }
}

if ($id_user_is_ok === false) {
    exit('Invalid config.json, invalid ID_USER (this should belong to your given API_KEY and ENV).'.PHP_EOL.PHP_EOL);
}

/*
Finally, load testing utilities:
*/
require 'utilities.php';
