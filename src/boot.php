<?php

	use Control\Administration\Definitions\Localization\Translate\Translate as Trans;
	use Control\Application;
	use DI\Model\Data\DataTableCreator;
	use DI\Model\Data\DataTypeDefinitions;
	use DI\Model\Entity\Administration\Definitions\Template\Document\Document_Template;
	use Control\BusinessPartners\Partner\Partner;
	use DI\Model\Entity\BusinessPartners\Partner_Contract\Partner_Contract;
	use DI\Model\Entity\BusinessPartners\Partner_Contract\Partner_Contract_Item;
	use DI\Model\Entity\FormMode;
	use DI\Model\Entity\Purchase\Delivery\Purchase_Delivery_Note;
	use DI\Model\Entity\Purchase\Invoice\Purchase_Invoice_Item;
	use DI\Model\Entity\Purchase\Order\Purchase_Order;
	use DI\Model\Entity\Sales\Delivery\Delivery_Note;
	use DI\Model\Entity\Sales\Invoice\Invoice_Item;
	use DI\Model\Entity\Sales\Order\Order;
	use DI\Model\Entity\TDocumentLine;
	use \DI\Model\Sql\Query as QE;


	//ürítési jegyzőkönyvből jutalékos számla létrehozásakor frissítse a jegyzőkönyvet
	\Control\Sales\Invoice\Invoice::On("Add", function($e, $invoice){
		if($invoice->objectType == "proceeding"){
			try{
				$proceeding = \DI\Model\Entity\CityMedia\Proceeding\Proceeding::Get(array("id" => $invoice->objectId));

				$proceeding->objectType = "invoice";
				$proceeding->objectId = $invoice->id;
				$proceeding->status = "C";

				\Control\CityMedia\Proceeding\Proceeding::Update(array("proceeding" => $proceeding->ToArray()));

			}catch(\Exception $ex){
				throw $ex;
			}catch(\Throwable $th){
				throw $th;
			}
		}
	});


	//ürítési jegyzőkönyvből jutalékos beszerzési számla létrehozásakor frissítse a jegyzőkönyvet
	\Control\Purchase\Invoice\Purchase_Invoice::On("Add", function($e, $invoice){
		if($invoice->objectType == "proceeding"){
			try{
				$proceeding = \DI\Model\Entity\CityMedia\Proceeding\Proceeding::Get(array("id" => $invoice->objectId));

				$proceeding->objectType = "purchase_invoice";
				$proceeding->objectId = $invoice->id;
				$proceeding->status = "C";

				\Control\CityMedia\Proceeding\Proceeding::Update(array("proceeding" => $proceeding->ToArray()));

			}catch(\Exception $ex){
				throw $ex;
			}catch(\Throwable $th){
				throw $th;
			}
		}
	});



	//felülírjuk a beszerzési számla nézetet
