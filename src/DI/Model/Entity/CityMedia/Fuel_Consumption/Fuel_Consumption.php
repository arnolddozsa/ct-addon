<?php

namespace DI\Model\Entity\CityMedia\Fuel_Consumption;

/**
 * Üzemanyag felhasználás adatmodell osztály.
 */
class Fuel_Consumption extends \DI\Model\Entity\CityMedia\CityMediaAbstractData{

	/** @var int Táblán belüli id */
	public $id;

	/** @var string Bizonylatszám */
	public $documentNumber;

	/** @var string Bizonylat dátuma */
	public $docDate;

	/** @var string Státusz */
	public $status = "O";

	/** @var datetime Létrehozás dátuma */
	public $createDate;

	/** @var int Létrehozó felhasználó azonosító */
	protected $createUserId;

	/** @var datetime|null Módosítás dátuma */
	public $modifyDate = null;

	/** @var int|null Módosító felhasználó azonosító */
	protected $modifyUserId = null;

	/** @var string|null Megjegyzés */
	public $comment = null;

	/** @var double Felhasznált üzemanyag mennyisége összesen (literben) */
	protected $usedFuelQuantitySum;

	/** @var double Megmaradt üzemanyag mennyisége összesen (literben) */
	protected $remainedFuelQuantitySum;


}