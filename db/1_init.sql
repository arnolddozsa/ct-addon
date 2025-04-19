SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

#--karbantartási bizonylat
CREATE TABLE `ct_maintenance` (
 `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Táblán belüli id',
 `documentNumber` varchar(100) NOT NULL COMMENT 'Bizonylatszám',
 `maintenanceDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Karbantartás dátuma',
 `createDate` datetime NOT NULL COMMENT 'Létrehozás dátuma',
 `createUserId` int(11) NOT NULL COMMENT 'Létrehozó felhasználó azonosító',
 `modifyDate` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Módosítás dátuma',
 `modifyUserId` int(11) DEFAULT NULL COMMENT 'Módosító felhasználó azonosító',
 `warehouseId` int(11) NOT NULL COMMENT 'Gép (raktár) azonosító',
 `warehouseName` varchar(255) NOT NULL COMMENT 'Gép (raktár) neve',
 `warehouseCode` varchar(100) NOT NULL COMMENT 'Gép (raktár) kódja',
 PRIMARY KEY (`id`),
 UNIQUE KEY `documentNumber` (`documentNumber`),
 KEY `warehouseId` (`warehouseId`),
 KEY `createUserId` (`createUserId`),
 KEY `modifyUserId` (`modifyUserId`),
 CONSTRAINT `fk_ct_maintenance_createUserId` FOREIGN KEY (`createUserId`) REFERENCES `nubes_user` (`id`) ON UPDATE CASCADE,
 CONSTRAINT `fk_ct_maintenance_modifyUserId` FOREIGN KEY (`modifyUserId`) REFERENCES `nubes_user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
 CONSTRAINT `fk_ct_maintenance_warehouseId` FOREIGN KEY (`warehouseId`) REFERENCES `nubes_warehouse` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Karbantartás bizonylat';

#--karbantartási bizonylat tétel
CREATE TABLE `ct_maintenance_item` (
 `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Táblán belüli id',
 `documentId` int(11) NOT NULL COMMENT 'Karbantartás azonosító',
 `name` varchar(255) NOT NULL COMMENT 'Név',
 PRIMARY KEY (`id`),
 KEY `documentId` (`documentId`),
 CONSTRAINT `fk_ct_maintenance_item_documentId` FOREIGN KEY (`documentId`) REFERENCES `ct_maintenance` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Karbantartás tétel';

#--bizonylatszám
INSERT INTO nubes_document_number(namespace,objectType,pattern,validFrom,validTo,lastDocumentNumber) VALUES ('DI\\Model\\Entity\\CityMedia\\Maintenance\\Maintenance','ct_maintenance','KAR{000}{00000000}/{Y}','2021-04-27','','');
INSERT INTO nubes_document_number_numbers(documentNumberId,num,maxNum,indx,length,isIncrement) VALUES ((SELECT id FROM nubes_document_number WHERE namespace = 'DI\\Model\\Entity\\CityMedia\\Maintenance\\Maintenance'),'0','99999999','0','8','1');
INSERT INTO nubes_document_number_numbers(documentNumberId,num,maxNum,indx,length,isIncrement) VALUES ((SELECT id FROM nubes_document_number WHERE namespace = 'DI\\Model\\Entity\\CityMedia\\Maintenance\\Maintenance'),'0','999','1','3','1');


#--2021.05.03. fordítások
INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('Maintenance','maintenance','DEVELOPMENT');
UPDATE nubes_translate_lang SET text = 'Karbantartás',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='maintenance');

INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('List of maintenances','list_of_maintenances','DEVELOPMENT');
UPDATE nubes_translate_lang SET text = 'Karbantartás lista',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='list_of_maintenances');

INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('Maintenance date','maintenance_date','DEVELOPMENT');
UPDATE nubes_translate_lang SET text = 'Karbantartás dátuma',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='maintenance_date');

INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('Warehouse code','warehouse_code','DEVELOPMENT');
UPDATE nubes_translate_lang SET text = 'Raktár kód',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='warehouse_code');

INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('Warehouse name','warehouse_name','DEVELOPMENT');
UPDATE nubes_translate_lang SET text = 'Raktár név',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='warehouse_name');


#--2021.05.04. karbantartás menü
INSERT INTO nubes_menu(id, name, url, parent_id, sort)
SELECT null, 'city_media', 'CityMedia', null, ( COUNT(sort) + 1 ) FROM nubes_menu WHERE parent_id IS NULL;

INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('City-Media','city_media','DEVELOPMENT');
UPDATE nubes_translate_lang SET text = 'City-Media',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='city_media');

INSERT INTO nubes_menu(id, name, url, parent_id, sort)
SELECT null, 'maintenance', 'Maintenance/MaintenanceList', id, (
SELECT COUNT(sort) + 1 FROM nubes_menu WHERE parent_id = (SELECT id FROM nubes_menu WHERE url = 'CityMedia' AND parent_id IS NULL)
) FROM nubes_menu WHERE url = 'CityMedia' AND parent_id IS NULL;



