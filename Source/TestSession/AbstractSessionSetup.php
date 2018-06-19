<?php
namespace IntegrationRay\TestSession;



use Annotation\Value;
use IntegrationRay\Exception\FatalEngineException;


abstract class AbstractSessionSetup implements ISessionSetup
{
	private $name;
	
	
	public function __construct(string $name = null)
	{
		$this->name = $name;
	}
	
	
	public function name(): string
	{
		if ($this->name)
			return $this->name;
		
		$value = Value::getValue(static::class, 'session');
		
		if (!$value)
			throw new FatalEngineException('Session name not set for ' . ISessionSetup::class . ' object');
		
		$this->name = $value;
		
		return $this->name;
	}
	
	public function dependencies(): array
	{
		return ['homepage'];
	}
}