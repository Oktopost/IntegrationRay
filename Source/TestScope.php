<?php
namespace IntegrationRay;


use SeTaco\Session;
use SeTaco\IBrowser;
use SeTaco\ISession;
use SeTaco\DriverConfig;
use SeTaco\IBrowserAssert;

use Skeleton\Skeleton;
use Skeleton\ConfigLoader\DirectoryConfigLoader;

use IntegrationRay\Utils\SkeletonConfigLoaderCollection;
use IntegrationRay\Plugins\IWithSkeleton;


class TestScope
{
	/** @var TestScope|null */
	private static $instance = null;
	
	
	private $configDirectory;
	
	/** @var Session */
	private $session;
	
	/** @var EngineConfig */
	private $config;
	
	/** @var Skeleton */
	private $skeleton;
	
	/** @var SkeletonConfigLoaderCollection */
	private $skeletonLoader;
	
	
	private function setupSkeleton()
	{
		$skeleton = $this->skeleton;
		
		$skeleton->enableKnot();
		$skeleton->useGlobal();
		
		$skeleton->set(TestScope::class,	$this);
		$skeleton->set(EngineConfig::class,	$this->config);
		$skeleton->set(ISession::class,		$this->session);
		
		$skeleton->set(IBrowserAssert::class,	function () { return $this->session->assert(); });
		
		$skeleton->set(
			IBrowser::class,	
			function ()
			{
				if (!$this->session->hasCurrent())
				{
					$this->session->openBrowser();
					$this->session->current()->goto('');
				}
					
				return $this->session->current(); 
			});
		
		$dir = join(DIRECTORY_SEPARATOR, [$this->configDirectory, 'config/skeleton']);
			
		$this->skeletonLoader->addLoader(new DirectoryConfigLoader(realpath($dir)));
	}
	
	
	public function __construct($dir, array $additional = [])
	{
		$this->configDirectory	= $dir;
		$this->config			= new EngineConfig($dir, $additional);
		$this->skeleton			= new Skeleton();
		$this->skeletonLoader	= new SkeletonConfigLoaderCollection();
		
		$this->skeleton->setConfigLoader($this->skeletonLoader);
	}
	
	
	public function setup(string $forTarget, string $driver = 'default'): void
	{
		$this->config->initialize($forTarget);
		$driverConfig = $this->config->get('driver');
		$this->session = new Session(DriverConfig::parse($driverConfig->toArray()));
		
		$this->setupSkeleton();
		
		self::$instance = $this;
	}
	
	
	public function getSession(): ISession
	{
		return $this->session;
	}
	
	/**
	 * @param string $name
	 * @return mixed
	 */
	public function getElement(string $name)
	{
		return $this->skeleton->get($name);
	}
	
	public function getSkeleton(): Skeleton
	{
		return $this->skeleton;
	}
	
	public function getConfig(): EngineConfig
	{
		return $this->config;
	}
	
	/**
	 * @param IElementsPlugin|string $plugin
	 * @return TestScope
	 */
	public function register($plugin): TestScope
	{
		if (is_string($plugin))
			$plugin = new $plugin;
		
		if ($plugin instanceof IWithSkeleton)
		{
			$this->skeletonLoader->addLoader($plugin->getLoader());
		}
		
		return $this;
	}
	
	
	public static function instance(): TestScope
	{
		return self::$instance;
	}
}