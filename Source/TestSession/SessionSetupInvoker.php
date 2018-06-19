<?php
namespace IntegrationRay\TestSession;


use SeTaco\IBrowser;
use Narrator\INarrator;


class SessionSetupInvoker
{
	/** @var ISessionSetup */
	private $setup;
	
	/** @var INarrator */
	private $narrator;
	
	
	private function invoke(string $method, ?INarrator $narrator = null)
	{
		if (!method_exists($this->setup, $method))
			return null;
		
		$narrator = $narrator ?: $this->narrator;
		return $narrator->invoke([$this->setup, $method]);
	}
	
	private function returnTestSuitCallback($instance): callable 
	{
		return 
			function(\ReflectionParameter $parameter, bool &$isFound)
				use ($instance) 
			{
				if ($parameter->name != 'testSuite')
				{
					$isFound = false;
					return null;
				}
				else
				{
					$isFound = true;
					return $instance;
				}
			};
	}
	
	private function returnTestCaseCallback(\ReflectionMethod $testCase): callable 
	{
		return 
			function(\ReflectionParameter $parameter, bool &$isFound)
				use ($testCase) 
			{
				if ($parameter->getClass() && $parameter->getClass()->getName() == \ReflectionMethod::class)
				{
					$isFound = true;
					return $testCase;
				}
				else
				{
					$isFound = false;
					return false;
				}
			};
	}
	
	
	public function __construct(ISessionSetup $setup, INarrator $narrator)
	{
		$this->setup = $setup;
	}
	
	
	public function setupNarrator(): INarrator
	{
		$original = clone $this->narrator;
		$narrator = clone $this->narrator;
		$narrator->params()->atPosition(0, $original);
		
		return $this->invoke(__FUNCTION__, $narrator) ?: $original;
	}
	
	public function openSession(): void
	{
		$this->invoke(__FUNCTION__);
	}
	
	public function openBrowser(IBrowser $browser): void
	{
		$narrator = clone $this->narrator;
		$narrator->params()->byType(IBrowser::class, $browser);
		
		$this->invoke(__FUNCTION__, $narrator);
	}
	
	public function closeSession(): void
	{
		$this->invoke(__FUNCTION__);
	}
	
	public function setUpTestSuite($instance): void
	{
		$narrator = clone $this->narrator;
		$narrator->params()->addCallback($this->returnTestSuitCallback($instance));
		
		$this->invoke($narrator, __FUNCTION__);
	}
	
	public function setUp($instance, \ReflectionMethod $testCase): void
	{
		$narrator = clone $this->narrator;
		$narrator->params()->addCallback($this->returnTestSuitCallback($instance));
		$narrator->params()->addCallback($this->returnTestCaseCallback($testCase));
		
		$this->invoke($narrator, __FUNCTION__);
	}
	
	public function tearDown($instance, \ReflectionMethod $testCase): void
	{
		$narrator = clone $this->narrator;
		$narrator->params()->addCallback($this->returnTestSuitCallback($instance));
		$narrator->params()->addCallback($this->returnTestCaseCallback($testCase));
		
		$this->invoke($narrator, __FUNCTION__);
	}
	
	public function cleanUpTestSuite($instance): void
	{
		$narrator = clone $this->narrator;
		$narrator->params()->addCallback($this->returnTestSuitCallback($instance));
		
		$this->invoke($narrator, __FUNCTION__);
	}
}