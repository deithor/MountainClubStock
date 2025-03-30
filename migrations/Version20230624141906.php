<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230624141906 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add tables for basket and rental history';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE basket_item (id INT AUTO_INCREMENT NOT NULL, item_id INT NOT NULL, user_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_D4943C2B126F525E (item_id), INDEX IDX_D4943C2BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(40) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE item (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, name VARCHAR(255) NOT NULL, quantity INT NOT NULL, price INT NOT NULL, description VARCHAR(511) DEFAULT NULL, INDEX IDX_1F1B251E12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rental_record (id INT AUTO_INCREMENT NOT NULL, item_id INT NOT NULL, lender_id INT NOT NULL, borrower_id INT NOT NULL, quantity INT NOT NULL, borrowed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', returned_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', comment VARCHAR(255) DEFAULT NULL, INDEX IDX_AB1A8BF6126F525E (item_id), INDEX IDX_AB1A8BF6855D3E3D (lender_id), INDEX IDX_AB1A8BF611CE312B (borrower_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE basket_item ADD CONSTRAINT FK_D4943C2B126F525E FOREIGN KEY (item_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE basket_item ADD CONSTRAINT FK_D4943C2BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE rental_record ADD CONSTRAINT FK_AB1A8BF6126F525E FOREIGN KEY (item_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE rental_record ADD CONSTRAINT FK_AB1A8BF6855D3E3D FOREIGN KEY (lender_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE rental_record ADD CONSTRAINT FK_AB1A8BF611CE312B FOREIGN KEY (borrower_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE basket_item DROP FOREIGN KEY FK_D4943C2B126F525E');
        $this->addSql('ALTER TABLE basket_item DROP FOREIGN KEY FK_D4943C2BA76ED395');
        $this->addSql('ALTER TABLE rental_record DROP FOREIGN KEY FK_AB1A8BF6126F525E');
        $this->addSql('ALTER TABLE rental_record DROP FOREIGN KEY FK_AB1A8BF6855D3E3D');
        $this->addSql('ALTER TABLE rental_record DROP FOREIGN KEY FK_AB1A8BF611CE312B');
        $this->addSql('DROP TABLE basket_item');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE item');
        $this->addSql('DROP TABLE rental_record');
    }
}
