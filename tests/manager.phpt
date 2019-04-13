<?php

use \Tester\Assert;

require __DIR__ . '/bootstrap.php';

$man = new \Smuuf\Mosk\Manager('\TestNamespace');

Assert::exception(function() use ($man) {
	$_ = $man->nonexisting;
}, \LogicException::class, "#Cannot.*get.*model.*nonexisting#");

$model1 = $man->existing;
Assert::type(\TestNamespace\ExistingModel::class, $model1);

$model2 = $man->pureClass;
Assert::type(\TestNamespace\PureClassModel::class, $model2);
