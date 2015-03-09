<?php
/**
 * Created by PhpStorm.
 * User: GaoJie
 * Date: 14-7-7
 * Time: 上午10:00
 */

class ExtTest extends CTestCase {
	public $html_code = '<script type="asdfasdf" data=\'asdfasdf\'>123123213</script>';
	public $html_mraid1 = '<script src="mraid.js"></script>';
	public $html_mraid2 = '<script src="mraid.js"></script><script>createCalendarEvent();</script>';
	public $html_orrma = '<div></div>>ormma.version();';

	/**
	 * 测试
	 */
	public function testIsHtml(){
		$this->assertTrue(GHtml::isHtml($this->html_code));
	}
	/**
	 * @depends testIsHtml
	 */
	public function testRichTypeMarid1(){
		$this->assertTrue(GHtml::isHtml($this->html_mraid1));
		$this->assertEquals(GHtml::richType($this->html_mraid1),'MRAID1.0');
	}
	/**
	 * @depends testIsHtml
	 */
	public function testRichTypeMarid2(){
		$this->assertTrue(GHtml::isHtml($this->html_mraid2));
		$this->assertEquals(GHtml::richType($this->html_mraid2),'MRAID2.0');
	}
	/**
	 * @depends testIsHtml
	 */
	public function testRichTypeOrmma(){
		$this->assertTrue(GHtml::isHtml($this->html_orrma));
		$this->assertEquals(GHtml::richType($this->html_orrma),'ORMMA');
	}
}
 