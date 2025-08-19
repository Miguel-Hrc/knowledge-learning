<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250816174850 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE certification (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, theme_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, is_obtained TINYINT(1) NOT NULL, date_obtention DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6C3C6D75A76ED395 (user_id), INDEX IDX_6C3C6D7559027487 (theme_id), INDEX IDX_6C3C6D75B03A8386 (created_by_id), INDEX IDX_6C3C6D75896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE command (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, user_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_8ECAEAD4B03A8386 (created_by_id), INDEX IDX_8ECAEAD4896DBBDE (updated_by_id), INDEX IDX_8ECAEAD4A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE command_item (id INT AUTO_INCREMENT NOT NULL, command_id INT DEFAULT NULL, lesson_id INT DEFAULT NULL, course_id INT DEFAULT NULL, price DOUBLE PRECISION NOT NULL, INDEX IDX_BEB18B9333E1689A (command_id), INDEX IDX_BEB18B93CDF80196 (lesson_id), INDEX IDX_BEB18B93591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE course (id INT AUTO_INCREMENT NOT NULL, theme_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_169E6FB959027487 (theme_id), INDEX IDX_169E6FB9B03A8386 (created_by_id), INDEX IDX_169E6FB9896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lesson (id INT AUTO_INCREMENT NOT NULL, course_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, video_name VARCHAR(255) DEFAULT NULL, content LONGTEXT NOT NULL, price DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_F87474F3591CC992 (course_id), INDEX IDX_F87474F3B03A8386 (created_by_id), INDEX IDX_F87474F3896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, command_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', sum DOUBLE PRECISION NOT NULL, means VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6D28840DB03A8386 (created_by_id), INDEX IDX_6D28840D33E1689A (command_id), INDEX IDX_6D28840D896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE theme (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_9775E708B03A8386 (created_by_id), INDEX IDX_9775E708896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL, is_verified TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_lesson (user_id INT NOT NULL, lesson_id INT NOT NULL, INDEX IDX_9D266FCEA76ED395 (user_id), INDEX IDX_9D266FCECDF80196 (lesson_id), PRIMARY KEY(user_id, lesson_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_course (user_id INT NOT NULL, course_id INT NOT NULL, INDEX IDX_73CC7484A76ED395 (user_id), INDEX IDX_73CC7484591CC992 (course_id), PRIMARY KEY(user_id, course_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE certification ADD CONSTRAINT FK_6C3C6D75A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE certification ADD CONSTRAINT FK_6C3C6D7559027487 FOREIGN KEY (theme_id) REFERENCES theme (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE certification ADD CONSTRAINT FK_6C3C6D75B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE certification ADD CONSTRAINT FK_6C3C6D75896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE command ADD CONSTRAINT FK_8ECAEAD4B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE command ADD CONSTRAINT FK_8ECAEAD4896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE command ADD CONSTRAINT FK_8ECAEAD4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE command_item ADD CONSTRAINT FK_BEB18B9333E1689A FOREIGN KEY (command_id) REFERENCES command (id)');
        $this->addSql('ALTER TABLE command_item ADD CONSTRAINT FK_BEB18B93CDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id)');
        $this->addSql('ALTER TABLE command_item ADD CONSTRAINT FK_BEB18B93591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB959027487 FOREIGN KEY (theme_id) REFERENCES theme (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_F87474F3591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_F87474F3B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_F87474F3896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D33E1689A FOREIGN KEY (command_id) REFERENCES command (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE theme ADD CONSTRAINT FK_9775E708B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE theme ADD CONSTRAINT FK_9775E708896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_lesson ADD CONSTRAINT FK_9D266FCEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_lesson ADD CONSTRAINT FK_9D266FCECDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_course ADD CONSTRAINT FK_73CC7484A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_course ADD CONSTRAINT FK_73CC7484591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE certification DROP FOREIGN KEY FK_6C3C6D75A76ED395');
        $this->addSql('ALTER TABLE certification DROP FOREIGN KEY FK_6C3C6D7559027487');
        $this->addSql('ALTER TABLE certification DROP FOREIGN KEY FK_6C3C6D75B03A8386');
        $this->addSql('ALTER TABLE certification DROP FOREIGN KEY FK_6C3C6D75896DBBDE');
        $this->addSql('ALTER TABLE command DROP FOREIGN KEY FK_8ECAEAD4B03A8386');
        $this->addSql('ALTER TABLE command DROP FOREIGN KEY FK_8ECAEAD4896DBBDE');
        $this->addSql('ALTER TABLE command DROP FOREIGN KEY FK_8ECAEAD4A76ED395');
        $this->addSql('ALTER TABLE command_item DROP FOREIGN KEY FK_BEB18B9333E1689A');
        $this->addSql('ALTER TABLE command_item DROP FOREIGN KEY FK_BEB18B93CDF80196');
        $this->addSql('ALTER TABLE command_item DROP FOREIGN KEY FK_BEB18B93591CC992');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB959027487');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9B03A8386');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9896DBBDE');
        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F3591CC992');
        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F3B03A8386');
        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F3896DBBDE');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840DB03A8386');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D33E1689A');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D896DBBDE');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE theme DROP FOREIGN KEY FK_9775E708B03A8386');
        $this->addSql('ALTER TABLE theme DROP FOREIGN KEY FK_9775E708896DBBDE');
        $this->addSql('ALTER TABLE user_lesson DROP FOREIGN KEY FK_9D266FCEA76ED395');
        $this->addSql('ALTER TABLE user_lesson DROP FOREIGN KEY FK_9D266FCECDF80196');
        $this->addSql('ALTER TABLE user_course DROP FOREIGN KEY FK_73CC7484A76ED395');
        $this->addSql('ALTER TABLE user_course DROP FOREIGN KEY FK_73CC7484591CC992');
        $this->addSql('DROP TABLE certification');
        $this->addSql('DROP TABLE command');
        $this->addSql('DROP TABLE command_item');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE lesson');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE theme');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_lesson');
        $this->addSql('DROP TABLE user_course');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
