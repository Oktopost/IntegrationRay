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
	
	
	private function browser(): IBrowser
	{
		if (!$this->_session->hasCurrent())
		{
			$browser = $this->_session->openBrowser();
			$browser->goto('');
		}
		
		return $this->_session->current();
	}
}