<?php
namespace IntegrationRay\Plugins;


use Skeleton\Base\IConfigLoader;


interface IWithSkeleton
{
	public function getLoader(): IConfigLoader;
}