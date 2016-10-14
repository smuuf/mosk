<?php

namespace Smuuf\Mosk;

abstract class Model extends Object implements IModel {

	const CLASS_SUFFIXES = [
		'Model',
	];

	/** @var Smuuf\Mosk\Manager Manager provides access to other models. */
	protected $manager;

	/** @var array Storage for model instances **/
	private $models = array();

	final public function setManager(Manager $manager) {

		if ($this->manager) {
			throw new \LogicException("Manager was already set.");
		}

		$this->manager = $manager;

	}

	/**
	 * Will be called by Manager right after this model is instantiated and provided with Manager dependency.
	 */
	public function startup() {

		// To be overloaded, if in need.

	}

	/**
	 * Getter for accessing separate models.
	 * Can be overridden.
	 *
	 * @param string $modelName Model to access.
	 * @return \BaseModel
	 */
	public function __get($name) {

		// Get name and namespace that will be passed to self::getModel() method, based on the naming convention passed
		// to the Manager.
		$tuple = $this->manager->getModelNameTuple($name, $this);
		return $this->getModel(...$tuple);

	}

	private function getModel($name, $namespace = null) {

		if (isset($this->models[$name])) {
			return $this->models[$name];
		}

		$potentialClasses = $this->manager->getPotentialClasses($name, $namespace, static::CLASS_SUFFIXES);

		foreach ($potentialClasses as $class) {

			if (class_exists($class)) {

				$instance = $this->manager->buildInstance($class, $name);

				// Not all models do have to be Models.
				// But those that are will have the manager available, so it can access other models though it.
				if ($instance instanceof IModel) {
					$instance->setManager($this->manager);
					$instance->startup();
				}

				return $this->models[$name] = $instance;

			}
		}

		throw new \LogicException(sprintf("Cannot get model '%s'. Tried: %s", $name, implode(', ', $potentialClasses)));

	}

}
