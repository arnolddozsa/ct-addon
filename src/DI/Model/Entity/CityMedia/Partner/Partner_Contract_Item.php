<?php
namespace DI\Model\Entity\CityMedia\Partner;


use DI\Model\Entity\CityMedia\CityMediaAbstractData;


/**
 * CityMedia szerződés tétel adatmodell osztály.
 */
class Partner_Contract_Item extends CityMediaAbstractData{

	/** @var int Táblán belüli azonosító */
	public $id;

	/** @var int Szerződés azonosító */
	protected $documentId;

	/** @var int Raktár (gép) azonosító */
	protected $warehouseId;

	/** @var string Raktár (gép) kód */
	public $warehouseCode;

	/** @var string Raktár (gép) név */
	public $warehouseName;

	/** @var float Egységár (fix bérleti díjas szerződés esetén) */
	protected $netPrice;

	/** @var int Cikk azonosító */
	protected $itemId;
	
	/** @var int Különbözet szerinti elszámolás */
	protected $settlementByDifference = 0;




}