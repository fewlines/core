<?php
namespace Fewlines\Core\Http\Router\Routes;

class Variable
{
	/**
	 * @var string
	 */
	const OPTIONAL_FLAG = '?';

	/**
	 * @var string
	 */
	private $name = '';

	/**
	 * @var string
	 */
	private $value = '';

	/**
	 * @var boolean
	 */
	private $optional = false;

	/**
	 * @param string $name
	 * @param string $value
	 */
	public function __construct($name, $value = '') {
		if (substr($name, 0, 1) == self::OPTIONAL_FLAG) {
			$this->optional = true;
			$this->name = substr($name, 1);
		}
		else {
			$this->name = $name;
		}

		$this->value = $value;
	}

	/**
	 * @param string $value
	 * @return self
	 */
	public function setValue($value) {
		$this->value = $value;
		return $this;
	}

	/**
	 * @param string
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * @param string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return boolean
	 */
	public function isOptional() {
		return $this->optional;
	}

	/**
	 * @return string
	 */
	public function __tostring() {
		return $this->value;
	}
}