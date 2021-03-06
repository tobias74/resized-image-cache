<?php 

use SugarLoaf as SL;

class ZeitfadenApplication
{
	
	public function __construct($config)
	{
		
		//$this->config = $config;
		
		$this->dependencyManager = SL\DependencyManager::getInstance();
		$this->dependencyManager->setProfilerName('PhpProfiler');
		$this->configureDependencies();
		
		$this->mySqlProfiler = $this->dependencyManager->get('SqlProfiler');
		$this->phpProfiler = $this->dependencyManager->get('PhpProfiler');

	}
	
	
	

	
    public function runRestful($serverContext)
    {
        //require_once('FirePHPCore/FirePHP.class.php');      
        $appTimer = $this->phpProfiler->startTimer('#####XXXXXXX A1A1-COMPLETE_RUN XXXXXXXXXXXX################');
        
        $serverContext->startSession();
        
        $request = $serverContext->getRequest();
        
        $response = new \PivoleUndPavoli\Response();
        


        // check for options-reuqest
        if ($request->getRequestMethod() === 'OPTIONS')
        {
          $appTimer->stop();
          
          $profilerJson = json_encode(array(
              'phpLog' => $this->phpProfiler->getHash(),
              'dbLog' => $this->mySqlProfiler->getHash()
          ));
          
          return $response;
        }        

        
        
        $this->getRouteManager()->analyzeRequest($request);
        
        $frontController = new \PivoleUndPavoli\FrontController($this);
        $frontController->setDependencyManager($this->dependencyManager);
        
        try
        {
            $frontController->dispatch($request,$response);
        }
        catch (ZeitfadenException $e)
        {
            die($e->getMessage());
        }
        catch (ZeitfadenNoMatchException $e)
        {
            die($e->getMessage());
        }
        
        $appTimer->stop();
        
        $profilerJson = json_encode(array(
            'phpLog' => $this->phpProfiler->getHash(),
            'dbLog' => $this->mySqlProfiler->getHash()
        ));
        
        //header("ZeitfadenProfiler: ".$profilerJson);
        $response->addHeader("ZeitfadenProfiler: ".$profilerJson);
        
        $serverContext->updateSession($request->getSession());
        
        return $response;
        
    }
		
	
	
	public function getRouteManager()
	{
		$routeManager = new \PivoleUndPavoli\RouteManager();
		

		$routeManager->addRoute(new \PivoleUndPavoli\Route(
			'/:controller/:action/*',
			array()
		));
		
    								
		return $routeManager;
	}
	
	
	
	protected function configureDependencies()
	{
		$dm = SL\DependencyManager::getInstance();
				
		$depList = $dm->registerDependencyManagedService(new SL\ManagedSingleton('SqlProfiler','\Tiro\Profiler'));
		
		$depList = $dm->registerDependencyManagedService(new SL\ManagedSingleton('PhpProfiler','\Tiro\Profiler'));


    $depList = $dm->registerDependencyManagedService(new SL\ManagedService('InstantScheduler','\CachedImageService\InstantScheduler'));
    $depList->addDependency('CachedVideoService', new SL\ManagedComponent('StationFlyVideoService','\CachedImageService\StationFlyVideoService'));

    $depList = $dm->registerDependencyManagedService(new SL\ManagedService('ZeitfadenVideoScheduler','\CachedImageService\OldVideoScheduler'));

    
    $depList = $dm->registerDependencyManagedService(new SL\ManagedService('StationFlyImageService', '\CachedImageService\FlyImageService'));
    $depList->addDependency('Profiler', new SL\ManagedComponent('PhpProfiler'));
						
    $depList = $dm->registerDependencyManagedService(new SL\ManagedService('StationFlyVideoService', '\CachedImageService\FlyVideoService'));
    $depList->addDependency('Profiler', new SL\ManagedComponent('PhpProfiler'));
    $depList->addDependency('Scheduler', new SL\ManagedComponent('ZeitfadenVideoScheduler'));

            		
    $depList = $dm->registerDependencyManagedService(new SL\ManagedService('ImageController'));
    $depList->addDependency('ImageCacheServiceProvider', new SL\ManagedComponentProvider('StationFlyImageService'));
    $depList->addDependency('Profiler', new SL\ManagedComponent('PhpProfiler'));
            		
    $depList = $dm->registerDependencyManagedService(new SL\ManagedService('VideoController'));
    $depList->addDependency('VideoCacheServiceProvider', new SL\ManagedComponentProvider('StationFlyVideoService'));
    $depList->addDependency('Profiler', new SL\ManagedComponent('PhpProfiler'));
		
	}
	
}




