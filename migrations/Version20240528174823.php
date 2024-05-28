<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240528174823 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('DROP INDEX author_id ON comment');
        $this->addSql('DROP INDEX post_id ON comment');
        $this->addSql('ALTER TABLE comment ADD author_id_id INT NOT NULL, ADD post_id_id INT NOT NULL, ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP createdAt, DROP updatedAt, DROP author_id, DROP post_id, CHANGE content content LONGTEXT NOT NULL, CHANGE reported reported TINYINT(1) NOT NULL');
        $this->addSql('CREATE INDEX IDX_9474526C69CCBE9A ON comment (author_id_id)');
        $this->addSql('CREATE INDEX IDX_9474526CE85F12B8 ON comment (post_id_id)');
        $this->addSql('DROP INDEX author_id ON contact_message');
        $this->addSql('ALTER TABLE contact_message ADD send_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP sendAt, CHANGE content content LONGTEXT NOT NULL, CHANGE author_id author_id_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_2C9211FE69CCBE9A ON contact_message (author_id_id)');
        $this->addSql('DROP INDEX author_id ON post');
        $this->addSql('ALTER TABLE post ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP createdAt, DROP updatedAt, CHANGE content content LONGTEXT NOT NULL, CHANGE author_id author_id_id INT NOT NULL');
        $this->addSql('CREATE INDEX IDX_5A8A6C8D69CCBE9A ON post (author_id_id)');
        $this->addSql('DROP INDEX email ON user');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C69CCBE9A');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CE85F12B8');
        $this->addSql('DROP INDEX IDX_9474526C69CCBE9A ON comment');
        $this->addSql('DROP INDEX IDX_9474526CE85F12B8 ON comment');
        $this->addSql('ALTER TABLE comment ADD createdAt DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP, ADD author_id INT NOT NULL, ADD post_id INT NOT NULL, DROP author_id_id, DROP post_id_id, DROP created_at, DROP updated_at, CHANGE content content TEXT NOT NULL, CHANGE reported reported TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('CREATE INDEX author_id ON comment (author_id)');
        $this->addSql('CREATE INDEX post_id ON comment (post_id)');
        $this->addSql('ALTER TABLE contact_message DROP FOREIGN KEY FK_2C9211FE69CCBE9A');
        $this->addSql('DROP INDEX IDX_2C9211FE69CCBE9A ON contact_message');
        $this->addSql('ALTER TABLE contact_message ADD sendAt DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, DROP send_at, CHANGE content content TEXT NOT NULL, CHANGE author_id_id author_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX author_id ON contact_message (author_id)');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D69CCBE9A');
        $this->addSql('DROP INDEX IDX_5A8A6C8D69CCBE9A ON post');
        $this->addSql('ALTER TABLE post ADD createdAt DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP, DROP created_at, DROP updated_at, CHANGE content content TEXT NOT NULL, CHANGE author_id_id author_id INT NOT NULL');
        $this->addSql('CREATE INDEX author_id ON post (author_id)');
        $this->addSql('CREATE UNIQUE INDEX email ON user (email(191))');
    }
}
