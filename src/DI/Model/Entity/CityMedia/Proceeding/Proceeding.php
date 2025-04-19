<?php
namespace DI\Model\Entity\CityMedia\Proceeding;


use DI\Model\Entity\CityMedia\CityMediaAbstractData;


/**
 * CityMedia jegyzőkönyv adatmodell osztály.
 */
class Proceeding extends CityMediaAbstractData{

	/** @var int Táblán belüli azonosító */
	public $id;

	/** @var string Szerződés szám */
	public $documentNumber;

	/** @var string Típus */
	public $type;

	/** @var string Státusz */
	public $status;

	/** @var string|null Objektum típusa */
	public $objectType = null;

    /** @var int|null Objektum Id */
	public $objectId = null;

	/** @var int|null Partner azonosító */
	protected $partnerId = null;

	/** @var datetime|null Kihelyezés dátuma */
	public $issueDate = null;

    /** @var datetime Létrehozás dátuma */
	public $createDate;

    /** @var int Létrehozó felhasználó azonosító */
	protected $createUserId;

    /** @var datetime|null Módosítás dátuma*/
	public $modifyDate = null;

    /** @var int|null Módosító felhasználló azonosító */
	protected $modifyUserId = null;

	/** @var string|null Megjegyzés */
	public $comment = null;

	/** @var double|null Bevétel */
	protected $incoming = null;

	/** @var double|null Kiadás */
	protected $outgoing = null;

	// /** @var double|null Jutalék */
	// protected $commission = null;

	/** @var double|null Jutalék összege nettó*/
	protected $netAmount = null;

	/** @var double|null Jutalék összege bruttó */
	protected $grossAmount = null;

	/** @var string|null Átvevő */
	protected $recipient = null;

	/** @var string|null Kiállította */
	protected $createdBy = null;


	/**
	 *
	 * Az átadott dokumentumot cast-olja át a megadott típusra.
	 *
	 * @param $destination string|object Cél típus.
	 * @param $source object Forrás objektum.
	 * @return object
	 * @throws \Exception
	 */
	public static function CloneDocument($destination, $source){
		try{
			//castoljuk az objektumot
			$object = Cast($destination, $source);

			//töröljük a documentNumber-t
			if(property_exists(get_class($object), "documentNumber")){
				$object->documentNumber = "";
			}

			//töröljük az id-t
			$object->id = null;

			//beállítjuk a hivatkozott tábla nevét
			$object->objectType = explode($source::GetDbPrefix(), $source::GetDbTableFromClassName(get_class($source)))[1];

			//beállítjuk a hivatkozott sor azonosítóját
			$object->objectId = $source->id;

			//kommentet töröljük
			$object->comment = null;

			return $object;
		}catch(\Exception $ex){
			throw $ex;
		}
	}

