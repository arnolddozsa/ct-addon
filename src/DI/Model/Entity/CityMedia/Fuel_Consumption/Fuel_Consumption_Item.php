<?php

namespace DI\Model\Entity\CityMedia\Fuel_Consumption;

/**
 * Üzemanyag felhasználás tétel adatmodell osztály.
 */
class Fuel_Consumption_Item extends \DI\Model\Entity\CityMedia\CityMediaAbstractData{

	/** @var int Táblán belüli id */
	public $id;

	/** @var int Üzemanyag felhasználás azonosító */
	public $documentId;

	/** @var int Raktár (gép) azonosító */
	public $warehouseId;

	/** @var string Raktár (gép) kód */
	public $warehouseCode;

	/** @var string Raktár (gép) név */
	public $warehouseName;

	/** @var double Felhasznált üzemanyag */
	public $usedFuelQuantity = 0;

}