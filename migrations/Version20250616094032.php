<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250616094032 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE devis DROP FOREIGN KEY FK_8B27C52B4A4A3511
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE devis ADD CONSTRAINT FK_8B27C52B4A4A3511 FOREIGN KEY (vehicule_id) REFERENCES vehicule (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE devis DROP FOREIGN KEY FK_8B27C52B4A4A3511
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE devis ADD CONSTRAINT FK_8B27C52B4A4A3511 FOREIGN KEY (vehicule_id) REFERENCES vehicule (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
    }
}
