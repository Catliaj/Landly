<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class MessageControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        session()->remove('user_id');
        session()->remove('UserID');
    }

    public function testGetSessionsRequiresAuthentication(): void
    {
        $result = $this->get('messages/sessions');

        $result->assertStatus(401);
        $result->assertJSONFragment(['status' => 'error']);
    }

    public function testGetMessagesRequiresAuthentication(): void
    {
        $result = $this->get('messages/sessions/1');

        $result->assertStatus(401);
        $result->assertJSONFragment(['status' => 'error']);
    }

    public function testSendMessageRequiresAuthentication(): void
    {
        $result = $this->post('messages/send', [
            'session_id' => 1,
            'message_text' => 'Hello',
        ]);

        $result->assertStatus(401);
        $result->assertJSONFragment(['status' => 'error']);
    }

    public function testStartSessionRequiresAuthentication(): void
    {
        $result = $this->post('messages/sessions/start', [
            'listing_id' => 1,
        ]);

        $result->assertStatus(401);
        $result->assertJSONFragment(['status' => 'error']);
    }
}
