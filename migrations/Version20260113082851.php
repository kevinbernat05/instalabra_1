<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260113082851 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mensaje (id INT AUTO_INCREMENT NOT NULL, contenido LONGTEXT DEFAULT NULL, fecha_envio DATETIME NOT NULL, leido TINYINT NOT NULL, remitente_id INT NOT NULL, destinatario_id INT NOT NULL, palabra_compartida_id INT DEFAULT NULL, INDEX IDX_9B631D011C3E945F (remitente_id), INDEX IDX_9B631D01B564FBC1 (destinatario_id), INDEX IDX_9B631D01B55CDF0B (palabra_compartida_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE mensaje ADD CONSTRAINT FK_9B631D011C3E945F FOREIGN KEY (remitente_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE mensaje ADD CONSTRAINT FK_9B631D01B564FBC1 FOREIGN KEY (destinatario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE mensaje ADD CONSTRAINT FK_9B631D01B55CDF0B FOREIGN KEY (palabra_compartida_id) REFERENCES palabra (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mensaje DROP FOREIGN KEY FK_9B631D011C3E945F');
        $this->addSql('ALTER TABLE mensaje DROP FOREIGN KEY FK_9B631D01B564FBC1');
        $this->addSql('ALTER TABLE mensaje DROP FOREIGN KEY FK_9B631D01B55CDF0B');
        $this->addSql('DROP TABLE mensaje');
    }
}
