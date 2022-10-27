-- ---------------------------------------------------------------------
-- mondial_relay_home_delivery_freeshipping
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `mondial_relay_home_delivery_freeshipping`;

CREATE TABLE `mondial_relay_home_delivery_freeshipping`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `active` TINYINT(1) DEFAULT 0,
    `freeshipping_from` DECIMAL(18,2),
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_mondial_relay_home_delivery_freeshipping_area_id`
        FOREIGN KEY (`id`)
            REFERENCES `area` (`id`)
            ON UPDATE RESTRICT
            ON DELETE CASCADE
) ENGINE=InnoDB;


-- ---------------------------------------------------------------------
-- mondial_relay_home_delivery_area_freeshipping
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `mondial_relay_home_delivery_area_freeshipping`;

CREATE TABLE `mondial_relay_home_delivery_area_freeshipping`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `area_id` INTEGER NOT NULL,
    `cart_amount` DECIMAL(18,2) DEFAULT 0.00,
    PRIMARY KEY (`id`),
    INDEX `fi_mondial_relay_home_delivery_area_freeshipping_area_id` (`area_id`),
    CONSTRAINT `fk_mondial_relay_home_delivery_area_freeshipping_area_id`
        FOREIGN KEY (`area_id`)
            REFERENCES `area` (`id`)
            ON UPDATE RESTRICT
            ON DELETE RESTRICT
) ENGINE=InnoDB;