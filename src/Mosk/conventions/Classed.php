<?php

namespace Smuuf\Mosk\NamingConventions;

class Classed implements \Smuuf\Mosk\INamingConvention {

	/**
	 * Return the pure class name for the new model, which is based on the fully qualified class name of the parent
	 * model that is going to have the new model under it.
	 */
	public function getName($calledName, \Smuuf\Mosk\IModel $parentModel) {

		// Create final model name.
		// If called as $manager->project->db->cluster->main;
		// It will try to create "\BaseNamespace\ProjectDbClusterMainModel";

		$parentModelClassName = get_class($parentModel);

		// Let $name be 'Db'.
		// Having static::class as '\XenieModule\ProjectModel'.
		$prefix = substr(strrchr($parentModelClassName, '\\'), 1);
		// $prefix is now 'ProjectModel'.

		// If static::CLASS_SUFFIXES is ['Model'], then ...
		foreach ($parentModel::CLASS_SUFFIXES as $suffix) {
			$prefix = preg_replace("/{$suffix}$/", null, $prefix);
			// $prefix is now 'Project'.
		}

		// $prefix . ucfirst($name) is therefore 'ProjectDb'.
		return $prefix . ucfirst($calledName);

	}

	/**
	 * Return null, because this naming convention does not use namespaces, but instead puts the whole
	 * model hiererchy into the pure class name.
	 */
	public function getNamespace($calledName, \Smuuf\Mosk\IModel $parentModel) {

		// Null.

	}

}
