<?php
namespace MIWeb\Community\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Account;
use Neos\Media\Domain\Model\Image;
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
	 * @var Image
	 * @ORM\OneToOne
	 */
	protected $icon;

	/**
	 * @return Account
	 */
	public function getAccount() {
		return $this->getAccounts()[0];
	}

	/**
	 * @return Image
	 */
	public function getIcon() {
		return $this->icon;
	}

	/**
	 * @param Image image
	 */
	public function setIcon($icon) {
		$this->icon = $icon;
	}
}
