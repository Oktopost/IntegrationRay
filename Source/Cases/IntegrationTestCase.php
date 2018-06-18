<?php
namespace IntegrationRay\Cases;


use Narrator\Narrator;
use Skeleton\Exceptions\ImplementerNotDefinedException;
use IntegrationRay\TestScope;

use CosmicRay\Wrappers\PHPUnit\UnitestCase;


class IntegrationTestCase extends UnitestCase
{
	protected function setUp()
	{
		if (!TestScope::instance()->getSession()->hasCurrent())
		{
			$browser = TestScope::instance()->getSession()->openBrowser();
			$browser->goto('');
		}
	}
	
	protected function tearDown()
	{
		TestScope::instance()->getSession()->clear();
	}
	
	
	protected function setupNarrator(Narrator $narrator): Narrator
	{
		$scope = TestScope::instance();
		$skeleton = $scope->getSkeleton();
		
		$narrator->params()
			->addCallback(function (\ReflectionParameter $param, bool &$isFound)
				use ($skeleton)
			{
				$isFound = false;
				
				$type = $param->getType();
				
				if (is_null($type) || 
					(!class_exists((string)$type) && !interface_exists((string)$type)))
				{
					return null;
				}
				
				try
				{
					$isFound = true;
					$res = $skeleton->get((string)$type);
					
					return $res;
					
				}
				catch (ImplementerNotDefinedException $e)
				{
					$isFound = false;
					return null;
				}
			});
		
		return parent::setupNarrator($narrator);
	}
}