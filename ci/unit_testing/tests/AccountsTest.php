<?php

declare (strict_types=1);

use PHPUnit\Framework\TestCase;

/*
 * @covers Accounts
 */

paybook\Paybook::init(true);

final class AccountsTest extends TestCase
{
    private static $testing_user = null;

    public function testGetAccounts()
    {
        global $TESTING_CONFIG;
        global $Utilities;

        $id_user = $TESTING_CONFIG['id_user'];

        $user = new paybook\User(null, $id_user);

        self::$testing_user = $user;

        $session = new paybook\Session($user);
        $accounts = paybook\Account::get($session);

        $this->assertInternalType('array', $accounts);

        if (count($accounts) == 0) {
            exit(PHP_EOL.'   --> TESTING COULD NOT CONTINUE. id_user does not have Accounts for testing'.PHP_EOL.PHP_EOL);
        }

        $account = $accounts[0];

        /*
        Check account instance type:
        */
        $this->assertInstanceOf(paybook\Account::class, $account);

        /*
        Check account instance structure and content:
        */
        $Utilities['assertAPIObject']($this, $TESTING_CONFIG['responses']['accounts'], $account);
    }
}
