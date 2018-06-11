<?php
namespace IntegrationRay\Utils;


use SeTaco\IBrowser;


trait TAutoloadBrowser
{
	/**
	 * @autoload
	 * @var \SeTaco\ISession
	 */
	private $_session;
	
	/** @var IBrowser|null */
	private $_browser = null;
	
	
	private function browser(): IBrowser
	{
		if ($this->_browser)
			return $this->_browser;
		
		return $this->_session->current();
	}
	
	
	/**
	 * @param IBrowser $browser
	 * @return static
	 */
	public function __invoke(IBrowser $browser)
	{
		$this->_browser = $browser;
		return $this;
	}
}