    /**
     * @inheritDoc
     * @Override
     */
    public function PrepareObject(string $destObjectNS){
        //$this: forrás
        //$destObj: cél

        $destObj = parent::PrepareObject($destObjectNS);
        //bizonylat dátuma
        $destObj->docDate = date("Y-m-d");
        $destObj->taxDate = date("Y-m-d");


        switch ($destObjectNS) {

            //számla
            case "\\DI\\Model\\Entity\\Sales\\Invoice\\Invoice":

                $destObj->paymentDate = \Control\BusinessPartners\Partner\Partner::SetPaymentDate($destObj->partnerId, $destObj->docDate);
                $destObj->comment = $this->comment . "<br/>" . $this->documentNumber." jegyzőkönyvből készített számla.";

                //adószámok
                try{
                    $partner = \DI\Model\Entity\BusinessPartners\Partner\Partner::Get(array("id" => $destObj->partnerId));

                    if($partner->isTaxPayer){
                        if($partner->customerTaxNumber_taxpayerId > 0 && $partner->customerTaxNumber_vatCode > 0 && $partner->customerTaxNumber_countyCode > 0){
                            $destObj->customerTaxNumber_taxpayerId = $partner->customerTaxNumber_taxpayerId;
                            $destObj->customerTaxNumber_vatCode = $partner->customerTaxNumber_vatCode;
                            $destObj->customerTaxNumber_countyCode = $partner->customerTaxNumber_countyCode;
                        }
                    }

                    if($partner->isGroupTaxPayer){
                        if($partner->groupMemberTaxNumber_taxpayerId > 0 && $partner->groupMemberTaxNumber_vatCode > 0 && $partner->groupMemberTaxNumber_countyCode > 0){
                            $destObj->customerGroupMemberTaxNumber_taxpayerId = $partner->groupMemberTaxNumber_taxpayerId;
                            $destObj->customerGroupMemberTaxNumber_vatCode = $partner->groupMemberTaxNumber_vatCode;
                            $destObj->customerGroupMemberTaxNumber_countyCode = $partner->groupMemberTaxNumber_countyCode;
                        }
                    }

                    $destObj->partnerName = $partner->name;
                    $destObj->partnerCode = $partner->code;


                    $destObj->currencyId = $partner->currencyId;
                    $destObj->currencyCode = $partner->currencyCode;

                    $destObj->payModeId = $partner->payModeId;
                    $destObj->shipModeId = $partner->shipModeId;

                    try{
                        $bankAccount = \DI\Model\Entity\Administration\Definitions\Finance\Bank\Bank_Account::Get(array("isDefault" => 1, "partnerId" => $partner->id));
                        $destObj->customerBankAccountNumber = $bankAccount->code;
                    }catch(\Exception $ex){
                        $destObj->customerBankAccountNumber = null;
                    }


                    try{
                        $billPartnerAddress = \DI\Model\Entity\BusinessPartners\Partner\Partner_Address::Get(array("partnerId" => $partner->id, "addressType" => "B", "isDefault" => 1));
                        $destObj->billCountryId = $billPartnerAddress->countryId;
                        $destObj->billCountryCode = \DI\Model\Entity\Administration\Definitions\Localization\Country\Country::Get(array("id" => $billPartnerAddress->countryId))->code;
                        $destObj->billRegionId = $billPartnerAddress->regionId;
                        $destObj->billPostalCode = $billPartnerAddress->postalCode;
                        $destObj->billCity = $billPartnerAddress->city;
                        $destObj->billStreetName = $billPartnerAddress->streetName;
                        $destObj->billPublicPlaceCategory = $billPartnerAddress->publicPlaceCategory;
                        $destObj->billNumber = $billPartnerAddress->number;
                        $destObj->billBuilding = $billPartnerAddress->building;
                        $destObj->billStaircase = $billPartnerAddress->staircase;
                        $destObj->billFloor = $billPartnerAddress->floor;
                        $destObj->billDoor = $billPartnerAddress->door;
                        $destObj->billLotNumber = $billPartnerAddress->lotNumber;

                    }catch(\Exception $ex){
                        $billPartnerAddress = null;
                    }


                    try{
                        $shipPartnerAddress = \DI\Model\Entity\BusinessPartners\Partner\Partner_Address::Get(array("partnerId" => $partner->id, "addressType" => "S", "isDefault" => 1));
                        $destObj->shipCountryId = $shipPartnerAddress->countryId;
                        $destObj->shipCountryCode = \DI\Model\Entity\Administration\Definitions\Localization\Country\Country::Get(array("id" => $shipPartnerAddress->countryId))->code;
                        $destObj->shipRegionId = $shipPartnerAddress->regionId;
                        $destObj->shipPostalCode = $shipPartnerAddress->postalCode;
                        $destObj->shipCity = $shipPartnerAddress->city;
                        $destObj->shipStreetName = $shipPartnerAddress->streetName;
                        $destObj->shipPublicPlaceCategory = $shipPartnerAddress->publicPlaceCategory;
                        $destObj->shipNumber = $shipPartnerAddress->number;
                        $destObj->shipBuilding = $shipPartnerAddress->building;
                        $destObj->shipStaircase = $shipPartnerAddress->staircase;
                        $destObj->shipFloor = $shipPartnerAddress->floor;
                        $destObj->shipDoor = $shipPartnerAddress->door;
                        $destObj->shipLotNumber = $shipPartnerAddress->lotNumber;

                    }catch(\Exception $ex){
                        $shipPartnerAddress = null;
                    }

                }catch(\Exception $ex){
                    //do nothing
                    $partner = null;
                }



                break;

            //breszerzési számla
            case "\\DI\\Model\\Entity\\Purchase\\Invoice\\Purchase_Invoice":

                $destObj->comment = $this->comment . "<br/>" . $this->documentNumber." jegyzőkönyvből készített beszerzési számla.";

                //adószámok
                try {
                    $partner = \DI\Model\Entity\BusinessPartners\Partner\Partner::Get(array("id" => $destObj->partnerId));

                    if ($partner->isTaxPayer) {
                        if ($partner->customerTaxNumber_taxpayerId > 0 && $partner->customerTaxNumber_vatCode > 0 && $partner->customerTaxNumber_countyCode > 0) {
                            $destObj->supplierTaxNumber_taxpayerId = $partner->customerTaxNumber_taxpayerId;
                            $destObj->supplierTaxNumber_vatCode = $partner->customerTaxNumber_vatCode;
                            $destObj->supplierTaxNumber_countyCode = $partner->customerTaxNumber_countyCode;
                        }
                    }

                    if ($partner->isGroupTaxPayer) {
                        if ($partner->groupMemberTaxNumber_taxpayerId > 0 && $partner->groupMemberTaxNumber_vatCode > 0 && $partner->groupMemberTaxNumber_countyCode > 0) {
                            $destObj->supplierGroupMemberTaxNumber_taxpayerId = $partner->groupMemberTaxNumber_taxpayerId;
                            $destObj->supplierGroupMemberTaxNumber_vatCode = $partner->groupMemberTaxNumber_vatCode;
                            $destObj->supplierGroupMemberTaxNumber_countyCode = $partner->groupMemberTaxNumber_countyCode;
                        }
                    }

                    $destObj->partnerName = $partner->name;
                    $destObj->partnerCode = $partner->code;


                    $destObj->currencyId = $partner->currencyId;
                    $destObj->currencyCode = $partner->currencyCode;

                    $destObj->payModeId = $partner->payModeId;
                    $destObj->shipModeId = $partner->shipModeId;

                    try{
                        $bankAccount = \DI\Model\Entity\Administration\Definitions\Finance\Bank\Bank_Account::Get(array("isDefault" => 1, "partnerId" => $partner->id));
                        $destObj->supplierBankAccountNumber = $bankAccount->code;
                    }catch(\Exception $ex){
                        $destObj->supplierBankAccountNumber = null;
                    }


                    try{
                        $supplierPartnerAddress = \DI\Model\Entity\BusinessPartners\Partner\Partner_Address::Get(array("partnerId" => $partner->id, "addressType" => "B", "isDefault" => 1));
                        $destObj->supplierCountryId = $supplierPartnerAddress->countryId;
                        $destObj->supplierCountryCode = \DI\Model\Entity\Administration\Definitions\Localization\Country\Country::Get(array("id" => $supplierPartnerAddress->countryId))->code;
                        $destObj->supplierRegionId = $supplierPartnerAddress->regionId;
                        $destObj->supplierPostalCode = $supplierPartnerAddress->postalCode;
                        $destObj->supplierCity = $supplierPartnerAddress->city;
                        $destObj->supplierStreetName = $supplierPartnerAddress->streetName;
                        $destObj->supplierPublicPlaceCategory = $supplierPartnerAddress->publicPlaceCategory;
                        $destObj->supplierNumber = $supplierPartnerAddress->number;
                        $destObj->supplierBuilding = $supplierPartnerAddress->building;
                        $destObj->supplierStaircase = $supplierPartnerAddress->staircase;
                        $destObj->supplierFloor = $supplierPartnerAddress->floor;
                        $destObj->supplierDoor = $supplierPartnerAddress->door;
                        $destObj->supplierLotNumber = $supplierPartnerAddress->lotNumber;

                    }catch(\Exception $ex){
                        $supplierPartnerAddress = null;
                    }


                }catch (\Exception $ex){
                    $partner = null;
                }

                break;


            default:
                throw new \Exception("Hiba! Nem definiált bizonylat típus. ".$destObjectNS);
                break;

        }


        return $destObj;


    }


}