<?php
namespace IntegrationRay\TestSession;


use SeTaco\IBrowser;
use SeTaco\DriverConfig;


/**
 * @session homepage
 */
class HomepageSessionSetup implements ISessionSetup
{
	public function openBrowser(
		IBrowser $browser,
		DriverConfig $config
	): void
	{
		$url = $config->Homepage->getURL('');
		
		if ($url)
		{
			$browser->goto($url);
		}
	}
	
	public function dependencies(): array 
	{
		return [];
	}
	
	public function name(): string
	{
		return 'homepage';
	}
}