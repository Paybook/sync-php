<?php

declare (strict_types=1);

use PHPUnit\Framework\TestCase;

/*
 * @covers Credentials
 */

paybook\Paybook::init(true);

final class CredentialsTest extends TestCase
{
    const TEST_USERNAME = 'php_lib_test_user';
    const ID_SITE_NORMAL_BANK = '56cf5728784806f72b8b4568';
    const ID_SITE_TOKEN_BANK = '56cf5728784806f72b8b4569';
    const BANK_ID_SITE = '5731fb37784806a6118b4571';#Santander
    private static $testing_user = null;
    private static $testing_session = null;
    private static $testing_credentials = null;
    private static $id_normal_credentials = null;
    private static $id_token_credentials = null;

    public function testCreateCredentials()
    {
        global $Utilities;
        global $TESTING_CONFIG;

        $name = self::TEST_USERNAME;
        $user = new paybook\User($name);

        self::$testing_user = $user;

        $session = new paybook\Session($user);

        self::$testing_session = $session;

        $params = [
            'username' => 'test',
            'password' => 'test',
        ];

        $id_site = self::ID_SITE_NORMAL_BANK;
        $credentials = new paybook\Credentials($session, null, $id_site, $params);

        self::$testing_credentials = $credentials;

        /*
        Check credentials instance structure:
        */
        $this->assertInstanceOf(paybook\Credentials::class, $credentials);

        /*
        Check credentials instance structure and content:
        */
        $Utilities['assertAPIObject']($this, $TESTING_CONFIG['responses']['credentials'], $credentials);
    }

    public function testGetCredentials()
    {
        global $Utilities;
        global $TESTING_CONFIG;

        $session = self::$testing_session;
        $credentials_list = paybook\Credentials::get($session);

        $this->assertInternalType('array', $credentials_list);

        /*
        Get credentials should retrieve just the created one:
        */
        $this->assertEquals(1, count($credentials_list));

        $credentials = $credentials_list[0];

        /*
        Check credentials instance structure:
        */
        $this->assertInstanceOf(paybook\Credentials::class, $credentials);

        /*
        Check credentials instance structure and content:
        */
        $Utilities['assertAPIObject']($this, $TESTING_CONFIG['responses']['credentials'], $credentials);
    }

    public function testDeleteCredentials()
    {
        $session = self::$testing_session;
        $credentials = self::$testing_credentials;
        $credentials_deleted = paybook\Credentials::delete($session, null, $credentials->id_credential);
        $this->assertEquals(1, $credentials_deleted);
    }

    public function testCredentialsStatus()
    {
        global $Utilities;
        global $TESTING_CONFIG;

        $session = self::$testing_session;

        $params = [
            'username' => 'test',
            'password' => 'test',
        ];

        $id_site = self::ID_SITE_NORMAL_BANK;
        $credentials = new paybook\Credentials($session, null, $id_site, $params);

        $wait = false;
        $got_status = null;
        $try = 1;
        while (!$wait) {
            $status = $credentials->get_status($session);

            $this->assertInternalType('array', $status);

            foreach ($status as $index => $each_status) {
                $code = $each_status['code'];
                if ($code == 200) {
                    $wait = true;
                    $got_status = $each_status;
                }//End of for
            }//End of foreach
            sleep(1);
            ++$try;
            if ($try == 10) {
                break;
            }//End of if
        }//End of while 

        $this->assertEquals(200, $got_status['code']);

        self::$id_normal_credentials = $credentials->id_credential;
    }

    public function testCredentialsTwofa()
    {
        global $Utilities;
        global $TESTING_CONFIG;

        $session = self::$testing_session;

        $params = [
            'username' => 'test',
            'password' => 'test',
        ];

        $id_site = self::ID_SITE_TOKEN_BANK;
        $credentials = new paybook\Credentials($session, null, $id_site, $params);

        $status_410 = $credentials->wait_for_status($session, 410);

        $this->assertEquals(410, $status_410['code']);
        $this->assertInternalType('array', $status_410);

        $twofa = $status_410['twofa'][0];
        $label = $twofa['label'];

        $credentials->set_twofa($session, null, 'test');

        $status_102 = $credentials->wait_for_status($session, 102);

        $this->assertEquals(102, $status_102['code']);
        $this->assertInternalType('array', $status_102);

        $status_200 = $credentials->wait_for_status($session, 200);

        $this->assertEquals(200, $status_200['code']);
        $this->assertInternalType('array', $status_200);

        self::$id_token_credentials = $credentials->id_credential;
    }

