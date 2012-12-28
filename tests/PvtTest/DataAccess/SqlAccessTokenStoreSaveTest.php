<?php

namespace PvtTest\DataAccess;

use Pvt\Core\AccessToken;

class SqlAccessTokenStoreSaveTest extends SqlAccessTokenStoreTestCase
{
    public function testInsertsDetails()
    {
        $this->db->expects($this->once())
            ->method('insert')
            ->with(
                'access_tokens',
                array(
                    'user_id' => 123456,
                    'access_token' => 'token_string',
                )
            );
        $accessToken = new AccessToken(123456,  'token_string');
        $this->store->save($accessToken);
    }
}
