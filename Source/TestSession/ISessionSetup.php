<?php
namespace IntegrationRay\TestSession;


interface ISessionSetup
{
	public function name(): string;
	
	/**
	 * @return string[]
	 */
	public function dependencies(): array;
	
	/**
	 * @method void openBrowser(...$params);
	 * @method void closeSession(...$params);
	 * 
	 * @method void setupNarrator(\Narrator\INarrator $n, ...$params): \Narrator\INarrator; 
	 * 
	 * @method void setUpTestSuite(...$params); 
	 * @method void setUp(...$params);
	 * @method void cleanUp(...$params);
	 * @method void cleanUpTestSuite(...$params); 
	 */
}