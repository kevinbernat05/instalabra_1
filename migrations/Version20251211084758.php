<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251211084758 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE comentario (id INT AUTO_INCREMENT NOT NULL, texto LONGTEXT NOT NULL, fecha_creacion DATETIME NOT NULL, usuario_id INT NOT NULL, palabra_id INT NOT NULL, INDEX IDX_4B91E702DB38439E (usuario_id), INDEX IDX_4B91E70228EA1B16 (palabra_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE palabra (id INT AUTO_INCREMENT NOT NULL, texto LONGTEXT NOT NULL, fecha_creacion DATETIME NOT NULL, contador_valoraciones INT NOT NULL, usuario_id INT NOT NULL, INDEX IDX_11B8C74DB38439E (usuario_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE seguimiento (id INT AUTO_INCREMENT NOT NULL, fecha_seguimiento DATETIME NOT NULL, seguidor_id INT NOT NULL, seguido_id INT NOT NULL, INDEX IDX_1B2181D2924E960 (seguidor_id), INDEX IDX_1B2181D3572B040 (seguido_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE usuario (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, fecha_registro DATETIME NOT NULL, biografia LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_2265B05DE7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE valoracion (id INT AUTO_INCREMENT NOT NULL, valor SMALLINT NOT NULL, fecha_creacion DATETIME NOT NULL, usuario_id INT NOT NULL, palabra_id INT NOT NULL, INDEX IDX_6D3DE0F4DB38439E (usuario_id), INDEX IDX_6D3DE0F428EA1B16 (palabra_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE comentario ADD CONSTRAINT FK_4B91E702DB38439E FOREIGN KEY (usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE comentario ADD CONSTRAINT FK_4B91E70228EA1B16 FOREIGN KEY (palabra_id) REFERENCES palabra (id)');
        $this->addSql('ALTER TABLE palabra ADD CONSTRAINT FK_11B8C74DB38439E FOREIGN KEY (usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE seguimiento ADD CONSTRAINT FK_1B2181D2924E960 FOREIGN KEY (seguidor_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE seguimiento ADD CONSTRAINT FK_1B2181D3572B040 FOREIGN KEY (seguido_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE valoracion ADD CONSTRAINT FK_6D3DE0F4DB38439E FOREIGN KEY (usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE valoracion ADD CONSTRAINT FK_6D3DE0F428EA1B16 FOREIGN KEY (palabra_id) REFERENCES palabra (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comentario DROP FOREIGN KEY FK_4B91E702DB38439E');
        $this->addSql('ALTER TABLE comentario DROP FOREIGN KEY FK_4B91E70228EA1B16');
        $this->addSql('ALTER TABLE palabra DROP FOREIGN KEY FK_11B8C74DB38439E');
        $this->addSql('ALTER TABLE seguimiento DROP FOREIGN KEY FK_1B2181D2924E960');
        $this->addSql('ALTER TABLE seguimiento DROP FOREIGN KEY FK_1B2181D3572B040');
        $this->addSql('ALTER TABLE valoracion DROP FOREIGN KEY FK_6D3DE0F4DB38439E');
        $this->addSql('ALTER TABLE valoracion DROP FOREIGN KEY FK_6D3DE0F428EA1B16');
        $this->addSql('DROP TABLE comentario');
        $this->addSql('DROP TABLE palabra');
        $this->addSql('DROP TABLE seguimiento');
        $this->addSql('DROP TABLE usuario');
        $this->addSql('DROP TABLE valoracion');
    }
}
