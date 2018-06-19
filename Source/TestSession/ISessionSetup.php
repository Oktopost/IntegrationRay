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
	 * @method void setupSession(...$params);
	 * @method void openBrowser(...$params);
	 * @method void cleanUpSession(...$params);
	 * 
	 * @method void setupNarrator(\Narrator\INarrator $n, ...$params): \Narrator\INarrator; 
	 * 
	 * @method void setupTestSuite(...$params); 
	 * @method void setup(...$params);
	 * @method void cleanUp(...$params);
	 * @method void cleanUpTestSuite(...$params); 
	 */
}