<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200120150139 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE annonce DROP FOREIGN KEY FK_F65593E59D86650F');
        $this->addSql('ALTER TABLE annonce DROP FOREIGN KEY FK_F65593E5ACEC0FEF');
        $this->addSql('DROP INDEX IDX_F65593E59D86650F ON annonce');
        $this->addSql('DROP INDEX IDX_F65593E5ACEC0FEF ON annonce');
        $this->addSql('ALTER TABLE annonce ADD rubrique_id INT DEFAULT NULL, ADD user_id INT DEFAULT NULL, DROP rubrique_id_id, DROP user_id_id');
        $this->addSql('ALTER TABLE annonce ADD CONSTRAINT FK_F65593E53BD38833 FOREIGN KEY (rubrique_id) REFERENCES rubrique (id)');
        $this->addSql('ALTER TABLE annonce ADD CONSTRAINT FK_F65593E5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_F65593E53BD38833 ON annonce (rubrique_id)');
        $this->addSql('CREATE INDEX IDX_F65593E5A76ED395 ON annonce (user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE annonce DROP FOREIGN KEY FK_F65593E53BD38833');
        $this->addSql('ALTER TABLE annonce DROP FOREIGN KEY FK_F65593E5A76ED395');
        $this->addSql('DROP INDEX IDX_F65593E53BD38833 ON annonce');
        $this->addSql('DROP INDEX IDX_F65593E5A76ED395 ON annonce');
        $this->addSql('ALTER TABLE annonce ADD rubrique_id_id INT NOT NULL, ADD user_id_id INT NOT NULL, DROP rubrique_id, DROP user_id');
        $this->addSql('ALTER TABLE annonce ADD CONSTRAINT FK_F65593E59D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE annonce ADD CONSTRAINT FK_F65593E5ACEC0FEF FOREIGN KEY (rubrique_id_id) REFERENCES rubrique (id)');
        $this->addSql('CREATE INDEX IDX_F65593E59D86650F ON annonce (user_id_id)');
        $this->addSql('CREATE INDEX IDX_F65593E5ACEC0FEF ON annonce (rubrique_id_id)');
    }
}
