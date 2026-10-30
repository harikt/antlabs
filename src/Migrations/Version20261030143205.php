<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261030143205 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create tasks table (and messenger_messages for the doctrine messenger transport)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE tasks (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, status VARCHAR(20) DEFAULT 'todo' NOT NULL, due_date DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX idx_tasks_status_due_date (status, due_date), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4");
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE tasks');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
