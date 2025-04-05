<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250405045744 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add full name and balance amount for user';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD first_name VARCHAR(255) NOT NULL, ADD last_name VARCHAR(255) NOT NULL, ADD middle_name VARCHAR(255) DEFAULT NULL, ADD balance INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP first_name, DROP last_name, DROP middle_name, DROP balance');
    }
}
