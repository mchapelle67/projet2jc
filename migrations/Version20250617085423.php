<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250617085423 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE rdv (id INT AUTO_INCREMENT NOT NULL, prestation_id INT NOT NULL, vehicule_id INT NOT NULL, nom VARCHAR(50) NOT NULL, prenom VARCHAR(50) NOT NULL, email VARCHAR(150) NOT NULL, tel VARCHAR(30) DEFAULT NULL, date_rdv DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', date_demande DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', date_modification DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', statut VARCHAR(50) NOT NULL, INDEX IDX_10C31F869E45C554 (prestation_id), UNIQUE INDEX UNIQ_10C31F864A4A3511 (vehicule_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rdv ADD CONSTRAINT FK_10C31F869E45C554 FOREIGN KEY (prestation_id) REFERENCES prestation (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rdv ADD CONSTRAINT FK_10C31F864A4A3511 FOREIGN KEY (vehicule_id) REFERENCES vehicule (id)
        SQL);
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
            ALTER TABLE rdv DROP FOREIGN KEY FK_10C31F869E45C554
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rdv DROP FOREIGN KEY FK_10C31F864A4A3511
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE rdv
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE devis DROP FOREIGN KEY FK_8B27C52B4A4A3511
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE devis ADD CONSTRAINT FK_8B27C52B4A4A3511 FOREIGN KEY (vehicule_id) REFERENCES vehicule (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
    }
}
