<?php

namespace Smuuf\Mosk\NamingConventions;

class Namespaced implements \Smuuf\Mosk\INamingConvention {

	/**
	 * Return just the called name we got.
	 */
	public function getName($calledName, \Smuuf\Mosk\IModel $parentModel) {

		// If the called name is "Remote", just return "Remote" (and see the method below).
		return ucfirst($calledName);

	}

	/**
	 * Return the namespace for the new model, which is based on the fully qualified class name of the parent model
	 * that is going to have the new model under it.
	 */
	public function getNamespace($calledName, \Smuuf\Mosk\IModel $parentModel) {

		// Example: $parentModelClassName is '\MyModule\ProjectModel'.
		$parentModelClassName = get_class($parentModel);

		// $ns will be '\ProjectModel'.
		$ns = substr(strrchr($parentModelClassName, '\\'), 1);

		// Now let's snip off any class suffix that the parent model uses.
		// Eg. if $parentModel::CLASS_SUFFIXES is ['Model'], the final $ns will be: '\Project'
		foreach ($parentModel::CLASS_SUFFIXES as $suffix) {
			$ns = preg_replace("/{$suffix}$/", null, $ns);
		}

		// Return '\Project'. This will be used as the namespace right before the class name from self::getName().
		// In the end the class name '\Project\RemoteModel' will be used. The "Model" is appended because of the
		// $parentModel::CLASS_SUFFIXES array.
		return $ns;

	}

}
