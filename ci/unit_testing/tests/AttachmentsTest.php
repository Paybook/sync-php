<?php

declare (strict_types=1);

use PHPUnit\Framework\TestCase;

/*
 * @covers Attachments
 */

/*
Important: get attachments calls were executed with temporal filters to avoid big-sized JSONs responses.
*/

paybook\Paybook::init(true);

final class AttachmentsTest extends TestCase
{
    const FROM = 1483228800;//Jan 1st 2017
    const TO = 1485907200;//Feb 1st 2017
    const WEEK = 669600;// One week in seconds mor or less
    const SAT_ID_SITE = '56cf5728784806f72b8b456f';

    private static $testing_user = null;
    private static $testing_session = null;
    private static $total_attachments_count = null;
    private static $id_transaction = null;
    private static $attachment_url = null;

    public function testGetAttachmentsCount()
    {
        global $TESTING_CONFIG;

        $id_user = $TESTING_CONFIG['id_user'];

        $user = new paybook\User(null, $id_user);

        self::$testing_user = $user;

        $session = new paybook\Session($user);

        self::$testing_session = $session;

        $total_attachments_count = paybook\Attachment::get_count($session);

        $this->assertInternalType('integer', $total_attachments_count);

        if ($total_attachments_count == 0) {
            exit(PHP_EOL.'   --> TESTING COULD NOT CONTINUE. id_user does not have Attachments for testing'.PHP_EOL.PHP_EOL);
        }

        $this->assertGreaterThan(0, $total_attachments_count);
    }

    public function testGetAttachmentsCountWithTemporalFilter()
    {
        $session = self::$testing_session;

        $options = [
            'dt_refresh_from' => self::FROM,
            'dt_refresh_to' => self::TO,
        ];

        $total_attachments_count = paybook\Attachment::get_count($session, null, $options);

        self::$total_attachments_count = $total_attachments_count;

        $this->assertInternalType('integer', $total_attachments_count);

        if ($total_attachments_count == 0) {
            exit(PHP_EOL.'   --> TESTING COULD NOT CONTINUE. id_user does not have Attachments for testing in Jan 2017'.PHP_EOL.PHP_EOL);
        }

        $this->assertGreaterThan(0, $total_attachments_count);
    }

    public function testGetAttachments()
    {
        global $TESTING_CONFIG;
        global $Utilities;
        $session = self::$testing_session;

        $options = [
            'dt_refresh_from' => self::FROM,
            'dt_refresh_to' => self::TO,
        ];

        $attachments = paybook\Attachment::get($session, null, null, null, $options);

        $this->assertInternalType('array', $attachments);
        $this->assertEquals(self::$total_attachments_count, count($attachments));

        $attachment = $attachments[0];

        /*
        Check attachment instance type:
        */
        $this->assertInstanceOf(paybook\Attachment::class, $attachment);

        /*
        Check attachment instance structure and content:
        */

        $Utilities['assertAPIObject']($this, $TESTING_CONFIG['responses']['attachments'], $attachment);

        self::$id_transaction = $attachment->id_transaction;
        self::$attachment_url = $attachment->url;
    }

    public function testGetAttachmentsWithApiKey()
    {
        global $TESTING_CONFIG;
        global $Utilities;

        $user = self::$testing_user;

        $options = [
            'dt_refresh_from' => self::FROM,
            'dt_refresh_to' => self::TO,
        ];

        $attachments = paybook\Attachment::get(null, $user->id_user, null, null, $options);

        $this->assertInternalType('array', $attachments);
    }

    public function testGetAttachmentsFilteredByIdTransaction()
    {
        $session = self::$testing_session;

        $options = [
            'id_transaction' => self::$id_transaction,
        ];
        $attachments = paybook\Attachment::get($session, null, null, null, $options);

        $this->assertInternalType('array', $attachments);
        $this->assertEquals(1, count($attachments));

        $attachment = $attachments[0];

        $this->assertEquals(self::$id_transaction, $attachment->id_transaction);
    }