#--2021.05.20. @bencefarkas update
#--szerződés
CREATE TABLE `ct_partner_contract` (
 `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Táblán belüli id',
 `documentNumber` varchar(100) NOT NULL COMMENT 'Bizonylatszám',
 `partnerId` int(11) NOT NULL COMMENT 'Partner azonosító',
 `partnerName` varchar(255) NOT NULL COMMENT 'Partner név',
 `partnerCode` varchar(100) NOT NULL COMMENT 'Partner kód',
 `expiresDate` datetime NOT NULL COMMENT 'Lejárati dátum',
 `status` char(1) NOT NULL DEFAULT 'O' COMMENT 'Státusz',
 `type` char(1) NOT NULL COMMENT 'Típus',
 `createDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Létrehozás dátuma',
 `createUserId` int(11) NOT NULL COMMENT 'Létrehozó felhasználó azonosító',
 `modifyDate` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Módosítás dátuma',
 `modifyUserId` int(11) DEFAULT NULL COMMENT 'Módosító felhasználó azonosító',
 PRIMARY KEY (`id`),
 UNIQUE KEY `documentNumber` (`documentNumber`),
 KEY `partnerId` (`partnerId`),
 KEY `createUserId` (`createUserId`),
 KEY `modifyUserId` (`modifyUserId`),
 CONSTRAINT `fk_ct_partner_contract_createUserId` FOREIGN KEY (`createUserId`) REFERENCES `nubes_user` (`id`) ON UPDATE CASCADE,
 CONSTRAINT `fk_ct_partner_contract_modifyUserId` FOREIGN KEY (`modifyUserId`) REFERENCES `nubes_user` (`id`) ON UPDATE SET NULL,
 CONSTRAINT `fk_ct_partner_contract_partnerId` FOREIGN KEY (`partnerId`) REFERENCES `ct_partner_contract` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='CityMedia szerződés';


#--szerződés tétel
CREATE TABLE `ct_partner_contract_item` (
 `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Táblán belüli id',
 `documentId` int(11) NOT NULL COMMENT 'Szerződés azonosító',
 `warehouseId` int(11) NOT NULL COMMENT 'Raktár (gép) azonosító',
 `warehouseCode` varchar(100) NOT NULL COMMENT 'Raktár (gép) kód',
 `warehouseName` varchar(255) NOT NULL COMMENT 'Raktár (gép) név',
 PRIMARY KEY (`id`),
 KEY `documentId` (`documentId`),
 KEY `warehouseId` (`warehouseId`),
 CONSTRAINT `fk_ct_partner_contract_item_documentId` FOREIGN KEY (`documentId`) REFERENCES `ct_partner_contract` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
 CONSTRAINT `fk_ct_partner_contract_item_warehouseId` FOREIGN KEY (`warehouseId`) REFERENCES `nubes_warehouse` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='CityMedia szerződés tétel';


#--szerződés menü
INSERT INTO nubes_menu(id, name, url, parent_id, sort)
SELECT null, 'contract', 'Partner/PartnerContractList', id, (
SELECT COUNT(sort) + 1 FROM nubes_menu WHERE parent_id = T1.id
) FROM nubes_menu AS T1 where getMenuNamespace(id) = 'CityMedia/';




INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('Contract','contract','DEVELOPMENT');
UPDATE nubes_translate_lang SET text = 'Szerződés',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='contract');

INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('List of contracts','list_of_contracts','DEVELOPMENT');
UPDATE nubes_translate_lang SET text = 'Szerződés lista',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='list_of_contracts');

INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('Expires date','expires_date','DEVELOPMENT');
UPDATE nubes_translate_lang SET text = 'Lejárati dátum',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='expires_date');


#--szerződés bizonylatszám
INSERT INTO nubes_document_number(namespace,objectType,pattern,validFrom,validTo,lastDocumentNumber) VALUES ('DI\\Model\\Entity\\CityMedia\\Partner\\Partner_Contract','ct_partner_contract','SZERZ{0000000}/{Y}','2021-05-20','','');
INSERT INTO nubes_document_number_numbers(documentNumberId,num,maxNum,indx,length,isIncrement) VALUES ((SELECT id FROM nubes_document_number WHERE namespace = 'DI\\Model\\Entity\\CityMedia\\Partner\\Partner_Contract'),'0','9999999','0','7','1');


#--szerződés érvényes értékek
INSERT INTO nubes_data_type(table_schema,table_name,column_name,column_default_value,is_nullable,data_type,sub_type,character_maximum_length,numeric_precision,numeric_scale,column_key,extra,regex,privileges) VALUES ('nubes','ct_partner_contract','status','O','0','char','','1','','','','','','select,insert,update,references');
INSERT INTO nubes_field_valid_values(col_id,value,description) VALUES ((SELECT id FROM nubes_data_type WHERE table_schema = 'nubes' AND table_name = 'ct_partner_contract' AND column_name = 'status'),'O','Nyitott');
INSERT INTO nubes_field_valid_values(col_id,value,description) VALUES ((SELECT id FROM nubes_data_type WHERE table_schema = 'nubes' AND table_name = 'ct_partner_contract' AND column_name = 'status'),'C','Zárt');

INSERT INTO nubes_data_type(table_schema,table_name,column_name,column_default_value,is_nullable,data_type,sub_type,character_maximum_length,numeric_precision,numeric_scale,column_key,extra,regex,privileges) VALUES ('nubes','ct_partner_contract','type','','0','char','','1','','','','','','select,insert,update,references');
INSERT INTO nubes_field_valid_values(col_id,value,description) VALUES ((SELECT id FROM nubes_data_type WHERE table_schema = 'nubes' AND table_name = 'ct_partner_contract' AND column_name = 'type'),'1','Marketing szerződés');
INSERT INTO nubes_field_valid_values(col_id,value,description) VALUES ((SELECT id FROM nubes_data_type WHERE table_schema = 'nubes' AND table_name = 'ct_partner_contract' AND column_name = 'type'),'3','Fix bérleti díjas szerződés');
INSERT INTO nubes_field_valid_values(col_id,value,description) VALUES ((SELECT id FROM nubes_data_type WHERE table_schema = 'nubes' AND table_name = 'ct_partner_contract' AND column_name = 'type'),'2','Jutalékos szerződés');


#--2021.05.12. szerződés megjegyzés mező
ALTER TABLE `ct_partner_contract` ADD `comment` LONGTEXT NULL DEFAULT NULL COMMENT 'Megjegyzés' AFTER `modifyUserId`;

#--partnerId külső kulcs javítás
ALTER TABLE `ct_partner_contract` DROP FOREIGN KEY `fk_ct_partner_contract_partnerId`;
ALTER TABLE `ct_partner_contract` ADD CONSTRAINT `fk_ct_partner_contract_partnerId` FOREIGN KEY (`partnerId`) REFERENCES `nubes_partner`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

#-- Jegyzőkönyvhöz a DB
CREATE TABLE `ct_proceeding` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Táblán belüli id',
  `documentNumber` varchar(100) NOT NULL COMMENT 'Bizonylatszám',
  `type` varchar(1) NOT NULL COMMENT 'Típus',
  `status` varchar(1) NOT NULL COMMENT 'Státusz',
  `objectType` varchar(255) DEFAULT NULL COMMENT 'Objektum típusa',
  `objectId` int(11) DEFAULT NULL COMMENT 'Objektum azonosító',
  `createDate` datetime NOT NULL COMMENT 'Létrehozás dátuma',
  `createUserId` int(11) NOT NULL COMMENT 'Létrehozó felhasználó azonosító',
  `modifyDate` datetime DEFAULT NULL COMMENT 'Módosítás dátuma',
  `modifyUserId` int(11) DEFAULT NULL COMMENT 'Módosító felhasználó azonosító',
  PRIMARY KEY (`id`),
  UNIQUE KEY `documentNumber` (`documentNumber`),
  KEY `fk_ct_proceeding_modifyUserId` (`modifyUserId`),
  KEY `fk_ct_proceeding_createUserId` (`createUserId`),
  CONSTRAINT `fk_ct_proceeding_createUserId` FOREIGN KEY (`createUserId`) REFERENCES `nubes_user` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ct_proceeding_modifyUserId` FOREIGN KEY (`modifyUserId`) REFERENCES `nubes_user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#-- valod mezők az értékekhez

INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('List of proceeding','list_of_proceeding','DEVELOPMENT');UPDATE nubes_translate_lang SET text = 'Jegyzőkönyvek listája',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='list_of_proceeding');

INSERT INTO nubes_data_type(table_schema,table_name,column_name,column_default_value,is_nullable,data_type,sub_type,character_maximum_length,numeric_precision,numeric_scale,column_key,extra,regex,privileges) VALUES ('citymedia','ct_proceeding','type','','0','varchar','','1','','','','','','select,insert,update,references');

INSERT INTO nubes_data_type(table_schema,table_name,column_name,column_default_value,is_nullable,data_type,sub_type,character_maximum_length,numeric_precision,numeric_scale,column_key,extra,regex,privileges) VALUES ('citymedia','ct_proceeding','status','','0','varchar','','1','','','','','','select,insert,update,references');

INSERT INTO nubes_field_valid_values(col_id,value,description) VALUES ((SELECT id FROM nubes_data_type WHERE table_schema = 'citymedia' AND table_name = 'ct_proceeding' AND column_name = 'status'),'O','Nyitott');
INSERT INTO nubes_field_valid_values(col_id,value,description) VALUES ((SELECT id FROM nubes_data_type WHERE table_schema = 'citymedia' AND table_name = 'ct_proceeding' AND column_name = 'status'),'C','Zárt');

INSERT INTO nubes_field_valid_values(col_id,value,description) VALUES ((SELECT id FROM nubes_data_type WHERE table_schema = 'citymedia' AND table_name = 'ct_proceeding' AND column_name = 'type'),'1','Marketing szerződés');
INSERT INTO nubes_field_valid_values(col_id,value,description) VALUES ((SELECT id FROM nubes_data_type WHERE table_schema = 'citymedia' AND table_name = 'ct_proceeding' AND column_name = 'type'),'3','Fix bérleti díjas szerződés');
INSERT INTO nubes_field_valid_values(col_id,value,description) VALUES ((SELECT id FROM nubes_data_type WHERE table_schema = 'citymedia' AND table_name = 'ct_proceeding' AND column_name = 'type'),'2','Jutalékos szerződés');

#--documentNumber a jegyzőkönyvhöz

INSERT INTO `nubes_document_number` (`namespace`, `objectType`, `pattern`, `validFrom`, `validTo`, `lastDocumentNumber`) VALUES ('DI\\Model\\Entity\\CityMedia\\Proceeding\\Proceeding', 'ct_proceeding', 'J{000000}/{Y}', '2021-07-26 00:00:00', NULL, 'J000003/2021');

INSERT INTO `nubes_document_number_numbers` (`documentNumberId`, `num`, `maxNum`, `indx`, `length`, `isIncrement`) VALUES ((SELECT id FROM nubes_document_number WHERE namespace = 'DI\\Model\\Entity\\CityMedia\\Proceeding\\Proceeding'), 3, 999999, 0, 6, 1);

#-- menü, fordítás jegyzőkönyvhöz

INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('Proceeding','proceeding','DEVELOPMENT');UPDATE nubes_translate_lang SET text = 'Jegyzőkönyv',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='proceeding');

INSERT INTO nubes_menu(id, name, url, parent_id, sort)
SELECT null, 'proceeding', 'Proceeding/ProceedingList', id, (
SELECT COUNT(sort) + 1 FROM nubes_menu WHERE parent_id = T1.id
) FROM nubes_menu AS T1 WHERE getMenuNamespace(id) = 'CityMedia/';



INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('Machines','machines','DEVELOPMENT');UPDATE nubes_translate_lang SET text = 'Gépek',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='machines');
INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('View machine','view_machine','DEVELOPMENT');UPDATE nubes_translate_lang SET text = 'Gép megtekintése',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='view_machine');

INSERT INTO nubes_menu(id, name, url, parent_id, sort)
SELECT null, 'map', 'Map', id, (
SELECT COUNT(sort) + 1 FROM nubes_menu WHERE parent_id = T1.id
) FROM nubes_menu AS T1 where getMenuNamespace(id) = 'CityMedia/';


INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('Map','map','DEVELOPMENT');UPDATE nubes_translate_lang SET text = 'Térkép',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='map');


#--üzemanyag 2021.08.24.
INSERT INTO `nubes_item` (`code`, `name`, `foreignName`, `isInventoryItem`, `isIntermediatedService`, `isStoreItem`, `isSellItem`, `isBuyItem`, `isAdvance`, `itemGroupId`, `manufacturerPartnerId`, `customsTariffCode`, `handleType`, `barCode`, `buyUnit`, `buyUnitQuantity`, `buyWidth`, `buyWidthUnit`, `buyLength`, `buyLengthUnit`, `buyHeight`, `buyHeightUnit`, `buyVolume`, `buyVolumeUnit`, `buyWeight`, `buyWeightUnit`, `sellUnit`, `sellUnitQuantity`, `sellWidth`, `sellWidthUnit`, `sellLength`, `sellLengthUnit`, `sellHeight`, `sellHeightUnit`, `sellVolume`, `sellVolumeUnit`, `sellWeight`, `sellWeightUnit`, `vatGroupId`, `inventoryUnit`, `isLevelByWarehouse`, `minimumLevel`, `maximumLevel`, `reorderLevel`, `defaultWarehouseId`, `minimumOrderQuantity`, `comment`, `createDate`, `createUserId`, `modifyDate`, `modifyUserId`) VALUES
('UZEMANYAG', 'Üzemanyag', NULL, 1, 0, 0, 0, 1, 0, 1, NULL, NULL, 0, NULL, 'l', '0.000000', '0.000000', NULL, '0.000000', NULL, '0.000000', NULL, '0.000000', NULL, '0.000000', NULL, 'l', '0.000000', '0.000000', NULL, '0.000000', NULL, '0.000000', NULL, '0.000000', NULL, '0.000000', NULL, NULL, 'l', 0, '0.000000', '0.000000', '0.000000', 1, '0.000000', NULL, '2021-08-24 09:24:32', 1, NULL, NULL);


#--jegyzőkönyv érvényes értékek
UPDATE nubes_field_valid_values SET value = 'O',description = 'Tervezet' WHERE 1 AND col_id=(SELECT id FROM nubes_field_valid_values WHERE table_schema='citymedia' AND table_name='ct_proceeding' AND column_name='status') AND value = 'O';
UPDATE nubes_field_valid_values SET value = 'C',description = 'Számlázott' WHERE 1 AND col_id=(SELECT id FROM nubes_field_valid_values WHERE table_schema='citymedia' AND table_name='ct_proceeding' AND column_name='status') AND value = 'C';
INSERT INTO nubes_field_valid_values(col_id,value,description) VALUES ((SELECT id FROM nubes_field_valid_values WHERE table_schema='citymedia' AND table_name='ct_proceeding' AND column_name='status'),'R','Jóváhagyott');

UPDATE nubes_field_valid_values SET value = '1',description = 'Kihelyezési' WHERE 1 AND col_id=(SELECT id FROM nubes_field_valid_values WHERE table_schema='citymedia' AND table_name='ct_proceeding' AND column_name='type') AND value = '1';
UPDATE nubes_field_valid_values SET value = '2',description = 'Ürítési' WHERE 1 AND col_id=(SELECT id FROM nubes_field_valid_values WHERE table_schema='citymedia' AND table_name='ct_proceeding' AND column_name='type') AND value = '2';
UPDATE nubes_field_valid_values SET value = '3',description = 'Panasz' WHERE 1 AND col_id=(SELECT id FROM nubes_field_valid_values WHERE table_schema='citymedia' AND table_name='ct_proceeding' AND column_name='type') AND value = '3';
INSERT INTO nubes_field_valid_values(col_id,value,description) VALUES ((SELECT id FROM nubes_field_valid_values WHERE table_schema='citymedia' AND table_name='ct_proceeding' AND column_name='type'),'4','Karbantartási');

ALTER TABLE ct_proceeding COMMENT = 'Jegyzőkönyv';




#--Raktár sorozatszám 2021.08.26
ALTER TABLE `nubes_warehouse`
	ADD COLUMN `U_serialNumber` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Sorozatszám' AFTER `isDefault`;


#--update bencefarkas 2021.10.20. jegyzőkönyv oszlopok
ALTER TABLE `ct_proceeding` ADD `partnerId` INT(11) NULL DEFAULT NULL COMMENT 'Partner azonosító' AFTER `objectId`, ADD INDEX (`partnerId`);
ALTER TABLE `ct_proceeding` ADD CONSTRAINT `fk_ct_proceeding_partnerId` FOREIGN KEY (`partnerId`) REFERENCES `nubes_partner`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `ct_proceeding` ADD `warehouseId` INT(11) NULL DEFAULT NULL COMMENT 'Gép raktár azonosító' AFTER `partnerId`, ADD `issueDate` DATETIME NULL DEFAULT NULL COMMENT 'Kihelyezés dátuma' AFTER `warehouseId`, ADD INDEX (`warehouseId`);
ALTER TABLE `ct_proceeding` ADD CONSTRAINT `fk_ct_proceeding_warehouseId` FOREIGN KEY (`warehouseId`) REFERENCES `nubes_warehouse`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `ct_proceeding` ADD `comment` LONGTEXT NULL DEFAULT NULL COMMENT 'Megjegyzés' AFTER `modifyUserId`;



#--fordítások
INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('Placement date','placement_date','DEVELOPMENT');UPDATE nubes_translate_lang SET text = 'Kihelyezés dátuma',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='placement_date');


#--2021.10.25. update bencefarkas jutalékos szerződés oszlopok
ALTER TABLE `ct_partner_contract`
ADD `commission` DECIMAL(19,8) NULL DEFAULT NULL COMMENT 'Jutalék' AFTER `comment`;


#--2021.10.27 Géplista extra infók és maps api key AIzaSyAxFmwrdqihYceXJl08WoNSrocwH_i7-RU
INSERT INTO `nubes_company_data` (`code`, `value`) VALUES ('googleMapsApiKey', '');

CREATE TABLE `ct_telemetry` (
	`id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Azonosító',
	`warehouseId` INT(11) NOT NULL COMMENT 'Raktár azonosító',
	`type` VARCHAR(100) NOT NULL DEFAULT 'other' COMMENT 'Típus ' COLLATE 'utf8mb4_general_ci',
	`description` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Leírás' COLLATE 'utf8mb4_general_ci',
	`value` DECIMAL(19,6) NULL DEFAULT NULL COMMENT 'Érték',
	`piLogId` INT(11) NULL DEFAULT NULL COMMENT 'Pi log azonosító',
	`piLogCreateDate` DATETIME NULL DEFAULT NULL COMMENT 'Pi log létrehozás dátuma',
	`createDate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Létrehozás dátuma',
	`createUserId` INT(11) NOT NULL COMMENT 'Létrehozó felhasználó azonosító',
	`modifyDate` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Módosítás dátuma',
	`modifyUserId` INT(11) NULL DEFAULT NULL COMMENT 'Módosító felhasználó azonosító',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `fk_ct_telemetry_warehouseId` (`warehouseId`) USING BTREE,
	INDEX `fk_ct_telemetry_createUserId` (`createUserId`) USING BTREE,
	INDEX `fk_ct_telemetry_modifyUserId` (`modifyUserId`) USING BTREE,
	CONSTRAINT `fk_ct_telemetry_createUserId` FOREIGN KEY (`createUserId`) REFERENCES `citymedia`.`nubes_user` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT `fk_ct_telemetry_modifyUserId` FOREIGN KEY (`modifyUserId`) REFERENCES `citymedia`.`nubes_user` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
	CONSTRAINT `fk_ct_telemetry_warehouseId` FOREIGN KEY (`warehouseId`) REFERENCES `citymedia`.`nubes_warehouse` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COMMENT='Telemetria adatok'
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB
;

#--access token helper ahhoz hogy tokeneket hozzunk létre a gépeknek
#--a gépek a tokent úgy küldik be, hogy {accessToken}_{serialNumber}
/*
INSERT INTO nubes_common.`nubes_com_oauth_access_token` (`access_token`, `company_id`, `client_id`, `user_id`, `expires`, `scope`)
SELECT CONCAT((SELECT SUBSTRING(access_token, 1, 36) FROM nubes_common.nubes_com_oauth_access_token WHERE id = 1), '_', U_serialNumber) 
,1, 1, 1, '2020-01-01 00:00:00', '\\'
FROM nubes_warehouse WHERE CODE != 'DEF'
*/

#--2021.10.26. update bencefarkas jutalék
INSERT INTO `nubes_item` (`id`, `code`, `name`, `foreignName`, `isInventoryItem`, `isIntermediatedService`, `isStoreItem`, `isSellItem`, `isBuyItem`, `isAdvance`, `itemGroupId`, `manufacturerPartnerId`, `customsTariffCode`, `handleType`, `barCode`, `buyUnit`, `buyUnitQuantity`, `buyWidth`, `buyWidthUnit`, `buyLength`, `buyLengthUnit`, `buyHeight`, `buyHeightUnit`, `buyVolume`, `buyVolumeUnit`, `buyWeight`, `buyWeightUnit`, `sellUnit`, `sellUnitQuantity`, `sellWidth`, `sellWidthUnit`, `sellLength`, `sellLengthUnit`, `sellHeight`, `sellHeightUnit`, `sellVolume`, `sellVolumeUnit`, `sellWeight`, `sellWeightUnit`, `vatGroupId`, `inventoryUnit`, `packagingVolume`, `packagingUnit`, `isLevelByWarehouse`, `minimumLevel`, `maximumLevel`, `reorderLevel`, `defaultWarehouseId`, `minimumOrderQuantity`, `comment`, `createDate`, `createUserId`, `modifyDate`, `modifyUserId`) VALUES
(NULL, 'JUTALEK', 'Jutalék', NULL, 0, 0, 0, 0, 0, 0, 1, NULL, NULL, 0, NULL, NULL, '0.000000', '0.000000', NULL, '0.000000', NULL, '0.000000', NULL, '0.000000', NULL, '0.000000', NULL, NULL, '0.000000', '0.000000', NULL, '0.000000', NULL, '0.000000', NULL, '0.000000', NULL, '0.000000', NULL, NULL, 'db', NULL, NULL, 0, '0.000000', '0.000000', '0.000000', NULL, '0.000000', NULL, '2021-10-26 10:25:35', 1, NULL, NULL);

#--2021.10.27. update bencefarkas jutalékos szerződés oszlopok
ALTER TABLE `ct_proceeding`
ADD `incoming` DECIMAL(19,8) NULL DEFAULT NULL COMMENT 'Bevétel' AFTER `comment`,
ADD `outgoing` DECIMAL(19,8) NULL DEFAULT NULL COMMENT 'Kiadás' AFTER `incoming`,
ADD `commission` DECIMAL(19,8) NULL DEFAULT NULL COMMENT 'Jutalék' AFTER `outgoing`;


#--2021.10.29. update bencefarkas üzemanyag
INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('Fuel consumption','fuel_consumption','DEVELOPMENT');UPDATE nubes_translate_lang SET text = 'Üzemanyag felhasználás',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='fuel_consumption');

INSERT INTO nubes_menu(id, name, url, parent_id, isVisible, sort)
SELECT null, 'fuel_consumption', 'Fuel_Consumption/FuelConsumptionList', id, '1',
( SELECT COUNT(sort) + 1 FROM nubes_menu WHERE parent_id = T1.id )
FROM nubes_menu AS T1 WHERE getMenuNamespace(id) = 'CityMedia/';



#--üzemanyag felhasználás tábla
CREATE TABLE `ct_fuel_consumption` (
 `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Táblán belüli id',
 `documentNumber` varchar(100) NOT NULL COMMENT 'Bizonylatszám',
 `docDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Bizonylat dátuma',
 `status` char(1) NOT NULL DEFAULT 'O' COMMENT 'Státusz',
 `createDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Létrehozás dátuma',
 `createUserId` int(11) NOT NULL COMMENT 'Létrehozó felhasználó azonosító',
 `modifyDate` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Módosítás dátuma',
 `modifyUserId` int(11) DEFAULT NULL COMMENT 'Módosító felhasználó azonosító',
 `comment` longtext COMMENT 'Megjegyzés',
 `usedFuelQuantitySum` decimal(19,8) NOT NULL COMMENT 'Felhasznált üzemanyag mennyisége összesen (literben)',
 `remainedFuelQuantitySum` decimal(19,8) NOT NULL COMMENT 'Megmaradt üzemanyag mennyisége összesen (literben)',
 PRIMARY KEY (`id`),
 UNIQUE KEY `documentNumber` (`documentNumber`),
 KEY `createUserId` (`createUserId`),
 KEY `modifyUserId` (`modifyUserId`),
 CONSTRAINT `ct_fuel_consumption_createUserId` FOREIGN KEY (`createUserId`) REFERENCES `nubes_user` (`id`) ON UPDATE CASCADE,
 CONSTRAINT `ct_fuel_consumption_modifyUserId` FOREIGN KEY (`modifyUserId`) REFERENCES `nubes_user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Üzemanyag felhasználás';


#--üzemanyag felhasználás tétel tábla
CREATE TABLE `ct_fuel_consumption_item` (
 `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Táblán belüli id',
 `documentId` int(11) NOT NULL COMMENT 'Üzemanyag felhasználás azonosító',
 `warehouseId` int(11) NOT NULL COMMENT 'Raktár (gép) azonosító',
 `warehouseCode` varchar(100) NOT NULL COMMENT 'Raktár (gép) kód',
 `warehouseName` varchar(255) NOT NULL COMMENT 'Raktár (gép) név',
 `usedFuelQuantity` decimal(19,8) NOT NULL DEFAULT '0.00000000' COMMENT 'Felhasznált üzemanyag',
 PRIMARY KEY (`id`),
 KEY `documentId` (`documentId`),
 KEY `warehouseId` (`warehouseId`),
 CONSTRAINT `ct_fuel_consumption_item_documentId` FOREIGN KEY (`documentId`) REFERENCES `ct_fuel_consumption` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
 CONSTRAINT `ct_fuel_consumption_item_warehouseId` FOREIGN KEY (`warehouseId`) REFERENCES `nubes_warehouse` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Üzemanyag felhasználás tétel';


INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('List of fuel consumptions','list_of_fuel_consumptions','DEVELOPMENT');UPDATE nubes_translate_lang SET text = 'Üzemanyag felhasználás lista',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='list_of_fuel_consumptions');


INSERT INTO nubes_data_type(table_schema,table_name,column_name,column_default_value,is_nullable,data_type,sub_type,character_maximum_length,numeric_precision,numeric_scale,column_key,extra,regex,privileges) VALUES ('citymedia','ct_fuel_consumption','status','O','0','char','','1','','','','','','select,insert,update,references');
INSERT INTO nubes_field_valid_values(col_id,value,description) VALUES ((SELECT id FROM nubes_data_type WHERE table_schema = 'citymedia' AND table_name = 'ct_fuel_consumption' AND column_name = 'status'),'O','Nyitott');
INSERT INTO nubes_field_valid_values(col_id,value,description) VALUES ((SELECT id FROM nubes_data_type WHERE table_schema = 'citymedia' AND table_name = 'ct_fuel_consumption' AND column_name = 'status'),'C','Zárt');


INSERT INTO nubes_document_number(namespace,objectType,pattern,validFrom,validTo,lastDocumentNumber) VALUES ('DI\\Model\\Entity\\CityMedia\\Fuel_Consumption\\Fuel_Consumption','ct_fuel_consumption','ÜFELH{0000000}/{Y}','2021-10-29','','');
INSERT INTO nubes_document_number_numbers(documentNumberId,num,maxNum,indx,length,isIncrement) VALUES ((SELECT id FROM nubes_document_number WHERE namespace = 'DI\\Model\\Entity\\CityMedia\\Fuel_Consumption\\Fuel_Consumption'),'0','9999999','0','7','1');

INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('Used fuel quantity','used_fuel_quantity','DEVELOPMENT');UPDATE nubes_translate_lang SET text = 'Felhasznált üzemanyag mennyisége',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='used_fuel_quantity');
INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('Remained fuel quantity','remained_fuel_quantity','DEVELOPMENT');UPDATE nubes_translate_lang SET text = 'Megmaradt üzemanyag mennyisége',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='remained_fuel_quantity');



