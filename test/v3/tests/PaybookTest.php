<?php

declare (strict_types=1);

use PHPUnit\Framework\TestCase;

/*
 * @covers Paybook
 */

/*
phpunit --bootstrap bootstrap.php --debug tests/PaybookTest.php
phpunit --bootstrap bootstrap.php --debug tests
phpunit --bootstrap bootstrap.php --debug --testdox tests
*/

paybook\Paybook::init(true);

final class PaybookTest extends TestCase
{
    public function testSyncApiSuccessResponse()
    {
        $params = [
            'api_key' => paybook\Paybook::$api_key,
        ];//End of $params
        $response = paybook\Paybook::call('users', 'get', $params, null, null, false, true);

        /*
        Test API response headers (code and content type):
        */
        $this->assertEquals(200, $response['http_code']);
        $this->assertEquals('application/json; charset=utf-8', $response['content_type']);

        /*
        Test API success body:
        */
        $body = $response['body'];
        $this->assertArrayHasKey('rid', $body);
        $this->assertArrayHasKey('code', $body);
        $this->assertArrayHasKey('errors', $body);
        $this->assertArrayHasKey('status', $body);
        $this->assertArrayHasKey('message', $body);
        $this->assertArrayHasKey('response', $body);
        $this->assertInternalType('string', $body['rid']);
        $this->assertEquals(200, $body['code']);
        $this->assertNull($body['errors']);
        $this->assertEquals(true, $body['code']);
        $this->assertNull($body['message']);
        $this->assertInternalType('array', $body['response']);
    }

    public function testSyncApiErrorResponse()
    {
        $response = paybook\Paybook::call('users', 'get', [], null, null, false, true);

        /*  
        Test API response headers (code and content type):
        */
        $this->assertNotEquals(200, $response['http_code']);
        $this->assertEquals('application/json; charset=utf-8', $response['content_type']);

        /*
        Test API error body:
        */
        $body = $response['body'];
        $this->assertArrayHasKey('rid', $body);
        $this->assertArrayHasKey('code', $body);
        $this->assertArrayHasKey('errors', $body);
        $this->assertArrayHasKey('status', $body);
        $this->assertArrayHasKey('message', $body);
        $this->assertArrayHasKey('response', $body);
        $this->assertInternalType('string', $body['rid']);
        $this->assertNotEquals(200, $body['code']);
        $this->assertInternalType('string', $body['message']);// Error should have an explanation
    }

    public function testInitializationWithInvalidApiKey()
    {
        $this->expectException(paybook\Error::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Unauthorized');

        /*
        Test Unauthorized error:
        */
        paybook\Paybook::init(false);
        paybook\User::get();
    }

    public function testInitializationWithValidApiKey()
    {
        /*
        Test Authorized response:
        */
        paybook\Paybook::init(true);
        $users = paybook\User::get();
        $this->assertInternalType('array', $users);
    }
}
