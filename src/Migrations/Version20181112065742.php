<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181112065742 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE email_account (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(100) NOT NULL, server VARCHAR(150) NOT NULL, port VARCHAR(4) NOT NULL, protocol VARCHAR(10) DEFAULT NULL, username VARCHAR(150) NOT NULL, password VARCHAR(50) NOT NULL, INDEX IDX_C0F63E6BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE email_account ADD CONSTRAINT FK_C0F63E6BA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
      
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE email_account');
        $this->addSql('ALTER TABLE companies CHANGE name name VARCHAR(120) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE address address VARCHAR(150) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE city city VARCHAR(70) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE state state VARCHAR(125) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE postcode postcode VARCHAR(12) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE phone phone VARCHAR(32) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE mobile mobile VARCHAR(32) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE global_menuoptions CHANGE id_company id_company INT DEFAULT NULL');
        $this->addSql('ALTER TABLE global_menuoptions RENAME INDEX fk_menuoptions_companies TO FK_80A2105E9122A03F');
        $this->addSql('ALTER TABLE notifications CHANGE user_id user_id INT DEFAULT NULL, CHANGE usergroup_id usergroup_id INT DEFAULT NULL, CHANGE users_id users_id INT DEFAULT NULL, CHANGE readed readed TINYINT(1) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE user_groups CHANGE company_id company_id INT DEFAULT NULL, CHANGE roles roles LONGTEXT DEFAULT NULL COLLATE utf8mb4_bin');
        $this->addSql('ALTER TABLE users CHANGE roles roles LONGTEXT NOT NULL COLLATE utf8mb4_bin, CHANGE name name VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE firstname firstname VARCHAR(150) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
    }
}
