<?php
namespace MIWeb\Community\Controller;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Exception;
use Neos\Flow\I18n\Translator;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Security\Authentication\Controller\AbstractAuthenticationController;
use Neos\Flow\Security\Exception\AuthenticationRequiredException;
use Neos\Error\Messages as Error;
/**
 * Controller for displaying a login/logout form and authenticating/logging out "frontend users"
 */
class AuthenticationController extends AbstractAuthenticationController {
	/**
	 * @var Translator
	 * @Flow\Inject
	 */
	protected $translator;

	/**
	 * @Flow\InjectConfiguration(package="MIWeb.Community")
	 * @var string
	 */
	protected $settings;

	/**
	 * @Flow\InjectConfiguration(package="MIWeb.Community", path="translation.packageKey")
	 * @var string
	 */
	protected $translationPackageKey;

	/**
	 * @Flow\InjectConfiguration(package="MIWeb.Community", path="translation.sourceName")
	 * @var string
	 */
	protected $translationSourceName;

	public function initializeAction() {
		//throw new Exception(print_r($this->request,true));
		//die(print_r($this->request,true));
		die($this->request->getControllerName() . ' -> ' . $this->request->getControllerActionName());
	}

	/**
	 * @return void
	 */
	public function indexAction() {
		$this->view->assign('account', $this->securityContext->getAccount());
		$this->view->assign('settings', $this->settings);
	}

	/**
	 * return void
	 */
	public function logoutAction() {
		parent::logoutAction();
		$uri = $this->request->getInternalArgument('__redirectAfterLogoutUri');
		if (empty($uri)) {
			$this->redirect('index');
		} else {
			$this->redirectToUri($uri);
		}
	}

	/**
	 * @param ActionRequest $originalRequest The request that was intercepted by the security framework, NULL if there was none
	 * @return void
	 */
	protected function onAuthenticationSuccess(ActionRequest $originalRequest = null) {
		$uri = $this->request->getInternalArgument('__redirectAfterLoginUri');
		if (empty($uri)) {
			$this->redirect('index');
		} else {
			$this->redirectToUri($uri);
		}
	}

	/**
	 * Create translated FlashMessage and add it to flashMessageContainer
	 *
	 * @param AuthenticationRequiredException $exception
	 * @return void
	 */
	protected function onAuthenticationFailure(AuthenticationRequiredException $exception = null) {
		$title = $this->getTranslationById('authentication.failure.title');
		$message = $this->getTranslationById('authentication.failure.message');
		$this->addFlashMessage($message, $title, Error\Message::SEVERITY_ERROR, [], $exception === null ? 1496914553 : $exception->getCode());
	}

	/**
	 * Get translation by label id for configured source name and package key
	 *
	 * @param string $labelId Key to use for finding translation
	 * @return string Translated message or NULL on failure
	 */
	protected function getTranslationById($labelId) {
		return $this->translator->translateById($labelId, [], null, null, $this->translationSourceName, $this->translationPackageKey);
	}

	/**
	 * Disable the technical error flash message
	 *
	 * @return boolean
	 */
	protected function getErrorFlashMessage() {
		return false;
	}
}
