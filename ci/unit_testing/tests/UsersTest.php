<?php

declare (strict_types=1);

use PHPUnit\Framework\TestCase;

/*
 * @covers Users
 */

paybook\Paybook::init(true);

final class UsersTest extends TestCase
{
    const TEST_USERNAME = 'php_lib_test_user';
    const TEST_ID_EXTERNAL = 'php_lib_id_external';
    const UPDATE_STRING = '_updated';
    private static $testing_id_user = null;
    private static $users_count = null;

    public function testGetUsers()
    {
        global $Utilities;
        global $TESTING_CONFIG;

        $users = paybook\User::get();
        $this->assertInternalType('array', $users);
        $this->assertGreaterThan(0, count($users), 'Test requires an API key with 1 user at least');
        self::$users_count = count($users);

        if (count($users) == 0) {
            exit(PHP_EOL.'   --> TESTING COULD NOT CONTINUE. api_key does not have Users for testing'.PHP_EOL.PHP_EOL);
        }

        $user = $users[0];

        /*
        Check user instance type:
        */
        $this->assertInstanceOf(paybook\User::class, $user);

        /*
        Check user instance structure and content:
        */
        $Utilities['assertAPIObject']($this, $TESTING_CONFIG['responses']['users'], $user);
    }

    public function testCreateUser()
    {
        global $Utilities;
        global $TESTING_CONFIG;

        $name = self::TEST_USERNAME;
        $id_external = self::TEST_ID_EXTERNAL;
        $user = new paybook\User($name, null, $id_external);

        /*
        Check user instance type:
        */
        $this->assertInstanceOf(paybook\User::class, $user);

        /*
        Check user instance structure and content:
        */
        $Utilities['assertAPIObject']($this, $TESTING_CONFIG['responses']['users'], $user);

        /*
        Do specific-purpose test:
        */
        $this->assertEquals(self::TEST_USERNAME, $user->name);
        $this->assertEquals(self::TEST_ID_EXTERNAL, $user->id_external);

        self::$testing_id_user = $user->id_user;
    }

    public function testGetUsersAfterCreation()
    {
        $users = paybook\User::get();
        /*
        User count should be the same plus one:
        */
        $this->assertEquals(self::$users_count + 1, count($users));
    }

    public function testUpdateUser()
    {
        global $Utilities;
        global $TESTING_CONFIG;

        $name = self::TEST_USERNAME.self::UPDATE_STRING;
        $id_user = self::$testing_id_user;
        $id_external = self::TEST_ID_EXTERNAL.self::UPDATE_STRING;
        $user = new paybook\User($name, $id_user, $id_external);

        /*
        Check user instance structure:
        */
        $this->assertInstanceOf(paybook\User::class, $user);

        /*
        Check user instance structure and content:
        */
        $Utilities['assertAPIObject']($this, $TESTING_CONFIG['responses']['users'], $user);

        /*
        Do specific-purpose test:
        */
        $this->assertEquals(self::TEST_USERNAME.self::UPDATE_STRING, $user->name);
        $this->assertEquals(self::TEST_ID_EXTERNAL.self::UPDATE_STRING, $user->id_external);
    }

    public function testGetUsersAfterUpdating()
    {
        $users = paybook\User::get();
        /*
        User count should be the same plus one:
        */
        $this->assertEquals(self::$users_count + 1, count($users));
    }

    public function testCreateUserWithExistingIdExternal()
    {
        /*
        Check id_external existance error:
        */
        $this->expectException(paybook\Error::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('User already register');

        $name = self::TEST_USERNAME;
        $id_external = self::TEST_ID_EXTERNAL.self::UPDATE_STRING;// This id_external already exists
        $user = new paybook\User($name, null, $id_external);
    }

    public function testDeleteUser()
    {
        $user_deleted = paybook\User::delete(self::$testing_id_user);

        $this->assertEquals(1, $user_deleted);
    }

    public function testGetUsersAfterDeletion()
    {
        $users = paybook\User::get();
        /*
        User count should be the same as the first step (testGetUsers):
        */
        $this->assertEquals(self::$users_count, count($users));
    }
}
