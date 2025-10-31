<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251031183708 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE address_entity (id INT AUTO_INCREMENT NOT NULL, country VARCHAR(50) NOT NULL, city VARCHAR(90) NOT NULL, postal_code VARCHAR(90) NOT NULL, street_name VARCHAR(90) NOT NULL, firstname VARCHAR(90) NOT NULL, lastname VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, user_id INT DEFAULT NULL, INDEX IDX_3571C1D7A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE cart (id INT AUTO_INCREMENT NOT NULL, total_price NUMERIC(5, 1) NOT NULL, updated_at DATETIME NOT NULL, user_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_BA388B7A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE cart_item (id INT AUTO_INCREMENT NOT NULL, product_id VARCHAR(255) NOT NULL, product_price NUMERIC(5, 1) NOT NULL, quantity INT NOT NULL, added_at DATETIME NOT NULL, cart_id INT DEFAULT NULL, INDEX IDX_F0FE25271AD5CDBF (cart_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, total_amount NUMERIC(10, 2) NOT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, billing_address_id INT NOT NULL, shipping_address_id INT NOT NULL, payment_method_id INT NOT NULL, cart_id INT DEFAULT NULL, INDEX IDX_F5299398A76ED395 (user_id), UNIQUE INDEX UNIQ_F529939879D0C0E4 (billing_address_id), UNIQUE INDEX UNIQ_F52993984D4CFF2B (shipping_address_id), UNIQUE INDEX UNIQ_F52993985AA1164F (payment_method_id), UNIQUE INDEX UNIQ_F52993981AD5CDBF (cart_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, provider_payment_id VARCHAR(255) NOT NULL, label VARCHAR(255) DEFAULT NULL, provider VARCHAR(100) NOT NULL, is_default TINYINT(1) NOT NULL, user_id INT DEFAULT NULL, INDEX IDX_6D28840DA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, product_id CHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, price NUMERIC(10, 2) NOT NULL, discount_price NUMERIC(10, 2) DEFAULT NULL, stock INT NOT NULL, is_active TINYINT(1) NOT NULL, category VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, image VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_D34A04AD4584665A (product_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, verified TINYINT(1) NOT NULL, role VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, acc_created DATETIME DEFAULT NULL, user_information_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D6494575EE58 (user_information_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_information (id INT AUTO_INCREMENT NOT NULL, firstname VARCHAR(255) DEFAULT NULL, lastname VARCHAR(255) DEFAULT NULL, phone_number VARCHAR(255) DEFAULT NULL, street_address VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, post_code VARCHAR(255) DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_8062D116A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_tokens (id INT AUTO_INCREMENT NOT NULL, verify_token VARCHAR(255) DEFAULT NULL, verify_token_at DATETIME DEFAULT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_CF080AB3A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE address_entity ADD CONSTRAINT FK_3571C1D7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE cart ADD CONSTRAINT FK_BA388B7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE25271AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F529939879D0C0E4 FOREIGN KEY (billing_address_id) REFERENCES address_entity (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993984D4CFF2B FOREIGN KEY (shipping_address_id) REFERENCES address_entity (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993985AA1164F FOREIGN KEY (payment_method_id) REFERENCES payment (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993981AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6494575EE58 FOREIGN KEY (user_information_id) REFERENCES user_information (id)');
        $this->addSql('ALTER TABLE user_information ADD CONSTRAINT FK_8062D116A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_tokens ADD CONSTRAINT FK_CF080AB3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE address_entity DROP FOREIGN KEY FK_3571C1D7A76ED395');
        $this->addSql('ALTER TABLE cart DROP FOREIGN KEY FK_BA388B7A76ED395');
        $this->addSql('ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE25271AD5CDBF');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A76ED395');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F529939879D0C0E4');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993984D4CFF2B');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993985AA1164F');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993981AD5CDBF');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840DA76ED395');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6494575EE58');
        $this->addSql('ALTER TABLE user_information DROP FOREIGN KEY FK_8062D116A76ED395');
        $this->addSql('ALTER TABLE user_tokens DROP FOREIGN KEY FK_CF080AB3A76ED395');
        $this->addSql('DROP TABLE address_entity');
        $this->addSql('DROP TABLE cart');
        $this->addSql('DROP TABLE cart_item');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_information');
        $this->addSql('DROP TABLE user_tokens');
    }
}
