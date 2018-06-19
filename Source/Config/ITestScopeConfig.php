<?php
namespace IntegrationRay\Config;


use Narrator\INarrator;
use IntegrationRay\TestSession\ISessionLoader;


interface ITestScopeConfig
{
	public function setupNarrator(INarrator $narrator): INarrator;
	public function setupSessions(ISessionLoader $loader): void;
	public function getConfigDirectory(): string;
	public function getAdditionalConfigDirectories(): array;
}