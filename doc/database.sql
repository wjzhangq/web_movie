CREATE  TABLE `wj_movie` (
  `mid` INT(11) NOT NULL AUTO_INCREMENT ,
  `uni_name` VARCHAR(255) NULL ,
  `en_name` VARCHAR(255) NULL ,
  `update` DATE NULL ,
  `cday` DATE NULL ,
  `is_del` TINYINT NULL DEFAULT 0 ,
  PRIMARY KEY (`mid`) ,
  UNIQUE INDEX `uni_name` (`uni_name`)) ENGINE = MyISAM COMMENT = '电影原始表';

CREATE  TABLE `wj_douban` (
  `mid` INT(11) NOT NULL ,
  `douban_id` VARCHAR(45) NULL ,
  `en_name` VARCHAR(255) NULL ,
  `zh_name` VARCHAR(255) NULL ,
  `pubdate` DATE NULL ,
  `imdb` VARCHAR(255) NULL ,
  `img_url` VARCHAR(255) NULL ,
  `summary` TEXT NULL ,
  `tags` TEXT NULL ,
  `update` DATE NULL ,
  PRIMARY KEY (`mid`) ) ENGINE = MyISAM COMMENT = '豆瓣表';
