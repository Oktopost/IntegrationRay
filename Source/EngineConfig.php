<?php
namespace IntegrationRay;


use IntegrationRay\Exception\FatalEngineException;

use Pofig\Config;
use Pofig\Loaders\PHPLoader;
use Pofig\Loaders\JsonLoader;
use Pofig\Loaders\HierarchicalIniLoader;


class EngineConfig
{
	private $dir;
	private $additional;
	
	/** @var Config */
	private $config;
	
	
	private function getPath(...$parts)
	{
		return join(DIRECTORY_SEPARATOR, $parts);
	}
	
	
	public function __construct(string $dir, array $additional = [])
	{
		$this->dir = $this->getPath($dir, 'config');
		$this->additional = $additional;
	}
	
	
	public function initialize(string $target): void
	{
		$config = new Config();
		
		$setup = $config->setup();
		$setup->addLoader([
			'ini'	=> new HierarchicalIniLoader(),
			'php' 	=> new PHPLoader(),
			'json'	=> new JsonLoader()
		]);
		
		$setup->addSimplePath(['ini', 'php', 'json']);
		
		$group = $config->setup()->group('basic');
		$group->addIncludePath($this->getPath($this->dir, 'basic'));
		
		$group = $config->setup()->group('target');
		$group->addIncludePath($this->getPath($this->dir, $target));
		
		if (isset($this->additional[$target]))
		{
			foreach ($this->additional[$target] as $path)
			{
				$group->addIncludePath($this->getPath($path, $target));
			}
		}
		
		$group = $config->setup()->group('host');
		$group->addIncludePath($this->getPath($this->dir, 'host'));
		
		$this->config = $config;
	}
	
	/**
	 * @return Config
	 */
	public function config(): Config
	{
		if (!$this->config)
			throw new FatalEngineException('initialize($target) must be called before accessing config');
		
		return $this->config;
	}
	
	/**
	 * @param string $name
	 * @return array|mixed|null
	 */
	public function get(string $name)
	{
		return $this->config->getConfigObject($name);
	}
}