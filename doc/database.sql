CREATE  TABLE `wj_movie` (
  `mid` INT(11) NOT NULL AUTO_INCREMENT ,
  `uni_name` VARCHAR(255) NULL ,
  `en_name` VARCHAR(255) NULL ,
  `update` DATE NULL ,
  `cday` DATE NULL ,
  `is_del` TINYINT NULL DEFAULT 0 ,
  PRIMARY KEY (`mid`) ,
  UNIQUE INDEX `uni_name` (`uni_name`))
ENGINE = MyISAM
COMMENT = '电影原始表';