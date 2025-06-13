<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250613135017 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE carburant (id INT AUTO_INCREMENT NOT NULL, type_carburant VARCHAR(150) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE vehicule (id INT AUTO_INCREMENT NOT NULL, carburant_id INT DEFAULT NULL, km INT DEFAULT NULL, annee_fabrication DATE DEFAULT NULL, marque VARCHAR(100) NOT NULL, modele VARCHAR(150) NOT NULL, INDEX IDX_292FFF1D32DAAD24 (carburant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vehicule ADD CONSTRAINT FK_292FFF1D32DAAD24 FOREIGN KEY (carburant_id) REFERENCES carburant (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE vehicule DROP FOREIGN KEY FK_292FFF1D32DAAD24
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE carburant
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE vehicule
        SQL);
    }
}