    public function testGetAttachmentsFilteredByIdAccount()
    {
        $session = self::$testing_session;

        $accounts = paybook\Account::get($session);

        $options = [
            'dt_refresh_from' => self::FROM,
            'dt_refresh_to' => self::TO,
        ];

        $attachments_filtered_by_id_account = 0;
        foreach ($accounts as $i => $account) {
            $options['id_account'] = $account->id_account;
            $attachments = paybook\Attachment::get($session, null, null, null, $options);
            // print_r(PHP_EOL.$account->name.' '.count($attachments));
            $this->assertInternalType('array', $attachments);
            $attachments_filtered_by_id_account = $attachments_filtered_by_id_account + count($attachments);
        }

        /*
        Total attachments should be equal to the sum of each bunch of attachments retrieved by id_account:
        */
        $this->assertEquals(self::$total_attachments_count, $attachments_filtered_by_id_account);
    }

    public function testGetAttachmentsFilteredByIdAttachmentType()
    {
        $session = self::$testing_session;

        $attachment_types = paybook\Catalogues::get_attachment_types($session);

        $options = [
            'dt_refresh_from' => self::FROM,
            'dt_refresh_to' => self::TO,
        ];

        $attachments_filtered_by_id_attachment_type = 0;
        foreach ($attachment_types as $i => $attachment_type) {
            $options['id_attachment_type'] = $attachment_type->id_attachment_type;
            $attachments = paybook\Attachment::get($session, null, null, null, $options);
            // print_r(PHP_EOL.$attachment_type->name.' '.count($attachments));
            $this->assertInternalType('array', $attachments);
            $attachments_filtered_by_id_attachment_type = $attachments_filtered_by_id_attachment_type + count($attachments);
        }

        /*
        Total attachments should be equal to the sum of each bunch of attachments retrieved by id_attachment_type:
        */
        $this->assertEquals(self::$total_attachments_count, $attachments_filtered_by_id_attachment_type);
    }

    public function testGetAttachmentsFilteredByIdCredential()
    {
        $session = self::$testing_session;

        $credentials_list = paybook\Credentials::get($session);

        $options = [
            'dt_refresh_from' => self::FROM,
            'dt_refresh_to' => self::TO,
        ];

        $attachments_filtered_by_id_credential = 0;
        foreach ($credentials_list as $i => $credentials) {
            $options['id_credential'] = $credentials->id_credential;
            $attachments = paybook\Attachment::get($session, null, null, null, $options);
            $this->assertInternalType('array', $attachments);
            // print_r(PHP_EOL.$credentials->username.' '.count($attachments));
            $attachments_filtered_by_id_credential = $attachments_filtered_by_id_credential + count($attachments);
        }

        /*
        Total attachments should be equal to the sum of each bunch of attachments retrieved by id_credential:
        */
        $this->assertEquals(self::$total_attachments_count, $attachments_filtered_by_id_credential);
    }

    public function testGetAttachmentsFilteredByWeek()
    {
        $session = self::$testing_session;

        $attachments_filtered_by_week = 0;
        $begin = self::FROM;
        $end = self::FROM + self::WEEK;

        // print_r(PHP_EOL.'Interval'.PHP_EOL);
        // print_r(date(DATE_RFC2822, self::FROM).' - '.date(DATE_RFC2822, self::TO).PHP_EOL);
        // print_r('Splitted'.PHP_EOL);

        for ($i = 0; $i <= 3; ++$i) {
            $options = [
                'dt_refresh_from' => $begin + 1, //Allows > instead of >=
                'dt_refresh_to' => $end,
            ];

            $attachments = paybook\Attachment::get($session, null, null, null, $options);

            // print_r(date(DATE_RFC2822, $begin).' - '.date(DATE_RFC2822, $end).' = '.count($attachments).PHP_EOL);

            $attachments_filtered_by_week = $attachments_filtered_by_week + count($attachments);

            $begin = $begin + self::WEEK;
            $end = $end + self::WEEK;
        }//End of for

        /*
        Total attachments should be equal to the sum of each bunch of attachments retrieved:
        */
        $this->assertEquals(self::$total_attachments_count, $attachments_filtered_by_week);
    }

