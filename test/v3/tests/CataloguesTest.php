<?php

declare (strict_types=1);

use PHPUnit\Framework\TestCase;

/*
 * @covers Catalogues
 */

paybook\Paybook::init(true);

final class CataloguesTest extends TestCase
{
    const ACME_ID_SITE_ORGANIZATION = '56cf4ff5784806152c8b4567';
    private static $testing_user = null;
    private static $testing_session = null;

    public function testGetAccountTypesWithToken()
    {
        global $TESTING_CONFIG;
        global $Utilities;

        $id_user = $TESTING_CONFIG['id_user'];

        $user = new paybook\User(null, $id_user);

        self::$testing_user = $user;

        $session = new paybook\Session($user);

        self::$testing_session = $session;

        $account_types = paybook\Catalogues::get_account_types($session);

        /*
        Check response
        */
        $this->assertInternalType('array', $account_types);

        foreach ($account_types as $i => $account_type) {

            /*
            Check account_type instance type:
            */
            $this->assertInstanceOf(paybook\Account_type::class, $account_type);

            /*
            Check account_type instance structure and content:
            */
            $Utilities['assertAPIObject']($this, $TESTING_CONFIG['responses']['catalogues']['account_types'], $account_type);
        }
    }

    public function testGetAccountTypesWithApiKey()
    {
        global $TESTING_CONFIG;
        global $Utilities;

        $user = self::$testing_user;

        $account_types = paybook\Catalogues::get_account_types(null, $user->id_user);

        $this->assertInternalType('array', $account_types);
    }

    public function testGetAttachmentTypesWithToken()
    {
        global $TESTING_CONFIG;
        global $Utilities;

        $session = self::$testing_session;

        $attachment_types = paybook\Catalogues::get_attachment_types($session);

        /*
        Check response
        */
        $this->assertInternalType('array', $attachment_types);

        foreach ($attachment_types as $i => $attachment_type) {

            /*
            Check attachment_type instance type:
            */
            $this->assertInstanceOf(paybook\Attachment_type::class, $attachment_type);

            /*
            Check attachment_type instance structure and content:
            */
            $Utilities['assertAPIObject']($this, $TESTING_CONFIG['responses']['catalogues']['attachment_types'], $attachment_type);
        }
    }

    public function testGetAttachmentTypesWithApiKey()
    {
        global $TESTING_CONFIG;
        global $Utilities;

        $user = self::$testing_user;

        $attachment_types = paybook\Catalogues::get_attachment_types(null, $user->id_user);

        $this->assertInternalType('array', $attachment_types);
    }

    public function testGetCountriesWithToken()
    {
        global $TESTING_CONFIG;
        global $Utilities;

        $session = self::$testing_session;

        $countries = paybook\Catalogues::get_countries($session);

        /*
        Check response
        */
        $this->assertInternalType('array', $countries);

        foreach ($countries as $i => $country) {

            /*
            Check country instance type:
            */
            $this->assertInstanceOf(paybook\Country::class, $country);

            /*
            Check country instance structure and content:
            */
            $Utilities['assertAPIObject']($this, $TESTING_CONFIG['responses']['catalogues']['countries'], $country);
        }
    }

    public function testGetCountriesWithApiKey()
    {
        global $TESTING_CONFIG;
        global $Utilities;

        $user = self::$testing_user;

        $countries = paybook\Catalogues::get_countries(null, $user->id_user);

        $this->assertInternalType('array', $countries);
    }

    public function testGetSitesWithToken()
    {
        global $TESTING_CONFIG;
        global $Utilities;

        $session = self::$testing_session;

        $sites = paybook\Catalogues::get_sites($session);

        /*
        Check response
        */
        $this->assertInternalType('array', $sites);

        foreach ($sites as $i => $site) {

            /*
            Check site instance type:
            */
            $this->assertInstanceOf(paybook\Site::class, $site);

            /*
            Check site instance structure and content:
            */
            $Utilities['assertAPIObject']($this, $TESTING_CONFIG['responses']['catalogues']['sites'], $site);

            /*
            Check site credentials structure and content:
            */
            $site_credentials = $site->credentials;

            $this->assertGreaterThan(1, count($site_credentials));// Minimum 2 cred per site (user and pass)

            foreach ($site_credentials as $key => $site_credential) {
                $this->assertInstanceOf(paybook\Credentials_structure::class, $site_credential);

                /*
                Check credential_structure instance structure and content:
                */
                $Utilities['assertAPIObject']($this, $TESTING_CONFIG['responses']['catalogues']['credentials_structure'], $site_credential);
            }
        }
    }