#--2021.11.02. update bencefarkas alkatrész
INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('Fixture','fixture','DEVELOPMENT');UPDATE nubes_translate_lang SET text = 'Alkatrész',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='fixture');
INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('Fixture usage','fixture_usage','DEVELOPMENT');UPDATE nubes_translate_lang SET text = 'Alkatrész felhasználás',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='fixture_usage');


INSERT INTO nubes_menu(id, name, url, parent_id, isVisible, sort)
SELECT null, 'fixture_usage', 'FixtureUsageReport', id, '1',
( SELECT COUNT(sort) + 1 FROM nubes_menu WHERE parent_id = T1.id )
FROM nubes_menu AS T1
WHERE getMenuNamespace(id) = 'CityMedia/';

INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('All warehouse','all_warehouse','DEVELOPMENT');UPDATE nubes_translate_lang SET text = 'Összes raktár',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='all_warehouse');


INSERT INTO `nubes_printpageeditor` (`namespace`, `typeCode`, `template`, `headerTemplate`, `footerTemplate`, `name`, `lastModifyState`) VALUES
('CityMedia\\FixtureUsageReport\\', 'DEFAULT', '<div class=\"page container-fluid\">\n            <h1 class=\"center-text\" style=\"/* width: 582.27px; *//* border: 1px solid black; *//* position: absolute; *//* left: 0; */\">Alkatrész felhasználás</h1>\n\n            <p></p>\n\n\n            <p><span>Készült: </span><span><code> return date(\"Y-m-d\");</code></span></p>          \n<p><span>Paraméterek</span></p><div>\n    <div>\n        Dátum-tól: <span><code> return $params[\"dateFrom\"];</code></span>    </div>\n    <div>\n        Dátum-ig: <span><code> return $params[\"dateTo\"];</code></span>    </div>\n<div>\n        Gép azonosító: <span><code> return ($params[\"warehouseId\"] > 0) ? $params[\"warehouseId\"] : \"Összes gép\";</code></span></div>\n</div>\n\n\n\n\n<p></p>\n<div class=\"border-bottom row\">\n    <div class=\"col-12\"><h2>Gép</h2></div>\n    \n    <div class=\"font-weight-bold\">\n            \n</div>\n\n</div><div class=\"border-bottom font-weight-bold row\">\n    <div class=\"col-3\">Bizonylatszám</div><div class=\"col-2\">Cikkszám</div>\n    <div class=\"col-5 left-text\">Megnevezés</div>\n<div class=\"col-2 right-text\">Mennyiség</div>\n    \n</div><div class=\"border-bottom print-datasource\" data-datasourcecode=\"fixture_usage\" <=\"\" div=\"\"><div class=\"row\">\n    <div class=\"col-3 font-weight-bold \" data-datasourcecode=\"fixture_usage\" data-key=\"documentNumber\">documentNumber</div><div class=\"col-2 font-weight-bold \" data-datasourcecode=\"fixture_usage\" data-key=\"code\">code</div>\n    <div class=\"col-5 font-weight-bold left-text\" data-datasourcecode=\"fixture_usage\" data-key=\"name\">name</div>\n    <div class=\"col-2 text-right font-weight-bold \"><span data-datasourcecode=\"fixture_usage\" data-key=\"quantity\" data-namespace=\"\\UI\\Html\\StaticNumber\" data-precision=\"2\">quantity</span>\n    <span data-datasourcecode=\"fixture_usage\" data-key=\"quantityUnit\">inventoryUnit</span>\n</div>\n    \n\n    \n    \n    \n</div></div>\n\n\n\n\n            \n            <style>\n                @page {\n                    margin-top: 150px;\n                    margin-bottom: 100px;\n                    margin-left: 0.1cm;\n                    margin-right: 0.5in;\n                    border:1px solid gray;\n                }\n\n            </style>\n\n        </div><style>.document { display:none;}</style>', '\n\n<div><style>\n    .logo{\n\n        max-width:297.45px;\n        max-height:50px;\n        display:inline-block;\n        box-sizing:border-box;\n        vertical-align:text-top;\n        padding:10px;\n        margin-left:50px;\n    }\n    .pageInfo{\n        vertical-align:text-top;\n        width:auto;\n        display:inline-block;\n        box-sizing:border-box;\n    }\n    .pageInfo > span{\n        display:inline-block;\n        vertical-align:text-top;\n        padding-right:10px;\n    }\n</style><div style=\"font-size: 10px;\">\n    <div>\n        <span class=\"logo\">\n    <code>$companyInfo = \\Control\\Application::GetInstance()->GetCompany()->GetCompanyInfo(); $view = new \\UI\\Html\\File\\MimeType\\ImageBase64();         $view->setModel($companyInfo->companyLogo); $view->style[\"max-height\"] = \"50px\"; return $view->Render();</code></span><span class=\"pageInfo\"><br>\n    \n    <span>\n            <div><code> $companyInfo = \\Control\\Application::GetInstance()->GetCompany()->GetCompanyInfo(); return $companyInfo->name;</code></div>\n<div><code> $companyInfo = \\Control\\Application::GetInstance()->GetCompany()->GetCompanyInfo(); return \"{$companyInfo->billCountryCode} {$companyInfo->billPostalCode} {$companyInfo->billCity} {$companyInfo->billStreetName} {$companyInfo->billNumber}\";</code></div>\n<div><span>Adószám</span> <span><code> $tax = \\DI\\Model\\Entity\\Administration\\SystemSettings\\Company\\Company_Data_Tax::Get(array(\"id\" => 1)); return $tax->taxNumber;</code></span></div>\n    </span><span>\n    <div><code> $companyInfo = \\Control\\Application::GetInstance()->GetCompany()->GetCompanyInfo(); return $companyInfo->web;</code>\n</div><div><code> $companyInfo = \\Control\\Application::GetInstance()->GetCompany()->GetCompanyInfo(); return $companyInfo->email;</code>\n</div>\n<div><code> $companyInfo = \\Control\\Application::GetInstance()->GetCompany()->GetCompanyInfo(); return $companyInfo->phone;</code></div></span></span></div>\n</div></div>', '\n    <div style=\"font-size:8px; width:21cm;\">\n    <div style=\"text-align:center\">MHzQTeam Nubes ERP rendszer <a href=\"http://mhzq.com\" title=\"Mega-Hercz-Q Kft\">www.mhzq.com</a></div>\n<br><div style=\"font-size:8px; text-align:center;\">\n        <span>Oldal&nbsp;</span><span class=\"pageNumber\">1</span>\n/\n<span class=\"totalPages\">1</span></div>\n</div>', 'Alkatrész felhasználás', 'DEVELOPMENT');

INSERT INTO `nubes_print_datasource` (`code`, `name`, `query`) VALUES
('fixture_usage', 'Alkatrész felhasználás', 'select T1.itemId, T1.itemCode as code, T1.itemName as name, T1.quantity, T1.quantityUnit, T1.warehouseId, T2.documentNumber as documentNumber, T2.createDate as createDate\nfrom nubes_inventory_exit_item as T1 \nleft join nubes_inventory_exit as T2 on T1.inventoryExitId = T2.id \nwhere  \n((:dateFrom IS NULL OR DATE(T2.createDate) >= :dateFrom) AND (:dateTo IS NULL OR DATE(T2.createDate) <= :dateTo)) \nand (:warehouseId IS NULL OR T1.warehouseId = :warehouseId)');

INSERT INTO `nubes_printpageeditor_datasource` (`printPageEditorId`, `printDataSourceId`) VALUES
((SELECT id FROM nubes_printpageeditor WHERE namespace = 'CityMedia\\FixtureUsageReport\\'), (SELECT id FROM nubes_print_datasource WHERE code = 'fixture_usage'));


#--2021.11.10. update bencefarkas jutalékos szerződés bevétel, kiadás mező nem kell
ALTER TABLE `ct_partner_contract`
  DROP IF EXISTS `incoming`,
  DROP IF EXISTS `outgoing`;


INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('Commission','commission','DEVELOPMENT');UPDATE nubes_translate_lang SET text = 'Jutalék',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='commission');
INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('Expenditure','expenditure','DEVELOPMENT');UPDATE nubes_translate_lang SET text = 'Kiadás',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='expenditure');


#--2021.11.11. update bencefarkas
INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('Signature','signature','DEVELOPMENT');UPDATE nubes_translate_lang SET text = 'Aláírás',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='signature');


#--2021.11.15. update bencefarkas alkatrész felhasználás report javítás
UPDATE `nubes_printpageeditor` SET `template` = '\r\n<div class=\"page container-fluid\">\r\n            <h1 class=\"center-text\" style=\"/* width: 582.27px; *//* border: 1px solid black; *//* position: absolute; *//* left: 0; */\">Alkatrész felhasználás</h1>\r\n\r\n            <p></p>\r\n\r\n\r\n            <p><span>Készült: </span><span><code> return date(\"Y-m-d\");</code></span></p>          \r\n<p><span>Paraméterek</span></p><div>\r\n    <div>\r\n        Dátum-tól: <span><code> return $params[\"dateFrom\"];</code></span>    </div>\r\n    <div>\r\n        Dátum-ig: <span><code> return $params[\"dateTo\"];</code></span>    </div>\r\n<div>\r\n        Gép azonosító: <span><code> return ($params[\"warehouseId\"] > 0) ? $params[\"warehouseId\"] : \"Összes gép\";</code></span></div>\r\n</div>\r\n\r\n\r\n\r\n\r\n<p></p>\r\n<div class=\"border-bottom row\">\r\n    <div class=\"col-12\"><h2>Gép</h2></div>\r\n    \r\n    <div class=\"font-weight-bold\">\r\n            \r\n</div>\r\n\r\n</div><div class=\"border-bottom font-weight-bold row\">\r\n    <div class=\"col-3\">Bizonylatszám</div><div class=\"col-2\">Cikkszám</div>\r\n    <div class=\"col-5 left-text\">Megnevezés</div>\r\n<div class=\"col-2 right-text\">Mennyiség</div>\r\n    \r\n</div><ul>\r\n<li class=\"print-datasource\" data-datasourcecode=\"fixture_usage_warehouse\">\r\n    Gép kódja: <span data-datasourcecode=\"fixture_usage_warehouse\" data-key=\"code\">code</span>; Gép neve: <span data-datasourcecode=\"fixture_usage_warehouse\" data-key=\"name\">name</span><div class=\"border-bottom print-datasource\" data-datasourcecode=\"fixture_usage\" data-use=\"fixture_usage_warehouse\" data-use-key=\"warehouseId\">\r\n        <div class=\"row\">\r\n            <div class=\"col-3\" data-datasourcecode=\"fixture_usage\" data-key=\"documentNumber\">documentNumber</div><div class=\"col-2\" data-datasourcecode=\"fixture_usage\" data-key=\"code\">code</div>\r\n            <div class=\"col-5 left-text\" data-datasourcecode=\"fixture_usage\" data-key=\"name\">name</div>\r\n            <div class=\"col-2 text-right\"><span data-datasourcecode=\"fixture_usage\" data-key=\"quantity\" data-namespace=\"\\UI\\Html\\StaticNumber\" data-precision=\"2\">quantity</span>\r\n            <span data-datasourcecode=\"fixture_usage\" data-key=\"quantityUnit\">inventoryUnit</span>\r\n        </div>\r\n\r\n\r\n\r\n\r\n\r\n        </div>\r\n    \r\n    </div>\r\n</li>\r\n</ul>\r\n\r\n\r\n\r\n\r\n            \r\n            <style>\r\n                @page {\r\n                    margin-top: 150px;\r\n                    margin-bottom: 100px;\r\n                    margin-left: 0.1cm;\r\n                    margin-right: 0.5in;\r\n                    border:1px solid gray;\r\n                }\r\n\r\n            </style>\r\n\r\n        </div><style>.document { display:none;}</style>' WHERE `nubes_printpageeditor`.`namespace` = 'CityMedia\\FixtureUsageReport\\';
UPDATE `nubes_print_datasource` SET `query` = '\r\nselect T1.itemId, T1.itemCode as code, T1.itemName as name, T1.quantity, T1.quantityUnit, T1.warehouseId, T2.documentNumber as documentNumber, T2.createDate as createDate\r\nfrom nubes_inventory_exit_item as T1 \r\ninner join nubes_inventory_exit as T2 on T1.inventoryExitId = T2.id \r\nwhere \r\n((:dateFrom IS NULL OR DATE(T2.createDate) >= :dateFrom) AND (:dateTo IS NULL OR DATE(T2.createDate) <= :dateTo)) \r\nand (:warehouseId IS NULL OR T1.warehouseId = :warehouseId)' WHERE `nubes_print_datasource`.`code` = 'fixture_usage';
UPDATE `nubes_print_datasource` SET `query` = '\r\nselect T1.id as warehouseId, T1.name as name, T1.code as code from nubes_warehouse as T1 where (:warehouseId IS NULL OR T1.id = :warehouseId)' WHERE `nubes_print_datasource`.`code` = 'fixture_usage_warehouse';

INSERT INTO `nubes_item` (`id`, `code`, `name`, `foreignName`, `isInventoryItem`, `isIntermediatedService`, `isStoreItem`, `isSellItem`, `isBuyItem`, `isAdvance`, `itemGroupId`, `manufacturerPartnerId`, `customsTariffCode`, `handleType`, `barCode`, `buyUnit`, `buyUnitQuantity`, `buyWidth`, `buyWidthUnit`, `buyLength`, `buyLengthUnit`, `buyHeight`, `buyHeightUnit`, `buyVolume`, `buyVolumeUnit`, `buyWeight`, `buyWeightUnit`, `sellUnit`, `sellUnitQuantity`, `sellWidth`, `sellWidthUnit`, `sellLength`, `sellLengthUnit`, `sellHeight`, `sellHeightUnit`, `sellVolume`, `sellVolumeUnit`, `sellWeight`, `sellWeightUnit`, `vatGroupId`, `inventoryUnit`, `packagingVolume`, `packagingUnit`, `isLevelByWarehouse`, `minimumLevel`, `maximumLevel`, `reorderLevel`, `defaultWarehouseId`, `minimumOrderQuantity`, `comment`, `createDate`, `createUserId`, `modifyDate`, `modifyUserId`) VALUES
(NULL, 'COIN', 'Érme', NULL, 0, 0, 0, 0, 0, 0, 1, NULL, NULL, 0, NULL, NULL, '0.000000', '0.000000', NULL, '0.000000', NULL, '0.000000', NULL, '0.000000', NULL, '0.000000', NULL, NULL, '0.000000', '0.000000', NULL, '0.000000', NULL, '0.000000', NULL, '0.000000', NULL, '0.000000', NULL, NULL, 'db', NULL, NULL, 0, '0.000000', '0.000000', '0.000000', NULL, '0.000000', NULL, '2021-10-26 10:25:35', 1, NULL, NULL);


