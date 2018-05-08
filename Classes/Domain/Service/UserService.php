<?php
namespace MIWeb\Community\Domain\Service;

use MIWeb\Community\AccountExistsException;
use MIWeb\Community\Domain\Model\User;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Exception;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Security\Account;
use Neos\Flow\Security\AccountFactory;
use Neos\Flow\Security\AccountRepository;
use Neos\Flow\Security\Cryptography\HashService;
use Neos\Flow\Security\Policy\PolicyService;
use Neos\Party\Domain\Model\AbstractParty;
use Neos\Party\Domain\Repository\PartyRepository;
use Neos\Party\Domain\Service\PartyService;

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
	 * @var AccountRepository
	 * @Flow\Inject
	 */
	protected $accountRepository;

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
	 * @param array $roleIdentifiers
	 * @param string $authenticationProviderName
	 * @return Account
	 * @throws AccountExistsException
	 */
	public function createUserAccount($user, $identifier, $password, $roleIdentifiers = null, $authenticationProviderName = null) {
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
	 * @param array $roleIdentifiers
	 * @param string $authenticationProviderName
	 * @return Account
	 * @throws AccountExistsException
	 */
	public function updateUserAccount($user, $identifier = null, $password = null, $roleIdentifiers = null, $authenticationProviderName = null) {
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

	public function getAll() {
		return $this->partyRepository->findAll();
	}
}
