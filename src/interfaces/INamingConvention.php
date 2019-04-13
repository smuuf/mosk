<?php

namespace Smuuf\Mosk;

interface INamingConvention {

	public function getName($calledName, IModel $currentModel);
	public function getNamespace($calledName, IModel $currentModel);

}
