<?php

namespace OCA\ShareImport\AppInfo;

// use OCA\ShareImport\Middleware\PublicShareMiddleware;

use OCA\ShareImport\Middleware\MyPublicShareMiddleware;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;

class Application extends App implements IBootstrap {
	public const APP_ID = 'share_import';

	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);
	}

	public function register(IRegistrationContext $context): void {
	}

	public function boot(IBootContext $context): void {
		$filesSharing = \OC::$server->getRegisteredAppContainer('files_sharing');
		$filesSharing->registerMiddleWare(MyPublicShareMiddleware::class);
	}
}