#--2022.04.13. bencefarkas karbantartás bizonylat
INSERT INTO `nubes_printpageeditor` (`id`, `namespace`, `typeCode`, `template`, `headerTemplate`, `footerTemplate`, `name`, `lastModifyState`) VALUES
(NULL, 'CityMedia\\Maintenance\\Maintenance\\', 'DEFAULT', '<div class=\"page print-datasource container-fluid\" data-datasourcecode=\"maintenance\">\n    <h1 class=\"center-text\" style=\"/* width: 582.27px; *//* border: 1px solid black; *//* position: absolute; *//* left: 0; */\">Karbantartás</h1>\n\n<p></p>\n\n\n\n<div class=\"row\" id=\"productionReportHead\" style=\"\n    font-size: 1.1rem;\n\"><div class=\"col-6\">\n    <div class=\"container-fluid\">\n        <div class=\"row\">\n    <div class=\"col-6 border border-dark\"><span>Bizonylatszám:</span></div>\n    <div class=\"col-6 border border-dark \">\n        <span data-datasourcecode=\"maintenance\" data-key=\"documentNumber\">Bizonylatszám</span></div>\n    <div class=\"col-6 border border-dark\"><span>Karbantartás dátuma:</span></div>\n    <div class=\"col-6 border border-dark \">\n        <span data-datasourcecode=\"maintenance\" data-key=\"maintenanceDate\">Karbantartás dátuma</span></div>\n    \n    \n    \n    \n    \n    \n        </div></div></div>\n    <div class=\"col-6 \">\n    <div class=\"container-fluid\">\n        <div class=\"row\">\n    <div class=\"col-6 border border-dark\"><span>Raktár név:</span></div>\n    <div class=\"col-6 border border-dark \"><span data-datasourcecode=\"maintenance\" data-key=\"warehouseName\">Raktár név</span></div>\n    <div class=\"col-6 border border-dark\">Raktár kód:</div>\n    <div class=\"col-6 border border-dark \"><span data-datasourcecode=\"maintenance\" data-key=\"warehouseCode\">Raktár kód</span></div>\n    \n    \n    \n    \n    \n    \n    </div></div></div>\n    \n    \n    \n    \n</div>\n\n\n\n    \n\n\n    \n<table class=\"table\">\n        <thead style=\"\n    border-bottom: 1px solid black;\n\">\n    <tr><th class=\"left-text\">Megnevezés</th>\n\n\n\n\n\n\n</tr>\n</thead>\n        <tbody style=\"\n    border-bottom: 1px solid black;\n\">\n            <tr class=\"print-datasource\" data-datasourcecode=\"maintenance_item\" data-use=\"maintenance\" data-use-key=\"id\" style=\"\">\n                <td data-datasourcecode=\"maintenance_item\" data-key=\"name\">name</td>\n\n\n\n\n\n\n\n\n           </tr>\n        </tbody>\n    </table>\n<style>\n     @page {\n        margin-top: 150px;\n        margin-bottom: 100px;\n        margin-left: 0.1cm;\n        margin-right: 0.5in;    \n        border:1px solid gray;\n    }\n\n</style>\n\n</div>', '\n\n<div><style>\n    .logo{\n\n        max-width:297.45px;\n        max-height:50px;\n        display:inline-block;\n        box-sizing:border-box;\n        vertical-align:text-top;\n        padding:10px;\n        margin-left:50px;\n    }\n    .pageInfo{\n        vertical-align:text-top;\n        width:auto;\n        display:inline-block;\n        box-sizing:border-box;\n    }\n    .pageInfo > span{\n        display:inline-block;\n        vertical-align:text-top;\n        padding-right:10px;\n    }\n</style><div style=\"font-size: 10px;\">\n    <div>\n        <span class=\"logo\">\n    <code>$companyInfo = \\Control\\Application::GetInstance()->GetCompany()->GetCompanyInfo(); $view = new \\UI\\Html\\File\\MimeType\\ImageBase64();         $view->setModel($companyInfo->companyLogo); $view->style[\"max-height\"] = \"50px\"; return $view->Render();</code></span><span class=\"pageInfo\"><br>\n    \n    <span>\n            <div><code> $companyInfo = \\Control\\Application::GetInstance()->GetCompany()->GetCompanyInfo(); return $companyInfo->name;</code></div>\n<div><code> $companyInfo = \\Control\\Application::GetInstance()->GetCompany()->GetCompanyInfo(); return \"{$companyInfo->billCountryCode} {$companyInfo->billPostalCode} {$companyInfo->billCity} {$companyInfo->billStreetName} {$companyInfo->billNumber}\";</code></div>\n<div><span>Adószám</span> <span><code> $tax = \\DI\\Model\\Entity\\Administration\\SystemSettings\\Company\\Company_Data_Tax::Get(array(\"id\" => 1)); return $tax->taxNumber;</code></span></div>\n    </span><span>\n    <div><code> $companyInfo = \\Control\\Application::GetInstance()->GetCompany()->GetCompanyInfo(); return $companyInfo->web;</code>\n</div><div><code> $companyInfo = \\Control\\Application::GetInstance()->GetCompany()->GetCompanyInfo(); return $companyInfo->email;</code>\n</div>\n<div><code> $companyInfo = \\Control\\Application::GetInstance()->GetCompany()->GetCompanyInfo(); return $companyInfo->phone;</code></div></span></span></div>\n</div></div>', '\n    <div style=\"font-size:8px; width:21cm;\">\n    <div style=\"text-align:center\">MHzQTeam Nubes ERP rendszer <a href=\"http://mhzq.com\" title=\"Mega-Hercz-Q Kft\">www.mhzq.com</a></div>\n<br><div style=\"font-size:8px; text-align:center;\">\n        <span>Oldal</span><span class=\"pageNumber\">1</span>\n/\n<span class=\"totalPages\">1</span></div>\n</div>', 'Karbantartás', 'DEVELOPMENT');

INSERT INTO `nubes_print_datasource` (`id`, `code`, `name`, `query`) VALUES
(NULL, 'maintenance_item', 'Karbantartás tétel', 'select T1.* from ct_maintenance_item as T1 inner join ct_maintenance as T2 on T1.documentId = T2.id where T2.id = :id'),
(NULL, 'maintenance', 'Karbantartás fej', 'select * from ct_maintenance where id = :id');

INSERT INTO `nubes_printpageeditor_datasource` (`id`, `printPageEditorId`, `printDataSourceId`) VALUES
(NULL, (SELECT id FROM nubes_printpageeditor WHERE namespace = 'CityMedia\\Maintenance\\Maintenance\\'), (SELECT id FROM nubes_print_datasource WHERE code = 'maintenance_item')),
(NULL, (SELECT id FROM nubes_printpageeditor WHERE namespace = 'CityMedia\\Maintenance\\Maintenance\\'), (SELECT id FROM nubes_print_datasource WHERE code = 'maintenance'));


#--2022.04.19. bencefarkas jegyzőkönyv bizonylat
INSERT INTO `nubes_printpageeditor` (`id`, `namespace`, `typeCode`, `template`, `headerTemplate`, `footerTemplate`, `name`, `lastModifyState`) VALUES
(NULL, 'CityMedia\\Proceeding\\Proceeding\\', 'DEFAULT', '<div class=\"page print-datasource container-fluid\" data-datasourcecode=\"proceeding\">\n    <h1 class=\"center-text\" style=\"/* width: 582.27px; *//* border: 1px solid black; *//* position: absolute; *//* left: 0; */\">Jegyzőkönyv</h1>\n\n<p></p>\n\n\n\n<div class=\"row\" id=\"productionReportHead\" style=\"\n    font-size: 1.1rem;\n\"><div class=\"col-6\">\n    <div class=\"container-fluid\">\n        <div class=\"row\">\n    <div class=\"col-6 border border-dark\"><span>Bizonylatszám:</span></div>\n    <div class=\"col-6 border border-dark \">\n        <span data-datasourcecode=\"proceeding\" data-key=\"documentNumber\">Bizonylatszám</span></div>\n    <div class=\"col-6 border border-dark\"><span>Státusz:</span></div>\n    <div class=\"col-6 border border-dark \">\n        <span data-datasourcecode=\"proceeding\" data-code=\"true\"><code class=\"eval\">$type = \"Jegyzőkönyv\"; switch($status){\n                        case \"O\":\n                        $type = \"Tervezet\";\n                        break;\n                        case \"C\":\n                        $type = \"Számlázott\";\n                        break;\n\n                        case \"R\":\n                        $type = \"Jóváhagyott\";\n                        break;\n\n                        }\n                        return $type;\n                    </code></span>\n</div>\n    \n    \n    \n    \n    \n    \n        <div class=\"col-6 border border-dark\"><span>Típus:</span></div><div class=\"col-6 border border-dark \">\n        <span data-datasourcecode=\"proceeding\" data-key=\"type\">Típus</span></div><div class=\"col-6 border border-dark\"><span>Raktár név:</span></div><div class=\"col-6 border border-dark \"><span data-datasourcecode=\"proceeding\" data-key=\"warehouseName\">Raktár név</span></div><div class=\"col-6 border border-dark\">Raktár kód:</div><div class=\"col-6 border border-dark \"><span data-datasourcecode=\"proceeding\" data-key=\"warehouseCode\">Raktár kód</span></div><div class=\"col-6 border border-dark\"><span>Kihelyezés dátuma:</span></div><div class=\"col-6 border border-dark \">\n        \n<span data-datasourcecode=\"proceeding\" data-key=\"issueDate\" data-namespace=\"\\UI\\Html\\StaticDate\">Kihelyezés dátuma</span></div></div></div></div>\n    <div class=\"col-6 \">\n    <div class=\"container-fluid\">\n        <div class=\"row\">\n    \n    \n    \n    \n    \n    \n    \n    \n    \n    \n    <div class=\"col-6 border border-dark\"><span>Objektum típus:</span></div><div class=\"col-6 border border-dark \">\n        <span data-datasourcecode=\"proceeding\" data-code=\"true\"><code class=\"eval\">$type = \"Jegyzőkönyv\"; switch($objectType){\n                        case \"invoice\":\n                        $type = \"Számla\";\n                        break;\n                        case \"purchase_invoice\":\n                        $type = \"Beszerzési számla\";\n                        break;\n\n                        case \"inventory_entry\":\n                        $type = \"Anyagbevételezés\";\n                        break;\n\n                        case \"inventory_exit\":\n                        $type = \"Anyagkiadás\";\n                        break;\n\n                        }\n                        return $type;\n                    </code></span></div><div class=\"col-6 border border-dark\"><span>Objektum bizonylatszám:</span></div><div class=\"col-6 border border-dark \">\n        <span data-datasourcecode=\"proceeding\" data-key=\"refDocumentNumber\">Objektum bizonylatszám</span></div><div class=\"col-6 border border-dark\"><span>Partner név:</span></div><div class=\"col-6 border border-dark \">\n        <span data-datasourcecode=\"proceeding\" data-key=\"partnerName\">Partner név</span></div><div class=\"col-6 border border-dark\"><span>Partner kód:</span></div><div class=\"col-6 border border-dark \">\n        <span data-datasourcecode=\"proceeding\" data-key=\"partnerCode\">Partner kód</span></div></div>\n\n        <div class=\"row document\">\n    <div class=\"col-6 border border-dark\">Bevétel</div>\n    <div class=\"col-6 border border-dark\">\n    <span data-precision=\"0\" class=\"numeric price\" data-datasourcecode=\"proceeding\" data-key=\"incoming\" data-namespace=\"\\UI\\Html\\StaticNumber\">Bevétel</span> Ft\n</div>\n    <div class=\"col-6 border border-dark\">Kiadás</div>\n    <div class=\"col-6 border border-dark\"><span data-precision=\"0\" class=\"numeric price\" data-datasourcecode=\"proceeding\" data-key=\"outgoing\" data-namespace=\"\\UI\\Html\\StaticNumber\">Kiadás</span> Ft</div>\n    <div class=\"col-6 border border-dark\">Jutalék</div>\n    <div class=\"col-6 border border-dark\"><span data-precision=\"0\" class=\"numeric price\" data-datasourcecode=\"proceeding\" data-key=\"commission\" data-namespace=\"\\UI\\Html\\StaticNumber\">Jutalék</span> %</div>\n    \n</div></div>\n\n        </div>\n    \n    \n    \n    \n</div>\n\n<div id=\"comment\"> <h2>Megjegyzés</h2> <div style=\"border:1px solid black;padding: 5px;\" data-datasourcecode=\"proceeding\" data-key=\"comment\">Megjegyzés szövege...</div> </div>\n\n\n\n    \n\n\n    \n\n<style>\n     @page {\n        margin-top: 150px;\n        margin-bottom: 100px;\n        margin-left: 0.1cm;\n        margin-right: 0.5in;    \n        border:1px solid gray;\n    }\n\n</style>\n<style data-datasourcecode=\"proceeding\">\n\n    <?php if(strlen($comment) == 0){ return \"#comment{ display:none;}\";}?>\n                                    \n</style>\n<style data-datasourcecode=\"proceeding\">\n\n    <?php if($type != 2){ return \".document{ display:none;}\";}?>\n                                    \n</style>\n\n</div>', '\n\n<div><style>\n    .logo{\n\n        max-width:297.45px;\n        max-height:50px;\n        display:inline-block;\n        box-sizing:border-box;\n        vertical-align:text-top;\n        padding:10px;\n        margin-left:50px;\n    }\n    .pageInfo{\n        vertical-align:text-top;\n        width:auto;\n        display:inline-block;\n        box-sizing:border-box;\n    }\n    .pageInfo > span{\n        display:inline-block;\n        vertical-align:text-top;\n        padding-right:10px;\n    }\n</style><div style=\"font-size: 10px;\">\n    <div>\n        <span class=\"logo\">\n    <code>$companyInfo = \\Control\\Application::GetInstance()->GetCompany()->GetCompanyInfo(); $view = new \\UI\\Html\\File\\MimeType\\ImageBase64();         $view->setModel($companyInfo->companyLogo); $view->style[\"max-height\"] = \"50px\"; return $view->Render();</code></span><span class=\"pageInfo\"><br>\n    \n    <span>\n            <div><code> $companyInfo = \\Control\\Application::GetInstance()->GetCompany()->GetCompanyInfo(); return $companyInfo->name;</code></div>\n<div><code> $companyInfo = \\Control\\Application::GetInstance()->GetCompany()->GetCompanyInfo(); return \"{$companyInfo->billCountryCode} {$companyInfo->billPostalCode} {$companyInfo->billCity} {$companyInfo->billStreetName} {$companyInfo->billNumber}\";</code></div>\n<div><span>Adószám</span> <span><code> $tax = \\DI\\Model\\Entity\\Administration\\SystemSettings\\Company\\Company_Data_Tax::Get(array(\"id\" => 1)); return $tax->taxNumber;</code></span></div>\n    </span><span>\n    <div><code> $companyInfo = \\Control\\Application::GetInstance()->GetCompany()->GetCompanyInfo(); return $companyInfo->web;</code>\n</div><div><code> $companyInfo = \\Control\\Application::GetInstance()->GetCompany()->GetCompanyInfo(); return $companyInfo->email;</code>\n</div>\n<div><code> $companyInfo = \\Control\\Application::GetInstance()->GetCompany()->GetCompanyInfo(); return $companyInfo->phone;</code></div></span></span></div>\n</div></div>', '\n    <div style=\"font-size:8px; width:21cm;\">\n    <div style=\"text-align:center\">MHzQTeam Nubes ERP rendszer <a href=\"http://mhzq.com\" title=\"Mega-Hercz-Q Kft\">www.mhzq.com</a></div>\n<br><div style=\"font-size:8px; text-align:center;\">\n        <span>Oldal</span><span class=\"pageNumber\">1</span>\n/\n<span class=\"totalPages\">1</span></div>\n</div>', 'Jegyzőkönyv', 'DEVELOPMENT');

INSERT INTO `nubes_print_datasource` (`id`, `code`, `name`, `query`) VALUES
(NULL, 'proceeding', 'Jegyzőkönyv fej', 'select \ncase IFNULL(T1.objectType, \'\')\n    when \'invoice\' THEN (SELECT documentNumber FROM nubes_invoice as T4 where T4.id = T1.objectId)\n    when \'purchase_invoice\' THEN (SELECT documentNumber FROM nubes_purchase_invoice as T4 where T4.id = T1.objectId)\n    when \'inventory_entry\' THEN (SELECT documentNumber FROM nubes_inventory_entry as T4 where T4.id = T1.objectId)\n    when \'inventory_exit\' THEN (SELECT documentNumber FROM nubes_inventory_exit as T4 where T4.id = T1.objectId)\n    END as refDocumentNumber,\n\n\nT2.code as warehouseCode, T2.name as warehouseName, T3.name as partnerName, T3.code as partnerCode, T1.* \nfrom ct_proceeding as T1 \ninner join nubes_warehouse as T2 on T1.warehouseId = T2.id\ninner join nubes_partner as T3 on T3.id = T1.partnerId\n\nwhere T1.id = :id');

INSERT INTO `nubes_printpageeditor_datasource` (`id`, `printPageEditorId`, `printDataSourceId`) VALUES
(NULL, (SELECT id FROM nubes_printpageeditor WHERE namespace = 'CityMedia\\Proceeding\\Proceeding\\'), (SELECT id FROM nubes_print_datasource WHERE code = 'proceeding'));


