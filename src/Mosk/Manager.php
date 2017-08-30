<?php

namespace Smuuf\Mosk;

/**
 * The Manager.
 *
 * This is the parent of all repositories and their child models, while itself being a kind of repository.
 */
class Manager extends Model {

	protected $defaults = [];

	/** @var string Base namespace used for building fully-qualified class names of models. **/
	protected $prefixNamespace;
	protected $addedNamespaces = [];

	/** @var INamingConvention **/
	protected $namingConvention;

	public function __construct(
		$prefixNamespace = null,
		array $defaults = [],
		INamingConvention $namingConvention = null
	) {

		// This is THE manager.
		$this->manager = $this;

		$this->namingConvention = $namingConvention;

		$this->defaults = $defaults;
		$this->prefixNamespace = $prefixNamespace;

	}

	/**
	 * Ability to reset this Manager's models.
	 * For testing purposes, when client needs
	 * to clear the Manager's instantiated sub-models.
	 */
	public function clear() {
		parent::___clear();
	}

	/**
	 * Add a possible namespace to be used from now on.
	 */
	public function addNamespace($namespace) {
		$this->addedNamespaces[] = $namespace;
	}

	/**
	 * For late binding defaults.
	 */
	public function setDefaults(array $defaults) {
		$this->defaults = array_merge($this->defaults, $defaults);
	}

	protected function getModelNameTuple($name, IModel $callingModel) {

		// Skip building names for first-level models, which would be mistakenly based on this Manager's class name.'
		if (get_class($callingModel) === self::class) return [$name];

		// If there is no naming convention defined, return the called name back.
		if (!$this->namingConvention) return [$name];

		return [
			$this->namingConvention->getName($name, $callingModel),
			$this->namingConvention->getNamespace($name, $callingModel),
		];

	}

	/**
	 * Factory method that handles instantiating a new instance of model class.
	 */
	protected function buildInstance($className) {

		if (!$this->defaults) {

			// There are no 'defaults' set, so we don't
			// have any arguments to pass to the constructor,
			// so just create the instance.
			$instance = new $className;

		} else {

			$r = new \ReflectionClass($className);

			// Catch this before it ends up with fatal error without any stacktrace.
			if ($r->isAbstract()) {
				throw new \LogicException("Cannot instantiate abstract class '$className'.");
			}

			if (!$constructor = $r->getConstructor()) {

				// The model class has no constructor method, so just simply create the instance.
				$instance = new $className;

			} else {

				$arguments = array();
				foreach ($constructor->getParameters() as $arg) {

					// If a 'default' with given name is defined, put it in the constructor's arguments array.
					if (isset($this->defaults[$arg->name])) {

						$arguments[] = $this->defaults[$arg->name];

					} else {

						// Optional arguments will have their default value passed as argument.
						if ($arg->isOptional()) {
							$arguments[] = $arg->getDefaultValue();
						} else {
							throw new \LogicException(
								"Cannot instantiate model class '$className'.
								Constructor dependency '\${$arg->name}' is missing."
							);
						}

					}

				}

				// Create instance and pass any existing arguments to the constructor.
				$instance = new $className(...$arguments);

			}

		}

		return $instance;

	}


	/**
	 * Get a list of potential fully qualified class names for a given model, optionally with an extra postfix
	 * namespace.
	 *
	 * @param string $modelName Name for the model. Eg. 'Permission'.
	 * @param string $suffixNamespace Namespace to be added to the previously added namespaces.
	 * @return array List of class names with namespaces.
	 */
	protected function getPotentialClasses($modelName, $nsSuffix = null, array $classSuffixes = []) {

		// Prepare postfix.
		$nsSuffix = $nsSuffix ? '\\' . ltrim($nsSuffix, '\\') : null;

		// Start without any namespace.
		$keep = null;

		// Build array of namespace hierarchy to go through.
		$namespaces = array_merge([$this->prefixNamespace], $this->addedNamespaces);

		$list = array();
		$absolutes = array();
		foreach ($namespaces as $added) {

			// If added namespace begins with a backslash, consider it being an absolute namespace.
			if (strpos($added, '\\') === 0) {

				$ns = ltrim($added, '\\');

				// If the namespace is already in the namespace suffix specified, do not put it in there twice.
				$finalNs = (strpos($nsSuffix, $ns) !== 0) ? "{$ns}{$nsSuffix}" : $nsSuffix;

				foreach ($classSuffixes as $classSuffix) {
					$list[] = $this->buildFullClass($modelName, $finalNs, $classSuffix);
				}

				continue;

			}

			// Current namespace is a relative one, so add it to the kept string of "total" namespace.
			if ($added) $keep = $keep . '\\' . $added;

			// If the namespace is already in the namespace suffix specified, do not put it in there twice.
			$finalNs = (strpos($nsSuffix, $keep) !== 0) ? "{$keep}{$nsSuffix}" : $nsSuffix;

			foreach ($classSuffixes as $classSuffix) {
				$list[] = $this->buildFullClass($modelName, $finalNs, $classSuffix);
			}

		}

		// Reverse - Go from the most specific to the most general namespace.
		return array_reverse($list);

	}

	/**
	 * Convert a model name to a model class name.
	 *
	 * @param string $class
	 * @param string $namespace
	 * @return string Model class name with a possible namespace prefix.
	 */
	protected function buildFullClass($className, $namespace = null, $classSuffix = null) {
		return $namespace . '\\' . ucfirst($className) . $classSuffix;
	}

}
