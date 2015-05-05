<?php

namespace Aeris\ZfDiConfigTest\ServiceManager\Mock;

class FooService {

	/** @var mixed[] */
	public $constructorArgs;

	public $bar;

	function __construct() {
		$this->constructorArgs = func_get_args();
	}

	public function setBar($bar) {
		$this->bar = $bar;
	}

	public function getBar() {
		return $this->bar;
	}


}