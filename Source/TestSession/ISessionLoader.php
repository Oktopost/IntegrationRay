<?php
namespace IntegrationRay\TestSession;


interface ISessionLoader
{
	public function addSetup(ISessionSetup $setup): ISessionLoader;
	public function addClass(string $className): ISessionLoader;
	public function addFolder(string $folder): ISessionLoader;
}