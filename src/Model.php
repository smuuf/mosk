<?php

namespace Smuuf\Mosk;

abstract class Model implements IModel {

	use StrictObject;

	const CLASS_SUFFIXES = [
		'Model',
	];

	/** @var Manager Manager provides access to other models. */
	protected $manager;

	/** @var array Storage for this model's submodels. **/
	private $models = [];

	final public function setManager(Manager $manager) {

		if ($this->manager) {
			throw new \LogicException("Manager is already set.");
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
	 * Do not call this directly from a client model.
	 * This is for internal use only.
	 */
	protected function ___clear() {
		$this->models = [];
	}

	/**
	 * Getter for accessing separate models.
	 *
	 * @param string $modelName Model to access.
	 */
	public function __get(string $name) {

		// Get name and namespace that will be passed to self::getModel(), based
		// on the naming convention passed to the Manager.
		$tuple = $this->manager->getModelNameTuple($name, $this);
		return $this->getModel(...$tuple);

	}

	private function getModel($name, $namespace = null) {

		if (isset($this->models[$name])) {
			return $this->models[$name];
		}

		$potentialClasses = $this->manager->getPotentialClasses(
			$name,
			$namespace,
			static::CLASS_SUFFIXES
		);

		foreach ($potentialClasses as $class) {

			if (class_exists($class)) {

				$instance = $this->manager->buildInstance($class, $name);

				// Not all models do have to be Models. But those that are will
				// have the manager available, so it can access other models
				// through it.
				if ($instance instanceof IModel) {
					$instance->setManager($this->manager);
					$instance->startup();
				}

				return $this->models[$name] = $instance;

			}
		}

		$msg = sprintf(
			"Cannot get model '%s'. Tried: %s",
			$name,
			implode(', ', $potentialClasses)
		);

		throw new \LogicException($msg);

	}

}