#--2022.04.20. bencefarkas translates
INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('Maintenance list','maintenance_list','DEVELOPMENT');UPDATE nubes_translate_lang SET text = 'Karbantartás lista',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='maintenance_list');
INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('Proceeding list','proceeding_list','DEVELOPMENT');UPDATE nubes_translate_lang SET text = 'Jegyzőkönyvek listája',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='proceeding_list');
INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('Fuel consumption list','fuel_consumption_list','DEVELOPMENT');UPDATE nubes_translate_lang SET text = 'Üzemanyag felhasználás lista',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='fuel_consumption_list');
INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('Fixture usage report','fixture_usage_report','DEVELOPMENT');UPDATE nubes_translate_lang SET text = 'Alkatrész felhasználás riport',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='fixture_usage_report');


#--2022.04.25. bencefarkas jegyzőkönyv raktár sor szint
ALTER TABLE ct_proceeding DROP FOREIGN KEY fk_ct_proceeding_warehouseId;
ALTER TABLE ct_proceeding DROP INDEX warehouseId;
ALTER TABLE ct_proceeding DROP warehouseId;

ALTER TABLE `ct_proceeding` DROP INDEX `fk_ct_proceeding_modifyUserId`, ADD INDEX `modifyUserId` (`modifyUserId`) USING BTREE;
ALTER TABLE `ct_proceeding` DROP INDEX `fk_ct_proceeding_createUserId`, ADD INDEX `createUserId` (`createUserId`) USING BTREE;

