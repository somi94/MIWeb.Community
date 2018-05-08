<?php
namespace MIWeb\Community\Controller;

use MIWeb\Community\AccountExistsException;
use MIWeb\Community\Domain\Model\User;
use MIWeb\Community\Domain\Service\UserService;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Exception;
use Neos\Flow\I18n\Translator;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\Exception\ForwardException;
use Neos\Flow\Security\Authentication\Controller\AbstractAuthenticationController;
use Neos\Flow\Security\Exception\AuthenticationRequiredException;
use Neos\Error\Messages as Error;
use Neos\Flow\Security\AccountFactory;
use Neos\Flow\Security\AccountRepository;
use Neos\Flow\Security\Context;

/**
 * Controller for displaying a login/logout form and authenticating/logging out "frontend users"
 */
class RegistrationController extends ActionController {
	/**
	 * @var Context
	 * @Flow\Inject
	 */
	protected $securityContext;

	/**
	 * @var Translator
	 * @Flow\Inject
	 */
	protected $translator;

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

	/**
	 * @Flow\InjectConfiguration(package="MIWeb.Community", path="registration")
	 * @var array
	 */
	protected $config;

	/**
	 * @var UserService
	 * @Flow\Inject
	 */
	protected $userService;

	/**
	 * @return void
	 */
	public function indexAction() {
		$this->view->assign('account', $this->securityContext->getAccount());
	}

	/**
	 * @param string $identifier
	 * @param string $password
	 * @Flow\Validate(argumentName="identifier", type="EmailAddress")
	 * @Flow\Validate(argumentName="identifier", type="NotEmpty")
	 * @Flow\Validate(argumentName="password", type="NotEmpty")
	 * @Flow\Validate(argumentName="password", type="StringLength", options={"minimum"=6})
	 * @Flow\Validate(argumentName="password", type="StringLength", options={"minimum"=6})
	 * @return void
	 * @throws \Neos\Flow\Persistence\Exception\IllegalObjectTypeException
	 * @throws ForwardException
	 */
	public function registerAction($identifier,$password,$passwordConfirm) {
		if($password !== $passwordConfirm) {
			$title = $this->getTranslationById('registration.failure.title');
			$message = $this->getTranslationById('registration.failure.message.passwordConfirm');

			$this->addFlashMessage($message, $title, Error\Message::SEVERITY_ERROR, []);
			$this->forwardToReferringRequest();
			return;
		}

		try {
			$this->userService->createUserAccount(new User(), $identifier, $password);
		} catch(AccountExistsException $e) {
			$title = $this->getTranslationById('registration.failure.title');
			$message = $this->getTranslationById('registration.failure.message.existing');

			$this->addFlashMessage($message, $title, Error\Message::SEVERITY_ERROR, []);
			$this->forwardToReferringRequest();
		}
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
}
