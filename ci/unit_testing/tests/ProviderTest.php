<?php

declare (strict_types=1);

use PHPUnit\Framework\TestCase;

/*
 * @covers Taxpayer
 */

paybook\Paybook::init(true);

final class ProviderTest extends TestCase
{
    public function testGetProviders()
    {
        global $TESTING_CONFIG;
        global $Utilities;

        $id_user = $TESTING_CONFIG['id_user'];

        $user = new paybook\User(null, $id_user);

        $session = new paybook\Session($user);
        $providers = paybook\Provider::get($session);

        $this->assertInternalType('array', $providers);

        $provider = $providers[0];

        /*
        Check provider instance type:
        */

        $this->assertInstanceOf(paybook\Provider::class, $provider);

        /*
        Check provider instance structure and content:
        */
        $Utilities['assertAPIObject']($this, $TESTING_CONFIG['responses']['providers'], $provider);
    }
}
