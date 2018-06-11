<?php
namespace IntegrationRay\Utils;


use Skeleton\Base\IConfigLoader;
use Skeleton\Base\IBoneConstructor;


class SkeletonConfigLoaderCollection implements IConfigLoader
{
	/** @var IBoneConstructor|null */
	private $constructor = null;
	
	/** @var IConfigLoader[] */
	private $loaders = [];
	
	
	/**
	 * @param string $path
	 * @return bool
	 */
	public function tryLoad($path)
	{
		foreach ($this->loaders as $loader)
		{
			if ($loader->tryLoad($path))
				return true;
		}
		
		return false;
	}
	
	/**
	 * @param IBoneConstructor $constructor
	 * @return static
	 */
	public function setBoneConstructor(IBoneConstructor $constructor)
	{
		foreach ($this->loaders as $loader)
		{
			$loader->setBoneConstructor($constructor);
		}
		
		$this->constructor = $constructor;
	}
	
	
	public function addLoader(IConfigLoader $loader): void
	{
		$this->loaders[] = $loader;
		
		if ($this->constructor)
		{
			$loader->setBoneConstructor($this->constructor);
		}
	}
}