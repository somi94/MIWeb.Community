<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs! This block will be used as the migration description if getDescription() is not used.
 */
class Version20180505204305 extends AbstractMigration
{

    /**
     * @return string
     */
    public function getDescription()
    {
        return '';
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function up(Schema $schema)
    {
        // this up() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');

        $this->addSql('CREATE TABLE miweb_community_domain_model_user (persistence_object_identifier VARCHAR(40) NOT NULL, PRIMARY KEY(persistence_object_identifier)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE miweb_community_domain_model_user ADD CONSTRAINT FK_7C8519A247A46B0A FOREIGN KEY (persistence_object_identifier) REFERENCES neos_party_domain_model_abstractparty (persistence_object_identifier) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema)
    {
        // this down() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');

        $this->addSql('DROP TABLE miweb_community_domain_model_user');
    }
}
