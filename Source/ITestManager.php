<?php
namespace IntegrationRay;


use Narrator\INarrator;


interface ITestManager
{
	public function setupTestCase($instance, string $method): void;
	public function cleanUpTestCase($instance, string $method): void;
	public function setupTestSuite(string $class): void;
	public function cleanUpTestSuite(string $class): void;
	
	public function getNarrator(): INarrator;
}