    public function testGetAttachmentsWithSkipLimit()
    {
        $session = self::$testing_session;

        $options = [
            'dt_refresh_from' => self::FROM,
            'dt_refresh_to' => self::TO,
        ];

        $batch_size = 8;
        $pages = intval(self::$total_attachments_count / $batch_size) + 1;

        // print_r(PHP_EOL.'Total: '.self::$total_attachments_count);
        $attachments_count = 0;
        $i = 0;

        while ($i < $pages) {
            $options['skip'] = $i * $batch_size;
            $options['limit'] = $batch_size;

            $attachments = paybook\Attachment::get($session, null, null, null, $options);

            // print_r(PHP_EOL.'   Batch '.$i.' --> '.count($attachments));
            //Check all pages to have $batch_size

            if ($i != $pages - 1) {
                $this->assertEquals($batch_size, count($attachments));
            /*
            But not the last one (last one could be batch_size or less)
            */
            } else {
                $this->assertLessThan($batch_size + 1, count($attachments));
            }//End of if

            $attachments_count = $attachments_count + count($attachments);
            $i = $i + 1;
        }//End of for

        /*
        Total attachments should be equal to the sum of each bunch of attachments retrieved:
        */
        $this->assertEquals(self::$total_attachments_count, $attachments_count);
    }

    public function testGetAttachmentsWithKeywordsAndSkipKewords()
    {
        $session = self::$testing_session;

        $credentials_list = paybook\Credentials::get($session);

        $sat_credentials = null;
        foreach ($credentials_list as $i => $credentials) {
            if ($credentials->id_site == self::SAT_ID_SITE && !is_null($credentials->keywords) && count($credentials->keywords) > 0) {
                $sat_credentials = $credentials;
                break;
            }
        }

        if (is_null($sat_credentials)) {
            exit(PHP_EOL.'   --> TESTING COULD NOT CONTINUE. id_user does not have Sat Credentials with keywords (keywords testing could not proceed)'.PHP_EOL.PHP_EOL);
        }

        $options = [
            'dt_refresh_from' => self::FROM,
            'dt_refresh_to' => self::TO,
            'id_credential' => $sat_credentials->id_credential,
        ];

        $total = paybook\Attachment::get_count($session, null, $options);
        // print_r(PHP_EOL.'Total: '.$total);

        $validation = [];
        foreach ($sat_credentials->keywords as $keyword) {
            $options['keywords'] = $keyword;
            $attachments = paybook\Attachment::get($session, null, null, null, $options);
            $validation[$keyword] = count($attachments);
            // print_r(PHP_EOL.'KW   '.$keyword.' -> '.count($attachments));
            break;
        }

        foreach ($sat_credentials->keywords as $keyword) {
            $options['skip_keywords'] = $keyword;
            $attachments = paybook\Attachment::get($session, null, null, null, $options);
            $validation[$keyword] = $validation[$keyword] + count($attachments);
            // print_r(PHP_EOL.'SKW  '.$keyword.' -> '.count($attachments));
            break;
        }

        /*
        The sum of each keyword should be the total always:
        */
        foreach ($validation as $keyword => $value) {
            $this->assertEquals($total, $value);
        }
    }

    public function testGetAttachmentFile()
    {
        $session = self::$testing_session;

        $attachment_url = self::$attachment_url;
        $id_attachment = substr($attachment_url, 1, strlen($attachment_url));
        $attachment = paybook\Attachment::get($session, null, $id_attachment);
        $this->assertInternalType('string', $attachment);
    }

    public function testGetAttachmentExtra()
    {
        $session = self::$testing_session;

        $attachment_url = self::$attachment_url;
        $items = explode('/', $attachment_url);
        $id_attachment = $items[count($items) - 1];
        $extra = paybook\Attachment::get($session, null, $id_attachment, true);
        $this->assertInternalType('array', $extra);
    }
}
