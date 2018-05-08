<?php
namespace MIWeb\Community\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Account;
use Neos\Party\Domain\Model\AbstractParty;
use Neos\Party\Domain\Model\ElectronicAddress;
use Neos\Party\Domain\Model\Person;
use Neos\Party\Domain\Model\PersonName;

/**
 * A person
 *
 * @Flow\Entity
 */
class User extends Person
{
	/**
	 * @return Account
	 */
	public function getAccount() {
		return $this->getAccounts()[0];
	}
}
