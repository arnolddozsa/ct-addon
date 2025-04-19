<?php
namespace DI\Model\Entity\CityMedia\Proceeding;


use DI\Model\Entity\CityMedia\CityMediaAbstractData;


/**
 * CityMedia jegyzőkönyv tétel adatmodell osztály.
 */
class Proceeding_Item extends CityMediaAbstractData{

	/** @var int Táblán belüli azonosító */
	public $id;

	/** @var int Jegyzőkönyv azonosító */
	protected $proceedingId;

	/** @var int Raktár azonosító */
	protected $warehouseId;

	/** @var string Raktár kódja */
	public $warehouseCode;

	/** @var string Raktár neve */
	public $warehouseName;

    /** @var double|null Feltöltött mennyiség */
	protected $uploadedQuantity = 0;

    /** @var double|null Mennyiség */
	protected $quantity = 0;

    /** @var double|null Nettó */
	protected $netPrice = 0;

    /** @var double|null Bevétel */
	protected $incoming = 0;

	/** @var double|null Kiadás */
	protected $outgoing = 0;

	/** @var double|null Jutalék */
	protected $commission = 0;

	/** @var double|null Jutalék összege nettó*/
	protected $netAmount = 0;

	/** @var double|null Adócsoport azonosító*/
	protected $vatGroupId = null;

	/** @var double|null Adóhányad*/
	protected $vatRate = 0;

	/** @var double|null Jutalék összege bruttó*/
	protected $grossAmount = 0;

    
	/** @var string Szerződés típus */
	protected $contractType;
	
	/** @var string Objektum típus */
	protected $objectType;

	/** @var string Objektum azonosító */
	protected $objectId;

    /**
     * @inheritDoc
     * @Override
     */
    public function PrepareObject(string $destObjectNS){
        //$this: forrás
        //$destObj: cél

        //$destObj = parent::PrepareObject($destObjectNS);

        $proceeding = \DI\Model\Entity\CityMedia\Proceeding\Proceeding::Get(array("id" => $this->proceedingId));

        switch ($destObjectNS){

            //számla tétel
            case "\\DI\\Model\\Entity\\Sales\\Invoice\\Invoice_Item":


                $invoiceItem = new \DI\Model\Entity\Sales\Invoice\Invoice_Item();

                $item = \DI\Model\Entity\StockManagement\Item\Item::Get(array("code" => "JUTALEK"));
                $invoiceItem->quantity = 1;
                $invoiceItem->openQuantity = 1;
                $invoiceItem->quantityUnit = $item->inventoryUnit;
                $invoiceItem->name = $item->name;
                $invoiceItem->code = $item->code;
                $invoiceItem->itemId = $item->id;
                $invoiceItem->warehouseId = $this->warehouseId;

                //ha van megadva vámtarifaszám, behúzuk az adó adatait
                if($item->customsTariffCode && strlen($item->customsTariffCode) > 0){
                    $customsTariff = \DI\Model\Entity\Administration\Definitions\Finance\Customs\Customs_Tariff::Get(array("code" => $item->customsTariffCode));
                    $vatGroup = \DI\Model\Entity\Administration\Definitions\Finance\Tax\Vat\Vat_Group\Vat_Group::Get(array("id" => $customsTariff->vatGroupId));
                    $invoiceItem->vatGroupId = $vatGroup->id;
                    $invoiceItem->vatRate = $vatGroup->rate;

                    //ha nincs, alapételmezett adócsoportot húzzuk be
                }else{
                    $vatGroup = \DI\Model\Entity\Administration\Definitions\Finance\Tax\Vat\Vat_Group\Vat_Group::Get(array("isDefault" => 1));
                    $invoiceItem->vatGroupId = $vatGroup->id;
                    $invoiceItem->vatRate = $vatGroup->rate;
                }

                $row = $invoiceItem->CreateDataSource();

                $row["quantity"] = $invoiceItem->quantity;

                //ha storno akkor - előjel
                if(isset($_GET["status"]) && $_GET["status"] == "S"){
                    $row["quantity"] = -($row["quantity"]);
                }

                $grossPrice = ($proceeding->incoming - $proceeding->outgoing) * ($proceeding->commission / 100);
                $row["grossPrice"] = $grossPrice;
                $row["netPrice"] = $row["grossPrice"] / (1 + ($row["vatRate"] / 100));


                $invoiceItem->FromArray($row);
                $destObj = $invoiceItem;

                break;


            //beszerzési számla tétel
            case "\\DI\\Model\\Entity\\Purchase\\Invoice\\Purchase_Invoice_Item":


                $invoiceItem = new \DI\Model\Entity\Purchase\Invoice\Purchase_Invoice_Item();

                $item = \DI\Model\Entity\StockManagement\Item\Item::Get(array("code" => "JUTALEK"));
                $invoiceItem->quantity = 1;
                $invoiceItem->openQuantity = 1;
                $invoiceItem->quantityUnit = $item->inventoryUnit;
                $invoiceItem->name = $item->name;
                $invoiceItem->code = $item->code;
                $invoiceItem->itemId = $item->id;
                $invoiceItem->warehouseId = $this->warehouseId;

                //ha van megadva vámtarifaszám, behúzuk az adó adatait
                if($item->customsTariffCode && strlen($item->customsTariffCode) > 0){
                    $customsTariff = \DI\Model\Entity\Administration\Definitions\Finance\Customs\Customs_Tariff::Get(array("code" => $item->customsTariffCode));
                    $vatGroup = \DI\Model\Entity\Administration\Definitions\Finance\Tax\Vat\Vat_Group\Vat_Group::Get(array("id" => $customsTariff->vatGroupId));
                    $invoiceItem->vatGroupId = $vatGroup->id;
                    $invoiceItem->vatRate = $vatGroup->rate;

                    //ha nincs, alapételmezett adócsoportot húzzuk be
                }else{
                    $vatGroup = \DI\Model\Entity\Administration\Definitions\Finance\Tax\Vat\Vat_Group\Vat_Group::Get(array("isDefault" => 1));
                    $invoiceItem->vatGroupId = $vatGroup->id;
                    $invoiceItem->vatRate = $vatGroup->rate;
                }

                $row = $invoiceItem->CreateDataSource();

                $row["quantity"] = $invoiceItem->quantity;

                //ha storno akkor - előjel
                if(isset($_GET["status"]) && $_GET["status"] == "S"){
                    $row["quantity"] = -($row["quantity"]);
                }

                $grossPrice = ($proceeding->incoming - $proceeding->outgoing) * ($proceeding->commission / 100);
                $row["grossPrice"] = $grossPrice;
                $row["netPrice"] = $row["grossPrice"] / (1 + ($row["vatRate"] / 100));


                $invoiceItem->FromArray($row);
                $destObj = $invoiceItem;

                break;

            default:
                throw new \Exception("Hiba! Nem definiált bizonylat típus. ".$destObjectNS);
                break;
        }




        return $destObj;
    }

}