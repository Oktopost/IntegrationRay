<?php
namespace IntegrationRay\Config;


use IntegrationRay\EngineConfig;
use Squid\MySql\Config\ConfigParser;
use Squid\MySql\Config\IConfigLoader;
use Squid\MySql\Config\MySqlConnectionConfig;


class MySQLConfigLoader implements IConfigLoader
{
	/** @var EngineConfig */
	private $config;
	
	/** @var array */
	private $mysqlConfig;
	
	
	private function getMySQLConfig(): array 
	{
		if (!$this->mysqlConfig)
		{
			$this->mysqlConfig = $this->config->get('mysql')->toArray();
		}
		
		return $this->mysqlConfig;
	}
	
	
	public function __construct(EngineConfig $config)
	{
		$this->config = $config;
	}
	
	
	/**
	 * @param string $connName
	 * @return MySqlConnectionConfig
	 */
	public function getConfig($connName)
	{
		$config = $this->getMySQLConfig();
		return ConfigParser::parse($config[$connName]);
	}
	
	/**
	 * @param string $connName
	 * @return bool
	 */
	public function hasConfig($connName)
	{
		$config = $this->getMySQLConfig();
		return isset($config[$connName]);
	}
}