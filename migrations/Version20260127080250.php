<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260127080250 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comentario ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE mensaje DROP FOREIGN KEY `FK_9B631D01B55CDF0B`');
        $this->addSql('ALTER TABLE mensaje ADD CONSTRAINT FK_9B631D01B55CDF0B FOREIGN KEY (palabra_compartida_id) REFERENCES palabra (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE palabra ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE usuario ADD roles JSON DEFAULT NULL, ADD is_blocked TINYINT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comentario DROP deleted_at');
        $this->addSql('ALTER TABLE mensaje DROP FOREIGN KEY FK_9B631D01B55CDF0B');
        $this->addSql('ALTER TABLE mensaje ADD CONSTRAINT `FK_9B631D01B55CDF0B` FOREIGN KEY (palabra_compartida_id) REFERENCES palabra (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE palabra DROP deleted_at');
        $this->addSql('ALTER TABLE usuario DROP roles, DROP is_blocked');
    }
}
