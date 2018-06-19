<?php
namespace IntegrationRay\Utils;


use SeTaco\ISession;
use SeTaco\IBrowser;
use SeTaco\DriverConfig;
use SeTaco\IBrowserAssert;


class BrowserSetupSessionDecorator implements ISession
{
	/** @var callable */
	private $setupCallback;
	
	/** @var ISession */
	private $child;
	
	
	public function __construct(callable $setup, ISession $session)
	{
		$this->setupCallback = $setup;
		$this->child = $session;
	}
	
	
	public function openBrowser(): IBrowser
	{
		$browser = $this->child->openBrowser();
		$setupCallback = $this->setupCallback;
		
		$setupCallback($browser);
		
		return $browser;
	}
	
	public function config(): DriverConfig
	{
		return $this->child->config();
	}
	
	public function clear(): void
	{
		$this->child->clear();
	}
	
	public function hasCurrent(): bool
	{
		return $this->child->hasCurrent();
	}
	
	public function current(): IBrowser
	{
		return $this->child->current();
	}
	
	public function assert(): IBrowserAssert
	{
		return $this->child->assert();
	}
}