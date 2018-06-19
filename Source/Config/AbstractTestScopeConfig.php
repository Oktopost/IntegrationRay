<?php
namespace IntegrationRay\Config;


use Narrator\INarrator;

use IntegrationRay\IElementsPlugin;
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
	
	public function getAdditionalConfigDirectories(): array
	{
		return [];
	}
	
	/**
	 * @return string[]|IElementsPlugin[]
	 */
	public function getPlugins(): array
	{
		return [];
	}
}