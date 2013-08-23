<?php

use Symfony\Component\Console\Application as SymfonyApp;

class Application extends SymfonyApp
{
	protected $author;

	public function __construct($name='UNKNOWN', $version='UNKNOWN', $author='UNKNOWN') {
		parent::__construct($name, $version);

		$this->author = $author;
	}

	public function getLongVersion() {
		$v = parent::getLongVersion();

		if ('UNKNOWN'!==$this->author) {
			return $v." by <comment>{$this->author}</comment>";
		}

		return $v;
	}
}
