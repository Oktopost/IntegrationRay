<?php
namespace IntegrationRay\Cases;


use Narrator\Narrator;
use CosmicRay\Wrappers\PHPUnit\UnitestCase;

use IntegrationRay\TestScope;
use IntegrationRay\ITestManager;


class IntegrationTestCase extends UnitestCase
{
	private $narrator;
	
	
	protected static function getTestManager(): ITestManager
	{
		return TestScope::instance();
	}
	
	
	protected function getNarrator(): Narrator
	{
		if ($this->narrator)
			return $this->narrator;
		
		$this->narrator = clone self::getTestManager()->getNarrator();
		$this->narrator = $this->setupNarrator($this->narrator);
		
		$this->narrator->params()
			->addCallback(function (\ReflectionParameter $param, bool &$isFound)
			{
				$isFound = false;
				
				$class = $param->getClass();
				
				if ($class && $class->isInstantiable()) 
				{
					$obj = TestScope::instance()->getSkeleton()->load($class->getName());
					$isFound = true;
					
					return $obj;
				}
				else
				{
					$isFound = false;
					return null;
				}
			});
		
		return $this->narrator;
	}
	
	
	protected function setUp()
	{
		parent::setUp();
		self::getTestManager()->setupTestCase($this, $this->getName());
	}
	
	protected function tearDown()
	{
		parent::tearDown();
		self::getTestManager()->cleanUpTestCase($this, $this->getName());
	}
	
	
	public static function setUpBeforeClass()
	{
		parent::tearDownAfterClass();
		self::getTestManager()->setupTestSuite(static::class);
	}
	
	public static function tearDownAfterClass()
	{
		parent::tearDownAfterClass();
		self::getTestManager()->cleanUpTestSuite(static::class);
	}
}