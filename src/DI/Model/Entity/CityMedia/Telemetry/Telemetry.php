<?php
namespace DI\Model\Entity\CityMedia\Telemetry;


use DI\Model\Entity\CityMedia\CityMediaAbstractData;


/**
 * Telemetria adat
 */
class Telemetry extends CityMediaAbstractData{

	/** @var int Táblán belüli azonosító */
	public $id;

	/** @var int Raktár azonosító */
	public $warehouseId;

	/** @var string Típus pl.: sales,doorOpen,doorClose,hopperLowLevel,hopperEmpty,other... */
	public $type;

    /** @var string Telemetria adat leírása */
	public $description;

    /** @var float Érték */
	public $value;

    /** @var int Raspberry pi adatbázis log elsődleges azonosító */
	public $piLogId;

    /** @var datetime Raspberry pi adatbázis log létrehozás ideje*/
	public $piLogCreateDate;

	/** @var datetime Létrehozás dátuma */
	public $createDate;

	/** @var int Létrehozó felhasználó azonosító */
	protected $createUserId;

	/** @var datetime|null Módosítás dátuma */
	public $modifyDate = null;

	/** @var int|null Módosító felhasználó azonosító */
	protected $modifyUserId = null;

}