CREATE TABLE `ct_proceeding_item` (
 `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Táblán belüli azonosító',
 `proceedingId` int(11) NOT NULL COMMENT 'Jegyzőkönyv azonosító',
 `warehouseId` int(11) NOT NULL COMMENT 'Raktár azonosító',
 `warehouseCode` varchar(100) NOT NULL COMMENT 'Raktár kód',
 `warehouseName` varchar(255) NOT NULL COMMENT 'Raktár név',
 PRIMARY KEY (`id`),
 KEY `proceedingId` (`proceedingId`),
 KEY `warehouseId` (`warehouseId`),
 CONSTRAINT `fk_ct_proceeding_item_proceedingId` FOREIGN KEY (`proceedingId`) REFERENCES `ct_proceeding` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
 CONSTRAINT `fk_ct_proceeding_item_warehouseId` FOREIGN KEY (`warehouseId`) REFERENCES `nubes_warehouse` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Jegyzőkönyv tétel';


ALTER TABLE `ct_proceeding` ADD `amount` DECIMAL(19,8) NULL DEFAULT NULL COMMENT 'Jutalék összeg' AFTER `commission`;



#--2022.04.26. bencefarkas jegyzőkönyv nyomtatási kép javítás

UPDATE `nubes_printpageeditor` SET `template` = '<div class=\"page print-datasource container-fluid\" data-datasourcecode=\"proceeding\">\r\n    <h1 class=\"center-text\" style=\"/* width: 582.27px; *//* border: 1px solid black; *//* position: absolute; *//* left: 0; */\">Jegyzőkönyv</h1>\r\n\r\n<p></p>\r\n\r\n\r\n\r\n<div class=\"row\" id=\"productionReportHead\" style=\"\r\n    font-size: 1.1rem;\r\n\"><div class=\"col-6\">\r\n    <div class=\"container-fluid\">\r\n        <div class=\"row\">\r\n    <div class=\"col-6 border border-dark\"><span>Bizonylatszám:</span></div>\r\n    <div class=\"col-6 border border-dark \">\r\n        <span data-datasourcecode=\"proceeding\" data-key=\"documentNumber\">Bizonylatszám</span></div>\r\n    <div class=\"col-6 border border-dark\"><span>Státusz:</span></div>\r\n    <div class=\"col-6 border border-dark \">\r\n        <span data-datasourcecode=\"proceeding\" data-code=\"true\"><code class=\"eval\">$type = \"Jegyzőkönyv\"; switch($status){\r\n                        case \"O\":\r\n                        $type = \"Tervezet\";\r\n                        break;\r\n                        case \"C\":\r\n                        $type = \"Számlázott\";\r\n                        break;\r\n\r\n                        case \"R\":\r\n                        $type = \"Jóváhagyott\";\r\n                        break;\r\n\r\n                        }\r\n                        return $type;\r\n                    </code></span>\r\n</div>\r\n    \r\n    \r\n    \r\n    \r\n    \r\n    \r\n        <div class=\"col-6 border border-dark\"><span>Típus:</span></div><div class=\"col-6 border border-dark \">\r\n        <span data-datasourcecode=\"proceeding\" data-code=\"true\"><code class=\"eval\">$res = \"Jegyzőkönyv\"; switch($type){\r\n                        case \'1\':\r\n                        $res = \"Kihelyezési\";\r\n                        break;\r\n                        case \'2\':\r\n                        $res = \"Ürítési\";\r\n                        break;\r\n                        case \'3\':\r\n                        $res = \"Panasz\";\r\n                        break;\r\n                        case \'4\':\r\n                        $res = \"Karbantartási\";\r\n                        break;\r\n                        }\r\n                        return $res;\r\n                    </code></span></div>\r\n\r\n\r\n        <div class=\"col-6 border border-dark\"><span>Partner név:</span></div><div class=\"col-6 border border-dark \">\r\n        <span data-datasourcecode=\"proceeding\" data-key=\"partnerName\">Partner név</span></div><div class=\"col-6 border border-dark\"><span>Partner kód:</span></div><div class=\"col-6 border border-dark \">\r\n        <span data-datasourcecode=\"proceeding\" data-key=\"partnerCode\">Partner kód</span></div><div class=\"col-6 border border-dark issue-date\"><span>Kihelyezés dátuma:</span></div><div class=\"col-6 border border-dark issue-date\">\r\n        \r\n<span data-datasourcecode=\"proceeding\" data-key=\"issueDate\" data-namespace=\"\\UI\\Html\\StaticDate\">Kihelyezés dátuma</span></div></div></div></div>\r\n    <div class=\"col-6 \">\r\n    <div class=\"container-fluid\">\r\n        <div class=\"row object\">\r\n    \r\n    \r\n    \r\n    \r\n    \r\n    \r\n    \r\n    \r\n    \r\n    \r\n    <div class=\"col-6 border border-dark\"><span>Objektum típus:</span></div><div class=\"col-6 border border-dark \">\r\n        <span data-datasourcecode=\"proceeding\" data-code=\"true\"><code class=\"eval\">$type = \"Jegyzőkönyv\"; switch($objectType){\r\n                        case \"invoice\":\r\n                        $type = \"Számla\";\r\n                        break;\r\n                        case \"purchase_invoice\":\r\n                        $type = \"Beszerzési számla\";\r\n                        break;\r\n\r\n                        case \"inventory_entry\":\r\n                        $type = \"Anyagbevételezés\";\r\n                        break;\r\n\r\n                        case \"inventory_exit\":\r\n                        $type = \"Anyagkiadás\";\r\n                        break;\r\n\r\n                        }\r\n                        return $type;\r\n                    </code></span></div><div class=\"col-6 border border-dark\"><span>Objektum bizonylatszám:</span></div><div class=\"col-6 border border-dark \">\r\n        <span data-datasourcecode=\"proceeding\" data-key=\"refDocumentNumber\">Objektum bizonylatszám</span></div></div>\r\n\r\n        <div class=\"row document\">\r\n    <div class=\"col-6 border border-dark\">Bevétel</div>\r\n    <div class=\"col-6 border border-dark\">\r\n    <span data-precision=\"0\" class=\"numeric price\" data-datasourcecode=\"proceeding\" data-key=\"incoming\" data-namespace=\"\\UI\\Html\\StaticNumber\">Bevétel</span> Ft\r\n</div>\r\n    <div class=\"col-6 border border-dark\">Kiadás</div>\r\n    <div class=\"col-6 border border-dark\"><span data-precision=\"0\" class=\"numeric price\" data-datasourcecode=\"proceeding\" data-key=\"outgoing\" data-namespace=\"\\UI\\Html\\StaticNumber\">Kiadás</span> Ft</div>\r\n    <div class=\"col-6 border border-dark\">Jutalék</div>\r\n    <div class=\"col-6 border border-dark\"><span data-precision=\"0\" class=\"numeric price\" data-datasourcecode=\"proceeding\" data-key=\"commission\" data-namespace=\"\\UI\\Html\\StaticNumber\">Jutalék</span> %</div>\r\n    \r\n<div class=\"col-6 border border-dark\">Jutalék összege</div><div class=\"col-6 border border-dark\"><span data-precision=\"0\" class=\"numeric price\" data-datasourcecode=\"proceeding\" data-key=\"amount\" data-namespace=\"\\UI\\Html\\StaticNumber\">Jutalék összege</span> Ft</div></div></div>\r\n\r\n        </div>\r\n    \r\n    \r\n    \r\n    \r\n</div><table class=\"table\">\r\n        <thead style=\"\r\n    border-bottom: 1px solid black;\r\n\">\r\n    <tr><th class=\"left-text\">Raktár kód</th>\r\n<th class=\"left-text\">Raktár név</th>\r\n\r\n\r\n\r\n\r\n\r\n\r\n</tr>\r\n</thead>\r\n        <tbody style=\"\r\n    border-bottom: 1px solid black;\r\n\">\r\n            <tr class=\"print-datasource\" data-datasourcecode=\"proceeding_item\" data-use=\"proceeding\" data-use-key=\"id\" style=\"\">\r\n                <td data-datasourcecode=\"proceeding_item\" data-key=\"warehouseCode\">code</td>\r\n<td data-datasourcecode=\"proceeding_item\" data-key=\"warehouseName\">name</td>\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n           </tr>\r\n        </tbody>\r\n    </table>\r\n\r\n\r\n\r\n<div id=\"signature\">\r\n    <h2>Aláírás</h2>\r\n\r\n    <span data-datasourcecode=\"proceeding\" data-code=\"true\">\r\n    <code class=\"eval\">try{ $file = \\DI\\Model\\Entity\\System\\File\\File::Get(array(\"namespace\" => \"\\CityMedia\\Proceeding\\Proceeding\", \"objectId\" => $id)); $ui = new \\UI\\Html\\File\\MimeType\\ImageBase64(); $ui->SetModel($file->name);\r\nreturn $ui->Render(); } catch(\\Throwable $ex){  }\r\n\r\n</code>\r\n</span>\r\n</div>\r\n<div id=\"comment\"> <h2>Megjegyzés</h2> <div style=\"border:1px solid black;padding: 5px;\" data-datasourcecode=\"proceeding\" data-key=\"comment\">Megjegyzés szövege...</div> </div>\r\n\r\n\r\n\r\n    \r\n\r\n\r\n    \r\n\r\n<style>\r\n     @page {\r\n        margin-top: 150px;\r\n        margin-bottom: 100px;\r\n        margin-left: 0.1cm;\r\n        margin-right: 0.5in;    \r\n        border:1px solid gray;\r\n    }\r\n\r\n</style>\r\n<style data-datasourcecode=\"proceeding\">\r\n\r\n    <?php if(strlen($comment) == 0){ return \"#comment{ display:none;}\";}?>\r\n                                    \r\n</style>\r\n<style data-datasourcecode=\"proceeding\">\r\n\r\n    <?php if($type != 2){ return \".document{ display:none;}\";}?>\r\n                                    \r\n</style>\r\n<style data-datasourcecode=\"proceeding\">\r\n\r\n    <?php if(strlen(trim($objectType)) < 1){ return \".object{ display:none;}\";}?>\r\n                                    \r\n</style><style data-datasourcecode=\"proceeding\">\r\n\r\n    <?php if(strlen(trim($issueDate)) < 1){ return \".issue-date{ display:none;}\";}?>\r\n                                    \r\n</style>\r\n\r\n</div>' WHERE `nubes_printpageeditor`.`namespace` = 'CityMedia\\Proceeding\\Proceeding\\';


UPDATE `nubes_print_datasource` SET `query` = '\r\nselect \r\ncase IFNULL(T1.objectType, \'\')\r\n when \'invoice\' THEN (SELECT documentNumber FROM nubes_invoice as T4 where T4.id = T1.objectId)\r\n when \'purchase_invoice\' THEN (SELECT documentNumber FROM nubes_purchase_invoice as T4 where T4.id = T1.objectId)\r\n when \'inventory_entry\' THEN (SELECT documentNumber FROM nubes_inventory_entry as T4 where T4.id = T1.objectId)\r\n when \'inventory_exit\' THEN (SELECT documentNumber FROM nubes_inventory_exit as T4 where T4.id = T1.objectId)\r\n END as refDocumentNumber,\r\n\r\n\r\nT3.name as partnerName, T3.code as partnerCode, T1.* \r\nfrom ct_proceeding as T1 \r\ninner join nubes_partner as T3 on T3.id = T1.partnerId\r\n\r\nwhere T1.id = :id' WHERE `nubes_print_datasource`.`code` = 'proceeding';


INSERT INTO `nubes_print_datasource` (`id`, `code`, `name`, `query`) VALUES
(NULL, 'proceeding_item', 'Jegyzőkönyv tétel', 'select * from ct_proceeding_item where proceedingId = :id');


INSERT INTO `nubes_printpageeditor_datasource` (`id`, `printPageEditorId`, `printDataSourceId`) VALUES
(NULL, (SELECT id FROM nubes_printpageeditor WHERE namespace = 'CityMedia\\Proceeding\\Proceeding\\'), (SELECT id FROM nubes_print_datasource WHERE code = 'proceeding_item'));


#--2022.08.04 arnolddozsa jegyzőkönyv egyszerűsítések
ALTER TABLE `ct_proceeding`
	ADD COLUMN `recipient` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Átvevő' AFTER `amount`;

UPDATE `nubes_printpageeditor` SET template = '<div class="page print-datasource container-fluid" data-datasourcecode="proceeding">\n    <h1 class="center-text" style="/* width: 582.27px; *//* border: 1px solid black; *//* position: absolute; *//* left: 0; */">Jegyzőkönyv</h1>\n\n<p></p>\n\n\n\n<div class="row" id="productionReportHead" style="\n    font-size: 1.1rem;\n"><div class="col-6">\n    <div class="container-fluid">\n        <div class="row">\n    <div class="col-6 border border-dark"><span>Bizonylatszám:</span></div>\n    <div class="col-6 border border-dark ">\n        <span data-datasourcecode="proceeding" data-key="documentNumber">Bizonylatszám</span></div>\n    <div class="col-6 border border-dark"><span>Státusz:</span></div>\n    <div class="col-6 border border-dark ">\n        <span data-datasourcecode="proceeding" data-code="true"><code class="eval">$type = "Jegyzőkönyv"; switch($status){\n                        case "O":\n                        $type = "Tervezet";\n                        break;\n                        case "C":\n                        $type = "Számlázott";\n                        break;\n\n                        case "R":\n                        $type = "Jóváhagyott";\n                        break;\n\n                        }\n                        return $type;\n                    </code></span>\n</div>\n    \n    \n    \n    \n    \n    \n        <div class="col-6 border border-dark"><span>Típus:</span></div><div class="col-6 border border-dark ">\n        <span data-datasourcecode="proceeding" data-code="true"><code class="eval">$res = "Jegyzőkönyv"; switch($type){\n                        case \'1\':\n                        $res = "Kihelyezési";\n                        break;\n                        case \'2\':\n                        $res = "Ürítési";\n                        break;\n                        case \'3\':\n                        $res = "Panasz";\n                        break;\n                        case \'4\':\n                        $res = "Karbantartási";\n                        break;\n                        }\n                        return $res;\n                    </code></span></div>\n\n\n        <div class="col-6 border border-dark"><span>Partner név:</span></div><div class="col-6 border border-dark ">\n        <span data-datasourcecode="proceeding" data-key="partnerName">Partner név</span></div><div class="col-6 border border-dark"><span>Partner kód:</span></div><div class="col-6 border border-dark ">\n        <span data-datasourcecode="proceeding" data-key="partnerCode">Partner kód</span></div><div class="col-6 border border-dark issue-date"><span>Kihelyezés dátuma:</span></div><div class="col-6 border border-dark issue-date">\n        \n<span data-datasourcecode="proceeding" data-key="issueDate" data-namespace="\\UI\\Html\\StaticDate">Kihelyezés dátuma</span></div></div></div></div>\n    <div class="col-6 ">\n    <div class="container-fluid">\n        <div class="row object">\n    \n    \n    \n    \n    \n    \n    \n    \n    \n    \n    <div class="col-6 border border-dark"><span>Objektum típus:</span></div><div class="col-6 border border-dark ">\n        <span data-datasourcecode="proceeding" data-code="true"><code class="eval">$type = "Jegyzőkönyv"; switch($objectType){\n                        case "invoice":\n                        $type = "Számla";\n                        break;\n                        case "purchase_invoice":\n                        $type = "Beszerzési számla";\n                        break;\n\n                        case "inventory_entry":\n                        $type = "Anyagbevételezés";\n                        break;\n\n                        case "inventory_exit":\n                        $type = "Anyagkiadás";\n                        break;\n\n                        }\n                        return $type;\n                    </code></span></div><div class="col-6 border border-dark"><span>Objektum bizonylatszám:</span></div><div class="col-6 border border-dark ">\n        <span data-datasourcecode="proceeding" data-key="refDocumentNumber">Objektum bizonylatszám</span></div></div>\n\n        <div class="row document">\n    <div class="col-6 border border-dark">Bevétel</div>\n    <div class="col-6 border border-dark">\n    <span data-precision="0" class="numeric price" data-datasourcecode="proceeding" data-key="incoming" data-namespace="\\UI\\Html\\StaticNumber">Bevétel</span> Ft\n</div>\n    <div class="col-6 border border-dark">Kiadás</div>\n    <div class="col-6 border border-dark"><span data-precision="0" class="numeric price" data-datasourcecode="proceeding" data-key="outgoing" data-namespace="\\UI\\Html\\StaticNumber">Kiadás</span> Ft</div>\n    <div class="col-6 border border-dark">Jutalék</div>\n    <div class="col-6 border border-dark"><span data-precision="0" class="numeric price" data-datasourcecode="proceeding" data-key="commission" data-namespace="\\UI\\Html\\StaticNumber">Jutalék</span> %</div>\n    \n<div class="col-6 border border-dark">Jutalék összege</div><div class="col-6 border border-dark"><span data-precision="0" class="numeric price" data-datasourcecode="proceeding" data-key="amount" data-namespace="\\UI\\Html\\StaticNumber">Jutalék összege</span> Ft</div></div></div>\n\n        </div>\n    \n    \n    \n    \n</div><table class="table">\n        <thead style="\n    border-bottom: 1px solid black;\n">\n    <tr><th class="left-text">Raktár kód</th>\n<th class="left-text">Raktár név</th>\n\n\n\n\n\n\n</tr>\n</thead>\n        <tbody style="\n    border-bottom: 1px solid black;\n">\n            <tr class="print-datasource" data-datasourcecode="proceeding_item" data-use="proceeding" data-use-key="id" style="">\n                <td data-datasourcecode="proceeding_item" data-key="warehouseCode">code</td>\n<td data-datasourcecode="proceeding_item" data-key="warehouseName">name</td>\n\n\n\n\n\n\n\n\n           </tr>\n        </tbody>\n    </table>\n\n\n\n<div class="container-fluid">\n    <div class="row">\n<div class="border-dark col-6 issue-date">Átvevő: \n        \n<span data-datasourcecode="proceeding" data-key="recipient">recipient</span></div>\n<div class="border-dark col-6 issue-date">Kiállította: \n<span class="" data-datasourcecode="proceeding" data-code="true"><code class="eval">$user = \\DI\\Model\\Entity\\Administration\\Definitions\\General\\User\\User::Get(array(id => $createUserId)); return $user->lastName . " " . $user->firstName;</code></span></div></div>\n</div>\n<div id="signature">\n    <h2>Aláírás</h2>\n\n    <span data-datasourcecode="proceeding" data-code="true">\n    <code class="eval">try{ $file = \\DI\\Model\\Entity\\System\\File\\File::Get(array("namespace" => "\\CityMedia\\Proceeding\\Proceeding", "objectId" => $id)); $ui = new \\UI\\Html\\File\\MimeType\\ImageBase64(); $ui->SetModel($file->name);\nreturn $ui->Render(); } catch(\\Throwable $ex){ echo $ex->getMessage();  }\n\n</code>\n</span>\n</div>\n<div id="comment"> <h2>Megjegyzés</h2> <div style="border:1px solid black;padding: 5px;" data-datasourcecode="proceeding" data-key="comment">Megjegyzés szövege...</div> </div>\n\n\n\n    \n\n\n    \n\n<style>\n     @page {\n        margin-top: 150px;\n        margin-bottom: 100px;\n        margin-left: 0.1cm;\n        margin-right: 0.5in;    \n        border:1px solid gray;\n    }\n\n</style>\n<style data-datasourcecode="proceeding">\n\n    <?php if(strlen($comment) == 0){ return "#comment{ display:none;}";}?>\n                                    \n</style>\n<style data-datasourcecode="proceeding">\n\n    <?php if($type != 2){ return ".document{ display:none;}";}?>\n                                    \n</style>\n<style data-datasourcecode="proceeding">\n\n    <?php if(strlen(trim($objectType)) < 1){ return ".object{ display:none;}";}?>\n                                    \n</style><style data-datasourcecode="proceeding">\n\n    <?php if(strlen(trim($issueDate)) < 1){ return ".issue-date{ display:none;}";}?>\n                                    \n</style>\n\n</div>'  WHERE namespace = 'CityMedia\\Proceeding\\Proceeding\\' AND typeCode = 'DEFAULT';
INSERT INTO `nubes_print_datasource` (`code`, `name`, `query`) VALUES ('proceeding', 'Jegyzőkönyv fej', '\r\nselect \r\ncase IFNULL(T1.objectType, \'\')\r\n when \'invoice\' THEN (SELECT documentNumber FROM nubes_invoice as T4 where T4.id = T1.objectId)\r\n when \'purchase_invoice\' THEN (SELECT documentNumber FROM nubes_purchase_invoice as T4 where T4.id = T1.objectId)\r\n when \'inventory_entry\' THEN (SELECT documentNumber FROM nubes_inventory_entry as T4 where T4.id = T1.objectId)\r\n when \'inventory_exit\' THEN (SELECT documentNumber FROM nubes_inventory_exit as T4 where T4.id = T1.objectId)\r\n END as refDocumentNumber,\r\n\r\n\r\nT3.name as partnerName, T3.code as partnerCode, T1.* \r\nfrom ct_proceeding as T1 \r\nLEFT JOIN nubes_partner as T3 on T3.id = T1.partnerId\r\n\r\nwhere T1.id = :id') ON DUPLICATE KEY UPDATE query = VALUES(query);
INSERT INTO `nubes_print_datasource` (`code`, `name`, `query`) VALUES ('proceeding_item', 'Jegyzőkönyv tétel', 'select * from ct_proceeding_item where proceedingId = :id') ON DUPLICATE KEY UPDATE query = VALUES(query);

#--2023.01.01 arnolddozsa jegyzőkönyv aláírás fájlok megjelenítése
UPDATE `nubes_printpageeditor` SET `template`='<div class="page print-datasource container-fluid" data-datasourcecode="proceeding">\n    <h1 class="center-text" style="/* width: 582.27px; *//* border: 1px solid black; *//* position: absolute; *//* left: 0; */">Jegyzőkönyv</h1>\n\n<p></p>\n\n\n\n<div class="row" id="productionReportHead" style="\n    font-size: 1.1rem;\n"><div class="col-6">\n    <div class="container-fluid">\n        <div class="row">\n    <div class="col-6 border border-dark"><span>Bizonylatszám:</span></div>\n    <div class="col-6 border border-dark ">\n        <span data-datasourcecode="proceeding" data-key="documentNumber">Bizonylatszám</span></div>\n    <div class="col-6 border border-dark"><span>Státusz:</span></div>\n    <div class="col-6 border border-dark ">\n        <span data-datasourcecode="proceeding" data-code="true"><code class="eval">$type = "Jegyzőkönyv"; switch($status){\n                        case "O":\n                        $type = "Tervezet";\n                        break;\n                        case "C":\n                        $type = "Számlázott";\n                        break;\n\n                        case "R":\n                        $type = "Jóváhagyott";\n                        break;\n\n                        }\n                        return $type;\n                    </code></span>\n</div>\n    \n    \n    \n    \n    \n    \n        <div class="col-6 border border-dark"><span>Típus:</span></div><div class="col-6 border border-dark ">\n        <span data-datasourcecode="proceeding" data-code="true"><code class="eval">$res = "Jegyzőkönyv"; switch($type){\n                        case \'1\':\n                        $res = "Kihelyezési";\n                        break;\n                        case \'2\':\n                        $res = "Ürítési";\n                        break;\n                        case \'3\':\n                        $res = "Panasz";\n                        break;\n                        case \'4\':\n                        $res = "Karbantartási";\n                        break;\n                        }\n                        return $res;\n                    </code></span></div>\n\n\n        <div class="col-6 border border-dark"><span>Partner név:</span></div><div class="col-6 border border-dark ">\n        <span data-datasourcecode="proceeding" data-key="partnerName">Partner név</span></div><div class="col-6 border border-dark"><span>Partner kód:</span></div><div class="col-6 border border-dark ">\n        <span data-datasourcecode="proceeding" data-key="partnerCode">Partner kód</span></div><div class="col-6 border border-dark issue-date"><span>Kihelyezés dátuma:</span></div><div class="col-6 border border-dark issue-date">\n        \n<span data-datasourcecode="proceeding" data-key="issueDate" data-namespace="\\UI\\Html\\StaticDate">Kihelyezés dátuma</span></div></div></div></div>\n    <div class="col-6 ">\n    <div class="container-fluid">\n        <div class="row object">\n    \n    \n    \n    \n    \n    \n    \n    \n    \n    \n    <div class="col-6 border border-dark"><span>Objektum típus:</span></div><div class="col-6 border border-dark ">\n        <span data-datasourcecode="proceeding" data-code="true"><code class="eval">$type = "Jegyzőkönyv"; switch($objectType){\n                        case "invoice":\n                        $type = "Számla";\n                        break;\n                        case "purchase_invoice":\n                        $type = "Beszerzési számla";\n                        break;\n\n                        case "inventory_entry":\n                        $type = "Anyagbevételezés";\n                        break;\n\n                        case "inventory_exit":\n                        $type = "Anyagkiadás";\n                        break;\n\n                        }\n                        return $type;\n                    </code></span></div><div class="col-6 border border-dark"><span>Objektum bizonylatszám:</span></div><div class="col-6 border border-dark ">\n        <span data-datasourcecode="proceeding" data-key="refDocumentNumber">Objektum bizonylatszám</span></div></div>\n\n        <div class="row document">\n    <div class="col-6 border border-dark">Bevétel</div>\n    <div class="col-6 border border-dark">\n    <span data-precision="0" class="numeric price" data-datasourcecode="proceeding" data-key="incoming" data-namespace="\\UI\\Html\\StaticNumber">Bevétel</span> Ft\n</div>\n    <div class="col-6 border border-dark">Kiadás</div>\n    <div class="col-6 border border-dark"><span data-precision="0" class="numeric price" data-datasourcecode="proceeding" data-key="outgoing" data-namespace="\\UI\\Html\\StaticNumber">Kiadás</span> Ft</div>\n    <div class="col-6 border border-dark">Jutalék</div>\n    <div class="col-6 border border-dark"><span data-precision="0" class="numeric price" data-datasourcecode="proceeding" data-key="commission" data-namespace="\\UI\\Html\\StaticNumber">Jutalék</span> %</div>\n    \n<div class="col-6 border border-dark">Jutalék összege</div><div class="col-6 border border-dark"><span data-precision="0" class="numeric price" data-datasourcecode="proceeding" data-key="amount" data-namespace="\\UI\\Html\\StaticNumber">Jutalék összege</span> Ft</div></div></div>\n\n        </div>\n    \n    \n    \n    \n</div><table class="table">\n        <thead style="\n    border-bottom: 1px solid black;\n">\n    <tr><th class="left-text">Raktár kód</th>\n<th class="left-text">Raktár név</th>\n\n\n\n\n\n\n</tr>\n</thead>\n        <tbody style="\n    border-bottom: 1px solid black;\n">\n            <tr class="print-datasource" data-datasourcecode="proceeding_item" data-use="proceeding" data-use-key="id" style="">\n                <td data-datasourcecode="proceeding_item" data-key="warehouseCode">code</td>\n<td data-datasourcecode="proceeding_item" data-key="warehouseName">name</td>\n\n\n\n\n\n\n\n\n           </tr>\n        </tbody>\n    </table>\n\n\n\n<div id="signature">\n    <h2>Aláírás</h2>\n\n    <span data-datasourcecode="proceeding" data-code="true">\n    <code class="eval">try{ $s = ""; $files = \\DI\\Model\\Entity\\System\\File\\File::GetObjectList(array("namespace" => "\\CityMedia\\Proceeding\\Proceeding", "objectId" => $id)); foreach($files as $file) { $ui = new \\UI\\Html\\File\\MimeType\\ImageBase64(); $ui->SetModel($file->name);\n$s .= $ui->Render(); } return $s; } catch(\\Throwable $ex){ echo $ex->getMessage();  }\n\n</code>\n</span>\n</div>\n<div id="comment"> <h2>Megjegyzés</h2> <div style="border:1px solid black;padding: 5px;" data-datasourcecode="proceeding" data-key="comment">Megjegyzés szövege...</div> </div>\n\n\n\n    \n\n\n    \n\n<style>\n     @page {\n        margin-top: 150px;\n        margin-bottom: 100px;\n        margin-left: 0.1cm;\n        margin-right: 0.5in;    \n        border:1px solid gray;\n    }\n\n</style>\n<style data-datasourcecode="proceeding">\n\n    <?php if(strlen($comment) == 0){ return "#comment{ display:none;}";}?>\n                                    \n</style>\n<style data-datasourcecode="proceeding">\n\n    <?php if($type != 2){ return ".document{ display:none;}";}?>\n                                    \n</style>\n<style data-datasourcecode="proceeding">\n\n    <?php if(strlen(trim($objectType)) < 1){ return ".object{ display:none;}";}?>\n                                    \n</style><style data-datasourcecode="proceeding">\n\n    <?php if(strlen(trim($issueDate)) < 1){ return ".issue-date{ display:none;}";}?>\n                                    \n</style>\n\n</div>', `headerTemplate`='\n\n<div><style>\n    .logo{\n\n        max-width:297.45px;\n        max-height:50px;\n        display:inline-block;\n        box-sizing:border-box;\n        vertical-align:text-top;\n        padding:10px;\n        margin-left:50px;\n    }\n    .pageInfo{\n        vertical-align:text-top;\n        width:auto;\n        display:inline-block;\n        box-sizing:border-box;\n    }\n    .pageInfo > span{\n        display:inline-block;\n        vertical-align:text-top;\n        padding-right:10px;\n    }\n</style><div style="font-size: 10px;">\n    <div>\n        <span class="logo">\n    <code>$companyInfo = \\Control\\Application::GetInstance()->GetCompany()->GetCompanyInfo(); $view = new \\UI\\Html\\File\\MimeType\\ImageBase64();         $view->setModel($companyInfo->companyLogo); $view->style["max-height"] = "50px"; return $view->Render();</code></span><span class="pageInfo"><br>\n    \n    <span>\n            <div><code> $companyInfo = \\Control\\Application::GetInstance()->GetCompany()->GetCompanyInfo(); return $companyInfo->name;</code></div>\n<div><code> $companyInfo = \\Control\\Application::GetInstance()->GetCompany()->GetCompanyInfo(); return "{$companyInfo->billCountryCode} {$companyInfo->billPostalCode} {$companyInfo->billCity} {$companyInfo->billStreetName} {$companyInfo->billNumber}";</code></div>\n<div><span>Adószám</span> <span><code> $tax = \\DI\\Model\\Entity\\Administration\\SystemSettings\\Company\\Company_Data_Tax::Get(array("id" => 1)); return $tax->taxNumber;</code></span></div>\n    </span><span>\n    <div><code> $companyInfo = \\Control\\Application::GetInstance()->GetCompany()->GetCompanyInfo(); return $companyInfo->web;</code>\n</div><div><code> $companyInfo = \\Control\\Application::GetInstance()->GetCompany()->GetCompanyInfo(); return $companyInfo->email;</code>\n</div>\n<div><code> $companyInfo = \\Control\\Application::GetInstance()->GetCompany()->GetCompanyInfo(); return $companyInfo->phone;</code></div></span></span></div>\n</div></div>', `footerTemplate`='\n    <div style="font-size:8px; width:21cm;">\n    <div style="text-align:center">MHzQTeam Nubes ERP rendszer <a href="http://mhzq.com" title="Mega-Hercz-Q Kft">www.mhzq.com</a></div>\n<br><div style="font-size:8px; text-align:center;">\n        <span>Oldal</span><span class="pageNumber">1</span>\n/\n<span class="totalPages">1</span></div>\n</div>', `name`='Jegyzőkönyv', `lastModifyState`='DEVELOPMENT' WHERE `namespace`='CityMedia\\Proceeding\\Proceeding\\' AND `typeCode`='DEFAULT';

#--2023.02.01 
INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('Company management','company_management','DEVELOPMENT');UPDATE nubes_translate_lang SET text = 'Cégüzemeltetés',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='company_management');
INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('Workshop management','workshop_management','DEVELOPMENT');UPDATE nubes_translate_lang SET text = 'Műhelyüzemeltetés',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='workshop_management');
INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('Machine management','machine_management','DEVELOPMENT');UPDATE nubes_translate_lang SET text = 'Gépüzemeltetés',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='machine_management');

DROP TABLE nubes_menu;

-- Struktúra mentése tábla test. nubes_menu
CREATE TABLE IF NOT EXISTS `nubes_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Táblán belüli azonosító',
  `name` varchar(50) NOT NULL DEFAULT 'new_menupoint' COMMENT 'Név',
  `url` varchar(100) NOT NULL DEFAULT '#' COMMENT 'Elérési útvonal(Az aktuális elérési részegység)',
  `parent_id` int(11) DEFAULT NULL COMMENT 'Szülő azonosító',
  `sort` int(11) NOT NULL COMMENT 'Sorszám',
  `isVisible` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Látható',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='Menu';

-- Tábla adatainak mentése test.nubes_menu: ~167 rows (hozzávetőleg)
INSERT INTO `nubes_menu` (`id`, `name`, `url`, `parent_id`, `sort`, `isVisible`) VALUES
	(1, 'home_page', '/', NULL, 1, 1),
	(168, 'company_management', '', NULL, 15, 1),
	(169, 'workshop_management', '', NULL, 16, 1),
	(2, 'administration', 'Administration', 168, 1, 1),
	(3, 'business_partners', 'BusinessPartners', 168, 2, 1),
	(4, 'inventory_management', 'StockManagement', 169, 2, 1),
	(5, 'definitions', 'Definitions', 2, 1, 1),
	(6, 'system_settings', 'SystemSettings', 2, 2, 1),
	(7, 'general', 'General', 5, 1, 1),
	(8, 'stock_management', 'StockManagement', 5, 2, 1),
	(9, 'user', 'User', 7, 1, 1),
	(10, 'user_tables', 'UserTable/UserTableList', 26, 2, 1),
	(11, 'query_creator', 'UserQuery/Queries', 26, 1, 1),
	(12, 'item', 'Item', 8, 1, 1),
	(13, 'warehouse', 'Warehouse/WarehouseList', 8, 2, 1),
	(14, 'site', 'Location/LocationList', 8, 3, 1),
	(15, 'itemgroup_editor', 'ItemGroup/ItemGroupEditor', 12, 1, 1),
	(16, 'menu_editor', 'Menu/MenuEditor', 6, 1, 1),
	(17, 'translates', 'Translate/Translate', 25, 1, 1),
	(18, 'tables', 'TableDefinitions/TableList', 6, 3, 1),
	(19, 'business_partner_master_datas', 'Partner/PartnerList', 3, 1, 1),
	(20, 'store_transactions', 'StoreTransactions', 4, 1, 1),
	(21, 'item_master_datas', 'Item/ItemList', 4, 2, 1),
	(22, 'inventory_entry', 'Inventory_Entry/InventoryEntryList', 20, 1, 1),
	(23, 'inventory_exit', 'Inventory_Exit/InventoryExitList', 20, 1, 1),
	(24, 'inventory_transfer', 'Inventory_Transfer/InventoryTransferList', 20, 1, 1),
	(25, 'localization', 'Localization', 5, 3, 1),
	(26, 'user_definitions', 'UserDefinitions', 6, 3, 1),
	(27, 'business_partners', 'BusinessPartners', 5, 4, 1),
	(28, 'partner_group', 'PartnerGroupList', 27, 1, 1),
	(29, 'production', 'Production', 169, 4, 1),
	(30, 'production_item', 'Production_Item/ProductionItemList', 29, 1, 1),
	(31, 'production', 'ProductionList', 29, 2, 1),
	(32, 'production_entry', 'Production_Entry/ProductionEntryList', 29, 3, 1),
	(33, 'production_exit', 'Production_Exit/ProductionExitList', 29, 4, 1),
	(34, 'print', 'Prints', 5, 5, 1),
	(35, 'print_page_editor', 'PrintPageList', 34, 1, 1),
	(36, 'document_number', 'DocumentNumber', 5, 6, 1),
	(37, 'document_number_list', 'DocumentNumberList', 36, 1, 1),
	(38, 'sales', 'Sales', 169, 5, 1),
	(39, 'invoice', 'Invoice', 38, 1, 1),
	(40, 'delivery_note', 'Delivery/DeliveryNoteList', 38, 2, 1),
	(41, 'resource', 'Resource/ResourceList', 169, 3, 1),
	(42, 'authorization', 'Authorization', 2, 3, 1),
	(43, 'access_token', 'OauthAccessTokenList', 42, 1, 1),
	(44, 'client', 'OauthClientList', 42, 2, 1),
	(45, 'human_resource', 'HumanResource', 168, 5, 1),
	(46, 'employee', 'EmployeeList', 45, 1, 1),
	(47, 'quotation', 'Quotation/QuotationList', 38, 3, 1),
	(48, 'order', 'Order/OrderList', 38, 4, 1),
	(49, 'human_resource', 'HumanResource', 5, 7, 1),
	(50, 'employee_position', 'EmployeePositionList', 49, 1, 1),
	(51, 'finance', 'Finance', 5, 8, 1),
	(52, 'bank', 'Bank/BankList', 51, 1, 1),
	(53, 'tax', 'Tax', 51, 2, 1),
	(54, 'vat', 'Vat', 53, 1, 1),
	(55, 'vat_group', 'Vat_Group/Vat_GroupList', 54, 1, 1),
	(56, 'pay_mode', 'Pay_ModeList', 51, 3, 1),
	(57, 'ship_mode', 'Ship_ModeList', 8, 4, 1),
	(58, 'price', 'Price', 4, 3, 1),
	(59, 'price_list', 'PriceList', 58, 1, 1),
	(60, 'discount_group', 'DiscountGroupList', 58, 2, 1),
	(61, 'partner_discount', 'PartnerDiscountList', 58, 3, 1),
	(62, 'purchase', 'Purchase', 169, 1, 1),
	(63, 'purchase_invoice', 'Invoice/PurchaseInvoiceList', 62, 1, 1),
	(64, 'purchase_delivery', 'Delivery/PurchaseDeliveryNoteList', 62, 2, 1),
	(65, 'purchase_order', 'Order/PurchaseOrderList', 62, 3, 1),
	(66, 'log', 'Log/Log', 6, 4, 1),
	(67, 'currency', 'Currency', 51, 4, 1),
	(68, 'lang', 'Lang/LangList', 25, 2, 1),
	(69, 'country', 'Country', 25, 3, 1),
	(70, 'country', 'CountryList', 69, 1, 1),
	(71, 'region', 'RegionList', 69, 2, 1),
	(72, 'company_data', 'Company_Data/Company_Data', 7, 2, 1),
	(73, 'project', 'Project/ProjectList', 51, 5, 1),
	(74, 'users', 'UserList', 9, 1, 1),
	(75, 'permission_editor', 'PermissionEditor', 9, 2, 1),
	(76, 'pay_box', 'Pay_Box/PayBoxList', 51, 6, 1),
	(77, 'finance', 'Finance', 169, 6, 1),
	(78, 'pay_box_document', 'Pay_Box/PayBoxDocumentList', 77, 1, 1),
	(79, 'worksheet', 'Worksheet/WorksheetList', 38, 5, 1),
	(80, 'customs_tariff', 'Customs/CustomsTariffList', 51, 7, 1),
	(81, 'no_tax_document', 'No_Tax_Document/No_Tax_Document_List', 38, 6, 1),
	(82, 'packaging_costs', 'Packaging_Costs', 51, 8, 1),
	(83, 'packaging_costs_material', 'Packaging_Costs_Material_List', 82, 1, 1),
	(84, 'packaging_costs_nature_list', 'Packaging_Costs_Nature_List', 82, 2, 1),
	(85, 'packaging_costs_reuse', 'Packaging_Costs_Reuse_List', 82, 3, 1),
	(86, 'packaging_costs_trans', 'Packaging_Costs_Trans_List', 82, 4, 1),
	(87, 'working_time_calendar', 'Working_Time_Calendar', 49, 2, 1),
	(88, 'reports', 'Reports', 169, 9, 1),
	(89, 'finance', 'Finance', 88, 1, 1),
	(90, 'pay_box_document', 'Pay_Box/Pay_Box_Document_Report', 89, 1, 1),
	(91, 'income', 'Income/Income_Report', 89, 2, 1),
	(92, 'sales', 'Sales', 88, 2, 1),
	(93, 'invoice', 'Invoice/Invoice_Report', 92, 1, 1),
	(94, 'invoice', 'InvoiceList', 39, 1, 1),
	(95, 'nav_invoice_informations', 'Nav_Invoice_List', 39, 2, 1),
	(96, 'template', 'Template', 5, 9, 1),
	(97, 'document_template', 'Document/DocumentTemplateList', 96, 1, 1),
	(98, 'message', 'Message', 5, 10, 1),
	(99, 'message', 'Message_List', 98, 1, 1),
	(100, 'message_template', 'Message_Template_List', 98, 2, 1),
	(101, 'inventory_control', 'Inventory_Control/Inventory_Control_List', 4, 4, 1),
	(102, 'payment', 'Payment/PaymentList', 77, 2, 1),
	(103, 'partner_contract', 'Partner_Contract/Partner_Contract_List', 3, 2, 1),
	(104, 'revenue_reports', 'RevenueReports', 38, 7, 1),
	(105, 'sales_by_partner', 'SalesByPartner', 104, 1, 1),
	(106, 'sales_by_partner_item', 'SalesByPartnerItem', 104, 2, 1),
	(107, 'sales_a_partner_item', 'SalesAPartnerItem', 104, 3, 1),
	(108, 'meo', 'MEO/MachineControllingList', 29, 5, 1),
	(109, 'service_management', 'ServiceManagement', 169, 8, 1),
	(110, 'service_portfolio_and_catalog_management', 'ServiceCatalog/ServiceCatalogList', 109, 1, 1),
	(111, 'service_design', 'ServiceDesign', 109, 2, 1),
	(112, 'service_level_agreement_design', 'ServiceLevelAgreementDesignList', 111, 1, 1),
	(113, 'capacity_management', 'CapacityManagement', 111, 2, 1),
	(114, 'capacity_design', 'CapacityDesignReport', 113, 1, 1),
	(115, 'capacity_check', 'CapacityCheckReport', 113, 2, 1),
	(116, 'operational_tasks', 'OperationalTasks', 109, 3, 1),
	(117, 'event_management', 'Event_Management', 116, 1, 1),
	(118, 'event_query', 'EventQueryReport', 116, 2, 1),
	(119, 'query_of_transfer_steps', 'QueryOfTransferStepsReport', 116, 3, 1),
	(120, 'tasks', 'Tasks', 5, 11, 1),
	(121, 'event_activity', 'EventActivityList', 120, 1, 1),
	(122, 'controlling', 'Controlling', 169, 7, 1),
	(123, 'task', 'Task/TaskList', 122, 1, 1),
	(124, 'document', 'Document', 168, 4, 1),
	(125, 'document_management', 'DocumentManagementList', 124, 1, 1),
	(126, 'workflow_management', 'WorkflowManagement', 122, 2, 1),
	(127, 'workflow_board', 'WorkflowBoard/WorkflowBoardList', 126, 1, 1),
	(128, 'list_of_settlements', 'SettlementList', 51, 9, 1),
	(129, 'knowledge_base', 'KnowledgeBaseKnowledgeBaseReport', 124, 2, 1),
	(130, 'machine_management', 'CityMedia', NULL, 17, 1),
	(131, 'maintenance', 'Maintenance/MaintenanceList', 130, 1, 1),
	(132, 'contract', 'CityMedia/Partner/PartnerContractList', 168, 3, 1),
	(135, 'map', 'Map', 130, 1, 1),
	(136, 'production_calendar', 'Production_Planner', 29, 6, 1),
	(137, 'overpaid_invoices', 'OverpaidInvoicesReport', 92, 2, 1),
	(138, 'expired_issue_date_invoices', 'ExpiredIssueDateInvoicesReport', 92, 3, 1),
	(139, 'outgoing_invoices', 'OutgoingInvoicesReport', 92, 4, 1),
	(140, 'inventory_information', 'Inventory_Information', 4, 5, 1),
	(141, 'inventory_movement', 'Inventory_Movement', 140, 1, 1),
	(142, 'item_sales_report', 'Item_Sales_Report', 140, 2, 1),
	(143, 'production_planner', 'Order_Production', 29, 7, 1),
	(144, 'revenue_reports', 'RevenueReports', 62, 4, 1),
	(145, 'sales_by_partner', 'SalesByPartner', 144, 1, 1),
	(146, 'sales_by_partner_item', 'SalesByPartnerItem', 144, 2, 1),
	(147, 'sales_a_partner_item', 'SalesAPartnerItem', 144, 3, 1),
	(148, 'fuel_consumption', 'Fuel_Consumption/FuelConsumptionList', 130, 4, 1),
	(149, 'fixture_usage', 'FixtureUsageReport', 130, 5, 1),
	(150, 'proceeding', 'Proceeding/ProceedingList', 130, 6, 1),
	(151, 'balance', 'Balance', 77, 3, 1),
	(152, 'demand_for_payment', 'DemandForPaymentReport', 151, 1, 1),
	(153, 'balance_sheet', 'BalanceSheetReport', 151, 2, 1),
	(154, 'payment_compensation', 'PaymentCompensationReport', 151, 3, 1),
	(155, 'open_quantities_report', 'OpenQuantitiesReport', 104, 4, 1),
	(156, 'sales_by_item_group', 'SalesByItemGroup', 104, 5, 1),
	(157, 'production', 'Production', 88, 3, 1),
	(158, 'production', 'Production', 157, 1, 1),
	(159, 'currency', 'CurrencyList', 67, 1, 1),
	(160, 'exchange_rate', 'ExchangeRateList', 67, 2, 1),
	(161, 'production', 'Production', 5, 12, 1),
	(162, 'time_data', 'TimeDataList', 161, 1, 1),
	(163, 'error_type', 'Error_Type', 161, 2, 1),
	(164, 'meo_error_type', 'MeoErrorTypeList', 163, 1, 1),
	(165, 'financial_letter', 'Financial_Letter/FinancialLetterList', 77, 4, 1),
	(166, 'fee_requester', 'Fee_Requester/FeeRequesterList', 38, 8, 1),
	(167, 'ageing_invoices', 'AgeingInvoicesReport', 92, 5, 1)
;

ALTER TABLE nubes_menu ADD CONSTRAINT `fk_nubes_menu_parent_id` 
FOREIGN KEY (`parent_id`) REFERENCES `nubes_menu` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
;

ALTER TABLE `ct_partner_contract_item`
	ADD COLUMN `itemId` VARCHAR(255) NOT NULL AFTER `warehouseName`;

ALTER TABLE `ct_proceeding_item`
ADD COLUMN `quantity` DECIMAL(19,6) NOT NULL DEFAULT 0 COMMENT 'Eladott érmék darabszáma' AFTER `warehouseName`,
ADD COLUMN `netPrice` DECIMAL(19,6) NOT NULL DEFAULT 0 COMMENT 'Egységár' AFTER `quantity`,
ADD COLUMN `incoming` DECIMAL(19,6) NOT NULL DEFAULT 0 COMMENT 'Bevétel' AFTER `netPrice`,
ADD COLUMN `outgoing` DECIMAL(19,6) NOT NULL DEFAULT 0 COMMENT 'Kiadás' AFTER `incoming`,
ADD COLUMN `commission` DECIMAL(19,6) NOT NULL DEFAULT 0 COMMENT 'Jutalék' AFTER `outgoing`,
ADD COLUMN `netAmount` DECIMAL(19,6) NOT NULL DEFAULT 0 COMMENT 'Nettó összeg' AFTER `commission`;

ALTER TABLE `ct_proceeding_item`
	ADD COLUMN `uploadedQuantity` DECIMAL(19,6) NOT NULL DEFAULT 0 COMMENT 'Feltöltve' AFTER `warehouseName`;

INSERT INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('Uploaded quantity','uploaded_quantity','DEVELOPMENT');UPDATE nubes_translate_lang SET text = 'Feltöltött mennyiség',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='uploaded_quantity');

