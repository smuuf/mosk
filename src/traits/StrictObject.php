<?php

namespace Smuuf\Mosk;

trait StrictObject {

	/**
	 * Prevent getting an undeclared property.
	 *
	 * @throws \LogicException
	 * @param $name
	 * @return mixed
	 */
	public function __get($name) {

		throw new \LogicException(sprintf(
			"Cannot read from an undeclared property '%s' in %s.",
			$name,
			static::class
		));

	}

	/**
	 * Prevent setting an undeclared property.
	 *
	 * @throws \LogicException
	 * @param $name
	 * @param $value
	 */
	public function __set($name, $value) {

		throw new \LogicException(sprintf(
			"Cannot write to an undeclared property '%s' in %s.",
			$name,
			static::class
		));

	}

}
