<?php

namespace UI\View\Html\CityMedia\Fuel_Consumption;

use Control\Administration\Definitions\Localization\Translate\Translate as Trans;
use DI\Model\Entity\Administration\SystemSettings\TableDefinitions\Field_Valid_Values;
use DI\Model\Entity\FormMode;
use UI\Html\Button\CancelButton;
use UI\Html\Button\SubmitButton;
use UI\Html\InputDate;

/**
 * Üzemanyag felhasználás nézet osztály.
 */
class Fuel_Consumption implements \UI\View\IView{

	private $model;

	/**
	 * @inheritDoc
	 */
	public function CreateContent(){

		$fuelConsumption = $this->GetModel()[0]["fuelConsumption"];
		$fuelConsumptionItem = $this->GetModel()[0]["fuelConsumptionItem"];

		$qe = $this->GetModel()["query"];
		$params = $this->GetModel()["params"];

		//control elérési útvonala
		$control = str_replace("\\", "/", $this->GetModel()["control"]);

		$formmode = $this->GetModel()["formmode"];
		FormMode::Check($formmode);

		ob_start();




		//grid
		$grid = new \UI\View\Html\Grid();
		$grid->id = $_GET["page"]."_fuelConsumptionItem";
		$grid->SetModel($fuelConsumptionItem);



		$warehouseId_cfl = new \UI\Html\InputCfl2();
		$warehouseId_cfl->name = "warehouseId";
		$warehouseId_cfl->data["class"] = "\DI\Model\Entity\Administration\Definitions\StockManagement\Warehouse\Warehouse";
		$warehouseId_cfl->data["cfl_column"] = "id";

		$grid->SetColumn("warehouseId", $warehouseId_cfl);




		?>

		<script>
		    $(function () {

		        var controlPath = "<?php echo $control; ?>";
		        var formmode = "<?php echo $formmode; ?>";

		        var form = $(window.elementID + "_form1");


		        //tábla létrehozás
		        var table1 = $(window.elementID + "_fuelConsumptionItem").datatable({
		            ajax: "api/Control/CityMedia/Fuel_Consumption/Fuel_Consumption/LoadList",
		            dataSource: {
                        fuelConsumptionId:  <?php echo $fuelConsumption->id?$fuelConsumption->id:"null";?>,
		                Query: <?php echo json_encode($qe); ?> ,
		                Columns: <?php echo json_encode($grid->GetColumns()); ?>,
		                Parameters: <?php echo json_encode($params); ?>},
		            pagination: {recordsPerPage: 15, page: 1},
                    enableContextMenu: false
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


                    if(formmode == "add") {
                        //lekérjük a becsült üzemanyag mennyiséget a raktárakhoz a kiszállások alapján
                        $.post("api/Control/CityMedia/Fuel_Consumption/Fuel_Consumption/HandleQuantity", {}, (data) => {

                            console.log(data);

                            $(`${window.elementID}_usedFuelQuantitySum`).val(data.allFuelQuantity);
                            $(`${window.elementID}_usedFuelQuantitySum`).attr("data-value", data.allFuelQuantity);

                            $.each(table1.find("tbody tr"), function (i, row) {

                                var warehouseId = $(row).find("input[name=warehouseId]").val();
                                var warehouseData = data.proceedingList.find(x => x.warehouseId == warehouseId);
                                if (warehouseData != null) {
                                    $(row).find("input[name=usedFuelQuantity]").val(warehouseData.fuelQuantity);
                                    $(row).find("input[name=usedFuelQuantity]").attr("data-prev_value", warehouseData.fuelQuantity);
                                }
                            });


                        });
                    }

		        });


		        $("#tabs").tabs({
		            create: function (event, ui) {
		                Application.UI.Grid.Refresh();
		            },
		            active: 0
		        });


		        $(document).on("cflSelect", "input[name=warehouseId]", function (e, row){
		            $(`${window.elementID}_warehouseName`).val(row.name);
		            $(`${window.elementID}_warehouseCode`).val(row.code);
		        });

                $(document).on("cflNotSelect", "input[name=warehouseId]", function (e, row){
		            $(`${window.elementID}_warehouseName`).val(null);
		            $(`${window.elementID}_warehouseCode`).val(null);
		        });


                //0-nál kisebb felhasznált üzemanyag mennyiséget nem engedünk
                $(document).on("change", "table tbody tr input[name=usedFuelQuantity]", function (e){
                    e.preventDefault();

                    if(this.value < 0){
                        this.value = 0;
                    }
                });


                $(document).on("focusout", "table tbody tr input[name=usedFuelQuantity]", function (e){
                    e.preventDefault();

                    var value = $(this).val();
                    var input = $(this).closest("input");

                    var allRowQuantity = 0;


                    //soronként összeadjuk a felhasznált üzemanyag mennyiséget
                    $.each(table1.find("tbody tr"), function(i, one){
                        var usedFuelQuantity = $(one).find("input[name=usedFuelQuantity]").val();
                        allRowQuantity += usedFuelQuantity;
                    });


                    //megnézzük mi a maximális felhasznált összes üzemanyag mennyiség
                    var currentUsedFuelQuantitySum = $(`${window.elementID}_usedFuelQuantitySum`).data("value");


                    //ha szükséges, módosítunk
                    if(allRowQuantity <= currentUsedFuelQuantitySum){
                        $(`${window.elementID}_usedFuelQuantitySum`).val(allRowQuantity);

                        $(`${window.elementID}_remainedFuelQuantitySum`).val(currentUsedFuelQuantitySum - allRowQuantity);

                        $(input).attr("data-prev_value", value);
                    //ha nagyobb mint a maximum, kezeljük
                    }else{
                        var prevValue = $(input).attr("data-prev_value");

                        if(prevValue !== undefined){
                            $(input).val(prevValue);
                        }else{
                            $(input).val(0);
                        }
                    }




                });

		        function localSetFormModeToAdd() {
		            formmode = "add";
		            Application.UI.Form.SetMode(formmode, form.formWidget());


                    //tinyMCE

                    //quickfix: ha add mode-ben töltjük be a lapot, így nem sír azért mert még nem töltötte be a tinyMCE-t
                    try{
                        tinyMCE.get("Fuel_Consumption_comment").getContent();
                        tinyMCE.get("Fuel_Consumption_comment").setContent("");
                    }catch (e) {

                    }
                    //ha add mode-ban töltjük be a lapot, nem tesszük írhatóvá a tinyMCE-t mert alapból az
                    if(tinyMCE.get("Fuel_Consumption_comment").readonly != undefined) {
                        tinyMCE.get("Fuel_Consumption_comment").setMode("code");
                    }
                    //fix: amikor engedélyezzük a tinymce szerkesztését, focus lesz az elem, ezért az 1. input mezőre teszem a focus-t
                    $(window.elementID+"_documentNumber").focus();

		        }

                function changeToAddMode(){
                    var dataSource = table1.data("nubes-datatable").option("dataSource");
                    dataSource.fuelConsumptionId = null;
                    table1.data("nubes-datatable").option("dataSource", dataSource);


                    table1.data("nubes-datatable").loadedPages = [];
                    var pagination = table1.data("nubes-datatable").option("pagination");
                    pagination.page = 1;
                    table1.data("nubes-datatable").option("pagination", pagination);


                    table1.data("nubes-datatable").tableBody.find("tbody tr").remove();


                    table1.data("nubes-datatable").requestForView();
                }


		        if(formmode == "add") {
		            localSetFormModeToAdd();
		        }

		        //add mode button
		        $(document).on("click", window.elementID + "_setAddFormModeButton", function(){
                    localSetFormModeToAdd();
                    changeToAddMode();
                });

		        //felvesszük a contextmenu-t
		        $(window.elementID + "_headDiv").addContextMenu('[{"id": "add", "name":"<?php echo Trans::Get("add_new"); ?>"}, {"id": "delete", "name": "<?php echo Trans::Get("delete"); ?>"}]');

		        //definiáljuk a contextmenu klikk eseményét
		        Application.UI.ContextMenu.addContextMenuEvent(form, function () {
		            switch (this.id) {
		                case "add":
		                    localSetFormModeToAdd();
                            changeToAddMode();
		                    break;

		                case "delete":
		                    Application.UI.DeleteDialog(controlPath, {"id": "<?php echo $_GET["id"]; ?>"});
		                    break;
		            }
		        });


		        form.submit(function (e) {
		            e.preventDefault();

		            //üres sorokat töröljük
		            table1.find("tbody tr input[name=name]").filter((j, one) => {
		                return $(one).val() < 1;
		            }).closest("tr").remove();


		            if ($(form).checkRequiredFieldValues()) {

		                var controller = {};
		                controller.fuelConsumption = Application.UI.Form.Serialize($(this));

		                controller.fuelConsumption.id = <?php echo isset($fuelConsumption->id) ? $fuelConsumption->id : "undefined";?>;
		                controller.fuelConsumption.id = (formmode == "add") ? "undefined" : controller.fuelConsumption.id;

		                controller.fuelConsumptionItem = table1.data("nubes-datatable").serialize();

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


		<h1 class="center-text"><?php echo Trans::Get("fuel_consumption"); ?></h1>

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
							$documentNumber->data["defaultvalue-ajax-data"] = json_encode(array("namespace" => get_class($fuelConsumption)));
							$documentNumber->required = true;
							$documentNumber->readonly = true;
							$documentNumber->maxlength = 100;
							$documentNumber->SetValue($fuelConsumption->documentNumber);

							echo $documentNumber->Render();
						?>
					</dd>

                    <dt><?php echo Trans::Get("status"); ?></dt>
                    <dd>
						<?php
							$status = new \UI\Html\Select();
							$status->name = "status";
							$status->id = $_GET["page"]."_status";
							$status->SetValidValues(Field_Valid_Values::GetValidValuesFromDataBaseToSelect("citymedia", "ct_fuel_consumption", $status->name));
							$status->data["defaultvalue"] = "O";
							$status->SetValue($fuelConsumption->status);
							$status->required = true;

							echo $status->Render();
						?>
                    </dd>

					<dt><?php echo Trans::Get("doc_date"); ?></dt>
					<dd>
						<?php
							$docDate = new InputDate();
							$docDate->name = "docDate";
							$docDate->id = $_GET["page"]."_docDate";
							$docDate->data["defaultvalue"] = date("Y-m-d");
							$docDate->required = true;
							$docDate->SetValue($fuelConsumption->docDate);

							echo $docDate->Render();
						?>
					</dd>
				</div>

				<div class="col-6">
                    <dt><?php echo Trans::Get("used_fuel_quantity"); ?></dt>
                    <dd>
						<?php
							$usedFuelQuantitySum = new \UI\Html\InputNumber();
							$usedFuelQuantitySum->name = "usedFuelQuantitySum";
							$usedFuelQuantitySum->max = 9999999999999;
							$usedFuelQuantitySum->precision = 6;
							$usedFuelQuantitySum->id = $_GET["page"]."_usedFuelQuantitySum";
                            $usedFuelQuantitySum->readonly = true;
							$usedFuelQuantitySum->SetValue($fuelConsumption->usedFuelQuantitySum);
							echo $usedFuelQuantitySum->Render();
						?>
                    </dd>

                    <dt><?php echo Trans::Get("remained_fuel_quantity"); ?></dt>
                    <dd>
						<?php
							$remainedFuelQuantitySum = new \UI\Html\InputNumber();
							$remainedFuelQuantitySum->name = "remainedFuelQuantitySum";
							$remainedFuelQuantitySum->max = 9999999999999;
							$remainedFuelQuantitySum->precision = 6;
							$remainedFuelQuantitySum->id = $_GET["page"]."_remainedFuelQuantitySum";
                            $remainedFuelQuantitySum->readonly = true;
							$remainedFuelQuantitySum->SetValue($fuelConsumption->remainedFuelQuantitySum);
							echo $remainedFuelQuantitySum->Render();
						?>
                    </dd>
				</div>
			</div>

			<div class="col-12 form-group" id="tabs" style="overflow: hidden">
				<ul>
					<li><a href="<?php echo $_SERVER["REQUEST_URI"]; ?>#tabs-1"><?php echo Trans::Get("content"); ?></a></li>
					<li><a href="<?php echo $_SERVER["REQUEST_URI"]; ?>#tabs-2"><?php echo Trans::Get("comment"); ?></a></li>
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
                        <div class="col-6">
                            <dt><?php echo Trans::Get("comment"); ?></dt>
                            <dd>
								<?php
									$comment = new \UI\Html\TextArea();
									$comment->name = "comment";
									$comment->id = $_GET["page"]."_comment";
									$comment->SetValue($fuelConsumption->comment);

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