//	\UI\View\Pub\Pages\Purchase\Invoice\Purchase_Invoice::On("CreateContent", function($e, $args){
//		$__DAO = Application::GetInstance()->GetSql();
//		$DI = $args->GetDI();
//		$UI = $args->GetUI();
//
//		ob_start();
//
//
//
//		try {
//
//			//alapértelmezett form mód
//			$formmode = FormMode::$ok;
//
//			//ha nincs érvényes id paraméter megadva az url-ben, akkor hibát dob
//			if(isset($_GET["id"]) && $_GET["id"] < 1){
//				throw new \Exception("Érvénytelen azonosító");
//			}
//
//			$id = isset($_GET['id']) ? $_GET['id'] : '';
//
//			$data = array();
//			$isFromDeliveryNote = false;
//			$isFromOrder = false;
//			$isFromInvoice = false;
//			$isFromProceeding = false;
//
//			try {
//				$data["purchaseInvoice"] = $DI::Get(array("id" => $id));
//				$args->SetTitle($args->GetTitle() . " - " . $data["purchaseInvoice"]->documentNumber);
//
//			} catch (\Exception $ex) {
//				$data["purchaseInvoice"] = new $DI();
//				$args->SetTitle($args->GetTitle() . " - " . Trans::Get("add_new"));
//				$object = null;
//
//				//ha származtatott számlát hozunk létre
//				if (!empty($_GET["objectType"]) && !empty($_GET["objectId"])) {
//					$type = "";
//					switch ($_GET["objectType"]) {
//						//szállítólevélből
//						case "purchase_delivery_note":
//							$type = "árubeérkezésből";
//							try {
//								$object = Purchase_Delivery_Note::Get(array("id" => $_GET["objectId"]));
//								$isFromDeliveryNote = true;
//							} catch (\Exception $ex) {
//								$isFromDeliveryNote = false;
//							}
//
//							break;
//
//						//rendelésből
//						case "purchase_order":
//							$type = "beszerzési rendelésből";
//							try {
//								$object = Purchase_Order::Get(array("id" => $_GET["objectId"]));
//								$isFromOrder = true;
//							} catch (\Exception $ex) {
//								$isFromOrder = false;
//							}
//
//							break;
//
//						//jegyzőkönyvből
//						case "proceeding":
//							$type = "jegyzőkönyvből";
//							try{
//								$object = \DI\Model\Entity\CityMedia\Proceeding\Proceeding::Get(array("id" => $_GET["objectId"]));
//
//								$object->docDate = date("Y-m-d");
//								//fizetési határidő
//								//$object->paymentDate = Partner::SetPaymentDate($object->partnerId, $object->docDate);
//
//
//								//teljesítési határidő
//
//
//
//
//								$isFromProceeding = true;
//							}catch(\Exception $ex){
//								$isFromProceeding = false;
//							}
//							break;
//
//						case "purchase_invoice":
//							try{
//								$data["purchaseInvoice"] = $DI::Get(array("id" => $_GET["objectId"]));
//								$data["purchaseInvoice"]->originalDocumentId = $_GET["objectId"];
//								$data["purchaseInvoice"]->originalDocumentDocumentNumber = $data["purchaseInvoice"]->documentNumber;
//								if(isset($_GET["status"]) && $_GET["status"] == "S"){
//									$data["purchaseInvoice"]->status = "S";
//									$data["purchaseInvoice"]->comment = $data["purchaseInvoice"]->originalDocumentDocumentNumber." beszerzési számlából készített érvénytelenítő beszerzési számla.";
//								}else{
//									$data["purchaseInvoice"]->comment = $data["purchaseInvoice"]->originalDocumentDocumentNumber." beszerzési számlából készített módosító beszerzési számla.";
//								}
//
//								$isFromInvoice = true;
//							}catch(\Exception $ex){
//								$isFromInvoice = false;
//							}
//
//							break;
//					}
//
//					if($object != null){
//						if($object->status == "C"){
//							throw new \Exception("Hiba! Nem lehet lezárt státuszú " . $type . " (" . $object->documentNumber . ") beszerzési számlát készíteni!");
//						}
//
//
//						$data["purchaseInvoice"] = $object::CloneDocument($DI, $object);
//						$data["purchaseInvoice"]->docDate = date("Y-m-d");
//						$data["purchaseInvoice"]->taxDate = date("Y-m-d");
//						$data["purchaseInvoice"]->comment = $object->documentNumber." ".$type." készített beszerzési számla.";
//					}
//				}
//
//				if($data["purchaseInvoice"]->partnerId > 0){
//					//adószámok
//					try{
//						$partner = \DI\Model\Entity\BusinessPartners\Partner\Partner::Get(array("id" => $data["purchaseInvoice"]->partnerId));
//
//						if($partner->isTaxPayer){
//							if($partner->customerTaxNumber_taxpayerId > 0 && $partner->customerTaxNumber_vatCode > 0 && $partner->customerTaxNumber_countyCode > 0){
//								$data["purchaseInvoice"]->supplierTaxNumber_taxpayerId = $partner->customerTaxNumber_taxpayerId;
//								$data["purchaseInvoice"]->supplierTaxNumber_vatCode = $partner->customerTaxNumber_vatCode;
//								$data["purchaseInvoice"]->supplierTaxNumber_countyCode = $partner->customerTaxNumber_countyCode;
//							}
//						}
//
//						if($partner->isGroupTaxPayer){
//							if($partner->groupMemberTaxNumber_taxpayerId > 0 && $partner->groupMemberTaxNumber_vatCode > 0 && $partner->groupMemberTaxNumber_countyCode > 0){
//								$data["purchaseInvoice"]->supplierGroupMemberTaxNumber_taxpayerId = $partner->groupMemberTaxNumber_taxpayerId;
//								$data["purchaseInvoice"]->supplierGroupMemberTaxNumber_vatCode = $partner->groupMemberTaxNumber_vatCode;
//								$data["purchaseInvoice"]->supplierGroupMemberTaxNumber_countyCode = $partner->groupMemberTaxNumber_countyCode;
//							}
//						}
//
//
//
//						if($isFromProceeding){
//							$data["purchaseInvoice"]->partnerName = $partner->name;
//							$data["purchaseInvoice"]->partnerCode = $partner->code;
//
//
//							$data["purchaseInvoice"]->currencyId = $partner->currencyId;
//							$data["purchaseInvoice"]->currencyCode = $partner->currencyCode;
//
//							$data["purchaseInvoice"]->payModeId = $partner->payModeId;
//							$data["purchaseInvoice"]->shipModeId = $partner->shipModeId;
//
//							try{
//								$bankAccount = \DI\Model\Entity\Administration\Definitions\Finance\Bank\Bank_Account::Get(array("isDefault" => 1, "partnerId" => $partner->id));
//								$data["purchaseInvoice"]->supplierBankAccountNumber = $bankAccount->code;
//							}catch(\Exception $ex){
//								$data["purchaseInvoice"]->supplierBankAccountNumber = null;
//							}
//
//
//							try{
//								$supplierPartnerAddress = \DI\Model\Entity\BusinessPartners\Partner\Partner_Address::Get(array("partnerId" => $partner->id, "addressType" => "B", "isDefault" => 1));
//								$data["purchaseInvoice"]->supplierCountryId = $supplierPartnerAddress->countryId;
//								$data["purchaseInvoice"]->supplierCountryCode = \DI\Model\Entity\Administration\Definitions\Localization\Country\Country::Get(array("id" => $supplierPartnerAddress->countryId))->code;
//								$data["purchaseInvoice"]->supplierRegionId = $supplierPartnerAddress->regionId;
//								$data["purchaseInvoice"]->supplierPostalCode = $supplierPartnerAddress->postalCode;
//								$data["purchaseInvoice"]->supplierCity = $supplierPartnerAddress->city;
//								$data["purchaseInvoice"]->supplierStreetName = $supplierPartnerAddress->streetName;
//								$data["purchaseInvoice"]->supplierPublicPlaceCategory = $supplierPartnerAddress->publicPlaceCategory;
//								$data["purchaseInvoice"]->supplierNumber = $supplierPartnerAddress->number;
//								$data["purchaseInvoice"]->supplierBuilding = $supplierPartnerAddress->building;
//								$data["purchaseInvoice"]->supplierStaircase = $supplierPartnerAddress->staircase;
//								$data["purchaseInvoice"]->supplierFloor = $supplierPartnerAddress->floor;
//								$data["purchaseInvoice"]->supplierDoor = $supplierPartnerAddress->door;
//								$data["purchaseInvoice"]->supplierLotNumber = $supplierPartnerAddress->lotNumber;
//
//							}catch(\Exception $ex){
//								$supplierPartnerAddress = null;
//							}
//
//
//
//
//
//						}
//
//					}catch(\Exception $ex){
//						//do nothing
//					}
//
//
//
//
//				}
//
//
//				$formmode = FormMode::$add;
//			}
//
//
//			$dt_creator = new DataTableCreator($__DAO);
//
//
//			$qe = new QE\Query();
//			$t1 = $qe->AddFromTable(DB_PREFIX . "purchase_invoice_item", "T1");
//
//			//0
//			$field = $qe->AddField("id");
//			$field->SetReference($t1);
//			//1
//			$field = $qe->AddField("itemId");
//			$field->SetReference($t1);
//			//2
//			$field = $qe->AddField("code");
//			$field->SetReference($t1);
//			//3
//			$field = $qe->AddField("name");
//			$field->SetReference($t1);
//			//4
//			$field = $qe->AddField("quantity");
//			$field->SetReference($t1);
//			//5
//			$field = $qe->AddField("quantityUnit");
//			$field->SetReference($t1);
//			//6
//			$field = $qe->AddField("netPrice");
//			$field->SetReference($t1);
//			//7
//			$field = $qe->AddField("vatGroupId");
//			$field->SetReference($t1);
//			//8
//			$field = $qe->AddField("vatRate");
//			$field->SetReference($t1);
//			//9
//			$field = $qe->AddField("grossPrice");
//			$field->SetReference($t1);
//			//@TODO:
//			//nettó érték
//			//bruttó érték
//			//@TODO: end
//			//10
//			$field = $qe->AddField("discount");
//			$field->SetReference($t1);
//			//11
//			$field = $qe->AddField("discountDescription");
//			$field->SetReference($t1);
//			//12
//			$field = $qe->AddField("warehouseId");
//			$field->SetReference($t1);
//			//13
//			$field = $qe->AddField("deliveryDate");
//			$field->SetReference($t1);
//			//14
//			$field = $qe->AddField("openQuantity");
//			$field->SetReference($t1);
//			//15
//			$field = $qe->AddField("isAdvanceIndicator");
//			$field->SetReference($t1);
//			//16
//			$field = $qe->AddField("projectId");
//			$field->SetReference($t1);
//			//17
//			$field = $qe->AddField("objectType");
//			$field->SetReference($t1);
//			//18
//			$field = $qe->AddField("objectId");
//			$field->SetReference($t1);
//
//
//
//			$cond = $qe->AddWhere();
//			$field = new QE\Field("documentId");
//			$field->SetReference($t1);
//			$cond->SetLeftField($field);
//
//			$orderBy = $qe->AddOrderBy("lineNumber");
//			$orderBy->SetType("ASC");
//
//
//			if($isFromInvoice){
//				$params = array($_GET["objectId"]);
//			}else {
//				$params = array($id);
//			}
//
//
//			$data["purchaseInvoiceItem"] = $dt_creator->FromDataSource($qe, $params);
//			$data["purchaseInvoiceItem"]->SetRows(null);
//
//			$data["purchaseInvoiceItem"]->GetColumn("id")->SetVisible(false);
//			$data["purchaseInvoiceItem"]->GetColumn("id")->SetEditable(false);
//
//
//			$data["purchaseInvoiceItem"]->GetColumn("quantity")->GetDataType()->data_type = DataTypeDefinitions::$decimal;
//			$data["purchaseInvoiceItem"]->GetColumn("quantity")->GetDataType()->numeric_precision = 19;
//			$data["purchaseInvoiceItem"]->GetColumn("quantity")->GetDataType()->numeric_scale = 8;
//
//			$data["purchaseInvoiceItem"]->GetColumn("netPrice")->GetDataType()->data_type = DataTypeDefinitions::$decimal;
//			$data["purchaseInvoiceItem"]->GetColumn("netPrice")->GetDataType()->numeric_precision = 19;
//			$data["purchaseInvoiceItem"]->GetColumn("netPrice")->GetDataType()->numeric_scale = 8;
//
//			$data["purchaseInvoiceItem"]->GetColumn("vatRate")->GetDataType()->data_type = DataTypeDefinitions::$decimal;
//			$data["purchaseInvoiceItem"]->GetColumn("vatRate")->GetDataType()->numeric_precision = 19;
//			$data["purchaseInvoiceItem"]->GetColumn("vatRate")->GetDataType()->numeric_scale = 8;
//
//			$data["purchaseInvoiceItem"]->GetColumn("grossPrice")->GetDataType()->data_type = DataTypeDefinitions::$decimal;
//			$data["purchaseInvoiceItem"]->GetColumn("grossPrice")->GetDataType()->numeric_precision = 19;
//			$data["purchaseInvoiceItem"]->GetColumn("grossPrice")->GetDataType()->numeric_scale = 8;
//
//			$data["purchaseInvoiceItem"]->GetColumn("discount")->GetDataType()->data_type = DataTypeDefinitions::$decimal;
//			$data["purchaseInvoiceItem"]->GetColumn("discount")->GetDataType()->numeric_precision = 19;
//			$data["purchaseInvoiceItem"]->GetColumn("discount")->GetDataType()->numeric_scale = 8;
//
//			$data["purchaseInvoiceItem"]->GetColumn("deliveryDate")->GetDataType()->data_type = DataTypeDefinitions::$date;
//
//			$data["purchaseInvoiceItem"]->GetColumn("openQuantity")->GetDataType()->data_type = DataTypeDefinitions::$decimal;
//			$data["purchaseInvoiceItem"]->GetColumn("openQuantity")->GetDataType()->numeric_precision = 19;
//			$data["purchaseInvoiceItem"]->GetColumn("openQuantity")->GetDataType()->numeric_scale = 8;
//			$data["purchaseInvoiceItem"]->GetColumn("openQuantity")->SetEditable(false);
//
//			$data["purchaseInvoiceItem"]->GetColumn("isAdvanceIndicator")->GetDataType()->data_type = DataTypeDefinitions::$tinyint;
//			$data["purchaseInvoiceItem"]->GetColumn("isAdvanceIndicator")->GetDataType()->numeric_precision = 1;
//
//
//			$data["purchaseInvoiceItem"]->GetColumn("objectType")->SetVisible(false);
//			$data["purchaseInvoiceItem"]->GetColumn("objectType")->SetEditable(false);
//
//			$data["purchaseInvoiceItem"]->GetColumn("objectId")->SetVisible(false);
//			$data["purchaseInvoiceItem"]->GetColumn("objectId")->SetEditable(false);
//
//
//			$data["invoiceItemContent"] = null;
//
//			if($isFromDeliveryNote || $isFromOrder || ($isFromInvoice && $data["purchaseInvoice"]->status != "S")) {
//
//				if ($isFromDeliveryNote) {
//					$list = \DI\Model\Entity\Purchase\Delivery\Purchase_Delivery_Note_Item::GetObjectList(array("documentId" => $_GET["objectId"]));
//					$type = "árubeérkezés";
//				} else if ($isFromOrder) {
//					$list = \DI\Model\Entity\Purchase\Order\Purchase_Order_Item::GetObjectList(array("documentId" => $_GET["objectId"]));
//					$type = "beszerzési rendelés";
//				}else if($isFromInvoice && $data["purchaseInvoice"]->status != "S"){
//					$list = Purchase_Invoice_Item::GetObjectList(array("documentId" => $_GET["objectId"]));
//					$type = "számla";
//				}
//
//				if (count($list) > 0) {
//					foreach ($list as $row) {
//						$invoiceItem = TDocumentLine::CloneDocument(new \DI\Model\Entity\Purchase\Invoice\Purchase_Invoice_Item(), $row);
//
//						$row = $invoiceItem->CreateDataSource();
//
//
//						//if($isFromInvoice || $isFromDocumentTemplate){
//						if($isFromInvoice){
//							$row["openQuantity"] = 0;
//						}
//
//
//						$data["invoiceItemContent"][] = $row;
//					}
//				} else {
//					throw new \Exception("Hiba! Érvénytelen " . $type . " tételek!");
//				}
//
//			}else if($isFromProceeding){
//				$type = "jegyzőkönyv";
//				$invoiceItem = new \DI\Model\Entity\Sales\Invoice\Invoice_Item();
//
//				$item = \DI\Model\Entity\StockManagement\Item\Item::Get(array("code" => "JUTALEK"));
//				$invoiceItem->quantity = 1;
//				$invoiceItem->openQuantity = 1;
//				$invoiceItem->quantityUnit = $item->inventoryUnit;
//				$invoiceItem->name = $item->name;
//				$invoiceItem->code = $item->code;
//				$invoiceItem->itemId = $item->id;
//				$invoiceItem->warehouseId = $object->warehouseId;
//
//				//ha van megadva vámtarifaszám, behúzuk az adó adatait
//				if($item->customsTariffCode && strlen($item->customsTariffCode) > 0){
//					$customsTariff = \DI\Model\Entity\Administration\Definitions\Finance\Customs\Customs_Tariff::Get(array("code" => $item->customsTariffCode));
//					$vatGroup = \DI\Model\Entity\Administration\Definitions\Finance\Tax\Vat\Vat_Group\Vat_Group::Get(array("id" => $customsTariff->vatGroupId));
//					$invoiceItem->vatGroupId = $vatGroup->id;
//					$invoiceItem->vatRate = $vatGroup->rate;
//
//				//ha nincs, alapételmezett adócsoportot húzzuk be
//				}else{
//					$vatGroup = \DI\Model\Entity\Administration\Definitions\Finance\Tax\Vat\Vat_Group\Vat_Group::Get(array("isDefault" => 1));
//					$invoiceItem->vatGroupId = $vatGroup->id;
//					$invoiceItem->vatRate = $vatGroup->rate;
//				}
//
//				$row = $invoiceItem->CreateDataSource();
//
//				$row["quantity"] = $invoiceItem->quantity;
////				$row["quantity"] = $invoiceItem["quantity"];
//
//				//ha storno akkor - előjel
//				if(isset($_GET["status"]) && $_GET["status"] == "S"){
//					$row["quantity"] = -($row["quantity"]);
//				}
//
//				$grossPrice = ($object->incoming - $object->outgoing) * ($object->commission / 100);
//				$row["grossPrice"] = $grossPrice;
//				$row["netPrice"] = $row["grossPrice"] / (1 + ($row["vatRate"] / 100));
//
//
//
//				$data["invoiceItemContent"][] = $row;
//
//			}
//
//
//		}catch (\Exception $ex){
//			(new \DI\Model\Exception\CommonException($ex))->Store();
//
//			if(!isset($_GET["id"]) || (isset($_GET["id"]) && $_GET["id"] < 1) || $formmode == FormMode::$add){
//				header("Location: " . parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));
//			}else{
//				header("Location: ".$_SERVER["REQUEST_URI"]."?".http_build_query(array("id" => $_GET["id"])));
//			}
//
//			exit();
//		}
//
//
//		$view = new $UI();
//		$view->SetModel(array($data, "query" => array($qe), "params" => array($params), "formmode" => $formmode, "control" => $args->GetControl()));
//		echo $view->Render();
//
//
//		$args->content = ob_get_contents();
//		ob_end_clean();
////		$this->content = ob_get_contents();
////		ob_end_clean();
////
////		return $this->content;
//
//	});



	//felülírjuk a számla nézetet
