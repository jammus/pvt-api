<?php

namespace PvtTest\DataAccess;

class SqlAccessTokenStoreFetchByUserIdTest extends SqlAccessTokenStoreTestCase
{
    public function testRestrictsQueryByUserId()
    {
        $this->db->expects($this->once())
            ->method('fetchAssoc')
            ->with(
                'SELECT * FROM access_tokens WHERE user_id = :user_id',
                array('user_id' => 4567)
            );
        $this->store->fetchByUserId(4567);
    }
}
