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
    private static $testing_user = null;
    private static $testing_session = null;
    private static $testing_credentials = null;

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
        }//End of while 

        $this->assertEquals(200, $got_status['code']);

        $credentials_deleted = paybook\Credentials::delete($session, null, $credentials->id_credential);
        $this->assertEquals(1, $credentials_deleted);
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

        $credentials_deleted = paybook\Credentials::delete($session, null, $credentials->id_credential);
        $this->assertEquals(1, $credentials_deleted);
    }

    public function testDeleteCredentialsUser()
    {
        $user = self::$testing_user;
        $user_deleted = paybook\User::delete($user->id_user);

        $this->assertEquals(1, $user_deleted);
    }
}
