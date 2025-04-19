<?php
namespace DI\Model\Entity\CityMedia\Maintenance;


use DI\Model\Entity\CityMedia\CityMediaAbstractData;


/**
 * Karbantartási bizonylat adatmodell osztály.
 */
class Maintenance extends CityMediaAbstractData{

	/** @var int Táblán belüli azonosító */
	public $id;

	/** @var string Bizonylatszám */
	public $documentNumber;

	/** @var datetime Karbantartás dátuma */
	public $maintenanceDate;

	/** @var datetime Létrehozás dátuma */
	public $createDate;

	/** @var int Létrehozó felhasználó azonosító */
	protected $createUserId;

	/** @var datetime|null Módosítás dátuma */
	public $modifyDate = null;

	/** @var int|null Módosító felhasználó azonosító */
	protected $modifyUserId = null;

	/** @var int Gép (raktár) azonosító */
	protected $warehouseId;

	/** @var string Gép (raktár) név */
	public $warehouseName;

	/** @var string Gép (raktár) kód */
	public $warehouseCode;


}