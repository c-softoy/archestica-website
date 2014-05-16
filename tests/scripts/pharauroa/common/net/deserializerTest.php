<?php
require_once('scripts/pharauroa/pharauroa.php');

class PharauroaDeserializerTest extends PHPUnit_Framework_TestCase {

	public function testReadByte() {
		$string = '';

		$in_byte1 = 0xFF;
		$in_byte2 = 0x00;
		$in_byte3 = 128;
		$in_byte4 = ord('a');

		$string = $string.chr($in_byte1);
		$string = $string.chr($in_byte2);
		$string = $string.chr($in_byte3);
		$string = $string.chr($in_byte4);

		$deserializer = new PharauroaDeserializer($string);

		$out_byte1 = $deserializer->readByte();
		$out_byte2 = $deserializer->readByte();
		$out_byte3 = $deserializer->readByte();
		$out_byte4 = $deserializer->readByte();

		$this->assertEquals($out_byte1, $in_byte1, 'Test case readByte() - 1');
		$this->assertEquals($out_byte2, $in_byte2, 'Test case readByte() - 2');
		$this->assertEquals($out_byte3, $in_byte3, 'Test case readByte() - 3');
		$this->assertEquals($out_byte4, $in_byte4, 'Test case readByte() - 4');
	}

	public function testReadInt() {
		$string = '';

		$in_int1 = 0xFF;
		$in_int2 = 0x00;
		$in_int3 = 128;
		$in_int4 = -1;

		$string = $string.pack("I", $in_int1);
		$string = $string.pack("I", $in_int2);
		$string = $string.pack("I", $in_int3);
		$string = $string.pack("I", $in_int4);

		$deserializer = new PharauroaDeserializer($string);

		$out_int1 = $deserializer->readInt();
		$out_int2 = $deserializer->readInt();
		$out_int3 = $deserializer->readInt();
		$out_int4 = $deserializer->readInt();

		$this->assertEquals($out_int1, $in_int1, 'Test case readInt() - 1');
		$this->assertEquals($out_int2, $in_int2, 'Test case readInt() - 2');
		$this->assertEquals($out_int3, $in_int3, 'Test case readInt() - 3');
		$this->assertEquals($out_int4, $in_int4, 'Test case readInt() - 4');
	}

	public function testReadString() {
		$string = '';

		$in_str1 = 'abc123';
		$in_str2 = '';
		$in_str3 = "a\nb\0c\rd";
		$in_str4 = '';
		for($i = 0; $i < 512; ++$i) {
			$in_str4 .= chr(rand(0, 255));
		}

		$string = $string.pack("I", strlen($in_str1)).$in_str1;
		$string = $string.pack("I", strlen($in_str2)).$in_str2;
		$string = $string.pack("I", strlen($in_str3)).$in_str3;
		$string = $string.pack("I", strlen($in_str4)).$in_str4;

		$deserializer = new PharauroaDeserializer($string);

		$out_str1 = $deserializer->readString();
		$out_str2 = $deserializer->readString();
		$out_str3 = $deserializer->readString();
		$out_str4 = $deserializer->readString();

		$this->assertEquals($out_str1, $in_str1, 'Test case readString() - 1');
		$this->assertEquals($out_str2, $in_str2, 'Test case readString() - 2');
		$this->assertEquals($out_str3, $in_str3, 'Test case readString() - 3');
		$this->assertEquals($out_str4, $in_str4, 'Test case readString() - 4');
	}

	public function testRead255LongString() {
		$string = '';

		$in_sstr1 = 'abc123';
		$in_sstr2 = '';
		$in_sstr3 = "a\nb\0c\rd";
		$in_sstr4 = '0123456789';

		$string = $string.chr(strlen($in_sstr1)).$in_sstr1;
		$string = $string.chr(strlen($in_sstr2)).$in_sstr2;
		$string = $string.chr(strlen($in_sstr3)).$in_sstr3;
		$string = $string.chr(strlen($in_sstr4)).$in_sstr4;

		$deserializer = new PharauroaDeserializer($string);

		$out_sstr1 = $deserializer->read255LongString();
		$out_sstr2 = $deserializer->read255LongString();
		$out_sstr3 = $deserializer->read255LongString();
		$out_sstr4 = $deserializer->read255LongString();

		$this->assertEquals($out_sstr1, $in_sstr1, 'Test case read255LongString() - 1');
		$this->assertEquals($out_sstr2, $in_sstr2, 'Test case read255LongString() - 2');
		$this->assertEquals($out_sstr3, $in_sstr3, 'Test case read255LongString() - 3');
		$this->assertEquals($out_sstr4, $in_sstr4, 'Test case read255LongString() - 4');
	}

