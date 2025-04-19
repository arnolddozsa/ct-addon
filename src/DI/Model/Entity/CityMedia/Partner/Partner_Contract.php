<?php
namespace DI\Model\Entity\CityMedia\Partner;


use DI\Model\Entity\CityMedia\CityMediaAbstractData;


/**
 * CityMedia szerződés adatmodell osztály.
 */
class Partner_Contract extends CityMediaAbstractData{

	/** @var int Táblán belüli azonosító */
	public $id;

	/** @var string Bizonylatszám */
	public $documentNumber;

	/** @var int Partner azonosító */
	protected $partnerId;

	/** @var string Partner név */
	public $partnerName;

	/** @var string Partner kód */
	public $partnerCode;

	/** @var datetime Lejárati dátum */
	public $expiresDate;

	/** @var string Státusz */
	public $status = "O";

	/** @var string Típus */
	public $type;

	/** @var datetime Létrehozás dátuma */
	public $createDate;

	/** @var int Létrehozó felhasználó azonosító */
	protected $createUserId;

	/** @var datetime Módosítás dátuma */
	public $modifyDate = null;

	/** @var int Módosító felhasználó azonosító */
	protected $modifyUserId = null;

	/** @var string|null Megjegyzés */
	public $comment = null;

	/** @var double|null Jutalék */
	protected $commission = null;



}