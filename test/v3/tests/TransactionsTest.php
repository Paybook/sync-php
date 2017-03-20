<?php

declare (strict_types=1);

use PHPUnit\Framework\TestCase;

/*
 * @covers Transactions
 */

/*
Important: get transactions calls were executed with temporal filters to avoid big-sized JSONs responses.
*/

paybook\Paybook::init(true);

final class CredentialsTest extends TestCase
{
    const TEST_USERNAME = 'php_lib_test_user';
    const FROM = 1483228800;//Jan 1st 2017
    const TO = 1485907200;//Feb 1st 2017
    const WEEK = 669600;// One week in seconds mor or less

    private static $testing_user = null;
    private static $testing_session = null;
    private static $id_transaction = null;
    private static $total_transactions_count = null;//Limited to a temporal period

    public function testGetTransactionsCount()
    {
        global $TESTING_CONFIG;

        $id_user = $TESTING_CONFIG['id_user'];

        $user = new paybook\User(null, $id_user);

        self::$testing_user = $user;

        $session = new paybook\Session($user);

        self::$testing_session = $session;

        $total_transactions_count = paybook\Transaction::get_count($session);

        $this->assertInternalType('integer', $total_transactions_count);

        if ($total_transactions_count == 0) {
            exit(PHP_EOL.'   --> TESTING COULD NOT CONTINUE. id_user does not have Transactions for testing'.PHP_EOL.PHP_EOL);
        }

        $this->assertGreaterThan(0, $total_transactions_count);
    }

    public function testGetTransactionsCountWithTemporalFilter()
    {
        global $TESTING_CONFIG;

        $session = self::$testing_session;

        $options = [
            'dt_transaction_from' => self::FROM,
            'dt_transaction_to' => self::TO,
        ];

        $total_transactions_count = paybook\Transaction::get_count($session, null, $options);

        self::$total_transactions_count = $total_transactions_count;

        $this->assertInternalType('integer', $total_transactions_count);

        if ($total_transactions_count == 0) {
            exit(PHP_EOL.'   --> TESTING COULD NOT CONTINUE. id_user does not have Transactions for testing in Jan 2017'.PHP_EOL.PHP_EOL);
        }

        $this->assertGreaterThan(0, $total_transactions_count);
    }

    public function testGetTransactions()
    {
        global $TESTING_CONFIG;
        global $Utilities;
        $session = self::$testing_session;

        $options = [
            'dt_transaction_from' => self::FROM,
            'dt_transaction_to' => self::TO,
        ];

        $transactions = paybook\Transaction::get($session, null, $options);

        $this->assertInternalType('array', $transactions);
        $this->assertEquals(self::$total_transactions_count, count($transactions));

        $transaction = $transactions[0];

        /*
        Check transaction instance type:
        */
        $this->assertInstanceOf(paybook\Transaction::class, $transaction);

        /*
        Check transaction instance structure and content:
        */
        $Utilities['assertAPIObject']($this, $TESTING_CONFIG['responses']['transactions'], $transaction);

        self::$id_transaction = $transaction->id_transaction;
    }

    public function testGetTransactionsFilteredByIdTransaction()
    {
        global $TESTING_CONFIG;
        global $Utilities;
        $session = self::$testing_session;

        $options = [
            'id_transaction' => self::$id_transaction,
        ];
        $transactions = paybook\Transaction::get($session, null, $options);

        $this->assertInternalType('array', $transactions);
        $this->assertEquals(1, count($transactions));

        $transaction = $transactions[0];

        $this->assertEquals(self::$id_transaction, $transaction->id_transaction);
    }

    public function testGetTransactionsFilteredByIdAccount()
    {
        global $TESTING_CONFIG;
        global $Utilities;
        $session = self::$testing_session;

        $accounts = paybook\Account::get($session);

        $options = [
            'dt_transaction_from' => self::FROM,
            'dt_transaction_to' => self::TO,
        ];

        $transactions_filtered_by_id_account = 0;
        foreach ($accounts as $i => $account) {
            $options['id_account'] = $account->id_account;
            $transactions = paybook\Transaction::get($session, null, $options);
            $this->assertInternalType('array', $transactions);
            $transactions_filtered_by_id_account = $transactions_filtered_by_id_account + count($transactions);
        }

        /*
        Total transactions should be equal to the sum of each bunch of transactions retrieved by id_account:
        */
        $this->assertEquals(self::$total_transactions_count, $transactions_filtered_by_id_account);
    }

