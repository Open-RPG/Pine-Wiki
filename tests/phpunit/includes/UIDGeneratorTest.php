<?php

class UIDGeneratorTest extends MediaWikiTestCase {
	/**
	 * @dataProvider provider_testTimestampedUID
	 */
	public function testTimestampedUID( $method, $digitlen, $bits, $tbits, $hostbits ) {
		$id = call_user_func( array( 'UIDGenerator', $method ) );
		$this->assertEquals( true, ctype_digit( $id ), "UID made of digit characters" );
		$this->assertLessThanOrEqual( $digitlen, strlen( $id ),
			"UID has the right number of digits" );
		$this->assertLessThanOrEqual( $bits, strlen( wfBaseConvert( $id, 10, 2 ) ),
			"UID has the right number of bits" );

		$ids = array();
		for ( $i = 0; $i < 300; $i++ ) {
			$ids[] = call_user_func( array( 'UIDGenerator', $method ) );
		}

		$lastId = array_shift( $ids );
		if ( $hostbits ) {
			$lastHost = substr( wfBaseConvert( $lastId, 10, 2, $bits ), -$hostbits );
		}

		$this->assertArrayEquals( array_unique( $ids ), $ids, "All generated IDs are unique." );

		foreach ( $ids as $id ) {
			$id_bin = wfBaseConvert( $id, 10, 2 );
			$lastId_bin = wfBaseConvert( $lastId, 10, 2 );

			$this->assertGreaterThanOrEqual(
				substr( $id_bin, 0, $tbits ),
				substr( $lastId_bin, 0, $tbits ),
				"New ID timestamp ($id_bin) >= prior one ($lastId_bin)." );

			if ( $hostbits ) {
				$this->assertEquals(
					substr( $id_bin, 0, -$hostbits ),
					substr( $lastId_bin, 0, -$hostbits ),
					"Host ID of ($id_bin) is same as prior one ($lastId_bin)." );
			}

			$lastId = $id;
		}
	}

	/**
	 * array( method, length, bits, hostbits )
	 */
	public static function provider_testTimestampedUID() {
		return array(
			array( 'newTimestampedUID128', 39, 128, 46, 48 ),
			array( 'newTimestampedUID128', 39, 128, 46, 48 ),
			array( 'newTimestampedUID88', 27, 88, 46, 32 ),
		);
	}

	public function testUUIDv4() {
		for ( $i = 0; $i < 100; $i++ ) {
			$id = UIDGenerator::newUUIDv4();
			$this->assertEquals( true,
				preg_match( '!^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$!', $id ),
				"UID $id has the right format" );

			$id = UIDGenerator::newRawUUIDv4();
			$this->assertEquals( true,
				preg_match( '!^[0-9a-f]{12}4[0-9a-f]{3}[89ab][0-9a-f]{15}$!', $id ),
				"UID $id has the right format" );

			$id = UIDGenerator::newRawUUIDv4( UIDGenerator::QUICK_RAND );
			$this->assertEquals( true,
				preg_match( '!^[0-9a-f]{12}4[0-9a-f]{3}[89ab][0-9a-f]{15}$!', $id ),
				"UID $id has the right format" );
		}
	}
}
