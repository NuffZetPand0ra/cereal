<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240912132841 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE manufacturer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE product_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE product_rating_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE product_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE manufacturer (id INT NOT NULL, name VARCHAR(255) NOT NULL, shorthand VARCHAR(1) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3D0AE6DC1D491BEA ON manufacturer (shorthand)');
        $this->addSql('CREATE TABLE product (id INT NOT NULL, mfr INT DEFAULT NULL, type INT DEFAULT NULL, name VARCHAR(255) NOT NULL, calories INT NOT NULL, protein INT NOT NULL, fat INT NOT NULL, sodium INT NOT NULL, fiber DOUBLE PRECISION NOT NULL, carbo DOUBLE PRECISION NOT NULL, sugars INT NOT NULL, potass INT DEFAULT NULL, vitamins INT NOT NULL, shelf INT NOT NULL, weight DOUBLE PRECISION NOT NULL, cups DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D34A04AD32E25D50 ON product (mfr)');
        $this->addSql('CREATE INDEX IDX_D34A04AD8CDE5729 ON product (type)');
        $this->addSql('CREATE TABLE product_rating (id INT NOT NULL, product_id INT DEFAULT NULL, "user" INT DEFAULT NULL, rating DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BAF567864584665A ON product_rating (product_id)');
        $this->addSql('CREATE INDEX IDX_BAF567868D93D649 ON product_rating ("user")');
        $this->addSql('CREATE UNIQUE INDEX product_user ON product_rating (product_id, "user")');
        $this->addSql('CREATE TABLE product_type (id INT NOT NULL, name VARCHAR(15) NOT NULL, shorthand VARCHAR(1) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_13675881D491BEA ON product_type (shorthand)');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME ON "user" (username)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD32E25D50 FOREIGN KEY (mfr) REFERENCES manufacturer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD8CDE5729 FOREIGN KEY (type) REFERENCES product_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_rating ADD CONSTRAINT FK_BAF567864584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_rating ADD CONSTRAINT FK_BAF567868D93D649 FOREIGN KEY ("user") REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE manufacturer_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE product_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE product_rating_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE product_type_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE user_id_seq CASCADE');
        $this->addSql('ALTER TABLE product DROP CONSTRAINT FK_D34A04AD32E25D50');
        $this->addSql('ALTER TABLE product DROP CONSTRAINT FK_D34A04AD8CDE5729');
        $this->addSql('ALTER TABLE product_rating DROP CONSTRAINT FK_BAF567864584665A');
        $this->addSql('ALTER TABLE product_rating DROP CONSTRAINT FK_BAF567868D93D649');
        $this->addSql('DROP TABLE manufacturer');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_rating');
        $this->addSql('DROP TABLE product_type');
        $this->addSql('DROP TABLE "user"');
    }
}
