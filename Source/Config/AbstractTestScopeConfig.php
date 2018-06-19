<?php
namespace IntegrationRay\Config;


use Narrator\INarrator;
use IntegrationRay\TestSession\ISessionLoader;


abstract class AbstractTestScopeConfig implements ITestScopeConfig
{
	public function setupNarrator(INarrator $narrator): INarrator
	{
		return $narrator;
	}
	
	public function setupSessions(ISessionLoader $loader): void
	{
		
	}
}