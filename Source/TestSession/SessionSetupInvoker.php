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
				if ($parameter->getType() != 'string' || $parameter->name != 'testClass')
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
		$this->narrator = $narrator;
	}
	
	
	public function setupNarrator(): INarrator
	{
		$original = clone $this->narrator;
		$narrator = clone $this->narrator;
		$narrator->params()->atPosition(0, function() use ($original) { return $original; });
		
		return $this->invoke(__FUNCTION__, $narrator) ?: $original;
	}
	
	public function setupSession(): void
	{
		$this->invoke(__FUNCTION__);
	}
	
	public function openBrowser(IBrowser $browser): void
	{
		$narrator = clone $this->narrator;
		$narrator->params()->byType(IBrowser::class, $browser);
		
		$this->invoke(__FUNCTION__, $narrator);
	}
	
	public function cleanUpSession(): void
	{
		$this->invoke(__FUNCTION__);
	}
	
	public function setupTestSuite(string $className): void
	{
		$narrator = clone $this->narrator;
		$narrator->params()->atPosition(0, $className);
		
		$this->invoke(__FUNCTION__, $narrator);
	}
	
	public function setup($instance, string $method): void
	{
		$narrator = clone $this->narrator;
		$narrator->params()->addCallback($this->returnTestSuitCallback($instance));
		$narrator->params()->addCallback($this->returnTestCaseCallback(new \ReflectionMethod($instance, $method)));
		
		$this->invoke(__FUNCTION__, $narrator);
	}
	
	public function cleanUp($instance, string $method): void
	{
		$narrator = clone $this->narrator;
		$narrator->params()->addCallback($this->returnTestSuitCallback($instance));
		$narrator->params()->addCallback($this->returnTestCaseCallback(new \ReflectionMethod($instance, $method)));
		
		$this->invoke(__FUNCTION__, $narrator);
	}
	
	public function cleanUpTestSuite(string $className): void
	{
		$narrator = clone $this->narrator;
		$narrator->params()->atPosition(0, $className);
		
		$this->invoke(__FUNCTION__, $narrator);
	}
	
	
	public static function create(ISessionSetup $setup, INarrator $narrator): SessionSetupInvoker
	{
		return new self($setup, $narrator);
	}
}