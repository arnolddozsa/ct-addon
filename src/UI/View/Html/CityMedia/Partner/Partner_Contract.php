<?php
namespace UI\View\Html\CityMedia\Partner;

use Control\Administration\Definitions\Localization\Translate\Translate as Trans;
use DI\Model\Entity\Administration\SystemSettings\TableDefinitions\Field_Valid_Values;
use DI\Model\Entity\FormMode;
use UI\Html\Button\CancelButton;
use UI\Html\Button\SubmitButton;
use UI\Html\InputDate;

/**
 * CityMedia szerződés nézet osztály.
 */
class Partner_Contract implements \UI\View\IView{

	private $model;

	/**
	 * @inheritDoc
	 */
	public function CreateContent(){
		$partnerContract = $this->GetModel()[0]["partnerContract"];
		$partnerContractItem = $this->GetModel()[0]["partnerContractItem"];

		$qe = $this->GetModel()["query"];
		$params = $this->GetModel()["params"];

		//control elérési útvonala
		$control = str_replace("\\", "/", $this->GetModel()["control"]);

		$formmode = $this->GetModel()["formmode"];
		FormMode::Check($formmode);

		ob_start();

		//grid
		$grid = new \UI\View\Html\Grid();
		$grid->id = $_GET["page"]."_partnerContractItem";
		$grid->SetModel($partnerContractItem);

		//raktár cfl
		$warehouseId_cfl = new \UI\Html\InputCfl2();
		$warehouseId_cfl->name = "warehouseId";
		$warehouseId_cfl->data["class"] = "\DI\Model\Entity\Administration\Definitions\StockManagement\Warehouse\Warehouse";
		$warehouseId_cfl->data["cfl_column"] = "id";
		$grid->SetColumn("warehouseId", $warehouseId_cfl);

		$itemId_cfl = new \UI\Html\InputCfl2();
		$itemId_cfl->name = "itemId";
		$itemId_cfl->data["class"] = "\DI\Model\Entity\StockManagement\Item\Item";
		$itemId_cfl->data["cfl_column"] = "id";

		$grid->SetColumn("itemId", $itemId_cfl);

		$ui = new \UI\Html\InputCheckBox();
		$ui->name = "settlementByDifference";

		$grid->SetColumn($ui->name, $ui);

        $ui = new \UI\Html\InputNumber();
		$ui->name = "quantity";
		$ui->readonly = true;

		$grid->SetColumn("quantity", $ui);

		?>

		<script>
			$(function(){
                var controlPath = "<?php echo $control; ?>";
                var formmode = "<?php echo $formmode; ?>";

                var form = $(window.elementID + "_form1");


                //tábla létrehozás
                var table1 = $(window.elementID + "_partnerContractItem").datatable({
                    ajax: "api/Control/DataTable/Load",
                    dataSource: {
                        Query: <?php echo json_encode($qe); ?> ,
                        Columns: <?php echo json_encode($grid->GetColumns()); ?>,
                        Parameters: <?php echo json_encode($params); ?>},
                    pagination: {recordsPerPage: 15, page: 1},
                    sort: [{column: "warehouseId", type: "ASC"}]
                });


                table1.on("pageLoad", function () {

                    //szerkesztjük a táblát
                    //végigmegyünk a sorokon
                    $(table1).find("tbody tr").each(function(i, one){
                        //végigmegyünk a mezőkön
                        $(one).find("td input, td select").each(function (j, input) {

                            //kötelező mezők beállítása
                            if(input.name == "warehouseId" || input.name == "warehouseCode" || input.name == "warehouseName"){
                                $(input).attr("ng-required", "displayCondition");

                            }
                        });
                    });

                    if($(`${window.elementID}_type`).val() == 3) {
                        $("#Partner_Contract_partnerContractItem").find("tbody input[name=netPrice]").prop("readonly", false);
                    } else {
                        $("#Partner_Contract_partnerContractItem").find("tbody input[name=netPrice]").prop("readonly", true);
                    }

                });


                $("#tabs").tabs({
                    create: function (event, ui) {
                        Application.UI.Grid.Refresh();
                    },
                    active: 0
                });


                $(`${window.elementID}_partnerId`).on("cflSelect", function (e, row){
                    $(`${window.elementID}_partnerCode`).val(row.code);
                    $(`${window.elementID}_partnerName`).val(row.name);
                });


                $(`${window.elementID}_partnerId`).on("cflNotSelect", function (e, row){
                    $(`${window.elementID}_partnerCode`).val(null);
                    $(`${window.elementID}_partnerName`).val(null);
                });


                $(document).on("cflSelect", "td input[name=warehouseId]", function (e, row){
                    var input = $(e.target);
                    var tableRow = input.closest("tr");
                    $(tableRow).find("td input[name=warehouseName]").val(row.name);
                    $(tableRow).find("td input[name=warehouseCode]").val(row.code);

                });

                $(document).on("cflNotSelect", "td input[name=warehouseId]", function (e, row){
                    var input = $(e.target);
                    var tableRow = input.closest("tr");
                    $(tableRow).find("td input[name=warehouseName]").val(null);
                    $(tableRow).find("td input[name=warehouseCode]").val(null);
                });


                if($(`${window.elementID}_type`).val() != 2) {
                    $(`${window.elementID}_commissionDiv`).hide();
                }else{
                    $(`${window.elementID}_commissionDiv`).show();
                }

                if($(`${window.elementID}_type`).val() == 3) {
                    $("#Partner_Contract_partnerContractItem").find("tbody input[name=netPrice]").prop("readonly", false);
                } else {
                    $("#Partner_Contract_partnerContractItem").find("tbody input[name=netPrice]").prop("readonly", true);
                }


                $(`${window.elementID}_type`).on("change", function (e){
                    if($(`${window.elementID}_type`).val() != 2) {
                        $(`${window.elementID}_commissionDiv`).hide();


                        $(`${window.elementID}_commission`).val(0);


                    }else{
                        $(`${window.elementID}_commissionDiv`).show();
                    }

                    if($(`${window.elementID}_type`).val() == 3) {
                        $("#Partner_Contract_partnerContractItem").find("tbody input[name=netPrice]").prop("readonly", false);
                    } else {
                        $("#Partner_Contract_partnerContractItem").find("tbody input[name=netPrice]").prop("readonly", true);
                    }
                });

                function localSetFormModeToAdd() {
                    formmode = "add";
                    Application.UI.Form.SetMode(formmode, form.formWidget());

                    //a táblának átadjuk az eredeti query-t
                    table1.data("nubes-datatable").option("dataSource", {
                        Query: <?php echo json_encode($qe); ?> ,
                        Columns: <?php echo json_encode($grid->GetColumns()); ?>,
                        Parameters: <?php echo json_encode($params); ?>,
                        Array: [<?php echo \json_encode(new \DI\Model\Entity\CityMedia\Partner\Partner_Contract_Item()); ?>]
                    });
                    //ürítjük a tábla betöltött lapjait
                    table1.data("nubes-datatable").loadedPages = [];
                    //generáljuk a táblát
                    table1.data("nubes-datatable").requestForView();



                    //tinyMCE

                    //quickfix: ha add mode-ben töltjük be a lapot, így nem sír azért mert még nem töltötte be a tinyMCE-t
                    try{
                        tinyMCE.get("Partner_Contract_comment").getContent();
                        tinyMCE.get("Partner_Contract_comment").setContent("");
                    }catch (e) {

                    }
                    //ha add mode-ban töltjük be a lapot, nem tesszük írhatóvá a tinyMCE-t mert alapból az
                    if(tinyMCE.get("Partner_Contract_comment").readonly != undefined) {
                        tinyMCE.get("Partner_Contract_comment").setMode("code");
                    }
                    //fix: amikor engedélyezzük a tinymce szerkesztését, focus lesz az elem, ezért az 1. input mezőre teszem a focus-t
                    $(window.elementID+"_documentNumber").focus();

                    var tabs = $(`#tabs`).find("li");
                    $(tabs[1]).hide();

                }

                if (formmode == "add") {
                    localSetFormModeToAdd();
                }

                //add mode button
                $(document).on("click", window.elementID + "_setAddFormModeButton", localSetFormModeToAdd);

                //felvesszük a contextmenu-t
                $(window.elementID + "_headDiv").addContextMenu('[{"id": "add", "name":"<?php echo Trans::Get("add_new"); ?>"}, {"id": "delete", "name": "<?php echo Trans::Get("delete"); ?>"}]');

                //definiáljuk a contextmenu klikk eseményét
                Application.UI.ContextMenu.addContextMenuEvent(form, function () {
                    switch (this.id) {
                        case "add":
                            localSetFormModeToAdd();
                            break;

                        case "delete":
                            Application.UI.DeleteDialog(controlPath, {"id": "<?php echo $_GET["id"]; ?>"});
                            break;
                    }
                });



                form.submit(function (e) {
                    e.preventDefault();

                    //üres sorokat töröljük
                    table1.find("tbody tr input[name=warehouseId]").filter((j, one) => {
                        return $(one).val() < 1;
                    }).closest("tr").remove();


                    if ($(form).checkRequiredFieldValues()) {

                        var controller = {};
                        controller.partnerContract = Application.UI.Form.Serialize($(this));

                        controller.partnerContract.id = <?php echo isset($partnerContract->id) ? $partnerContract->id : "undefined";?>;
                        controller.partnerContract.id = (formmode == "add") ? "undefined" : controller.partnerContract.id;

                        controller.partnerContractItem = table1.data("nubes-datatable").serialize();

                        controller.partnerContractItem.insertedRows = controller.partnerContractItem.insertedRows.map((o) => {
                            delete o.quantity;
                            return o;
                        });
                        controller.partnerContractItem.modifiedRows = controller.partnerContractItem.modifiedRows.map((o) => {
                            delete o.quantity;
                            return o;
                        });

                        var mode = form.data("nubes-formWidget").getMethod();
                        if(mode == undefined){
                            return;
                        }




                        $(form).find("button[type=submit]").attr("disabled", "disabled");
                        Application.DI.Ajax.post("api" + controlPath + "/" + mode, {controller}, "json")
                            .done(function (data) {
                                switch (mode) {
                                    case "Add":
                                        Application.Tools.BrowserInfo.History.replaceState({}, document.title, window.location.origin + window.location.pathname + "?id=" + data.id);
                                        window.location.reload();

                                        break;
                                    case "Update":
                                        $(form).data("nubes-formWidget").setMode("ok");
                                        break;
                                }
                            })
                            .always(() => {
                                $(form).find("button[type=submit]").removeAttr("disabled");
                            });



                    }
                });


			});
		</script>

		<h1 class="center-text"><?php echo Trans::Get("contract"); ?></h1>

		<form id="<?php echo $_GET["page"]; ?>_form1" class='formwidget'>
			<div id="<?php echo $_GET["page"]; ?>_headDiv" class="row">
				<div class="col-6">
                    <dt><?php echo Trans::Get("document_number"); ?></dt>
                    <dd>
						<?php
							$documentNumber = new \UI\Html\InputText();
							$documentNumber->name = "documentNumber";
							$documentNumber->id = $_GET["page"]."_documentNumber";
							$documentNumber->data["defaultvalue-ajax"] = "Control\Administration\Definitions\DocumentNumber\Document_Number\Generate";
							$documentNumber->data["defaultvalue-ajax-data"] = json_encode(array("namespace" => get_class($partnerContract)));
							$documentNumber->required = true;
							$documentNumber->readonly = true;
							$documentNumber->maxlength = 100;
							$documentNumber->SetValue($partnerContract->documentNumber);

							echo $documentNumber->Render();
						?>
                    </dd>

                    <dt><?php echo Trans::Get("expires_date"); ?></dt>
                    <dd>
						<?php
							$expiresDate = new InputDate();
							$expiresDate->name = "expiresDate";
							$expiresDate->id = $_GET["page"]."_expiresDate";
							$expiresDate->data["defaultvalue"] = date("Y-m-d");
							$expiresDate->required = true;
							$expiresDate->SetValue($partnerContract->expiresDate);

							echo $expiresDate->Render();
						?>
                    </dd>

                    <dt><?php echo Trans::Get("status"); ?></dt>
                    <dd>
						<?php
							$status = new \UI\Html\Select();
							$status->name = "status";
							$status->id = $_GET["page"]."_status";
							$status->SetValidValues(Field_Valid_Values::GetValidValuesFromDataBaseToSelect("nubes", "ct_partner_contract", $status->name));
							$status->data["defaultvalue"] = "O";
							$status->SetValue($partnerContract->status);
							$status->required = true;

							echo $status->Render();
						?>
                    </dd>


                    <dt><?php echo Trans::Get("type"); ?></dt>
                    <dd>
						<?php
							$type = new \UI\Html\Select();
							$type->name = "type";
							$type->id = $_GET["page"]."_type";
							$type->SetValidValues(Field_Valid_Values::GetValidValuesFromDataBaseToSelect("nubes", "ct_partner_contract", $type->name));
							$type->SetValue($partnerContract->type);
							$type->required = true;

							echo $type->Render();
						?>
                    </dd>


				</div>

				<div class="col-6">
                    <dt><?php echo Trans::Get("partner_id"); ?></dt>
                    <dd>
						<?php
							$partnerId = new \UI\Html\InputCfl2();
							$partnerId->name = "partnerId";
							$partnerId->id = $_GET["page"]."_partnerId";
							$partnerId->data["class"] = "\DI\Model\Entity\BusinessPartners\Partner\Partner";
							$partnerId->data["cfl_column"] = "id";
							$partnerId->required = true;
							$partnerId->SetValue($partnerContract->partnerId);

							echo $partnerId->Render();
						?>
                    </dd>

                    <dt><?php echo Trans::Get("partner_name"); ?></dt>
                    <dd>
						<?php
							$partnerName = new \UI\Html\InputText();
							$partnerName->name = "partnerName";
							$partnerName->id = $_GET["page"]."_partnerName";
							$partnerName->maxlength = 255;
							$partnerName->SetValue($partnerContract->partnerName);
							$partnerName->required = true;
							$partnerName->readonly = true;

							echo $partnerName->Render();
						?>
                    </dd>

                    <dt><?php echo Trans::Get("partner_code"); ?></dt>
                    <dd>
						<?php
							$partnerCode = new \UI\Html\InputText();
							$partnerCode->name = "partnerCode";
							$partnerCode->id = $_GET["page"]."_partnerCode";
							$partnerCode->maxlength = 255;
							$partnerCode->SetValue($partnerContract->partnerCode);
							$partnerCode->required = true;
							$partnerCode->readonly = true;

							echo $partnerCode->Render();
						?>
                    </dd>


                    <div id="<?php echo $_GET["page"]; ?>_commissionDiv">

                    <dt><?php echo Trans::Get("commission"); ?></dt>
                    <dd>
						<?php
							$commission = new \UI\Html\InputNumber();
							$commission->name = "commission";
							$commission->max = 9999999999999;
							$commission->step = 0.1;
							$commission->precision = 6;
							$commission->id = $_GET["page"]."_commission";
							$commission->SetValue($partnerContract->commission);
							echo $commission->Render();
						?>
                    </dd>
                    </div>

				</div>

			</div>

			<div class="col-12 form-group" id="tabs" style="overflow: hidden">
				<ul>
					<li><a href="<?php echo $_SERVER["REQUEST_URI"]; ?>#tabs-1"><?php echo Trans::Get("content"); ?></a></li>
                    <li><a href="<?php echo $_SERVER["REQUEST_URI"]; ?>#tabs-2"><?php echo Trans::Get("file"); ?></a></li>
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

                        <div class="col-md-6">
							<?php
								echo \Control\EntityController::RenderFileManager($partnerContract);
							?>
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
									$comment->id = $_GET["page"]."_comment";
									$comment->SetValue($partnerContract->comment);

									echo $comment->Render();
								?>
                            </dd>
                        </div>
                    </div>
                </div>

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
				if($formmode != FormMode::$add) {
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
	public function Render(){
		$this->CreateContent();
		return $this->content;
	}

	/**
	 * @inheritDoc
	 */
	public function SetModel($obj){
		$this->model = $obj;
	}

	/**
	 * @inheritDoc
	 */
	public function &GetModel(){
		return $this->model;
	}
}