    public function testGetTransactionsFilteredByIdCredential()
    {
        global $TESTING_CONFIG;
        global $Utilities;
        $session = self::$testing_session;

        $credentials_list = paybook\Credentials::get($session);

        $options = [
            'dt_transaction_from' => self::FROM,
            'dt_transaction_to' => self::TO,
        ];

        $transactions_filtered_by_id_credential = 0;
        foreach ($credentials_list as $i => $credentials) {
            $options['id_credential'] = $credentials->id_credential;
            $transactions = paybook\Transaction::get($session, null, $options);
            $this->assertInternalType('array', $transactions);
            $transactions_filtered_by_id_credential = $transactions_filtered_by_id_credential + count($transactions);
        }

        /*
        Total transactions should be equal to the sum of each bunch of transactions retrieved by id_credential:
        */
        $this->assertEquals(self::$total_transactions_count, $transactions_filtered_by_id_credential);
    }

    public function testGetTransactionsFilteredByIdSite()
    {
        global $TESTING_CONFIG;
        global $Utilities;
        $session = self::$testing_session;

        $sites = paybook\Catalogues::get_sites($session);

        $options = [
            'dt_transaction_from' => self::FROM,
            'dt_transaction_to' => self::TO,
        ];

        $transactions_filtered_by_id_site = 0;
        foreach ($sites as $i => $site) {
            $options['id_site'] = $site->id_site;
            $transactions = paybook\Transaction::get($session, null, $options);
            $this->assertInternalType('array', $transactions);
            $transactions_filtered_by_id_site = $transactions_filtered_by_id_site + count($transactions);
        }

        /*
        Total transactions should be equal to the sum of each bunch of transactions retrieved by id_site:
        */
        $this->assertEquals(self::$total_transactions_count, $transactions_filtered_by_id_site);
    }

    public function testGetTransactionsFilteredByIdSiteOrganization()
    {
        global $TESTING_CONFIG;
        global $Utilities;
        $session = self::$testing_session;

        $site_organizations = paybook\Catalogues::get_site_organizations($session);

        $options = [
            'dt_transaction_from' => self::FROM,
            'dt_transaction_to' => self::TO,
        ];

        $transactions_filtered_by_id_site_organization = 0;
        foreach ($site_organizations as $i => $site_organization) {
            $options['id_site_organization'] = $site_organization->id_site_organization;
            $transactions = paybook\Transaction::get($session, null, $options);
            $this->assertInternalType('array', $transactions);
            $transactions_filtered_by_id_site_organization = $transactions_filtered_by_id_site_organization + count($transactions);
        }

        /*
        Total transactions should be equal to the sum of each bunch of transactions retrieved by id_site_organization:
        */
        $this->assertEquals(self::$total_transactions_count, $transactions_filtered_by_id_site_organization);
    }

    public function testGetTransactionsFilteredByWeek()
    {
        global $TESTING_CONFIG;
        global $Utilities;
        $session = self::$testing_session;

        $transactions_filtered_by_week = 0;
        $begin = self::FROM;
        $end = self::FROM + self::WEEK;

        // print_r(PHP_EOL.'Interval'.PHP_EOL);
        // print_r(date(DATE_RFC2822, self::FROM).' - '.date(DATE_RFC2822, self::TO).PHP_EOL);
        // print_r('Splitted'.PHP_EOL);

        for ($i = 0; $i <= 3; ++$i) {
            $options = [
                'dt_transaction_from' => $begin + 1, //Allows > instead of >=
                'dt_transaction_to' => $end,
            ];

            $transactions = paybook\Transaction::get($session, null, $options);

            // print_r(date(DATE_RFC2822, $begin).' - '.date(DATE_RFC2822, $end).' = '.count($transactions).PHP_EOL);

            $transactions_filtered_by_week = $transactions_filtered_by_week + count($transactions);

            $begin = $begin + self::WEEK;
            $end = $end + self::WEEK;
        }//End of for

        /*
        Total transactions should be equal to the sum of each bunch of transactions retrieved by id_site_organization:
        */
        $this->assertEquals(self::$total_transactions_count, $transactions_filtered_by_week);
    }
}
