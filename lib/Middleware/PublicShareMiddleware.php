<?php

namespace OCA\FileSharingInvalidNotifier\Middleware;

use OCA\Files_Sharing\Controller\ShareController;
use OCA\FileSharingInvalidNotifier\AppInfo\Application;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Middleware;
use OCP\IConfig;
use OCP\IGroup;
use OCP\IUser;
use Psr\Log\LoggerInterface;
use Swift_TransportException;

class PublicShareMiddleware extends Middleware {
	/** @var IConfig */
	private $config;
	private $log;
	public function __construct(
		IConfig $config,
		LoggerInterface $log
	) {
		$this->config = $config;
		$this->log = $log;
	}

	public function afterController($controller, $methodName, Response $response) {
		if (!$controller instanceof ShareController) {
			return $response;
		}

		// Only accept 404
		$status = $response->getStatus();
		if ($status !== 404) {
			return $response;
		}

		// Only accept if appconfig invalid_link_group is not empty
		$invalidLinkGroup = $this->config->getAppValue(Application::APP_ID, 'invalid_link_group');
		if (!$invalidLinkGroup) {
			return $response;
		}

		// Only accept if group exists
		$group = \OC::$server->getGroupManager()->get($invalidLinkGroup);
		if (!$group instanceof IGroup) {
			return $response;
		}

		// Only accept if have user with email
		$users = $group->getUsers();
		$users = array_filter($users, function (IUser $user) {
			$email = $user->getEMailAddress();
			return !empty($email);
		});
		if (empty($users)) {
			return $response;
		}

		// Send email
		$token = $controller->getToken();
		$this->sendEmail($users, $token);

		return $response;
	}

	/**
	 * @param $users IUser[]
	 * @param $token string
	 */
	private function sendEmail(array $users, string $token) {
		foreach ($users as $user) {
			$email = $user->getEMailAddress();
			$displayName = $user->getDisplayName();
			/** @var IMailer */
			$mailer = \OC::$server->getMailer();
	
			$message = $mailer->createMessage();
			$message->setSubject('Invalid link');
			$message->setTo([$email => $displayName]);
			$message->setPlainBody('Invalid link accessed: ' . $token);
			try {
				$mailer->send($message);
			} catch (Swift_TransportException $e) {
				$this->log->error($e->getMessage(), ['exception' => $e]);
			}
		}
	}
}