ALTER TABLE `ct_proceeding_item`
	ADD COLUMN `vatGroupId` INT NOT NULL DEFAULT 0 COMMENT 'Adócsoport azonosító' AFTER `netAmount`,
	ADD COLUMN `grossAmount` DECIMAL(19,6) NOT NULL DEFAULT 0 AFTER `vatGroupId`;


ALTER TABLE `ct_proceeding_item`
	CHANGE COLUMN `grossAmount` `grossAmount` DECIMAL(19,6) NOT NULL DEFAULT '0.000000' COMMENT 'Bruttó összeg' AFTER `vatGroupId`;

ALTER TABLE `ct_proceeding_item`
	ADD COLUMN `vatRate` DECIMAL(19,6) NOT NULL DEFAULT 0 COMMENT 'Adóhányad' AFTER `vatGroupId`;


#--2023.05.24 Dózsa Arnold
ALTER TABLE `ct_partner_contract_item`
	ADD COLUMN `netPrice` DECIMAL(19,6) NOT NULL DEFAULT 0 COMMENT 'Nettó egységár (fix bérleti díjas szerződés esetén)' AFTER `warehouseName`;

ALTER TABLE `ct_proceeding_item`
	ADD COLUMN `contractType` CHAR(1) NULL COMMENT 'Szerződés típus' AFTER `grossAmount`,
	ADD COLUMN `objectType` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Objektum típus' AFTER `contractType`,
	ADD COLUMN `objectId` INT NULL DEFAULT NULL COMMENT 'Objektum azonosító' AFTER `objectType`;