//	\UI\View\Pub\Pages\Sales\Invoice\Invoice::On("CreateContent", function($e, $args){
//		$__DAO = \Control\Application::GetInstance()->GetSql();
//		$DI = $args->GetDI();
//		$UI = $args->GetUI();
//
//		ob_start();
//
//
//
//		try {
//
//			//alapértelmezett form mód
//			$formmode = FormMode::$ok;
//
//			//ha nincs érvényes id paraméter megadva az url-ben, akkor hibát dob
//			if(isset($_GET["id"]) && $_GET["id"] < 1){
//				throw new \Exception("Érvénytelen azonosító");
//			}
//
//			$id = isset($_GET['id']) ? $_GET['id'] : '';
//
//			$data = array();
//			$isFromDeliveryNote = false;
//			$isFromOrder = false;
//			$isFromInvoice = false;
//			$isFromWorksheet = false;
//			$isFromDocumentTemplate = false;
//			$isFromPartnerContract = false;
//			$isFromProceeding = false;
//
//			try {
//				$data["invoice"] = $DI::Get(array("id" => $id));
//				$args->SetTitle($args->GetTitle() . " - " . $data["invoice"]->documentNumber . " - " . $data["invoice"]->partnerName);
//
//			} catch (\Exception $ex) {
//				$data["invoice"] = new $DI();
//				$args->SetTitle($args->GetTitle() . " - " . Trans::Get("add_new"));
//				$object = null;
//
//				//ha származtatott számlát hozunk létre
//				if (!empty($_GET["objectType"]) && !empty($_GET["objectId"])) {
//					$type = "";
//					switch ($_GET["objectType"]) {
//						//szállítólevélből
//						case "delivery_note":
//							$type = "szállítólevélből";
//							try {
//								$object = Delivery_Note::Get(array("id" => $_GET["objectId"]));
//								$object->docDate = date("Y-m-d");
//								$object->taxDate = date("Y-m-d");
//								$isFromDeliveryNote = true;
//							} catch (\Exception $ex) {
//								$isFromDeliveryNote = false;
//							}
//
//							break;
//
//						//munkalapból
//						case "worksheet":
//							$type = "munkalapból";
//							try {
//								$object = \DI\Model\Entity\Sales\Worksheet\Worksheet::Get(array("id" => $_GET["objectId"]));
//								$object->docDate = date("Y-m-d");
//								$object->taxDate = date("Y-m-d");
//								$isFromWorksheet = true;
//							} catch (\Exception $ex) {
//								$isFromWorksheet = false;
//							}
//
//							break;
//
//						//rendelésből
//						case "order":
//							$type = "vevői rendelésből";
//							try {
//								$object = Order::Get(array("id" => $_GET["objectId"]));
//								$object->docDate = date("Y-m-d");
//								$object->taxDate = date("Y-m-d");
//								//fizetési határidő
//
//								$isFromOrder = true;
//							} catch (\Exception $ex) {
//								$isFromOrder = false;
//							}
//
//							break;
//
//						case "partner_contract":
//							$type = "partner szerződésből";
//							try{
//								$object = Partner_Contract::Get(array("id" => $_GET["objectId"]));
//								//@TODO: taxdate
//								$object->docDate = date("Y-m-d");
//								//fizetési határidő
//								$object->paymentDate = Partner::SetPaymentDate($object->partnerId, $object->docDate);
//
//
//								//teljesítési határidő
//
//
//								$isFromPartnerContract = true;
//							}catch(\Exception $ex){
//								$isFromPartnerContract = false;
//							}
//							break;
//
//						//számla tervezetből
//						case "document_template":
//							$type = "számla tervezetből";
//							try{
//								$object = Document_Template::Get(array("id" => $_GET["objectId"]));
//								//@TODO: taxdate
//								$object->docDate = date("Y-m-d");
//
//								//fizetési határidő
//								$object->paymentDate = Partner::SetPaymentDate($object->partnerId, $object->docDate);
//
//								//teljesítési határidő
//								//taxDate
//
//								$isFromDocumentTemplate = true;
//							}catch(\Exception $ex){
//								$isFromDocumentTemplate = false;
//							}
//							break;
//
//						//jegyzőkönyvből
//						case "proceeding":
//							$type = "jegyzőkönyvből";
//							try{
//								$object = \DI\Model\Entity\CityMedia\Proceeding\Proceeding::Get(array("id" => $_GET["objectId"]));
//
//								$object->docDate = date("Y-m-d");
//								//fizetési határidő
//								//$object->paymentDate = Partner::SetPaymentDate($object->partnerId, $object->docDate);
//
//
//								//teljesítési határidő
//
//
//
//
//								$isFromProceeding = true;
//							}catch(\Exception $ex){
//								$isFromProceeding = false;
//							}
//							break;
//
//						//számlából
//						case "invoice":
//							try{
//								$data["invoice"] = $DI::Get(array("id" => $_GET["objectId"]));
//								$data["invoice"]->originalDocumentId = $_GET["objectId"];
//								$data["invoice"]->originalDocumentDocumentNumber = $data["invoice"]->documentNumber;
//
//								if(isset($_GET["status"]) && $_GET["status"] == "S"){
//									$data["invoice"]->status = "S";
//									$data["invoice"]->comment = $data["invoice"]->originalDocumentDocumentNumber." számlából készített érvénytelenítő számla.";
//
//								}else{
//									$data["invoice"]->comment = $data["invoice"]->originalDocumentDocumentNumber." számlából készített módosító számla.";
//
//								}
//
//								$data["invoice"]->docDate = date("Y-m-d");
//
//
//								$isFromInvoice = true;
//							}catch(\Exception $ex){
//								$isFromInvoice = false;
//							}
//
//							break;
//					}
//
//					if($object != null){
//						if($object->status == "C"){
//							throw new \Exception("Hiba! Nem lehet lezárt státuszú " . $type . " (" . $object->documentNumber . ") számlát készíteni!");
//						}
//
//
//						$data["invoice"] = $object::CloneDocument($DI, $object);
//						$data["invoice"]->docDate = date("Y-m-d");
//						$data["invoice"]->taxDate = date("Y-m-d");
//						$data["invoice"]->paymentDate = Partner::SetPaymentDate($data["invoice"]->partnerId, $data["invoice"]->docDate);
//						$data["invoice"]->comment = $object->documentNumber." ".$type." készített számla.";
//						//$data["invoice"]->comment = $object->documentNumber." ".$type." készített számla. " . isset($object->comment)?$object->comment:"";
//
//						if($isFromPartnerContract){
//							$partner = \DI\Model\Entity\BusinessPartners\Partner\Partner::Get(array("id" => $object->partnerId));
//
//							$data["invoice"]->currencyId = $partner->currencyId;
//							$data["invoice"]->currencyCode = $partner->currencyCode;
//							$data["invoice"]->payModeId = $partner->payModeId;
//							$data["invoice"]->shipModeId = $partner->shipModeId;
//
//
//
//							//fizetési határidő
//							$data["invoice"]->paymentDate = Partner::SetPaymentDate($object->partnerId, $object->docDate);
//							$data["invoice"]->taxDate = \Control\BusinessPartners\Partner_Contract\Partner_Contract::SetTaxDate($object, $data["invoice"]);
//
//							//Folyamatos teljesítés és időszakra szóló dátumok lekezelése
//							\Control\BusinessPartners\Partner_Contract\Partner_Contract::SetAccountingDeliveryDate($data["invoice"]);
//						}
//
//					}
//				}
//
//
//				if($data["invoice"]->partnerId > 0){
//					//adószámok
//					try{
//						$partner = \DI\Model\Entity\BusinessPartners\Partner\Partner::Get(array("id" => $data["invoice"]->partnerId));
//
//						if($partner->isTaxPayer){
//							if($partner->customerTaxNumber_taxpayerId > 0 && $partner->customerTaxNumber_vatCode > 0 && $partner->customerTaxNumber_countyCode > 0){
//								$data["invoice"]->customerTaxNumber_taxpayerId = $partner->customerTaxNumber_taxpayerId;
//								$data["invoice"]->customerTaxNumber_vatCode = $partner->customerTaxNumber_vatCode;
//								$data["invoice"]->customerTaxNumber_countyCode = $partner->customerTaxNumber_countyCode;
//							}
//						}
//
//						if($partner->isGroupTaxPayer){
//							if($partner->groupMemberTaxNumber_taxpayerId > 0 && $partner->groupMemberTaxNumber_vatCode > 0 && $partner->groupMemberTaxNumber_countyCode > 0){
//								$data["invoice"]->customerGroupMemberTaxNumber_taxpayerId = $partner->groupMemberTaxNumber_taxpayerId;
//								$data["invoice"]->customerGroupMemberTaxNumber_vatCode = $partner->groupMemberTaxNumber_vatCode;
//								$data["invoice"]->customerGroupMemberTaxNumber_countyCode = $partner->groupMemberTaxNumber_countyCode;
//							}
//						}
//
//
//
//						if($isFromProceeding){
//							$data["invoice"]->partnerName = $partner->name;
//							$data["invoice"]->partnerCode = $partner->code;
//
//
//							$data["invoice"]->currencyId = $partner->currencyId;
//							$data["invoice"]->currencyCode = $partner->currencyCode;
//
//							$data["invoice"]->payModeId = $partner->payModeId;
//							$data["invoice"]->shipModeId = $partner->shipModeId;
//
//							try{
//								$bankAccount = \DI\Model\Entity\Administration\Definitions\Finance\Bank\Bank_Account::Get(array("isDefault" => 1, "partnerId" => $partner->id));
//								$data["invoice"]->customerBankAccountNumber = $bankAccount->code;
//							}catch(\Exception $ex){
//								$data["invoice"]->customerBankAccountNumber = null;
//							}
//
//
//							try{
//								$billPartnerAddress = \DI\Model\Entity\BusinessPartners\Partner\Partner_Address::Get(array("partnerId" => $partner->id, "addressType" => "B", "isDefault" => 1));
//								$data["invoice"]->billCountryId = $billPartnerAddress->countryId;
//								$data["invoice"]->billCountryCode = \DI\Model\Entity\Administration\Definitions\Localization\Country\Country::Get(array("id" => $billPartnerAddress->countryId))->code;
//								$data["invoice"]->billRegionId = $billPartnerAddress->regionId;
//								$data["invoice"]->billPostalCode = $billPartnerAddress->postalCode;
//								$data["invoice"]->billCity = $billPartnerAddress->city;
//								$data["invoice"]->billStreetName = $billPartnerAddress->streetName;
//								$data["invoice"]->billPublicPlaceCategory = $billPartnerAddress->publicPlaceCategory;
//								$data["invoice"]->billNumber = $billPartnerAddress->number;
//								$data["invoice"]->billBuilding = $billPartnerAddress->building;
//								$data["invoice"]->billStaircase = $billPartnerAddress->staircase;
//								$data["invoice"]->billFloor = $billPartnerAddress->floor;
//								$data["invoice"]->billDoor = $billPartnerAddress->door;
//								$data["invoice"]->billLotNumber = $billPartnerAddress->lotNumber;
//
//							}catch(\Exception $ex){
//								$billPartnerAddress = null;
//							}
//
//
//							try{
//								$shipPartnerAddress = \DI\Model\Entity\BusinessPartners\Partner\Partner_Address::Get(array("partnerId" => $partner->id, "addressType" => "S", "isDefault" => 1));
//								$data["invoice"]->shipCountryId = $shipPartnerAddress->countryId;
//								$data["invoice"]->shipCountryCode = \DI\Model\Entity\Administration\Definitions\Localization\Country\Country::Get(array("id" => $shipPartnerAddress->countryId))->code;
//								$data["invoice"]->shipRegionId = $shipPartnerAddress->regionId;
//								$data["invoice"]->shipPostalCode = $shipPartnerAddress->postalCode;
//								$data["invoice"]->shipCity = $shipPartnerAddress->city;
//								$data["invoice"]->shipStreetName = $shipPartnerAddress->streetName;
//								$data["invoice"]->shipPublicPlaceCategory = $shipPartnerAddress->publicPlaceCategory;
//								$data["invoice"]->shipNumber = $shipPartnerAddress->number;
//								$data["invoice"]->shipBuilding = $shipPartnerAddress->building;
//								$data["invoice"]->shipStaircase = $shipPartnerAddress->staircase;
//								$data["invoice"]->shipFloor = $shipPartnerAddress->floor;
//								$data["invoice"]->shipDoor = $shipPartnerAddress->door;
//								$data["invoice"]->shipLotNumber = $shipPartnerAddress->lotNumber;
//
//							}catch(\Exception $ex){
//								$shipPartnerAddress = null;
//							}
//
//
//						}
//
//					}catch(\Exception $ex){
//						//do nothing
//					}
//
//
//
//
//				}
//
//
//
//				$formmode = FormMode::$add;
//			}
//
//
//
//
//			$dt_creator = new DataTableCreator($__DAO);
//
//
//			$qe = new QE\Query();
//			$t1 = $qe->AddFromTable(DB_PREFIX . "invoice_item", "T1");
//
//			//0
//			$field = $qe->AddField("id");
//			$field->SetReference($t1);
//			//1
//			$field = $qe->AddField("itemId");
//			$field->SetReference($t1);
//			//2
//			$field = $qe->AddField("code");
//			$field->SetReference($t1);
//			//3
//			$field = $qe->AddField("name");
//			$field->SetReference($t1);
//			//4
//			$field = $qe->AddField("quantity");
//			$field->SetReference($t1);
//			//5
//			$field = $qe->AddField("quantityUnit");
//			$field->SetReference($t1);
//			//6
//			$field = $qe->AddField("netPrice");
//			$field->SetReference($t1);
//			//7
//			$field = $qe->AddField("vatGroupId");
//			$field->SetReference($t1);
//			//8
//			$field = $qe->AddField("vatRate");
//			$field->SetReference($t1);
//			//9
//			$field = $qe->AddField("grossPrice");
//			$field->SetReference($t1);
//			//@TODO:
//			//nettó érték
//			//bruttó érték
//			//@TODO: end
//			//10
//			$field = $qe->AddField("discount");
//			$field->SetReference($t1);
//			//11
//			$field = $qe->AddField("discountDescription");
//			$field->SetReference($t1);
//			//12
//			$field = $qe->AddField("warehouseId");
//			$field->SetReference($t1);
//			//13
//			$field = $qe->AddField("deliveryDate");
//			$field->SetReference($t1);
//			//14
//			$field = $qe->AddField("commitedDate");
//			$field->SetReference($t1);
//			//15
//			$field = $qe->AddField("openQuantity");
//			$field->SetReference($t1);
//			//16
//			$field = $qe->AddField("isAdvanceIndicator");
//			$field->SetReference($t1);
//			//17
//			$field = $qe->AddField("openAdvanceAmount");
//			$field->SetReference($t1);
//			//18
//			$field = $qe->AddField("advanceInvoiceItemId");
//			$field->SetReference($t1);
//			//19
//			$field = $qe->AddField("projectId");
//			$field->SetReference($t1);
//			//20
//			$field = $qe->AddField("objectType");
//			$field->SetReference($t1);
//			//21
//			$field = $qe->AddField("objectId");
//			$field->SetReference($t1);
//
//
//
//			$cond = $qe->AddWhere();
//			$field = new QE\Field("documentId");
//			$field->SetReference($t1);
//			$cond->SetLeftField($field);
//
//			$orderBy = $qe->AddOrderBy("lineNumber");
//			$orderBy->SetType("ASC");
//
//
//			if($isFromInvoice){
//				$params = array($_GET["objectId"]);
//			}else {
//				$params = array($id);
//			}
//
//
//			$data["invoiceItem"] = $dt_creator->FromDataSource($qe, $params);
//			$data["invoiceItem"]->SetRows(null);
//
//			$data["invoiceItem"]->GetColumn("id")->SetVisible(false);
//			$data["invoiceItem"]->GetColumn("id")->SetEditable(false);
//
//
//			$data["invoiceItem"]->GetColumn("quantity")->GetDataType()->data_type = DataTypeDefinitions::$decimal;
//			$data["invoiceItem"]->GetColumn("quantity")->GetDataType()->numeric_precision = 19;
//			$data["invoiceItem"]->GetColumn("quantity")->GetDataType()->numeric_scale = 8;
//
//			$data["invoiceItem"]->GetColumn("netPrice")->GetDataType()->data_type = DataTypeDefinitions::$decimal;
//			$data["invoiceItem"]->GetColumn("netPrice")->GetDataType()->numeric_precision = 19;
//			$data["invoiceItem"]->GetColumn("netPrice")->GetDataType()->numeric_scale = 8;
//
//			$data["invoiceItem"]->GetColumn("vatRate")->GetDataType()->data_type = DataTypeDefinitions::$decimal;
//			$data["invoiceItem"]->GetColumn("vatRate")->GetDataType()->numeric_precision = 19;
//			$data["invoiceItem"]->GetColumn("vatRate")->GetDataType()->numeric_scale = 8;
//
//			$data["invoiceItem"]->GetColumn("grossPrice")->GetDataType()->data_type = DataTypeDefinitions::$decimal;
//			$data["invoiceItem"]->GetColumn("grossPrice")->GetDataType()->numeric_precision = 19;
//			$data["invoiceItem"]->GetColumn("grossPrice")->GetDataType()->numeric_scale = 8;
//
//			$data["invoiceItem"]->GetColumn("discount")->GetDataType()->data_type = DataTypeDefinitions::$decimal;
//			$data["invoiceItem"]->GetColumn("discount")->GetDataType()->numeric_precision = 19;
//			$data["invoiceItem"]->GetColumn("discount")->GetDataType()->numeric_scale = 8;
//
//			$data["invoiceItem"]->GetColumn("deliveryDate")->GetDataType()->data_type = DataTypeDefinitions::$date;
//			$data["invoiceItem"]->GetColumn("commitedDate")->GetDataType()->data_type = DataTypeDefinitions::$date;
//
//			$data["invoiceItem"]->GetColumn("openQuantity")->GetDataType()->data_type = DataTypeDefinitions::$decimal;
//			$data["invoiceItem"]->GetColumn("openQuantity")->GetDataType()->numeric_precision = 19;
//			$data["invoiceItem"]->GetColumn("openQuantity")->GetDataType()->numeric_scale = 8;
//			$data["invoiceItem"]->GetColumn("openQuantity")->SetEditable(false);
//
//			$data["invoiceItem"]->GetColumn("isAdvanceIndicator")->GetDataType()->data_type = DataTypeDefinitions::$tinyint;
//			$data["invoiceItem"]->GetColumn("isAdvanceIndicator")->GetDataType()->numeric_precision = 1;
//
//			$data["invoiceItem"]->GetColumn("openAdvanceAmount")->GetDataType()->data_type = DataTypeDefinitions::$decimal;
//			$data["invoiceItem"]->GetColumn("openAdvanceAmount")->GetDataType()->numeric_precision = 19;
//			$data["invoiceItem"]->GetColumn("openAdvanceAmount")->GetDataType()->numeric_scale = 8;
//			$data["invoiceItem"]->GetColumn("openAdvanceAmount")->SetEditable(false);
//
//			$data["invoiceItem"]->GetColumn("advanceInvoiceItemId")->GetDataType()->data_type = DataTypeDefinitions::$text;
//			$data["invoiceItem"]->GetColumn("advanceInvoiceItemId")->SetVisible(false);
//			$data["invoiceItem"]->GetColumn("advanceInvoiceItemId")->SetEditable(false);
//
//			$data["invoiceItem"]->GetColumn("objectType")->SetVisible(false);
//			$data["invoiceItem"]->GetColumn("objectType")->SetEditable(false);
//
//			$data["invoiceItem"]->GetColumn("objectId")->SetVisible(false);
//			$data["invoiceItem"]->GetColumn("objectId")->SetEditable(false);
//
//
//
//			$data["invoiceItemContent"] = null;
//
//			if($isFromDeliveryNote || $isFromOrder || $isFromWorksheet || $isFromDocumentTemplate || $isFromPartnerContract || ($isFromInvoice)) {
//
//				if ($isFromDeliveryNote) {
//					$list = \DI\Model\Entity\Sales\Delivery\Delivery_Note_Item::GetObjectList(array("documentId" => $_GET["objectId"]));
//					$type = "szállítólevél";
//				} else if ($isFromOrder) {
//					$list = \DI\Model\Entity\Sales\Order\Order_Item::GetObjectList(array("documentId" => $_GET["objectId"]));
//					$type = "rendelés";
//				}else if($isFromWorksheet){
//					$list = \DI\Model\Entity\Sales\Worksheet\Worksheet_Item::GetObjectList(array("documentId" => $_GET["objectId"]));
//					$type = "munkalap";
//				}else if($isFromDocumentTemplate){
//					$list = \DI\Model\Entity\Administration\Definitions\Template\Document\Document_Item_Template::GetObjectList(array("documentId" => $_GET["objectId"]));
//					$type = "számla tervezet";
//				}else if($isFromInvoice){
//					$list = Invoice_Item::GetObjectList(array("documentId" => $_GET["objectId"]));
//					$type = "számla";
//				}else if($isFromPartnerContract){
//					$list = Partner_Contract_Item::GetObjectList(array("partnerContractId" => $_GET["objectId"]));
//					$type = "partner szerződés";
//				}
//
//				if (count($list) > 0) {
//					foreach ($list as $row) {
//						$invoiceItem = TDocumentLine::CloneDocument(new \DI\Model\Entity\Sales\Invoice\Invoice_Item(), $row);
//						if(!$isFromDocumentTemplate && !$isFromPartnerContract){
//							$invoiceItem->quantity = $invoiceItem->openQuantity;
//						}
//
//						$row = $invoiceItem->CreateDataSource();
//
//						if($isFromPartnerContract){
//							try{
//								$item = \DI\Model\Entity\StockManagement\Item\Item::Get(array("id" => $invoiceItem->itemId));
//								$row["warehouseId"] = $item->defaultWarehouseId;
//							}catch(\Exception $ex){
//								$row["warehouseId"] = null;
//							}
//						}
//
//
//						if($isFromDocumentTemplate || $isFromPartnerContract){
//							$row["quantity"] = $invoiceItem->quantity;
//						}
//
//						//ha storno akkor - előjel
//						if(isset($_GET["status"]) && $_GET["status"] == "S"){
//							$row["quantity"] = -($row["quantity"]);
//						}
//
//						if($isFromInvoice || $isFromDocumentTemplate || $isFromPartnerContract){
//							$row["openQuantity"] = 0;
//						}
//						$data["invoiceItemContent"][] = $row;
//
//
//
//					}
//				} else {
//					throw new \Exception("Hiba! Érvénytelen " . $type . " tételek!");
//				}
//
//			}else if($isFromProceeding){
//				$type = "jegyzőkönyv";
//				$invoiceItem = new \DI\Model\Entity\Sales\Invoice\Invoice_Item();
//
//				$item = \DI\Model\Entity\StockManagement\Item\Item::Get(array("code" => "JUTALEK"));
//				$invoiceItem->quantity = 1;
//				$invoiceItem->openQuantity = 1;
//				$invoiceItem->quantityUnit = $item->inventoryUnit;
//				$invoiceItem->name = $item->name;
//				$invoiceItem->code = $item->code;
//				$invoiceItem->itemId = $item->id;
//				$invoiceItem->warehouseId = $object->warehouseId;
//
//				//ha van megadva vámtarifaszám, behúzuk az adó adatait
//				if($item->customsTariffCode && strlen($item->customsTariffCode) > 0){
//					$customsTariff = \DI\Model\Entity\Administration\Definitions\Finance\Customs\Customs_Tariff::Get(array("code" => $item->customsTariffCode));
//					$vatGroup = \DI\Model\Entity\Administration\Definitions\Finance\Tax\Vat\Vat_Group\Vat_Group::Get(array("id" => $customsTariff->vatGroupId));
//					$invoiceItem->vatGroupId = $vatGroup->id;
//					$invoiceItem->vatRate = $vatGroup->rate;
//
//                //ha nincs, alapételmezett adócsoportot húzzuk be
//                }else{
//					$vatGroup = \DI\Model\Entity\Administration\Definitions\Finance\Tax\Vat\Vat_Group\Vat_Group::Get(array("isDefault" => 1));
//					$invoiceItem->vatGroupId = $vatGroup->id;
//					$invoiceItem->vatRate = $vatGroup->rate;
//                }
//
//				$row = $invoiceItem->CreateDataSource();
//
//				$row["quantity"] = $invoiceItem->quantity;
////				$row["quantity"] = $invoiceItem["quantity"];
//
//				//ha storno akkor - előjel
//				if(isset($_GET["status"]) && $_GET["status"] == "S"){
//					$row["quantity"] = -($row["quantity"]);
//				}
//
//				$grossPrice = ($object->incoming - $object->outgoing) * ($object->commission / 100);
//				$row["grossPrice"] = $grossPrice;
//				$row["netPrice"] = $row["grossPrice"] / (1 + ($row["vatRate"] / 100));
//
//
//
//				$data["invoiceItemContent"][] = $row;
//
//			}
//
//
//
//			$qe2 = new QE\Query();
//			$t1 = $qe2->AddFromTable(DB_PREFIX."invoice_additional_data", "T1");
//
//			$field = $qe2->AddField("id");
//			$field->SetReference($t1);
//			$field = $qe2->AddField("name");
//			$field->SetReference($t1);
//			$field = $qe2->AddField("description");
//			$field->SetReference($t1);
//			$field = $qe2->AddField("value");
//			$field->SetReference($t1);
//
//			$cond = $qe2->AddWhere();
//			$field = new QE\Field("invoiceId");
//			$field->SetReference($t1);
//			$cond->SetLeftField($field);
//
//
//			if($isFromInvoice){
//				$params2 = array($_GET["originalDocumentId"]);
//			}else {
//				$params2 = array($id);
//			}
//
//
//			$data["invoiceAdditionalData"] = $dt_creator->FromDataSource($qe2, $params2);
//			$data["invoiceAdditionalData"]->SetRows(null);
//
//			$data["invoiceAdditionalData"]->GetColumn("id")->SetVisible(false);
//			$data["invoiceAdditionalData"]->GetColumn("id")->SetEditable(false);
//
//
//
//			$qe3 = new QE\Query();
//			$t1 = $qe3->AddFromTable(DB_PREFIX."invoice_item_additional_data", "T1");
//
//			$field = $qe3->AddField("id");
//			$field->SetReference($t1);
//			$field = $qe3->AddField("name");
//			$field->SetReference($t1);
//			$field = $qe3->AddField("description");
//			$field->SetReference($t1);
//			$field = $qe3->AddField("value");
//			$field->SetReference($t1);
//
//			$cond = $qe3->AddWhere();
//			$field = new QE\Field("invoiceId");
//			$field->SetReference($t1);
//			$cond->SetLeftField($field);
//
//			if($isFromInvoice){
//				$params3 = array($_GET["originalDocumentId"]);
//			}else {
//				$params3 = array($id);
//			}
//
//
//			$data["invoiceItemAdditionalData"] = $dt_creator->FromDataSource($qe3, $params3);
//			$data["invoiceItemAdditionalData"]->SetRows(null);
//
//			$data["invoiceItemAdditionalData"]->GetColumn("id")->SetVisible(false);
//			$data["invoiceItemAdditionalData"]->GetColumn("id")->SetEditable(false);
//
//
//			$qe4 = new QE\Query();
//			$t1 = $qe4->AddFromTable(DB_PREFIX."invoice_item_product_code");
//
//			$field = $qe4->AddField("id");
//			$field->SetReference($t1);
//			$field = $qe4->AddField("category");
//			$field->SetReference($t1);
//			$field = $qe4->AddField("code");
//			$field->SetReference($t1);
//			$field = $qe4->AddField("codeOwn");
//			$field->SetReference($t1);
//
//			$cond = $qe4->AddWhere();
//			$field = new QE\Field("invoiceId");
//			$field->SetReference($t1);
//			$cond->SetLeftField($field);
//
//			if($isFromInvoice){
//				$params4 = array($_GET["originalDocumentId"]);
//			}else {
//				$params4 = array($id);
//			}
//
//			$data["invoiceItemProductCode"] = $dt_creator->FromDataSource($qe4, $params4);
//			$data["invoiceItemProductCode"]->SetRows(null);
//
//			$data["invoiceItemProductCode"]->GetColumn("id")->SetVisible(false);
//			$data["invoiceItemProductCode"]->GetColumn("id")->SetEditable(false);
//
//
//			$qe5 = new QE\Query();
//			$t1 = $qe5->AddFromTable(DB_PREFIX."invoice_item_product_fee");
//
//			$field = $qe5->AddField("id");
//			$field->SetReference($t1);
//			$field = $qe5->AddField("invoiceItemId");
//			$field->SetReference($t1);
//			$field = $qe5->AddField("category");
//			$field->SetReference($t1);
//			$field = $qe5->AddField("code");
//			$field->SetReference($t1);
//			$field = $qe5->AddField("codeOwn");
//			$field->SetReference($t1);
//			$field = $qe5->AddField("quantity");
//			$field->SetReference($t1);
//			$field = $qe5->AddField("quantity", "baseQuantity");
//			$field->SetReference($t1);
//			$field = $qe5->AddField("measuringUnit");
//			$field->SetReference($t1);
//			$field = $qe5->AddField("rate");
//			$field->SetReference($t1);
//			$field = $qe5->AddField("amount");
//			$field->SetReference($t1);
//
//			$cond = $qe5->AddWhere();
//			$field = new QE\Field("invoiceId");
//			$field->SetReference($t1);
//			$cond->SetLeftField($field);
//
//			if($isFromInvoice){
//				$params5 = array($_GET["originalDocumentId"]);
//			}else {
//				$params5 = array($id);
//			}
//
//			$data["invoiceItemProductFee"] = $dt_creator->FromDataSource($qe5, $params5);
//			$data["invoiceItemProductFee"]->SetRows(null);
//
//			$data["invoiceItemProductFee"]->GetColumn("id")->SetVisible(false);
//			$data["invoiceItemProductFee"]->GetColumn("id")->SetEditable(false);
//
//			$data["invoiceItemProductFee"]->GetColumn("invoiceItemId")->SetVisible(false);
//			$data["invoiceItemProductFee"]->GetColumn("invoiceItemId")->SetEditable(false);
//
//			$data["invoiceItemProductFee"]->GetColumn("baseQuantity")->SetVisible(false);
//			$data["invoiceItemProductFee"]->GetColumn("baseQuantity")->SetEditable(false);
//
//			$data["invoiceItemProductFee"]->GetColumn("quantity")->GetDataType()->data_type = DataTypeDefinitions::$decimal;
//			$data["invoiceItemProductFee"]->GetColumn("quantity")->GetDataType()->numeric_precision = 19;
//			$data["invoiceItemProductFee"]->GetColumn("quantity")->GetDataType()->numeric_scale = 8;
//
//			$data["invoiceItemProductFee"]->GetColumn("baseQuantity")->GetDataType()->data_type = DataTypeDefinitions::$decimal;
//			$data["invoiceItemProductFee"]->GetColumn("baseQuantity")->GetDataType()->numeric_precision = 19;
//			$data["invoiceItemProductFee"]->GetColumn("baseQuantity")->GetDataType()->numeric_scale = 8;
//
//			$data["invoiceItemProductFee"]->GetColumn("amount")->GetDataType()->data_type = DataTypeDefinitions::$decimal;
//			$data["invoiceItemProductFee"]->GetColumn("amount")->GetDataType()->numeric_precision = 19;
//			$data["invoiceItemProductFee"]->GetColumn("amount")->GetDataType()->numeric_scale = 8;
//
//
//
//
//
//		}catch (\Exception $ex){
//			(new \DI\Model\Exception\CommonException($ex))->Store();
//
//			if(!isset($_GET["id"]) || (isset($_GET["id"]) && $_GET["id"] < 1) || $formmode == FormMode::$add){
//				header("Location: " . parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));
//			}else{
//				header("Location: ".$_SERVER["REQUEST_URI"]."?".http_build_query(array("id" => $_GET["id"])));
//			}
//
//			exit();
//		}
//
//
//
//		$view = new $UI();
//		$view->SetModel(array($data, "query" => array($qe, $qe2, $qe3, $qe4, $qe5), "params" => array($params, $params2, $params3, $params4, $params5), "formmode" => $formmode, "control" => $args->GetControl()));
//		echo $view->Render();
//
//		$args->content = ob_get_contents();
//		ob_end_clean();
//
//
//	});


?>