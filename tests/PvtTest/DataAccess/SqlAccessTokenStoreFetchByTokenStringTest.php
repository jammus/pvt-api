<?php

namespace PvtTest\DataAccess;

use Pvt\Core\AccessToken;

class SqlAccessTokenStoreFetchByTokenString extends SqlAccessTokenStoreTestCase
{
    public function testFetchesAssociativeArray()
    {
        $this->db->expects($this->once())
            ->method('fetchAssoc')
            ->with(
                'SELECT * FROM access_tokens WHERE access_token = :access_token',
                array('access_token' => 'abcdefgh')
            );
        $this->store->fetchByTokenString('abcdefgh');
    }

    public function testReturnsAccessTokenWhenSuccessful()
    {
        $this->db->expects($this->once())
            ->method('fetchAssoc')
            ->will($this->returnValue(
                array(
                    'user_id' => 10001,
                    'access_token' => 'abcdefgh'
                )
            ));
        $expectedToken = new AccessToken(10001, 'abcdefgh');
        $this->assertEquals($expectedToken, $this->store->fetchByTokenString('abcdefgh'));
    }

    public function testReturnsNullWhenStringNotFound()
    {
        $this->db->expects($this->once())
            ->method('fetchAssoc')
            ->will($this->returnValue(
                array()
            ));
        $this->assertEquals(null, $this->store->fetchByTokenString('unknown_string'));
    }
}
