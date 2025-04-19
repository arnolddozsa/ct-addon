<?php

namespace UI\View\Html\CityMedia\Proceeding;

use Control\Administration\Definitions\Localization\Translate\Translate as Trans;
use Control\Administration\Definitions\DocumentNumber\Document_Number as Document_Number;
use DI\Model\Entity\FormMode;
use UI\Html\Button\CancelButton;
use UI\Html\Button\SubmitButton;
use DI\Model\Entity\Administration\SystemSettings\TableDefinitions\Field_Valid_Values;
use UI\Html\Button;
use UI\Html\InputDate;
use UI\Html\InputText;
use UI\Html\Signature;

/**
 * Karbantartás nézet osztály.
 */
class Proceeding implements \UI\View\IView
{

    private $model;

    /**
     * @inheritDoc
     */
    public function CreateContent()
    {
        $proceeding = $this->GetModel()[0]["proceeding"];
        $proceedingItem = $this->GetModel()[0]["proceedingItem"];

        $qe = $this->GetModel()["query"];
        $params = $this->GetModel()["params"];


        //control elérési útvonala
        $control = str_replace("\\", "/", $this->GetModel()["control"]);

        $formmode = $this->GetModel()["formmode"];
        FormMode::Check($formmode);

        ob_start();


        //grid
        $grid = new \UI\View\Html\Grid();
        $grid->id = $_GET["page"] . "_proceedingItem";
        $grid->SetModel($proceedingItem);


        //raktár cfl
        $warehouseId_cfl = new \UI\Html\InputCfl2();
        $warehouseId_cfl->name = "warehouseId";
        $warehouseId_cfl->data["class"] = "\DI\Model\Entity\Administration\Definitions\StockManagement\Warehouse\Warehouse";
        $warehouseId_cfl->data["cfl_column"] = "id";

        $grid->SetColumn("warehouseId", $warehouseId_cfl);

        //raktár cfl
        $vatGroupId_cfl = new \UI\Html\InputCfl2();
        $vatGroupId_cfl->name = "vatGroupId";
        $vatGroupId_cfl->data["class"] = "\DI\Model\Entity\Administration\Definitions\Finance\Tax\Vat\Vat_Group\Vat_Group";
        $vatGroupId_cfl->data["cfl_column"] = "id";

        $grid->SetColumn("vatGroupId", $vatGroupId_cfl);

        $number = new \UI\Html\InputNumber();
        $number->name = "quantity";
        $number->step = 1;
        $number->precision = 0;

        $grid->SetColumn("Eladott_db", $number);

        $number = new \UI\Html\InputNumber();
        $number->name = "uploadQuantity";
        $number->precision = 0;
        $grid->SetColumn("uploadQuantity", $number);

        $number = new \UI\Html\InputNumber();
        $number->name = "netPrice";
        $number->precision = 2;
        $grid->SetColumn("netPrice", $number);

        $number = new \UI\Html\InputNumber();
        $number->name = "vatRate";
        $number->readonly = true;
        $number->precision = 2;
        $grid->SetColumn("vatRate", $number);

        $number = new \UI\Html\InputNumber();
        $number->name = "outgoing";
        $number->readonly = true;
        $number->precision = 2;
        $grid->SetColumn("Érme_összesen", $number);

        $number = new \UI\Html\InputNumber();
        $number->name = "incoming";
        $number->precision = 2;
        $grid->SetColumn("Megszámolt", $number);

        $number = new \UI\Html\InputNumber();
        $number->name = "commission";
        $number->precision = 2;
        $grid->SetColumn("commission", $number);

        $number = new \UI\Html\InputNumber();
        $number->name = "netAmount";
        $number->readonly = true;
        $number->precision = 2;
        $grid->SetColumn("netAmount", $number);

        $number = new \UI\Html\InputNumber();
        $number->name = "grossAmount";
        $number->readonly = true;
        $number->precision = 2;
        $grid->SetColumn("grossAmount", $number);

        $number = new \UI\Html\InputText();
        $number->name = "contractType";
        $number->visible = false;
        $grid->SetColumn("contractType", $number);

        $number = new \UI\Html\InputText();
        $number->name = "objectType";
        $number->visible = false;
        $grid->SetColumn("objectType", $number);

        $number = new \UI\Html\InputNumber();
        $number->name = "objectId";
        $number->visible = false;
        $grid->SetColumn("objectId", $number);




?>

        <script>
            $(function() {

                var controlPath = "<?php echo $control; ?>";
                var formmode = "<?php echo $formmode; ?>";

                var form = $(window.elementID + "_form1");


                var href = new URL(window.location.href);
                var id = href.searchParams.get("id");

                //tábla létrehozás
                var table1 = $(window.elementID + "_proceedingItem").datatable({
                    ajax: "api/Control/DataTable/Load",
                    dataSource: {
                        Query: <?php echo json_encode($qe); ?>,
                        Columns: <?php echo json_encode($grid->GetColumns()); ?>,
                        Parameters: <?php echo json_encode($params); ?>
                    },
                    pagination: {
                        recordsPerPage: 15,
                        page: 1
                    }
                });


                table1.on("pageLoad", function() {

                    //szerkesztjük a táblát
                    //végigmegyünk a sorokon
                    $(table1).find("tbody tr").each(function(i, one) {
                        //végigmegyünk a mezőkön
                        $(one).find("td input, td select").each(function(j, input) {

                            //kötelező mezők beállítása
                            if (input.name == "warehouseId") {
                                $(input).attr("ng-required", "displayCondition");

                            }
                        });
                    });

                });

                $(document).on("cflSelect", "td input[name=warehouseId]", async function(e, row) {
                    var input = $(e.target);
                    var tableRow = input.closest("tr");
                    $(tableRow).find("td input[name=warehouseName]").val(row.name);
                    $(tableRow).find("td input[name=warehouseCode]").val(row.code);


                    var type = $(`${window.elementID}_type`).val();
                    var partnerId = $(`${window.elementID}_partnerId`).val();

                    if ((type == 2) && row.id > 0 && partnerId > 0) {
                        let res = await fetch("api/Control/CityMedia/Partner/Partner_Contract/GetPartnerContractInfo", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json"
                            },
                            body: JSON.stringify({
                                partnerId: partnerId,
                                warehouseId: row.id
                            })
                        });

                        if (res.ok) {
                            res = await res.json();

                            //Fix bérleti díjas szerződés
                            if(res.contractType == "3"){

                                input.closest("tr").find("input[name=incoming]").val(0);
                                input.closest("tr").find("input[name=incoming]").prop("disabled", true);
                                input.closest("tr").find("input[name=uploadedQuantity]").val(0);
                                input.closest("tr").find("input[name=uploadedQuantity]").prop("disabled", true);
                                input.closest("tr").find("input[name=quantity]").val(1);
                                input.closest("tr").find("input[name=quantity]").prop("readonly", true);

                                input.closest("tr").find("input[name=commission]").val(res.commission);
                                input.closest("tr").find("input[name=netPrice]").val(res.netPrice);
                                input.closest("tr").find("input[name=netPrice]").prop("readonly", true);
                                input.closest("tr").find("input[name=netPrice]").trigger("change");

                            } else{

                                input.closest("tr").find("input[name=incoming]").val(0);
                                input.closest("tr").find("input[name=incoming]").prop("disabled", false);
                                input.closest("tr").find("input[name=uploadedQuantity]").val(0);
                                input.closest("tr").find("input[name=uploadedQuantity]").prop("disabled", false);
                                input.closest("tr").find("input[name=quantity]").val(0);
                                input.closest("tr").find("input[name=quantity]").prop("readonly", false);


                                input.closest("tr").find("input[name=commission]").val(res.commission);
                                input.closest("tr").find("input[name=netPrice]").val(res.netPrice);
                                input.closest("tr").find("input[name=netPrice]").prop("readonly", false);
                                input.closest("tr").find("input[name=netPrice]").trigger("change");

                                
                            }

                            input.closest("tr").find("input[name=contractType]").val(res.contractType);
                            input.closest("tr").find("input[name=objectType]").val(res.objectType);
                            input.closest("tr").find("input[name=objectId]").val(res.objectId);

                            if (row.customsTariffCode && row.customsTariffCode.length > 0) {
                                    $.post("api/DI/Model/Entity/Administration/Definitions/Finance/Customs/Customs_Tariff/Get", {
                                        properties: {
                                            code: row.customsTariffCode
                                        }
                                    }, function(data) {
                                        $(tableRow).find("td input[name='vatGroupId']").val(data.vatGroupId);
                                        var cfl = $(tableRow).find("td input[name='vatGroupId']").cfl2();
                                        cfl.data("nubes-cfl2").setTitles();
                                    });
                                    //ha nincs, alapételmezett adócsoportot húzzuk be
                                } else {
                                    $.post("api/DI/Model/Entity/Administration/Definitions/Finance/Tax/Vat/Vat_Group/Vat_Group/Get", {
                                        properties: {
                                            isDefault: 1
                                        }
                                    }, function(data) {
                                        $(tableRow).find("td input[name='vatGroupId']").val(data.id);
                                        var cfl = $(tableRow).find("td input[name='vatGroupId']").cfl2();
                                        cfl.data("nubes-cfl2").setTitles();
                                    });
                                }

                        } else {
                            res = await res.json();
                            console.log(res);

                        }

                    }

                });

                //cikk cfl select eseményére cserélje a cikk nevét és kódját a megfelelőre
                $(document).on("cflSelect", "td input[name='vatGroupId']", function(e, row) {
                    var input = $(e.target);
                    var tableRow = input.closest("tr");

                    $(tableRow).find("td input[name='vatRate']").val(row.rate);
                });

                $(document).on("cflNotSelect", "td input[name=warehouseId]", function(e, row) {
                    var input = $(e.target);
                    var tableRow = input.closest("tr");
                    $(tableRow).find("td input[name=warehouseName]").val(null);
                    $(tableRow).find("td input[name=warehouseCode]").val(null);
                });



                //bizonylat választás
                $(document).on("change", `${window.elementID}_objectType`, function(e) {
                    e.preventDefault();

                    var objectId = $(`${window.elementID}_objectId`);


                    var url = "";

                    switch ($(this).val()) {
                        case "invoice":
                            objectId.data("class", "\\DI\\Model\\Entity\\Sales\\Invoice\\Invoice");
                            url = "/Sales/Invoice/Invoice/";
                            break;

                        case "purchase_invoice":
                            objectId.data("class", "\\DI\\Model\\Entity\\Purchase\\Invoice\\Purchase_Invoice");
                            url = "/Purchase/Invoice/Purchase_Invoice/";
                            break;

                        case "inventory_entry":
                            objectId.data("class", "\\DI\\Model\\Entity\\StockManagement\\StoreTransactions\\Inventory_Entry\\Inventory_Entry");
                            url = "/StockManagement/StoreTransactions/Inventory_Entry/Inventory_Entry/";
                            break;

                        case "inventory_exit":
                            objectId.data("class", "\\DI\\Model\\Entity\\StockManagement\\StoreTransactions\\Inventory_Exit\\Inventory_Exit");
                            url = "/StockManagement/StoreTransactions/Inventory_Exit/Inventory_Exit/";
                            break;


                        default:
                            objectId.removeData("class");
                            objectId.closest(".input-group").find(".input-group-append").hide();
                            break;

                    }

                    if (objectId.data("class").length > 0) {
                        objectId.closest(".input-group").find(".input-group-append").show();
                    }

                    var link = `<span class="link">
			<a class="link" href="${url}" name="" target="_blank" value="${url}" title="<?php echo Trans::Get('view'); ?>" style=""><i class="icon-external-link"></i></a>
		</span>`;

                    objectId.closest(".input-group").find(".input-group-append span.input-group-text:eq(1)").html(link);

                    // objectId.cfl2();
                    // $(objectId).data("nubes-cfl2").setTitles();


                    $(objectId).next().data("da-atc").dynamicList.data("da-dynamicList").option("dataSource", {
                        path: objectId.data("class")
                    });

                });


                $(`${window.elementID}_type`).on("change", function() {
                    HandleType(this.value);

                    var partnerId = $(`${window.elementID}_partnerId`).val();
                    //var warehouseId = $(`${window.elementID}_warehouseId`).val();
                    var warehouseId = $(document).find("tbody tr input[name=warehouseId]").first().val();

                    if (this.value == 2 && partnerId > 0 && warehouseId > 0) {
                        $.post("api/Control/CityMedia/Partner/Partner_Contract/GetPartnerContract", {
                            partnerId: partnerId,
                            warehouseId: warehouseId
                        }, function(data) {
                            // if(data != null && data.commission > 0) {
                            //     $(`${window.elementID}_commission`).val(data.commission);
                            //     $(`${window.elementID}_commission`).trigger("focusout");
                            // }
                        });
                    }
                });


                $(`${window.elementID}_partnerId`).on("cflSelect", function(e, row) {
                    var type = $(`${window.elementID}_type`).val();
                    //var warehouseId = $(`${window.elementID}_warehouseId`).val();
                    var warehouseId = $(document).find("tbody tr input[name=warehouseId]").first().val();

                    if (type == 2 && row.id > 0 && warehouseId > 0) {
                        $.post("api/Control/CityMedia/Partner/Partner_Contract/GetPartnerContract", {
                            partnerId: row.id,
                            warehouseId: warehouseId
                        }, function(data) {
                            // if(data != null && data.commission > 0) {
                            //     $(`${window.elementID}_commission`).val(data.commission);
                            //     $(`${window.elementID}_commission`).trigger("focusout");
                            // }
                        });
                    }

                });



                $("#Proceeding_proceedingItem").on("focusout, change", `input[name=quantity], input[name=netPrice], input[name=incoming], input[name=commission]`, debounce(function(e) {

                    var quantity = parseFloat($(this).closest("tr").find("input[name=quantity]").val());
                    var netPrice = parseFloat($(this).closest("tr").find("input[name=netPrice]").val());
                    var commission = parseFloat($(this).closest("tr").find("input[name=commission]").val());
                    var incomingInput = $(this).closest("tr").find("input[name=incoming]");
                    var outgoingInput = $(this).closest("tr").find("input[name=outgoing]");
                    var netAmountInput = $(this).closest("tr").find("input[name=netAmount]");
                    var vatGroupIdInput = $(this).closest("tr").find("input[name=vatGroupId]");
                    var grossAmountInput = $(this).closest("tr").find("input[name=grossAmount]");
                    var vatRate = $(this).closest("tr").find("input[name=vatRate]").val();
                    outgoingInput.val(quantity * netPrice * ((100 + vatRate) / 100));

                    var incoming = incomingInput.val();
                    var outgoing = $(this).closest("tr").find("input[name=outgoing]").val();

                    grossAmountInput.val((incoming - outgoing) * (commission / 100));

                    var netAmount = grossAmountInput.val() / ((100 + vatRate) / 100)


                    netAmountInput.val(netAmount);

                }, 100));




                function HandleType(value) {
                    switch (value) {

                        //Kihelyezési
                        case "1":
                            $(`${window.elementID}_warehouseId`).attr("ng-required", "displayCondition");
                            $(`${window.elementID}_issueDate`).attr("ng-required", "displayCondition");
                            $(`${window.elementID}_partnerId`).attr("ng-required", "displayCondition");
                            $(`${window.elementID}_incoming`).removeAttr("ng-required");
                            $(`${window.elementID}_outgoing`).removeAttr("ng-required");
                            // $(`${window.elementID}_commission`).removeAttr("ng-required");

                            if (formmode != "ok") {
                                $(`${window.elementID}_incoming`).val(0);
                                $(`${window.elementID}_outgoing`).val(0);
                                // $(`${window.elementID}_commission`).val(0);
                            }

                            $(`${window.elementID}_commissionDiv`).hide();


                            break;

                            //Ürítési
                        case "2":
                            $(`${window.elementID}_warehouseId`).attr("ng-required", "displayCondition");
                            $(`${window.elementID}_partnerId`).attr("ng-required", "displayCondition");
                            $(`${window.elementID}_issueDate`).removeAttr("ng-required");
                            $(`${window.elementID}_incoming`).attr("ng-required", "displayCondition");
                            $(`${window.elementID}_outgoing`).attr("ng-required", "displayCondition");
                            // $(`${window.elementID}_commission`).attr("ng-required", "displayCondition");

                            $(`${window.elementID}_commissionDiv`).show();

                            break;

                            //Panasz
                        case "3":
                            $(`${window.elementID}_warehouseId`).attr("ng-required", "displayCondition");
                            $(`${window.elementID}_partnerId`).attr("ng-required", "displayCondition");
                            $(`${window.elementID}_issueDate`).removeAttr("ng-required");
                            $(`${window.elementID}_incoming`).removeAttr("ng-required");
                            $(`${window.elementID}_outgoing`).removeAttr("ng-required");
                            // $(`${window.elementID}_commission`).removeAttr("ng-required");

                            if (formmode != "ok") {
                                $(`${window.elementID}_incoming`).val(0);
                                $(`${window.elementID}_outgoing`).val(0);
                                $(`${window.elementID}_commission`).val(0);
                            }

                            $(`${window.elementID}_commissionDiv`).hide();

                            break;

                            //Karbantartási
                        case "4":
                            $(`${window.elementID}_warehouseId`).attr("ng-required", "displayCondition");
                            $(`${window.elementID}_partnerId`).removeAttr("ng-required");
                            $(`${window.elementID}_issueDate`).removeAttr("ng-required");
                            $(`${window.elementID}_incoming`).removeAttr("ng-required");
                            $(`${window.elementID}_outgoing`).removeAttr("ng-required");
                            // $(`${window.elementID}_commission`).removeAttr("ng-required");

                            if (formmode != "ok") {
                                $(`${window.elementID}_incoming`).val(0);
                                $(`${window.elementID}_outgoing`).val(0);
                                // $(`${window.elementID}_commission`).val(0);
                            }

                            $(`${window.elementID}_commissionDiv`).hide();

                            break;


                    }
                }

                HandleType($(`${window.elementID}_type`).val());





                function localSetFormModeToAdd() {
                    formmode = "add";
                    Application.UI.Form.SetMode(formmode, form.formWidget());

                    //tinyMCE

                    //quickfix: ha add mode-ben töltjük be a lapot, így nem sír azért mert még nem töltötte be a tinyMCE-t
                    try {
                        tinyMCE.get("Proceeding_comment").getContent();
                        tinyMCE.get("Proceeding_comment").setContent("");
                    } catch (e) {

                    }
                    //ha add mode-ban töltjük be a lapot, nem tesszük írhatóvá a tinyMCE-t mert alapból az
                    if (tinyMCE.get("Proceeding_comment").readonly != undefined) {
                        tinyMCE.get("Proceeding_comment").setMode("code");
                    }
                    //fix: amikor engedélyezzük a tinymce szerkesztését, focus lesz az elem, ezért az 1. input mezőre teszem a focus-t
                    $(window.elementID + "_documentNumber").focus();


                    //a táblának átadjuk az eredeti query-t
                    table1.data("nubes-datatable").option("dataSource", {
                        Query: <?php echo json_encode($qe); ?>,
                        Columns: <?php echo json_encode($grid->GetColumns()); ?>,
                        Parameters: <?php echo json_encode($params); ?>,
                        Array: [<?php echo \json_encode(new \DI\Model\Entity\CityMedia\Proceeding\Proceeding_Item()); ?>]
                    });
                    //ürítjük a tábla betöltött lapjait
                    table1.data("nubes-datatable").loadedPages = [];
                    //generáljuk a táblát
                    table1.data("nubes-datatable").requestForView();


                    var tabs = $(`#tabs`).find("li");
                    $(tabs[1]).hide();

                }

                if (formmode == "add") {
                    localSetFormModeToAdd();
                }

                //add mode button
                $(document).on("click", window.elementID + "_setAddFormModeButton", localSetFormModeToAdd);

                //felvesszük a contextmenu-t
                $(window.elementID + "_headDiv").addContextMenu('[{"id": "add", "name":"<?php echo Trans::Get("add_new"); ?>"}, {"id": "createInvoice", "name":"<?php echo Trans::Get("create_invoice"); ?>"}, {"id": "createPurchaseInvoice", "name":"<?php echo Trans::Get("create_purchase_invoice"); ?>"}, {"id": "delete", "name": "<?php echo Trans::Get("delete"); ?>"}]');

                //definiáljuk a contextmenu klikk eseményét
                Application.UI.ContextMenu.addContextMenuEvent(form, function() {
                    switch (this.id) {
                        case "add":
                            localSetFormModeToAdd();
                            break;



                        case "createInvoice":
                            if ($(`${window.elementID}_status`).val() != "C" && $(`${window.elementID}_type`).val() == 2) {
                                Application.UI.Dialog("Figyelem!", "Biztosan létre akarsz hozni ebből a jegyzőkönyvből egy számlát?", 2, {
                                    resizable: false,
                                    dialogClass: "warning",
                                    buttons: [{
                                        text: document.translate.yes
                                    }, {
                                        text: document.translate.cancel
                                    }],
                                    onYesButtonMethod: function() {
                                        // Application.Tools.BrowserInfo.History.replaceState({}, document.title, "Sales/Invoice/Invoice/?objectType=delivery_note&objectId=" + id);
                                        // window.location.reload();

                                        $.post("api/Control/CityMedia/Proceeding/Proceeding/StoreTDocumentSource", {
                                            objectId: id,
                                            destObjectNS: "\\DI\\Model\\Entity\\Sales\\Invoice\\Invoice",

                                        }, function(data) {
                                            console.log(data.id);
                                            Application.Tools.BrowserInfo.History.replaceState({}, document.title, "Sales/Invoice/Invoice/?tDocumentSourceId=" + data.id);
                                            window.location.reload();
                                        });
                                    }
                                });
                            } else {
                                Application.UI.Dialog("Figyelem!", "Ürítési jegyzőkönyvből lehet létrehozni számlát", 1, {
                                    dialogClass: "error",
                                    resizable: false,
                                    buttons: [{
                                        text: document.translate.ok
                                    }]
                                });
                            }

                            break;



                        case "createPurchaseInvoice":
                            if ($(`${window.elementID}_status`).val() != "C" && $(`${window.elementID}_type`).val() == 2) {
                                Application.UI.Dialog("Figyelem!", "Biztosan létre akarsz hozni ebből a jegyzőkönyvből egy beszerzési számlát?", 2, {
                                    resizable: false,
                                    dialogClass: "warning",
                                    buttons: [{
                                        text: document.translate.yes
                                    }, {
                                        text: document.translate.cancel
                                    }],
                                    onYesButtonMethod: function() {
                                        // Application.Tools.BrowserInfo.History.replaceState({}, document.title, "Sales/Invoice/Invoice/?objectType=delivery_note&objectId=" + id);
                                        // window.location.reload();

                                        $.post("api/Control/CityMedia/Proceeding/Proceeding/StoreTDocumentSource", {
                                            objectId: id,
                                            destObjectNS: "\\DI\\Model\\Entity\\Purchase\\Invoice\\Purchase_Invoice",

                                        }, function(data) {
                                            console.log(data.id);
                                            Application.Tools.BrowserInfo.History.replaceState({}, document.title, "Purchase/Invoice/Purchase_Invoice/?tDocumentSourceId=" + data.id);
                                            window.location.reload();
                                        });
                                    }
                                });
                            } else {
                                Application.UI.Dialog("Figyelem!", "Ürítési jegyzőkönyvből lehet létrehozni számlát", 1, {
                                    dialogClass: "error",
                                    resizable: false,
                                    buttons: [{
                                        text: document.translate.ok
                                    }]
                                });
                            }

                            break;


                        case "delete":
                            Application.UI.DeleteDialog(controlPath, {
                                "id": "<?php echo $_GET["id"]; ?>"
                            });
                            break;
                    }
                });


                $("#tabs").tabs({});

                form.submit(function(e) {
                    e.preventDefault();

                    if ($(form).checkRequiredFieldValues()) {

                        var controller = {};
                        controller.proceeding = Application.UI.Form.Serialize($(this));

                        controller.proceeding.id = <?php echo isset($proceeding->id) ? $proceeding->id : "undefined"; ?>;
                        controller.proceeding.id = (formmode == "add") ? "undefined" : controller.proceeding.id;


                        controller.proceedingItem = table1.data("nubes-datatable").serialize();

                        var mode = form.data("nubes-formWidget").getMethod();
                        if (mode == undefined) {
                            return;
                        }


                        $(form).find("button[type=submit]").attr("disabled", "disabled");
                        Application.DI.Ajax.post("api" + controlPath + "/" + mode, {
                                controller
                            }, "json")
                            .done(function(data) {
                                switch (mode) {
                                    case "Add":
                                        Application.Tools.BrowserInfo.History.replaceState({}, document.title, window.location.origin + window.location.pathname + "?id=" + data.id);


                                        break;
                                    case "Update":

                                        break;
                                }
                                window.location.reload();
                            })
                            .always(() => {
                                $(form).find("button[type=submit]").removeAttr("disabled");
                            });



                    }
                });

            });
        </script>


        <h1 class="center-text"><?php echo Trans::Get("proceeding"); ?></h1>


        <form id="<?php echo $_GET["page"]; ?>_form1" class='formwidget'>
            <div id="<?php echo $_GET["page"]; ?>_headDiv" class="row">
                <div class="col-md-6">
                    <dt><?php echo Trans::Get("document_number"); ?></dt>
                    <dd>
                        <?php
                        $documentNumber = new \UI\Html\InputText();
                        $documentNumber->name = "documentNumber";
                        $documentNumber->id = $_GET["page"] . "_documentNumber";
                        $documentNumber->data["defaultvalue-ajax"] = "Control\Administration\Definitions\DocumentNumber\Document_Number\Generate";
                        $documentNumber->data["defaultvalue-ajax-data"] = json_encode(array("namespace" => get_class($proceeding)));
                        $documentNumber->required = true;
                        $documentNumber->readonly = true;
                        $documentNumber->maxlength = 100;
                        $documentNumber->SetValue($proceeding->documentNumber);

                        echo $documentNumber->Render();
                        ?>
                    </dd>

                    <dt><?php echo Trans::Get("status"); ?></dt>
                    <dd>
                        <?php
                        $status = new \UI\Html\Select();
                        $status->name = "status";
                        $status->id = $_GET["page"] . "_status";
                        $status->SetValidValues(Field_Valid_Values::GetValidValuesFromDataBaseToSelect("citymedia", "ct_proceeding", $status->name));
                        $status->data["defaultvalue"] = "O";
                        $status->SetValue($proceeding->status);
                        $status->required = true;

                        echo $status->Render();
                        ?>
                    </dd>

                    <dt><?php echo Trans::Get("type"); ?></dt>
                    <dd>
                        <?php
                        $type = new \UI\Html\Select();
                        $type->name = "type";
                        $type->id = $_GET["page"] . "_type";
                        $type->SetValidValues(Field_Valid_Values::GetValidValuesFromDataBaseToSelect("citymedia", "ct_proceeding", $type->name));
                        $type->data["defaultvalue"] = "1";
                        $type->SetValue($proceeding->type);
                        $type->required = true;

                        echo $type->Render();
                        ?>
                    </dd>



                    <dt><?php echo Trans::Get("date"); ?></dt>
                    <dd>
                        <?php
                        $issueDate = new \UI\Html\InputDate();
                        $issueDate->name = "issueDate";
                        $issueDate->id = $_GET["page"] . "_issueDate";
                        $issueDate->SetValue($proceeding->issueDate);

                        echo $issueDate->Render();
                        ?>
                    </dd>


                </div>

                <div class="col-md-6">

                    <dt><?php echo Trans::Get("object_type"); ?></dt>
                    <dd>
                        <?php
                        $objectType = new \UI\Html\Select();
                        $objectType->name = "objectType";
                        $objectType->SetValidValues(array(
                            array("invoice", Trans::Get("invoice")),
                            array("purchase_invoice", Trans::Get("purchase_invoice")),
                            array("inventory_entry", Trans::Get("inventory_entry")),
                            array("inventory_exit", Trans::Get("inventory_exit")),
                        ));

                        $objectType->id = $_GET["page"] . "_objectType";
                        $objectType->SetValue($proceeding->objectType);

                        echo $objectType->Render();

                        ?>
                    </dd>


                    <dt><?php echo Trans::Get("object_id"); ?></dt>
                    <dd>
                        <?php
                        $objectId = new \UI\Html\InputCfl2();
                        $objectId->name = "objectId";
                        $objectId->id = $_GET["page"] . "_objectId";
                        switch ($proceeding->objectType) {
                            case "invoice":
                                $objectId->data["class"] = "\DI\Model\Entity\Sales\Invoice\Invoice";
                                break;
                            case "purchase_invoice":
                                $objectId->data["class"] = "\DI\Model\Entity\Purchase\Invoice\Purchase_Invoice";
                                break;

                            case "inventory_entry":
                                $objectId->data["class"] =  "\\DI\\Model\\Entity\\StockManagement\\StoreTransactions\\Inventory_Entry\\Inventory_Entry";
                                break;

                            case "inventory_exit":
                                $objectId->data["class"] =  "\\DI\\Model\\Entity\\StockManagement\\StoreTransactions\\Inventory_Exit\\Inventory_Exit";
                                break;
                        }
                        $objectId->data["cfl_column"] = "id";
                        $objectId->SetValue($proceeding->objectId);

                        echo $objectId->Render();
                        ?>
                    </dd>



                    <dt><?php echo Trans::Get("partner_id"); ?></dt>
                    <dd>
                        <?php
                        $partnerId = new \UI\Html\InputCfl2();
                        $partnerId->name = "partnerId";
                        $partnerId->id = $_GET["page"] . "_partnerId";
                        $partnerId->data["class"] = "\DI\Model\Entity\BusinessPartners\Partner\Partner";
                        $partnerId->data["cfl_column"] = "id";
                        $partnerId->SetValue($proceeding->partnerId);

                        echo $partnerId->Render();
                        ?>
                    </dd>
                    <dt>Átvevő</dt>
                    <dd>
                        <?php
                        $recipient = new \UI\Html\InputText();
                        $recipient->name = "recipient";
                        $recipient->id = $_GET["page"] . "_recipient";
                        $recipient->maxlength = 100;
                        $recipient->SetValue($proceeding->recipient);

                        echo $recipient->Render();
                        ?>
                    </dd>

                    <dt>Kiállította</dt>
                    <dd>
                        <?php
                        $createdBy = new \UI\Html\InputText();
                        $createdBy->name = "createdBy";
                        $createdBy->id = $_GET["page"] . "_createdBy";
                        $createdBy->maxlength = 100;
                        $createdBy->SetValue($proceeding->createdBy);

                        echo $createdBy->Render();
                        ?>
                    </dd>




                </div>
            </div>

            <div class="col-12 form-group" id="tabs" style="overflow: hidden">
                <ul>
                    <li><a href="<?php echo $_SERVER["REQUEST_URI"]; ?>#tabs-1"><?php echo Trans::Get("content"); ?></a></li>
                    <li><a href="<?php echo $_SERVER["REQUEST_URI"]; ?>#tabs-2"><?php echo Trans::Get("signature"); ?></a></li>
                    <li><a href="<?php echo $_SERVER["REQUEST_URI"]; ?>#tabs-3"><?php echo Trans::Get("comment"); ?></a></li>
                </ul>

                <div id="tabs-1" class="center-text">
                    <div class="col-12">
                        <?php
                        echo $grid->Render();
                        ?>
                    </div>
                </div>

                <div id="tabs-2">
                    <div class="row">
                        <script>
                            $(function() {

                                $(".saveSignature").on("click", function(e) {
                                    e.preventDefault();

                                    var canvas = document.getElementById("Proceeding_sign");
                                    var img = canvas.toDataURL("image/png");

                                    var objectId = '<?php echo $proceeding->id; ?>';



                                    $.post("api/Control/System/File/FileManager/UploadBase64EncodedFile", {
                                        file: img,
                                        name: "alairas.png",
                                        mimeType: "image/png",
                                        classPath: "\\CityMedia\\Proceeding\\Proceeding",
                                        objectId: objectId
                                    }, function(data) {

                                        var filemanagerDiv = $(document).find(".nb-filemanager");

                                        var isSet = false;

                                        $.each($(filemanagerDiv).find(`span.nb-file`), function(i, one) {


                                            //ez itt csak felülírja, le kell kezelni azt amikor beszúrjuk
                                            if ($(one).data("name") == data.name) {

                                                isSet = true;

                                                var lImg = $(one).find("img").first();
                                                var src = lImg[0].src;


                                                lImg.removeAttr("src");

                                                // create a new timestamp
                                                var timestamp = new Date().getTime();
                                                lImg.attr("src", src + "?t=" + timestamp);

                                                return false;
                                            }
                                        });


                                        //beszúrjuk
                                        if (!isSet) {
                                            var deleteButton = $(`<div class="nb-filemanager-delete">
                                                    <svg class="delete" style="position: absolute; width: 100%; height: 100%; left: 0px;" xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
                                                    <line x1="0" y1="0" x2="100" y2="100"></line>
                                                    <line x1="0" y1="100" x2="100" y2="0"></line>
                                                    </svg>
                                                </div>`);

                                            var view = $(data.view);
                                            view.append(deleteButton);


                                            $(filemanagerDiv).append(view);
                                        }
                                    });

                                    canvas = document.getElementById("Proceeding_sign2");
                                    img = canvas.toDataURL("image/png");


                                    $.post("api/Control/System/File/FileManager/UploadBase64EncodedFile", {
                                        file: img,
                                        name: "alairas2.png",
                                        mimeType: "image/png",
                                        classPath: "\\CityMedia\\Proceeding\\Proceeding",
                                        objectId: objectId
                                    }, function(data) {

                                        var filemanagerDiv = $(document).find(".nb-filemanager");

                                        var isSet = false;

                                        $.each($(filemanagerDiv).find(`span.nb-file`), function(i, one) {


                                            //ez itt csak felülírja, le kell kezelni azt amikor beszúrjuk
                                            if ($(one).data("name") == data.name) {

                                                isSet = true;

                                                var lImg = $(one).find("img").first();
                                                var src = lImg[0].src;


                                                lImg.removeAttr("src");

                                                // create a new timestamp
                                                var timestamp = new Date().getTime();
                                                lImg.attr("src", src + "?t=" + timestamp);

                                                return false;
                                            }
                                        });


                                        //beszúrjuk
                                        if (!isSet) {
                                            var deleteButton = $(`<div class="nb-filemanager-delete">
                                                    <svg class="delete" style="position: absolute; width: 100%; height: 100%; left: 0px;" xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
                                                    <line x1="0" y1="0" x2="100" y2="100"></line>
                                                    <line x1="0" y1="100" x2="100" y2="0"></line>
                                                    </svg>
                                                </div>`);

                                            var view = $(data.view);
                                            view.append(deleteButton);


                                            $(filemanagerDiv).append(view);
                                        }
                                    });


                                });
                            });
                        </script>



                        <div class="col-md-6">
                            <?php
                            echo \Control\EntityController::RenderFileManager($proceeding);
                            ?>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="form-group text-center">

                                    <label for="Proceeding_sign">Aláírás</label>
                                    <?php
                                    $sign = new Signature();
                                    $sign->id = "Proceeding_sign";
                                    echo $sign->Render();
                                    ?>
                                </div>
                                <div class="form-group text-center">

                                    <label for="Proceeding_sign2">Aláírás</label>
                                    <?php
                                    $sign = new Signature();
                                    $sign->id = "Proceeding_sign2";
                                    echo $sign->Render();
                                    ?>
                                </div>
                            </div>
                            <div>
                                <?php 
                                $btn = new Button();
                                $btn->type = "button";
                                $btn->class[] = "saveSignature";
                                $btn->innerHtml = "Aláírás mentése";
                                echo $btn->Render();
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="tabs-3">
                    <div class="row">
                        <div class="col-6">
                            <dt><?php echo Trans::Get("comment"); ?></dt>
                            <dd>
                                <?php
                                $comment = new \UI\Html\TextArea();
                                $comment->name = "comment";
                                $comment->id = $_GET["page"] . "_comment";
                                $comment->SetValue($proceeding->comment);

                                echo $comment->Render();
                                ?>
                            </dd>
                        </div>
                    </div>
                </div>
            </div>

            <div id="<?php echo $_GET["page"]; ?>_commissionDiv">
                <dt><?php echo Trans::Get("income"); ?></dt>
                <dd>

                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-auto">

                                <?php
                                $incoming = new \UI\Html\InputNumber();
                                $incoming->name = "incoming";
                                $incoming->max = 9999999999999;
                                $incoming->step = 0.1;
                                $incoming->precision = 6;
                                $incoming->id = $_GET["page"] . "_incoming";
                                $incoming->readonly = true;
                                $incoming->SetValue($proceeding->incoming);
                                echo $incoming->Render();
                                ?>
                            </div>
                        </div>
                    </div>

                </dd>

                <dt><?php echo Trans::Get("expenditure"); ?></dt>
                <dd>
                    <div class="row">
                        <div class="col-auto">

                            <?php
                            $outgoing = new \UI\Html\InputNumber();
                            $outgoing->name = "outgoing";
                            $outgoing->max = 9999999999999;
                            $outgoing->step = 0.1;
                            $outgoing->precision = 6;
                            $outgoing->id = $_GET["page"] . "_outgoing";
                            $outgoing->readonly = true;
                            $outgoing->SetValue($proceeding->outgoing);
                            echo $outgoing->Render();
                            ?>
                        </div>
                    </div>

                </dd>

                <dt><?php echo Trans::Get("grossAmount"); ?></dt>
                <dd>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-auto">

                                <?php
                                $amount = new \UI\Html\InputNumber();
                                $amount->name = "grossAmount";
                                $amount->max = 9999999999999;
                                $amount->step = 0.1;
                                $amount->precision = 6;
                                $amount->id = $_GET["page"] . "_grossAmount";
                                $amount->readonly = true;
                                $amount->SetValue($proceeding->grossAmount);
                                echo $amount->Render();
                                ?>
                            </div>
                        </div>
                    </div>

                </dd>



            </div>

            <div class="row">
                <div class="col-12">
                    <?php
                    $button = new SubmitButton();
                    echo $button->Render();


                    $cancelButton = new CancelButton();
                    echo $cancelButton->Render();
                    ?>
                </div>
            </div>
        </form>


        <div class="row">
            <?php
            if ($formmode != FormMode::$add) {
                $button = new \UI\Html\SetAddFormModeButton();
                $button->SetTitle(Trans::Get("add_new"));

                echo $button->Render();
            }
            ?>
        </div>




<?php

        $this->content = ob_get_contents();
        ob_end_clean();
    }

    /**
     * @inheritDoc
     */
    public function Render()
    {
        $this->CreateContent();
        return $this->content;
    }

    /**
     * @inheritDoc
     */
    public function SetModel($obj)
    {
        $this->model = $obj;
    }

    /**
     * @inheritDoc
     */
    public function &GetModel()
    {
        return $this->model;
    }
}
