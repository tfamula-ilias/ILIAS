<?php

/**
 * TestCase for the ilWACCheckingInstanceTest
 *
 * @author                 Fabian Schmid <fs@studer-raimann.ch>
 * @version                1.0.0
 *
 * @group                  needsInstalledILIAS
 */
class ilWACPathTest extends PHPUnit_Framework_TestCase {

	/**
	 * Setup
	 */
	protected function setUp() {
		require_once('./Services/WebAccessChecker/classes/class.ilWACPath.php');
		require_once('./Services/WebAccessChecker/classes/class.ilWACSignedPath.php');
		parent::setUp();
	}


	public function testMobs() {
		$ilWacPath = new ilWACPath('http://trunk.local/data/trunk/mobs/mm_270/Koeniz_Komturei1.jpg');
		$this->assertEquals('mobs', $ilWacPath->getModuleType());
		$this->assertEquals('mm_270', $ilWacPath->getModuleIdentifier());
		$this->assertEquals('Koeniz_Komturei1.jpg', $ilWacPath->getAppendix());
		$this->assertEquals('trunk', $ilWacPath->getClient());
		$this->assertFalse($ilWacPath->isInSecFolder());
		$this->assertFalse($ilWacPath->isStreamable());
		$this->assertFalse($ilWacPath->isVideo());
		$this->assertFalse($ilWacPath->isAudio());
	}


	public function testUserImage() {
		$ilWacPath = new ilWACPath('http://trunk.local/data/trunk/usr_images/usr_6_small.jpg?t=63944');
		$this->assertEquals('usr_images', $ilWacPath->getModuleType());
		$this->assertEquals(null, $ilWacPath->getModuleIdentifier());
		$this->assertEquals('usr_6_small.jpg', $ilWacPath->getAppendix());
		$this->assertEquals('trunk', $ilWacPath->getClient());
		$this->assertFalse($ilWacPath->isInSecFolder());
		$this->assertFalse($ilWacPath->isStreamable());
		$this->assertFalse($ilWacPath->isVideo());
		$this->assertFalse($ilWacPath->isAudio());
	}


	public function testBlogInSec() {
		$ilWacPath = new ilWACPath('http://trunk.local/data/trunk/sec/ilBlog/blog_123/Header.mp4');
		$this->assertEquals('ilBlog', $ilWacPath->getModuleType());
		$this->assertEquals('blog_123', $ilWacPath->getModuleIdentifier());
		$this->assertEquals('Header.mp4', $ilWacPath->getAppendix());
		$this->assertEquals('trunk', $ilWacPath->getClient());
		$this->assertTrue($ilWacPath->isInSecFolder());
		$this->assertTrue($ilWacPath->isStreamable());
		$this->assertTrue($ilWacPath->isVideo());
		$this->assertFalse($ilWacPath->isAudio());
	}
}