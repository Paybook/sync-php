<?php

declare (strict_types=1);

use PHPUnit\Framework\TestCase;

/*
 * @covers Sessions
 */

paybook\Paybook::init(true);

final class SessionsTest extends TestCase
{
    const TEST_USERNAME = 'php_lib_test_user';
    private static $testing_user = null;
    private static $testing_session = null;
    private static $testing_token = null;

    public function testCreateSession()
    {
        global $Utilities;
        global $TESTING_CONFIG;

        $name = self::TEST_USERNAME;
        $user = new paybook\User($name);

        self::$testing_user = $user;

        $session = new paybook\Session($user);

        self::$testing_session = $session;
        self::$testing_token = $session->token;

        /*
        Check session instance structure:
        */
        $this->assertInstanceOf(paybook\Session::class, $session);

        /*
        Check session instance structure and content:
        */
        $Utilities['assertAPIObject']($this, $TESTING_CONFIG['responses']['sessions'], $session);

        $this->assertObjectHasAttribute('user', $session);
        $this->assertInstanceOf(paybook\User::class, $session->user);
    }

    public function testVerifySession()
    {
        $session = self::$testing_session;
        $session_verified = $session->verify();
        $this->assertEquals(1, $session_verified);
    }

    public function testVerifySessionCreatedWithValidToken()
    {
        $token = self::$testing_token;
        $session = new paybook\Session(null, $token);
        $session_verified = $session->verify();
        $this->assertEquals(1, $session_verified);
    }

    public function testVerifySessionCreatedWithInvalidToken()
    {
        $this->expectException(paybook\Error::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Unauthorized');
        $token = 'invalid_token';
        $session = new paybook\Session(null, $token);
        $session_verified = $session->verify();
    }

    public function testDeleteSession()
    {
        $session = self::$testing_session;
        $session_deleted = paybook\Session::delete($session->token);
        $this->assertEquals(1, $session_deleted);
    }

    public function testDeleteSessionAlreadyDeleted()
    {
        /*
        You can't delete a session that has already been deleted
        */
        $this->expectException(paybook\Error::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Unauthorized');
        $session = self::$testing_session;
        $session_deleted = paybook\Session::delete($session->token);
    }

    public function testDeleteSessionCreatedFromToken()
    {
        $user = self::$testing_user;
        $session = new paybook\Session($user);
        $token = $session->token;
        $token_session = new paybook\Session(null, $token);
        $token_session_deleted = paybook\Session::delete($token_session->token);
        $this->assertEquals(1, $token_session_deleted);
    }

    public function testVerifyDeletedSession()
    {
        /*
        You can't verify a session that has already been deleted
        */
        $this->expectException(paybook\Error::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Unauthorized');
        $session = self::$testing_session;
        $session_verified = $session->verify();
    }

    public function testDeleteSessionUser()
    {
        $user = self::$testing_user;
        $user_deleted = paybook\User::delete($user->id_user);

        $this->assertEquals(1, $user_deleted);
    }
}
