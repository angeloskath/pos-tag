<?php

include('vendor/autoload.php');

class PosTaggerTrainTest extends PHPUnit_Framework_TestCase
{
	protected $tagger;

	public function testInstatiate() {
		$this->tagger = new PosTagger();
		$this->assertInstanceOf('PosTagger', $this->tagger);
	}

	/**
	 * @depends testInstantiate
	 */
	public function testTrain() {
		$tset = new NlpTools\Documents\TrainingSet();
	}
}
