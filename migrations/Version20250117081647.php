<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250117081647 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task ADD slug VARCHAR(300) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_527EDB255E237E06 ON task (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_527EDB25989D9B62 ON task (slug)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_527EDB255E237E06 ON task');
        $this->addSql('DROP INDEX UNIQ_527EDB25989D9B62 ON task');
        $this->addSql('ALTER TABLE task DROP slug');
    }
}
