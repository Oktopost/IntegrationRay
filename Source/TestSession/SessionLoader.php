<?php
namespace IntegrationRay\TestSession;


use Itarator\Filters\PHPFileFilter;
use Itarator\Consumers\PHPRequireConsumer;


class SessionLoader implements ISessionLoader
{
	/** @var SessionSetupCollection */
	private $collection;
	
	
	public function __construct()
	{
		$this->collection = new SessionSetupCollection();
	}
	
	
	public function addSetup(ISessionSetup $setup): ISessionLoader
	{
		$this->collection->add($setup);
		return $this;
	}
	
	public function addClass(string $className): ISessionLoader
	{
		$this->collection->add(new $className);
		return $this;
	}
	
	public function addFolder(string $folder): ISessionLoader
	{
		$classes = get_declared_classes();
		
		$itarator = new \Itarator();
		$itarator
			->setFilter(new PHPFileFilter())
			->setRootDirectory($folder)
			->setRelativeDirectory('/')
			->setFileConsumer(new PHPRequireConsumer())
			->execute();
		
		$newClasses = array_diff(get_declared_classes(), $classes); 
		
		foreach ($newClasses as $class)
		{
			$ref = new \ReflectionClass($class);
			
			if ($ref->isInstantiable() && $ref->isSubclassOf(ISessionSetup::class))
			{
				$this->addClass($class);
			}
		}
		
		return $this;
	}
	
	
	public function getCollection(): SessionSetupCollection
	{
		return $this->collection;
	}
}