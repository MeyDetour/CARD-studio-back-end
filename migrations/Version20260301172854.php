<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260301172854 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT fk_8d93d649c4cf44dc');
        $this->addSql('ALTER TABLE game DROP CONSTRAINT fk_232b318c3da5256d');
        $this->addSql('DROP SEQUENCE image_id_seq CASCADE');
        $this->addSql('ALTER TABLE image DROP CONSTRAINT fk_c53d045fe48fd905');
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP INDEX uniq_232b318c3da5256d');
        $this->addSql('ALTER TABLE game ADD image TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE game DROP image_id');
        $this->addSql('DROP INDEX uniq_8d93d649c4cf44dc');
        $this->addSql('ALTER TABLE "user" DROP profile_image_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE image_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE image (id SERIAL NOT NULL, game_id INT DEFAULT NULL, image_name VARCHAR(255) DEFAULT NULL, image_size INT DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_c53d045fe48fd905 ON image (game_id)');
        $this->addSql('COMMENT ON COLUMN image.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT fk_c53d045fe48fd905 FOREIGN KEY (game_id) REFERENCES game (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD profile_image_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT fk_8d93d649c4cf44dc FOREIGN KEY (profile_image_id) REFERENCES image (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_8d93d649c4cf44dc ON "user" (profile_image_id)');
        $this->addSql('ALTER TABLE game ADD image_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE game DROP image');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT fk_232b318c3da5256d FOREIGN KEY (image_id) REFERENCES image (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_232b318c3da5256d ON game (image_id)');
    }
}