	public function testMixed() {
		$string = '';

		$in_byte1 = 0xFF;
		$in_byte2 = 0x00;
		$in_byte3 = 128;
		$in_byte4 = ord('a');

		$in_int1 = 0xFF;
		$in_int2 = 0x00;
		$in_int3 = 128;
		$in_int4 = 65535;

		$in_str1 = 'abc123';
		$in_str2 = '';
		$in_str3 = "a\nb\0c\rd";
		$in_str4 = '';
		for($i = 0; $i < 512; ++$i) {
			$in_str4 .= chr(rand(0, 255));
		}

		$in_sstr1 = 'abc123';
		$in_sstr2 = '';
		$in_sstr3 = "a\nb\0c\rd";
		$in_sstr4 = '0123456789';

		$string = $string.chr($in_byte1);
		$string = $string.pack("I", $in_int1);
		$string = $string.pack("I", strlen($in_str1)).$in_str1;
		$string = $string.chr(strlen($in_sstr1)).$in_sstr1;
		$string = $string.chr(strlen($in_sstr3)).$in_sstr3;
		$string = $string.chr($in_byte3);
		$string = $string.pack("I", $in_int2);
		$string = $string.chr($in_byte4);
		$string = $string.pack("I", $in_int3);
		$string = $string.pack("I", $in_int4);
		$string = $string.pack("I", strlen($in_str2)).$in_str2;
		$string = $string.chr(strlen($in_sstr2)).$in_sstr2;
		$string = $string.pack("I", strlen($in_str3)).$in_str3;
		$string = $string.pack("I", strlen($in_str4)).$in_str4;
		$string = $string.chr(strlen($in_sstr4)).$in_sstr4;
		$string = $string.chr($in_byte2);

		$deserializer = new PharauroaDeserializer($string);

		$out_byte1 = $deserializer->readByte();
		$out_int1 = $deserializer->readInt();
		$out_str1 = $deserializer->readString();
		$out_sstr1 = $deserializer->read255LongString();
		$out_sstr3 = $deserializer->read255LongString();
		$out_byte3 = $deserializer->readByte();
		$out_int2 = $deserializer->readInt();
		$out_byte4 = $deserializer->readByte();
		$out_int3 = $deserializer->readInt();
		$out_int4 = $deserializer->readInt();
		$out_str2 = $deserializer->readString();
		$out_sstr2 = $deserializer->read255LongString();
		$out_str3 = $deserializer->readString();
		$out_str4 = $deserializer->readString();
		$out_sstr4 = $deserializer->read255LongString();
		$out_byte2 = $deserializer->readByte();

		$this->assertEquals($out_byte1, $in_byte1, 'Test case mix - readByte() - 1');
		$this->assertEquals($out_byte2, $in_byte2, 'Test case mix - readByte() - 2');
		$this->assertEquals($out_byte3, $in_byte3, 'Test case mix - readByte() - 3');
		$this->assertEquals($out_byte4, $in_byte4, 'Test case mix - readByte() - 4');
		$this->assertEquals($out_int1, $in_int1, 'Test case mix - readInt() - 1');
		$this->assertEquals($out_int2, $in_int2, 'Test case mix - readInt() - 2');
		$this->assertEquals($out_int3, $in_int3, 'Test case mix - readInt() - 3');
		$this->assertEquals($out_int4, $in_int4, 'Test case mix - readInt() - 4');
		$this->assertEquals($out_str1, $in_str1, 'Test case mix - readString() - 1');
		$this->assertEquals($out_str2, $in_str2, 'Test case mix - readString() - 2');
		$this->assertEquals($out_str3, $in_str3, 'Test case mix - readString() - 3');
		$this->assertEquals($out_str4, $in_str4, 'Test case mix - readString() - 4');
		$this->assertEquals($out_sstr1, $in_sstr1, 'Test case mix - read255LongString() - 1');
		$this->assertEquals($out_sstr2, $in_sstr2, 'Test case mix - read255LongString() - 2');
		$this->assertEquals($out_sstr3, $in_sstr3, 'Test case mix - read255LongString() - 3');
		$this->assertEquals($out_sstr4, $in_sstr4, 'Test case mix - read255LongString() - 4');
	}

	public function testIOException() {
		$deserializer = new PharauroaDeserializer('a');
		$deserializer->readByte();
		try {
			$deserializer->readByte();
			fail('Excepted PharauroaIOException');
		} catch (PharauroaIOException $e) {
			// okay
		}
	}
}
?>
