<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230428213411 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE stock_item_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE stock_item 
                (id INT NOT NULL, 
                item_number VARCHAR(255) NOT NULL, 
                item_name VARCHAR(255) NOT NULL, 
                item_description TEXT DEFAULT NULL, 
                supplier_cost NUMERIC(2, 2) NOT NULL, 
                price NUMERIC(10, 2) NOT NULL, 
                PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE stock_item_id_seq CASCADE');
        $this->addSql('DROP TABLE stock_item');
    }
}