    public function testGetSitesWithApiKey()
    {
        global $TESTING_CONFIG;
        global $Utilities;

        $user = self::$testing_user;

        $sites = paybook\Catalogues::get_sites(null, $user->id_user);

        $this->assertInternalType('array', $sites);
    }

    public function testGetSitesOrganizationsWithToken()
    {
        global $TESTING_CONFIG;
        global $Utilities;

        $session = self::$testing_session;

        $site_organizations = paybook\Catalogues::get_site_organizations($session);

        /*
        Check response
        */
        $this->assertInternalType('array', $site_organizations);

        foreach ($site_organizations as $i => $site_organization) {

            /*
            Check site_organization instance type:
            */
            $this->assertInstanceOf(paybook\Site_organization::class, $site_organization);

            /*
            Check site_organization instance structure and content:
            */
            $Utilities['assertAPIObject']($this, $TESTING_CONFIG['responses']['catalogues']['site_organizations'], $site_organization);
        }
    }

    public function testGetSitesOrganizationsWithApiKey()
    {
        global $TESTING_CONFIG;
        global $Utilities;

        $user = self::$testing_user;

        $site_organizations = paybook\Catalogues::get_site_organizations(null, $user->id_user);

        $this->assertInternalType('array', $site_organizations);
    }

    public function testGetTestingSitesWithToken()
    {
        global $TESTING_CONFIG;
        global $Utilities;

        $session = self::$testing_session;

        $sites = paybook\Catalogues::get_sites($session, null, null, true);

        /*
        Check response
        */
        $this->assertInternalType('array', $sites);

        foreach ($sites as $i => $site) {

            /*
            Check site instance type:
            */
            $this->assertInstanceOf(paybook\Site::class, $site);

            /*
            Check all belongs to ACME:
            */
            $this->assertEquals(self::ACME_ID_SITE_ORGANIZATION, $site->id_site_organization);

            /*
            Check site instance structure and content:
            */
            $Utilities['assertAPIObject']($this, $TESTING_CONFIG['responses']['catalogues']['sites'], $site);

            /*
            Check site credentials structure and content:
            */
            $site_credentials = $site->credentials;

            $this->assertGreaterThan(1, count($site_credentials));// Minimum 2 cred per site (user and pass)

            foreach ($site_credentials as $key => $site_credential) {
                $this->assertInstanceOf(paybook\Credentials_structure::class, $site_credential);

                /*
                Check credential_structure instance structure and content:
                */
                $Utilities['assertAPIObject']($this, $TESTING_CONFIG['responses']['catalogues']['credentials_structure'], $site_credential);
            }
        }
    }

    public function testGetTestingSitesWithApiKey()
    {
        global $TESTING_CONFIG;
        global $Utilities;

        $user = self::$testing_user;

        $sites = paybook\Catalogues::get_sites(null, $user->id_user, null, true);

        $this->assertInternalType('array', $sites);
    }

    public function testGetTestingSitesOrganizationsWithToken()
    {
        global $TESTING_CONFIG;
        global $Utilities;

        $session = self::$testing_session;

        $site_organizations = paybook\Catalogues::get_site_organizations($session, null, null, true);

        /*
        Check response
        */
        $this->assertInternalType('array', $site_organizations);

        $this->assertEquals(1, count($site_organizations));//Just ACME

        foreach ($site_organizations as $i => $site_organization) {

            /*
            Check site_organization instance type:
            */
            $this->assertInstanceOf(paybook\Site_organization::class, $site_organization);

            /*
            Check site_organization instance structure and content:
            */
            $Utilities['assertAPIObject']($this, $TESTING_CONFIG['responses']['catalogues']['site_organizations'], $site_organization);
        }
    }

    public function testGetTestingSitesOrganizationsWithApiKey()
    {
        global $TESTING_CONFIG;
        global $Utilities;

        $user = self::$testing_user;

        $site_organizations = paybook\Catalogues::get_site_organizations(null, $user->id_user, null, true);

        $this->assertInternalType('array', $site_organizations);

        $this->assertEquals(1, count($site_organizations));//Just ACME
    }
}
