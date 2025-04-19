<?php
namespace DI\Model\Entity\CityMedia\Maintenance;


use DI\Model\Entity\CityMedia\CityMediaAbstractData;


/**
 * Karbantartási bizonylat tétel adatmodell osztály.
 */
class Maintenance_Item extends CityMediaAbstractData{

	/** @var int Táblán belüli azonosító */
	public $id;

	/** @var string Karbantartásio bizonylat azonosító */
	protected $documentId;

	/** @var string Név */
	public $name;


}