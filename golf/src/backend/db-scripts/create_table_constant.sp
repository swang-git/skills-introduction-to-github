use golf_dev;
CREATE TABLE `constant` (
  `id` varchar(45) NOT NULL,
  `double_value` DOUBLE DEFAULT NULL,
  PRIMARY KEY (`id`)
);
INSERT INTO constant VALUE ('back_n_days', -60);
