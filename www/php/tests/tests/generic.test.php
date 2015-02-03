<?php

require_once __DIR__.'/../../functions/generic.php';

class GenericTest extends PHPUnit_Framework_TestCase {
	public function testTags() {

		$mode = array();
		tag_parser($mode, $result, true);
		$this->assertEquals(0, $result);
		tag_parser($mode, $result, false);
		$this->assertEquals(array(), $mode);



		$mode = array(G::TAG_PRIVATE);
		tag_parser($mode, $result, true);
		$this->assertEquals(1, $result);
		tag_parser($mode, $result, false);
		$this->assertEquals(array(
			G::TAG_PRIVATE => G::$tags[G::TAG_PRIVATE]
		), $mode);



		$mode = array(G::TAG_IN_DEVELOPMENT);
		tag_parser($mode, $result, true);
		$this->assertEquals(2, $result);
		tag_parser($mode, $result, false);
		$this->assertEquals(array(
			G::TAG_IN_DEVELOPMENT => G::$tags[G::TAG_IN_DEVELOPMENT]
		), $mode);



		$mode = array(G::TAG_PRIVATE, G::TAG_IN_DEVELOPMENT);
		tag_parser($mode, $result, true);
		$this->assertEquals(3, $result);
		tag_parser($mode, $result, false);
		$this->assertEquals(array(
			G::TAG_PRIVATE => G::$tags[G::TAG_PRIVATE],
			G::TAG_IN_DEVELOPMENT => G::$tags[G::TAG_IN_DEVELOPMENT]
		), $mode);
	}
}