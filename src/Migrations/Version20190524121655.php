<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190524121655 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user ADD email VARCHAR(255) NOT NULL, ADD `key` VARCHAR(255) NOT NULL, CHANGE position position VARCHAR(255) DEFAULT NULL, CHANGE seniority seniority INT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6498A90ABA9 ON user (`key`)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_8D93D6498A90ABA9 ON user');
        $this->addSql('ALTER TABLE user DROP email, DROP `key`, CHANGE position position VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE seniority seniority INT NOT NULL');
    }
}
