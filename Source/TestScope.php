<?php
namespace IntegrationRay;


use Annotation\Value;
use IntegrationRay\Config\ITestScopeConfig;
use IntegrationRay\TestSession\HomepageSessionSetup;
use IntegrationRay\TestSession\ISessionSetup;
use IntegrationRay\TestSession\SessionLoader;
use IntegrationRay\TestSession\SessionSetupCollection;
use IntegrationRay\TestSession\SessionSetupInvoker;
use IntegrationRay\Utils\BrowserSetupSessionDecorator;

use Narrator\Narrator;
use Narrator\INarrator;

use SeTaco\Session;
use SeTaco\IBrowser;
use SeTaco\ISession;
use SeTaco\DriverConfig;
use SeTaco\IBrowserAssert;

use Skeleton\Exceptions\ImplementerNotDefinedException;
use Skeleton\Skeleton;
use Skeleton\ConfigLoader\DirectoryConfigLoader;

use IntegrationRay\Utils\SkeletonConfigLoaderCollection;
use IntegrationRay\Plugins\IWithSkeleton;


class TestScope implements ITestManager
{
	/** @var TestScope|null */
	private static $instance = null;
	
	
	/** @var string|null */
	private $currentGroup = null;
	
	/** @var INarrator */
	private $narrator;
	
	/** @var Session */
	private $session;
	
	/** @var EngineConfig */
	private $config;
	
	/** @var ITestScopeConfig */
	private $sessionConfig;
	
	/** @var Skeleton */
	private $skeleton;
	
	/** @var SkeletonConfigLoaderCollection */
	private $skeletonLoader;
	
	/** @var SessionSetupCollection */
	private $sessionsCollection;
	
	/** @var ISessionSetup[] */
	private $sessionSetups = [];
	
	
	/**
	 * @return iterable|SessionSetupInvoker[]
	 */
	private function foreachSessionInvoker(): iterable
	{
		foreach ($this->sessionSetups as $setup)
		{
			yield SessionSetupInvoker::create($setup, $this->narrator);
		}
	}
	
	private function getGroup($from): ?string
	{
		return Value::getValue($from, 'session', null) ?: null;
	}
	
	private function loadSessionSetups(): void
	{
		$loader = new SessionLoader();
		$loader->addClass(HomepageSessionSetup::class);
		
		$this->sessionConfig->setupSessions($loader);
		$this->sessionsCollection = $loader->getCollection();
	}
	
	private function switchGroup(?string $group): void
	{
		// Close Session
		if ($this->currentGroup)
		{
			foreach ($this->foreachSessionInvoker() as $setup)
			{
				$setup->cleanUpSession();
			}
		}
		
		$this->session->clear();
		$this->sessionSetups = [];
		
		// Open new session
		if ($group)
		{
			$this->sessionSetups = $this->sessionsCollection->getSessions($group);
			
			$this->createNarrator();
			
			foreach ($this->foreachSessionInvoker() as $setup)
			{
				$setup->setupSession();
			}
		}
		else
		{
			$this->createNarrator();
		}
		
		$this->currentGroup = $group;
	}
	
	private function createNarrator(): void
	{
		$this->narrator = new Narrator();
		
		$this->narrator->params()
			->addCallback(function (\ReflectionParameter $param, bool &$isFound)
			{
				$isFound = false;
				
				$type = $param->getType();
				
				if (is_null($type) || 
					(!class_exists((string)$type) && !interface_exists((string)$type)))
				{
					return null;
				}
				
				try
				{
					$isFound = true;
					return $this->skeleton->get((string)$type);
				}
				catch (ImplementerNotDefinedException $e)
				{
					$isFound = false;
					return null;
				}
			});
		
		foreach ($this->foreachSessionInvoker() as $setup)
		{
			$this->narrator = $setup->setupNarrator();
		}
	}
	
	private function loadBrowser(IBrowser $browser): void
	{
		foreach ($this->foreachSessionInvoker() as $setup)
		{
			$setup->openBrowser($browser);
		}
	}
	
	private function createSession(): void
	{
		$driverConfig = $this->config->get('driver');
		$this->session = new BrowserSetupSessionDecorator(
			function(IBrowser $b): void 
			{
				$this->loadBrowser($b);
			},
			new Session(DriverConfig::parse($driverConfig->toArray()))
		);
	}
	
	private function createSkeleton(): void
	{
		$this->skeleton = new Skeleton();
		$skeleton = $this->skeleton;
		
		$this->skeleton
			->enableKnot()
			->useGlobal()
			->set(TestScope::class,			$this)
			->set(EngineConfig::class,		$this->config)
			->set(ISession::class,			$this->session)
			->set(IBrowserAssert::class,	$this->session->assert())
			->set(IBrowser::class,	
				function ()
				{
					if (!$this->session->hasCurrent())
					{
						$this->session->openBrowser();
						$this->session->current()->goto('');
					}
						
					return $this->session->current(); 
				});
		
		$dir = join(DIRECTORY_SEPARATOR, [$this->sessionConfig->getConfigDirectory(), 'skeleton']);
		$this->skeletonLoader->addLoader(new DirectoryConfigLoader(realpath($dir)));
		
		$skeleton->setConfigLoader($this->skeletonLoader);
	}
	
	
	/**
	 * @param string|ITestScopeConfig $configObject
	 */
	public function __construct($configObject)
	{
		$this->sessionConfig = is_string($configObject) ? new $configObject : $configObject;
		
		$this->config = new EngineConfig(
			$this->sessionConfig->getConfigDirectory(), 
			$this->sessionConfig->getAdditionalConfigDirectories()
		);
	}
	
	
	/**
	 * @param string $forTarget
	 */
	public function setup(string $forTarget): void
	{
		self::$instance = $this;
		
		$this->config->initialize($forTarget);
				
		$this->createSession();
		$this->createSkeleton();
		
		$this->skeleton->load($this->sessionConfig);
		
		$this->loadSessionSetups();
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
	
	
	public function setupTestSuite(string $class): void
	{
		$group = $this->getGroup($class);
		
		if ($this->currentGroup !== $group)
		{
			$this->switchGroup($group);
		}
		
		foreach ($this->foreachSessionInvoker() as $setup)
		{
			$setup->setupTestSuite($class);
		}
	}
	
	public function setupTestCase($instance, string $method): void
	{
		foreach ($this->foreachSessionInvoker() as $setup)
		{
			$setup->setup($instance, $method);
		}
	}
	
	public function cleanUpTestCase($instance, string $method): void
	{
		foreach ($this->foreachSessionInvoker() as $setup)
		{
			$setup->cleanUp($instance, $method);
		}
	}
	
	public function cleanUpTestSuite(string $class): void
	{
		foreach ($this->foreachSessionInvoker() as $setup)
		{
			$setup->cleanUpTestSuite($class);
		}
	}
	
	public function getNarrator(): INarrator
	{
		return $this->narrator;
	}
	
	
	public static function instance(): TestScope
	{
		return self::$instance;
	}
}