    public function testSyncExistingCredentialsAndStatus()
    {
        global $Utilities;
        global $TESTING_CONFIG;

        $session = self::$testing_session;
        $id_normal_credentials = self::$id_normal_credentials;
        $credentials = new paybook\Credentials();
        $credentials->sync($session, null, $id_normal_credentials);

        $wait = false;
        $got_status = null;
        $try = 1;
        while (!$wait) {
            // print_r('Get status '.$credentials->status.'?token='.$session->token.PHP_EOL);
            $status = $credentials->get_status($session);
            // print_r($status);
            $this->assertInternalType('array', $status);

            foreach ($status as $index => $each_status) {
                $code = $each_status['code'];
                if ($code == 200) {
                    $wait = true;
                    $got_status = $each_status;
                }//End of for
            }//End of foreach
            ++$try;
            if ($try == 20) {
                break;
            }//End of if
            sleep(1);
        }//End of while 

        $this->assertEquals(200, $got_status['code']);

        $credentials_deleted = paybook\Credentials::delete($session, null, $credentials->id_credential);
        $this->assertEquals(1, $credentials_deleted);
    }

    public function testSyncExistingCredentialsAndTwofa()
    {
        global $Utilities;
        global $TESTING_CONFIG;

        $session = self::$testing_session;

        $id_token_credentials = self::$id_token_credentials;
        $credentials = new paybook\Credentials();

        $credentials->sync($session, null, $id_token_credentials);

        $status_410 = $credentials->wait_for_status($session, 410);

        $this->assertEquals(410, $status_410['code']);
        $this->assertInternalType('array', $status_410);

        $twofa = $status_410['twofa'][0];
        $label = $twofa['label'];

        $credentials->set_twofa($session, null, 'test');

        $status_102 = $credentials->wait_for_status($session, 102);

        $this->assertEquals(102, $status_102['code']);
        $this->assertInternalType('array', $status_102);

        $status_200 = $credentials->wait_for_status($session, 200);

        $this->assertEquals(200, $status_200['code']);
        $this->assertInternalType('array', $status_200);

        $credentials_deleted = paybook\Credentials::delete($session, null, $credentials->id_credential);
        $this->assertEquals(1, $credentials_deleted);
    }

    public function testSyncExistingRealCredentials()
    {
        global $Utilities;

        global $TESTING_CONFIG;

        $id_user = $TESTING_CONFIG['id_user'];

        $user = new paybook\User(null, $id_user);

        $session = new paybook\Session($user);

        $credentials_list = paybook\Credentials::get($session);

        $bank_credentials = null;
        foreach ($credentials_list as $i => $credentials) {
            if ($credentials->id_site == self::BANK_ID_SITE) {
                # Santander
                $bank_credentials = $credentials;
                break;
            }
        }

        if (is_null($bank_credentials)) {
            exit(PHP_EOL.'   --> TESTING COULD NOT CONTINUE. id_user does not have Bank Credentials'.PHP_EOL.PHP_EOL);
        }

        $id_bank_credentials = $bank_credentials->id_credential;
        // $limit = 5;

        // $options = [
        //     'id_credential' => $id_bank_credentials,
        //     'order' => '-dt_transaction',
        //     'limit' => $limit,
        // ];

        // $transactions = paybook\Transaction::get($session, null, $options);
        // $this->assertInternalType('array', $transactions);

        // print_r(PHP_EOL.'Last '.$limit.' transactions: '.PHP_EOL);
        // foreach ($transactions as $i => $transaction) {
        //     print_r($i.'. '.date('Y-m-d H:i:s', $transaction->dt_transaction).' '.$transaction->description.' '.$transaction->amount.PHP_EOL);
        // }

        $real_credentials = new paybook\Credentials();
        // print_r(PHP_EOL.'Sync again'.PHP_EOL);
        $real_credentials->sync($session, null, $id_bank_credentials);

        $wait = false;
        $got_status = null;
        $try = 1;
        while (!$wait) {
            $status = $real_credentials->get_status($session);

            // print_r('Status: '.PHP_EOL);
            // print_r($status);
            $this->assertInternalType('array', $status);

            foreach ($status as $index => $each_status) {
                $code = $each_status['code'];
                if (($code >= 200 && $code < 300) || ($code >= 400 && $code < 500)) {
                    $wait = true;
                    $got_status = $each_status;
                }//End of for
            }//End of foreach
            ++$try;
            if ($try == 20) {
                break;
            }//End of if
            sleep(3);
        }//End of while 

        $this->assertInternalType('integer', $got_status['code']);
    }

    public function testDeleteCredentialsUser()
    {
        $user = self::$testing_user;
        $user_deleted = paybook\User::delete($user->id_user);

        $this->assertEquals(1, $user_deleted);
    }
}
