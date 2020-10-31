-- MySQL Script generated by MySQL Workbench
-- Sat Oct 31 16:57:47 2020
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema yeticave_db
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema yeticave_db
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `yeticave_db` DEFAULT CHARACTER SET utf8 ;
USE `yeticave_db` ;

-- -----------------------------------------------------
-- Table `yeticave_db`.`category`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `yeticave_db`.`category` (
                                                        `category_id` INT NOT NULL AUTO_INCREMENT,
                                                        `category_title` VARCHAR(100) NOT NULL,
                                                        `symbolic_code` VARCHAR(100) NOT NULL,
                                                        PRIMARY KEY (`category_id`),
                                                        UNIQUE INDEX `title_UNIQUE` (`category_title` ASC) VISIBLE)
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `yeticave_db`.`item`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `yeticave_db`.`item` (
                                                    `item_id` INT NOT NULL AUTO_INCREMENT,
                                                    `creation_date` TIMESTAMP(14) NOT NULL,
                                                    `item_title` VARCHAR(100) NOT NULL,
                                                    `description` VARCHAR(255) NOT NULL,
                                                    `image` VARCHAR(255) NOT NULL,
                                                    `start_price` INT NOT NULL,
                                                    `completion_date` TIMESTAMP(14) NOT NULL,
                                                    `bet_step` INT NOT NULL,
                                                    PRIMARY KEY (`item_id`))
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `yeticave_db`.`bet`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `yeticave_db`.`bet` (
                                                   `bet_id` INT NOT NULL AUTO_INCREMENT,
                                                   `bet_date` TIMESTAMP(14) NOT NULL,
                                                   `total` DECIMAL(10,2) NOT NULL,
                                                   PRIMARY KEY (`bet_id`))
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `yeticave_db`.`user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `yeticave_db`.`user` (
                                                    `iser_id` INT NOT NULL AUTO_INCREMENT,
                                                    `registration_date` TIMESTAMP(14) NOT NULL,
                                                    `email` VARCHAR(100) NOT NULL,
                                                    `name` VARCHAR(45) NOT NULL,
                                                    `password` VARCHAR(45) NOT NULL,
                                                    `contacts` VARCHAR(255) NOT NULL,
                                                    PRIMARY KEY (`iser_id`),
                                                    UNIQUE INDEX `email_UNIQUE` (`email` ASC) VISIBLE)
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `yeticave_db`.`author_item`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `yeticave_db`.`author_item` (
                                                           `author_id` INT NOT NULL AUTO_INCREMENT,
                                                           `user_id` INT NOT NULL,
                                                           `item_id` INT NOT NULL,
                                                           PRIMARY KEY (`author_id`),
                                                           INDEX `user_idx` (`user_id` ASC) VISIBLE,
                                                           INDEX `item_idx` (`item_id` ASC) VISIBLE,
                                                           CONSTRAINT `user`
                                                               FOREIGN KEY (`user_id`)
                                                                   REFERENCES `yeticave_db`.`user` (`iser_id`)
                                                                   ON DELETE CASCADE
                                                                   ON UPDATE CASCADE,
                                                           CONSTRAINT `item`
                                                               FOREIGN KEY (`item_id`)
                                                                   REFERENCES `yeticave_db`.`item` (`item_id`)
                                                                   ON DELETE CASCADE
                                                                   ON UPDATE CASCADE)
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `yeticave_db`.`winner_item`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `yeticave_db`.`winner_item` (
                                                           `winner_id` INT NOT NULL AUTO_INCREMENT,
                                                           `user_id` INT NOT NULL,
                                                           `item_id` INT NOT NULL,
                                                           PRIMARY KEY (`winner_id`),
                                                           INDEX `user_idx` (`user_id` ASC) VISIBLE,
                                                           INDEX `item_idx` (`item_id` ASC) VISIBLE,
                                                           CONSTRAINT `user`
                                                               FOREIGN KEY (`user_id`)
                                                                   REFERENCES `yeticave_db`.`user` (`iser_id`)
                                                                   ON DELETE CASCADE
                                                                   ON UPDATE CASCADE,
                                                           CONSTRAINT `item`
                                                               FOREIGN KEY (`item_id`)
                                                                   REFERENCES `yeticave_db`.`item` (`item_id`)
                                                                   ON DELETE CASCADE
                                                                   ON UPDATE CASCADE)
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `yeticave_db`.`category_item`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `yeticave_db`.`category_item` (
                                                             `category_item_id` INT NOT NULL AUTO_INCREMENT,
                                                             `category_id` INT NOT NULL,
                                                             `item_id` INT NOT NULL,
                                                             INDEX `category_idx` (`category_id` ASC) VISIBLE,
                                                             INDEX `item_idx` (`item_id` ASC) VISIBLE,
                                                             PRIMARY KEY (`category_item_id`),
                                                             CONSTRAINT `category`
                                                                 FOREIGN KEY (`category_id`)
                                                                     REFERENCES `yeticave_db`.`category` (`category_id`)
                                                                     ON DELETE CASCADE
                                                                     ON UPDATE CASCADE,
                                                             CONSTRAINT `item`
                                                                 FOREIGN KEY (`item_id`)
                                                                     REFERENCES `yeticave_db`.`item` (`item_id`)
                                                                     ON DELETE CASCADE
                                                                     ON UPDATE CASCADE)
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `yeticave_db`.`user_bet`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `yeticave_db`.`user_bet` (
                                                        `user_bet_id` INT NOT NULL AUTO_INCREMENT,
                                                        `user_id` INT NOT NULL,
                                                        `bet_id` INT NOT NULL,
                                                        `item_id` INT NOT NULL,
                                                        PRIMARY KEY (`user_bet_id`),
                                                        INDEX `user_idx` (`user_id` ASC) VISIBLE,
                                                        INDEX `bet_idx` (`bet_id` ASC) VISIBLE,
                                                        INDEX `item_idx` (`item_id` ASC) VISIBLE,
                                                        CONSTRAINT `user`
                                                            FOREIGN KEY (`user_id`)
                                                                REFERENCES `yeticave_db`.`user` (`iser_id`)
                                                                ON DELETE CASCADE
                                                                ON UPDATE CASCADE,
                                                        CONSTRAINT `bet`
                                                            FOREIGN KEY (`bet_id`)
                                                                REFERENCES `yeticave_db`.`bet` (`bet_id`)
                                                                ON DELETE CASCADE
                                                                ON UPDATE CASCADE,
                                                        CONSTRAINT `item`
                                                            FOREIGN KEY (`item_id`)
                                                                REFERENCES `yeticave_db`.`item` (`item_id`)
                                                                ON DELETE CASCADE
                                                                ON UPDATE CASCADE)
    ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