INSERT IGNORE INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('Zsolt\'s dashboard','zsolts_dashboard','DEVELOPMENT');UPDATE nubes_translate_lang SET text = 'Zsolt kezelőpultja',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='zsolts_dashboard');

INSERT INTO nubes_menu(id, name, url, parent_id, sort)
SELECT null, 'zsolts_dashboard', 'MapDashboard', id, (
SELECT COUNT(sort) + 1 FROM nubes_menu WHERE parent_id = T1.id
) FROM nubes_menu AS T1 where getMenuNamespace(id) = 'CityMedia/';

ALTER TABLE `nubes_warehouse`
	ADD COLUMN `U_companyName` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Cégnév' AFTER `U_registrationNumber`;


INSERT INTO nubes_data_type(table_schema,table_name,column_name,column_default_value,is_nullable,data_type,sub_type,character_maximum_length,numeric_precision,numeric_scale,column_key,extra,regex,privileges) VALUES ('nubes','nubes_warehouse','U_companyName','','0','varchar','','100',0,0,'','','','select,insert,update,references');
INSERT INTO nubes_field_valid_values(col_id,value,description) VALUES ((SELECT id FROM nubes_data_type WHERE table_schema = 'nubes' AND table_name = 'nubes_warehouse' AND column_name = 'U_companyName'),'Emlék-kép Kft','Emlék-kép Kft');

INSERT INTO nubes_field_valid_values(col_id,value,description) VALUES ((SELECT id FROM nubes_data_type WHERE table_schema = 'nubes' AND table_name = 'nubes_warehouse' AND column_name = 'U_companyName'),'Coinmat','Coinmat');


INSERT INTO nubes_field_valid_values(col_id,value,description) VALUES ((SELECT id FROM nubes_data_type WHERE table_schema = 'nubes' AND table_name = 'nubes_warehouse' AND column_name = 'U_companyName'),'Citymedia','Citymedia');


INSERT INTO nubes_field_valid_values(col_id,value,description) VALUES ((SELECT id FROM nubes_data_type WHERE table_schema = 'nubes' AND table_name = 'nubes_warehouse' AND column_name = 'U_companyName'),'Coindesign','Coindesign');

INSERT INTO `nubes_printpageeditor` (`namespace`, `typeCode`, `template`, `headerTemplate`, `footerTemplate`, `name`, `lastModifyState`) VALUES ('CityMedia\\CoinSales\\', 'DEFAULT', '<div class="page container-fluid">\n            \n\n\n            \n            \n\n\n            \n\n\n\n            <h2 class="uk-margin-small">Gépenként</h2>\n<table class="table">\n                <thead style="\n    border-bottom: 1px solid black;\n">\n                <tr><th class="left-text">Cég</th>\n                    <th class="left-text">Gép</th><th class="left-text">Irsz</th><th class="left-text">Város</th><th class="left-text">Cím</th><th class="right-text">Mennyiség</th>\n                    <th class="right-text">Összeg</th>\n                    \n                    \n\n                    \n                    </tr>\n                </thead>\n                <tbody style="\n    border-bottom: 1px solid black;\n">\n                <tr class="print-datasource" data-datasourcecode="coin_sales" style="">\n                    <td class="p-0"><div data-datasourcecode="coin_sales" data-key="U_companyName" class="p-0">U_companyName</div></td><td class="p-0"><div data-datasourcecode="coin_sales" data-key="name" class="p-0">name</div><div data-datasourcecode="coin_sales" data-key="code" class="p-0">code</div><div data-datasourcecode="coin_sales" data-key="U_serialNumber" class="p-0">U_serialNumber</div></td>\n                    \n                    <td class="p-0"><div data-datasourcecode="coin_sales" data-key="postalCode" class="p-0">postalCode</div></td><td class="p-0"><div data-datasourcecode="coin_sales" data-key="city" class="p-0">city</div></td><td class="p-0"><span data-datasourcecode="coin_sales" data-key="streetName" class="p-0">streetName</span>\n    <span data-datasourcecode="coin_sales" data-key="publicPlaceCategory" class="p-0">publicPlaceCategory</span>\n    <span data-datasourcecode="coin_sales" data-key="number" class="p-0">number</span></td><td class="numeric p-0"><span data-datasourcecode="coin_sales" data-key="quantity" data-namespace="\\UI\\Html\\StaticNumber">quantity</span>\n                        </td>\n                    <td class="numeric p-0"><span data-datasourcecode="coin_sales" data-key="amount" data-namespace="\\UI\\Html\\StaticNumber">amount</span></td>\n                    \n                    \n                    \n\n                    \n                </tr>\n                </tbody>\n                \n            </table>\n            \n<h2 class="uk-margin-small">Cégenként</h2><table class="table">\n                <thead style="\n    border-bottom: 1px solid black;\n">\n                <tr><th class="left-text">Cég</th>\n                    <th class="right-text">Mennyiség</th>\n                    <th class="right-text">Összeg</th>\n                    \n                    \n\n                    \n                    </tr>\n                </thead>\n                <tbody style="\n    border-bottom: 1px solid black;\n">\n                <tr class="print-datasource" data-datasourcecode="coin_sales_company_summary" style="">\n                    <td class="p-0"><div data-datasourcecode="coin_sales_company_summary" data-key="U_companyName" class="p-0">U_companyName</div></td>\n                    \n                    <td class="numeric p-0"><span data-datasourcecode="coin_sales_company_summary" data-key="quantity" data-namespace="\\UI\\Html\\StaticNumber">quantity</span>\n                        </td>\n                    <td class="numeric p-0"><span data-datasourcecode="coin_sales_company_summary" data-key="amount" data-namespace="\\UI\\Html\\StaticNumber">amount</span></td>\n                    \n                    \n                    \n\n                    \n                </tr>\n                </tbody>\n                \n            </table><h2>Teljes összesítő</h2><table class="table">\n                <thead style="\n    border-bottom: 1px solid black;\n">\n                <tr>\n                    <th class="right-text">Mennyiség</th>\n                    <th class="right-text">Összeg</th>\n                    \n                    \n\n                    \n                    </tr>\n                </thead>\n                <tbody style="\n    border-bottom: 1px solid black;\n">\n                <tr class="print-datasource" data-datasourcecode="coin_sales_summary" style="">\n                    \n                    \n                    <td class="numeric p-0"><span data-datasourcecode="coin_sales_summary" data-key="quantity" data-namespace="\\UI\\Html\\StaticNumber">quantity</span>\n                        </td>\n                    <td class="numeric p-0"><span data-datasourcecode="coin_sales_summary" data-key="amount" data-namespace="\\UI\\Html\\StaticNumber">amount</span></td>\n                    \n                    \n                    \n\n                    \n                </tr>\n                </tbody>\n                \n            </table><div style="\n    font-size: 11px;\n">\n    \n</div>\n            <style>\n    @page{\n        margin-top:130px;\n    }\n</style><style>\n                <?php return ($isPrintPrice==1)?"":".price{display:none;}"; ?>\n\n            </style>\n<style>\n                <?php return (strlen($comment)!=0)?"":"#comment{display:none;}"; ?>\n\n            </style>\n        </div>\n\n', '\n\n<div style="\n    position: relative;\n    width: 16cm;\n    max-width: 16cm;\n    min-height: 41px;\n    /* border: 1px solid black; */\n    margin: 0px 5mm;\n">\n    <div style="font-size: 10px;">\n    <h1 class="bold mb-2 p-0" style="\n    background: transparent;\n    font-size: 12px;\n    margin: 0px;\n    /* margin-left: 5mm; */\n    /* height: 50px; */\n    position: absolute;\n    bottom: 0px;\n    left: 0;\n    right: 0;\n    text-align: center;\n    /* border: 1px solid black; */\n">Érme értékesítés riport</h1><div style="\n    position: relative;\n    height: 50px;\n    font-size:8px;\n    /* width: 19cm; */\n    padding: 0px;\n">\n        <span class="logo">\n    <code>$companyInfo = \\Control\\Application::GetInstance()->GetCompany()->GetCompanyInfo(); $view = new \\UI\\Html\\File\\MimeType\\ImageBase64();         $view->setModel($companyInfo->companyLogo); $view->style["max-height"] = "50px"; return $view->Render();</code></span><div style="font-size:8px;text-align: right;position: absolute;bottom: 0;right: 0;">\n        <span>Oldal</span><span class="pageNumber">1</span>\n/\n<span class="totalPages">1</span></div></div><div>\n    <span>\n    <code>if($params["dateFrom"] != null){ return \'Dátum -tól: \'; }</code>\n    </span>\n        <span><code> return $params["dateFrom"];</code></span></div><div>\n    <span>\n    <code>if($params["dateTo"] != null){ return \'Dátum -ig: \'; }</code>\n    </span>\n        <span><code> return $params["dateTo"];</code></span></div>\n</div>\n</div>', '\n    <div style="font-size:8px; width:21cm;">\n    <div style="text-align:center">MHzQTeam Nubes ERP rendszer <a href="http://mhzq.com" title="Mega-Hercz-Q Kft">www.mhzq.com</a></div>\n<br><div style="font-size:8px; text-align:center;">\n        <span>Oldal&nbsp;</span><span class="pageNumber">1</span>\n/\n<span class="totalPages">1</span></div>\n</div>', 'Érme értékesítések', 'DEVELOPMENT')
ON DUPLICATE KEY UPDATE template = VALUES(template), headerTemplate = VALUES(headerTemplate), footerTemplate = VALUES(footerTemplate)
;
INSERT INTO `nubes_print_datasource` (`code`, `name`, `query`) VALUES ('coin_sales_summary', 'Érme értékesítések összesítő', 'SELECT SUM(T2.quantity) AS quantity, SUM(T2.amount) AS amount FROM \n(SELECT DISTINCT U_companyName FROM nubes_warehouse\nWHERE LENGTH(U_companyName) > 0\n) AS T\nLEFT JOIN (\nSELECT T2.U_companyName, T1.warehouseId, COUNT(T1.id) AS quantity\n,  COUNT(T1.id) *\nCASE WHEN T1.warehouseId = 40 then 1200\nELSE 1000 END AS amount\nFROM ct_telemetry AS T1\nINNER JOIN nubes_warehouse AS T2 ON T2.id = T1.warehouseId\nWHERE (:dateFrom IS NULL OR DATE(T1.piLogCreateDate) >= :dateFrom)\nAND T2.name NOT LIKE \'%teszt%\'\nAND T1.`type` = \'sales\'\nAND \n(:dateTo IS NULL OR DATE(T1.piLogCreateDate) <= :dateTo)\nGROUP BY T1.warehouseId\n) AS T2 ON T2.U_companyName = T.U_companyName\n\n\n;') ON DUPLICATE KEY UPDATE query = VALUES(query);
INSERT INTO `nubes_print_datasource` (`code`, `name`, `query`) VALUES ('coin_sales_company_summary', 'Érme értékesítések cégenként összesítő', '\nSELECT T.U_companyName, SUM(T2.quantity) AS quantity, SUM(T2.amount) AS amount FROM \n(SELECT DISTINCT U_companyName FROM nubes_warehouse\nWHERE LENGTH(U_companyName) > 0\n) AS T\nLEFT JOIN (\nSELECT T2.U_companyName, T1.warehouseId, COUNT(T1.id) AS quantity\n,  COUNT(T1.id) *\nCASE WHEN T1.warehouseId = 40 then 1200\nELSE 1000 END AS amount\nFROM ct_telemetry AS T1\nINNER JOIN nubes_warehouse AS T2 ON T2.id = T1.warehouseId\nWHERE (:dateFrom IS NULL OR DATE(T1.piLogCreateDate) >= :dateFrom)\nAND T2.name NOT LIKE \'%teszt%\'\nAND T1.`type` = \'sales\'\nAND \n(:dateTo IS NULL OR DATE(T1.piLogCreateDate) <= :dateTo)\nGROUP BY T1.warehouseId\n) AS T2 ON T2.U_companyName = T.U_companyName \n\nGROUP BY T.U_companyName\n;') ON DUPLICATE KEY UPDATE query = VALUES(query);
INSERT INTO `nubes_print_datasource` (`code`, `name`, `query`) VALUES ('coin_sales', 'Érme értékesítések', 'SELECT T2.U_companyName, T1.warehouseId, T2.U_serialNumber, T2.code, T2.name, T2.postalCode, T2.city, T2.streetName, T2.publicPlaceCategory, T2.number, COUNT(T1.id) AS quantity\n,  COUNT(T1.id) *\nCASE WHEN T1.warehouseId = 40 then 1200\nELSE 1000 END AS amount\nFROM ct_telemetry AS T1\nINNER JOIN nubes_warehouse AS T2 ON T2.id = T1.warehouseId\nWHERE (:dateFrom IS NULL OR DATE(T1.piLogCreateDate) >= :dateFrom)\nAND T2.name NOT LIKE \'%teszt%\'\nAND T1.`type` = \'sales\'\nAND \n(:dateTo IS NULL OR DATE(T1.piLogCreateDate) <= :dateTo)\nGROUP BY T1.warehouseId\nORDER BY 1,4\n;') ON DUPLICATE KEY UPDATE query = VALUES(query);

INSERT INTO nubes_menu(id, name, url, parent_id, sort)
SELECT null, 'coin_sales', 'CoinSales', id, (
SELECT COUNT(sort) + 1 FROM nubes_menu WHERE parent_id = T1.id
) FROM nubes_menu AS T1 where getMenuNamespace(id) = 'CityMedia/';


INSERT IGNORE INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('Coin sales','coin_sales','DEVELOPMENT');UPDATE nubes_translate_lang SET text = 'Érme értékesítések',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='coin_sales');

#--2024.12.02
ALTER TABLE `ct_partner_contract_item`
	CHANGE COLUMN `itemId` `itemId` VARCHAR(255) NOT NULL COMMENT 'Cikk azonosító' COLLATE 'utf8_general_ci' AFTER `netPrice`,
	ADD COLUMN `settlementByDifference` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Különbözet szerinti elszámolás' AFTER `itemId`;

INSERT IGNORE INTO nubes_translate(name,unique_name,lastModifyState) VALUES ('Settlement by difference','settlement_by_difference','DEVELOPMENT');UPDATE nubes_translate_lang SET text = 'Különbözet szerinti elszámolás',langId = '1',lastModifyState = 'DEVELOPMENT' WHERE 1 AND langId='1' AND translateId=(SELECT id FROM nubes_translate WHERE unique_name='settlement_by_difference');

COMMIT;
