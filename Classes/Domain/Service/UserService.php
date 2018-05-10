<?php
namespace MIWeb\Community\Domain\Service;

use MIWeb\Community\AccountExistsException;
use MIWeb\Community\Domain\Model\User;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Exception;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Flow\Security\Account;
use Neos\Flow\Security\AccountFactory;
use Neos\Flow\Security\AccountRepository;
use Neos\Flow\Security\Cryptography\HashService;
use Neos\Flow\Security\Policy\PolicyService;
use Neos\Media\Domain\Model\Image;
use Neos\Media\Domain\Repository\ImageRepository;
use Neos\Party\Domain\Model\AbstractParty;
use Neos\Party\Domain\Repository\PartyRepository;
use Neos\Party\Domain\Service\PartyService;
use Neos\Flow\Security\Context as SecurityContext;

/**
 * This is the Domain Service which acts as a helper for tasks
 * affecting entities inside the Party context.
 *
 * @Flow\Scope("singleton")
 */
class UserService {
	/**
	 * @var AccountFactory
	 * @Flow\Inject
	 */
	protected $accountFactory;

	/**
	 * @var SecurityContext
	 * @Flow\Inject
	 */
	protected $securityContext;

	/**
	 * @var AccountRepository
	 * @Flow\Inject
	 */
	protected $accountRepository;

	/**
	 * @var ImageRepository
	 * @Flow\Inject
	 */
	protected $imageRepository;

	/**
	 * @var ResourceManager
	 * @Flow\Inject
	 */
	protected $resourceManager;

	/**
	 * @var PartyRepository
	 * @Flow\Inject
	 */
	protected $partyRepository;

	/**
	 * @var PartyService
	 * @Flow\Inject
	 */
	protected $partyService;

	/**
	 * @Flow\InjectConfiguration(package="MIWeb.Community", path="registration")
	 * @var array
	 */
	protected $communityConfig;

	/**
	 * @var HashService
	 * @Flow\Inject
	 */
	protected $hashService;

	/**
	 * @var PolicyService
	 * @Flow\Inject
	 */
	protected $policyService;

	/**
	 * @param User $user
	 * @param string $identifier
	 * @param string $password
	 * @param array $iconInfo
	 * @param array $roleIdentifiers
	 * @param string $authenticationProviderName
	 * @return Account
	 * @throws AccountExistsException
	 */
	public function createUserAccount($user, $identifier, $password, $iconInfo = null, $roleIdentifiers = null, $authenticationProviderName = null) {
		if(!$authenticationProviderName) {
			$authenticationProviderName = $this->communityConfig['defaultAuthenticationProvider'];
		}
		if(!$roleIdentifiers) {
			$roleIdentifiers = $this->communityConfig['defaultRoles'];
		}

		$existing = $this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($identifier,$authenticationProviderName);
		if($existing) {
			throw new AccountExistsException("Account identifier '$identifier' already existing.");
		}

		if($iconInfo) {
			$this->updateIcon($user, $iconInfo);
		}

		$account = $this->accountFactory->createAccountWithPassword($identifier, $password, $roleIdentifiers, $authenticationProviderName);

		$this->partyService->assignAccountToParty($account, $user);
		$this->partyRepository->add($user);

		$this->accountRepository->add($account);

		return $account;
	}

	/**
	 * @param User $user
	 * @param string $identifier
	 * @param string $password
	 * @param array $iconInfo
	 * @param array $roleIdentifiers
	 * @param string $authenticationProviderName
	 * @return Account
	 * @throws AccountExistsException
	 */
	public function updateUserAccount($user, $identifier = null, $password = null, $iconInfo = null, $roleIdentifiers = null, $authenticationProviderName = null) {
		if(!$authenticationProviderName) {
			$authenticationProviderName = $this->communityConfig['defaultAuthenticationProvider'];
		}
		/**
		 * @var Account $account
		 */
		$account = $user->getAccounts()[0];

		if($identifier && $identifier != $account->getAccountIdentifier()) {
			$existing = $this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($identifier,$authenticationProviderName);
			if($existing) {
				throw new AccountExistsException("Account identifier '$identifier' already existing.");
			}

			$account->setAccountIdentifier($identifier);
		}

		if($password) {
			$account->setCredentialsSource($this->hashService->hashPassword($password, 'default'));
		}

		if($iconInfo) {
			$this->updateIcon($user, $iconInfo);
		}

		if($roleIdentifiers) {
			$roles = [];
			foreach ($roleIdentifiers as $roleIdentifier) {
				$roles[] = $this->policyService->getRole($roleIdentifier);
			}
			$account->setRoles($roles);
		}

		$this->partyRepository->update($user);
		$this->accountRepository->update($account);
	}

	/**
	 * @param User $user
	 * @param array $iconInfo
	 */
	public function updateIcon($user,$iconInfo = null) {
		$currentIcon = $user->getIcon();
		if($currentIcon) {
			$this->resourceManager->deleteResource($currentIcon->getResource());
			$this->imageRepository->remove($currentIcon);
		}

		if(!$iconInfo) {
			return;
		}

		$iconResource = $this->resourceManager->importUploadedResource($iconInfo);

		$image = new Image($iconResource);
		$this->imageRepository->add($image);
		$user->setIcon($image);

		$this->partyRepository->update($user);
	}

	/**
	 * @return User
	 */
	public function getAuthenticatedUser() {
		$account = $this->securityContext->getAccount();
		if(!$account) {
			return null;
		}

		return $this->partyService->getAssignedPartyOfAccount($account);
	}

	/**
	 * @return \Neos\Flow\Persistence\QueryResultInterface<User>
	 */
	public function getAll() {
		return $this->partyRepository->findAll();
	}

	/**
	 * @return int
	 */
	public function countAll() {
		return $this->partyRepository->countAll();
	}
}
