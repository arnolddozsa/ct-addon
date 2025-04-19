<?php

namespace UI\View\Html\CityMedia;

use Control\Administration\Definitions\Localization\Translate\Translate;
use Control\Application;
use Control\StockManagement\Item\Item;
use DI\Model\Entity\Administration\CompanyManagement\Com_Company;
use UI\Html\InputCheckBox;
use UI\Html\InputHidden;
use UI\Html\Link;
use DI\Model\Entity\Administration\SystemSettings\TableDefinitions\Field_Valid_Values as VV;
use DI\Model\Entity\StockManagement\Item\Item as ItemItem;
use UI\Html\StaticDate;
use UI\Html\StaticNumber;

/**
 * City-Media Térkép nézet osztály.
 */
class MapDashboard implements \UI\View\IView
{

    private $model;

    /**
     * @inheritDoc
     */
    public function CreateContent()
    {

        ob_start();


?>


        <h1 class="center-text"><?php echo Translate::Get("map"); ?></h1>

        <?php

        $centerDb = Application::GetInstance()->GetCompany()->db_name;

        $dao = \Control\Application::GetInstance()->GetSql();
        ?>

        <script>
            var socket;
            $(function() {

                var url = new URL(window.location.href);
                url.port = (url.protocol == "https:" ? 40006 : 40005);
                url.pathname = "";

                socket = io(`${url.toString()}`);

                socket.on("connect", () => {

                    $(".list-group-item .isActive").removeClass(
                            "text-white text-success text-danger")
                        .addClass("text-black-50");
                });

                socket.on("disconnect", () => {
                    $(".list-group-item .isActive").removeClass(
                            "text-black-50 text-success text-danger")
                        .addClass("text-white");
                });

                socket.on("piconnected", ({room, data}) => {
                    let piItem = $(`[data-room=${room}]`);
                    if (data.connected) {
                        piItem.find(".isActive").removeClass(
                                "text-black-50 text-white text-danger")
                            .addClass("text-success");

                        socket.emit("getInfo", {
                            room: data.room
                        });

                        socket.emit("getErrors", {
                            room: data.room
                        }, async (data, err) => {
                            if (err) {
                                console.log(err);
                                return;
                            }



                            if (data.data.length) {
                                piItem.find(".hasErrors").removeClass("d-none");
                                var content = data.data.filter((o) => {
                                    return o.errorType != "notRolled";
                                }).map((e) => {
                                    return `${e.errorType}: ${e.description}   `
                                }).join();
                                console.log(content);
                                if (content && content.length) {

                                    await Notification.requestPermission();
                                    var n = new Notification(`${room} hibák!`, {
                                        body: `${room} hibák: ${content}`,
                                        icon: "https://citymedia.synology.me/vendor/mhzq-com/nubes-ui/css/icons/nubes2.ico"
                                    });
                                }
                            } else {
                                piItem.find(".hasErrors").addClass("d-none");
                            }

                        });

                    } else {
                        piItem.find(".isActive").removeClass(
                                "text-black-50 text-success text-white")
                            .addClass("text-danger");
                    }
                });

                socket.on("infoChange", ({room, data}) => {
                    let piItem = $(`[data-room=${room}]`);

                    piItem.find(".coinCount").text(data.salesInfo.coinCount);

                    if (parseFloat(data.coinCount) <= 100) {
                        piItem.find(".coinCount").addClass("bg-danger");
                    } else {
                        piItem.find(".coinCount").removeClass("bg-danger");
                    }

                });

                socket.on("rawError", async ({room, err}) => {
                    switch (err.errorType) {
                        case "stuck":
                            await Notification.requestPermission();
                            var n = new Notification(`${room} beragadt!`, {
                                body: `${room} beragadt: ${err.description}`,
                                icon: "https://citymedia.synology.me/vendor/mhzq-com/nubes-ui/css/icons/nubes2.ico"
                            });
                            piItem.find(".hasErrors").removeClass("d-none");
                            break;
                    }
                });

            })
        </script>
        <div class="row">
            <div class="col-md-4 input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fa icon-search"></i></span>
                </div>
                <?php
                $searchItem = new \UI\Html\InputText();
                $searchItem->id = $_GET["page"] . "_searchWarehouse";
                $searchItem->placeholder = "Gép keresése";
                echo $searchItem->Render();
                ?>
            </div>
            <div class="col-md-4 input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fa icon-search"></i>Cégnév</span>
                </div>
                <?php
                try {
                    $vv = VV::GetValidValuesFromDataBaseToSelect("nubes", "nubes_warehouse", "U_companyName");

                    if (count($vv)) {
                        $input = new \UI\Html\Select();
                        $input->id = $_GET["page"] . "_searchCompany";
                        $input->SetValidValues($vv);
                        echo $input->Render();
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                }
                ?>
            </div>
        </div>

        <div class="container-fluid">
            <ul class="list-group list-group-flush sticky-top bold">

                <li class="list-group-item list-group-item-action p-0" data-id="1">
                    <div class="container-fluid p-0">
                        <div class="row">

                            <div class="col-md-3">
                                Megnevezés
                            </div>
                            <div class="col-md-1  d-md-block d-none ">
                                Lekapcsolódva ekkor
                            </div>
                            <div class="col-md-4">

                                <div class="">
                                    <div class="row">
                                        <div class="col  text-center text-md-right">
                                            Előző hó



                                        </div>
                                        <div class="col  text-center text-md-right">
                                            Előző 7 nap

                                        </div>
                                        <div class="col  text-center text-md-right">
                                            Előző nap
                                        </div>
                                        <div class="col  text-center text-md-right">
                                            Ma
                                        </div>
                                    </div>
                                </div>
                            </div>





                            <div class="col-md-4">
                                <div class="row">

                                    <div class="col">Kapcsolat


                                    </div>
                                    <div class="col">Útvonal



                                    </div>
                                    <div class="col">

                                        Karbantartás


                                    </div>
                                    <div class="col">
                                        Gépben lévő db
                                    </div>
                                    <div class="col">
                                        Raktáron
                                    </div>
                                    <div class="col">
                                        Rendelés
                                    </div>
                                </div>
                            </div>




                        </div>

                    </div>

                </li>



            </ul>

            <ul class="p-0">
                <li>



                    <dt>
                        <?php
                        // $checkbox = new InputCheckBox();
                        // $checkbox->name = "isAllOnMap";

                        // echo $checkbox->Render();


                        // echo "Összes";

                        ?>
                    </dt>






                    <?php

                    // $sql = "SELECT * FROM 
                    //     (";

                    //@TODO egyenlőre csak a TEST-ben dolgozunk és az U_companyName mezőt használjuk

                    // $companies = Com_Company::GetObjectList();

                    // $companies = array_filter($companies, function ($o) use ($dao) {
                    //     $s = "SELECT column_name FROM information_schema.`COLUMNS` WHERE TABLE_SCHEMA = '$o->db_name' AND TABLE_NAME = 'nubes_warehouse' AND COLUMN_NAME = 'U_serialNumber'  ";
                    //     return count($dao->GetObjects($s)) > 0;
                    // });

                    $companies = VV::GetValidValuesFromDataBaseToSelect("nubes", "nubes_warehouse", "U_companyName");
                    $companies = array_merge(array(array("value" => null, "description" => "Összes gép")), $companies);
                    ?>
                    <ul uk-accordion class="p-0">
                        <?php
                        foreach ($companies as $one) {
                            $sql = " SELECT '" . ($dao->GetDbName()) . "' AS company, T1.* FROM nubes_warehouse AS T1 WHERE LENGTH(U_serialNumber) > 0 AND (:U_companyName IS NULL OR U_companyName = :U_companyName)"
                            // $sql = " SELECT '$one->db_name' AS company, T1.* FROM $one->db_name.nubes_warehouse AS T1 WHERE LENGTH(U_serialNumber) > 0 "
                            // UNION ALL";
                        ?>
                            <li>
                                <a class="uk-accordion-title" href="#">


                                    <?php
                                    echo $one["description"];
                                    ?>
                                </a>
                                <?php

                                ?>

                                <div class="uk-accordion-content pl-3">
                                    <ul uk-accordion>

                                        <?php
                                        $warehouseList = $dao->GetObjects($sql, array(":U_companyName" => $one["value"]));


                                        $warehouseCategories = array();
                                        foreach ($warehouseList as $wh) {

                                            if ($wh->regionId < 1) {
                                                if (!isset($warehouseCategories[0])) {
                                                    $warehouseCategories[0] = array();
                                                }

                                                $warehouseCategories[0][] = $wh;
                                            } else {

                                                if (!isset($warehouseCategories[$wh->regionId])) {
                                                    $warehouseCategories[$wh->regionId] = array();
                                                }

                                                $warehouseCategories[$wh->regionId][] = $wh;
                                            }
                                        }


                                        foreach ($warehouseCategories as $key => $whCat) {
                                            if ($key == 0) {
                                                $title = "Nem kategorizált";
                                            } else {
                                                $region = $dao->GetObjects("SELECT * FROM $whCat->company.nubes_region WHERE id = :id", [":id" => $key], "\\DI\\Model\\Entity\\Administration\\Definitions\\Localization\\Country\\Region")[0];
                                                $title = $region->name;
                                            }

                                        ?>


                                            <li>

                                                <?php
                                                $checkbox = new InputCheckBox();
                                                $checkbox->name = "isGroupOnMap";

                                                echo $checkbox->Render();
                                                ?>
                                                <a class="uk-accordion-title" href="#">


                                                    <?php
                                                    echo $title;

                                                    ?>
                                                </a>
                                                <span>
                                                    <?php
                                                    $id = new InputHidden();
                                                    $id->name = "city";
                                                    $id->SetValue($title);
                                                    echo $id->Render();
                                                    ?>
                                                </span>
                                                <div class="uk-accordion-content pl-1">
                                                    <ul class="list-group list-group-flush">

                                                        <?php
                                                        foreach ($whCat as $warehouse) {
                                                            echo $this->RenderWarehouseItem($warehouse);
                                                        }
                                                        ?>
                                                    </ul>
                                                </div>
                                            </li>

                                        <?php
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </li>
                        <?php
                        }
                        ?>
                    </ul>
                    <?php

                    // $sql = preg_replace("/(UNION ALL)$/", "", $sql);

                    // $sql .= ") AS T";

                    //$warehouseList = \DI\Model\Entity\Administration\Definitions\StockManagement\Warehouse\Warehouse::GetObjectList(array("U_serialNumber|>" => 0));
                    ?>
                </li>

            </ul>

        </div>
        <?php
        $companyInfo = \Control\Application::GetInstance()->GetCompany()->GetCompanyInfo();
        $googleMapsApiKey = $companyInfo->googleMapsApiKey;

        if (empty($googleMapsApiKey)) {

            $notification = new \UI\Html\Notification();
            $notification->SetTitle("Térkép");
            $notification->SetMessageType("warning");

            $notification->SetMessage("A térkép használatához állítsd be a googleMapsApiKey kulcsot a cégbeállításokban!");

            echo $notification->Render();
        } else {
        ?>
            <script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo $companyInfo->googleMapsApiKey; ?>&callback=myMap">
            </script>

            <!--        <script async defer src="https://maps.googleapis.com/maps/api/js?sensor=false&callback=myMap"></script>-->
        <?php
        }

        ?>
        <div class="container-fluid p-0">
            <p><i class="fas fa-info-circle"></i> A térkép jelzőjén duplakattintásra is elérhető a karbantartási lap
            </p>
        </div>
        <div class="embed-responsive embed-responsive-16by9">


            <div class="embed-responsive-item" id="googleMap">

            </div>


            <script>
                function getLocation() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(showPosition, showError);
                    } else {
                        var mapOptions = {
                            center: new google.maps.LatLng(46.5, 20),
                            zoom: 10,
                            mapTypeId: google.maps.MapTypeId.HYBRID
                        };
                        window.map = new google.maps.Map(document.getElementById("googleMap"), mapOptions);
                    }

                }

                function showPosition(position) {
                    var mapOptions = {
                        center: new google.maps.LatLng(position.coords.latitude, position.coords.longitude),
                        zoom: 10,
                        mapTypeId: google.maps.MapTypeId.HYBRID
                    };
                    window.map = new google.maps.Map(document.getElementById("googleMap"), mapOptions);
                }


                function showError(error) {
                    var mapOptions = {
                        center: new google.maps.LatLng(46.5, 20),
                        zoom: 10,
                        mapTypeId: google.maps.MapTypeId.HYBRID
                    };
                    window.map = new google.maps.Map(document.getElementById("googleMap"), mapOptions);

                }

                function myMap() {
                    getLocation();
                }
            </script>



            <script>
                $(function() {
                    //ebben tároljuk a jelölőket
                    var gmarkers = [];


                    function HandleMarkers(li, map) {
                        //checkbox
                        var input = $(li).find("input[type=checkbox][name=isOnMap]");

                        //raktár szélesség, hosszúság, kód-név, cím
                        var lat = $($(li).find("span input[name=latitude]")[0]).val();
                        var lng = $($(li).find("span input[name=longitude]")[0]).val();
                        var codename = $($(li).find("span input[name=codename]")[0]).val();
                        var id = $($(li).find("span input[name=id]")[0]).val();


                        //raktár koordináta objektum
                        var latLng = {
                            lat: parseFloat(lat),
                            lng: parseFloat(lng)
                        };

                        //ha bepipálja, rátesszük a jelölőt
                        if ($(input).is(":checked")) {
                            //rátesszük a térképre a jelölőt
                            var marker = new google.maps.Marker({
                                position: latLng,
                                map,
                                title: codename,
                                warehouseId: id
                            });

                            marker.addListener("dblclick", function() {
                                window.open("/CityMedia/Warehouse/?id=" + this.warehouseId.toString());
                            });

                            //eltároljuk a tömbben a jelölőt
                            gmarkers.push(marker);
                            //ha kiszedi a pipát, levesszük a jelölőt
                        } else {

                            //megkeressük a jelölőt
                            var res = gmarkers.find(x => {
                                return x.warehouseId === id;
                            });


                            //leszedjük a térképről
                            res.setMap(null);

                            //töröljük a tömbből
                            gmarkers = gmarkers.filter(x => {
                                return x.warehouseId !== id;
                            });


                        }
                    }


                    $.each($(document).find("input[type=checkbox][name=isOnMap]:checked"), function(i, one) {
                        var li = $(one).closest("li");

                        HandleMarkers(li, window.map);

                    });

                    $(document).find("input[type=checkbox][name=isOnMap]").on("change", function(e) {
                        e.preventDefault();

                        var li = $(this).closest("li");


                        HandleMarkers(li, window.map);
                    });


                    $(document).find("input[type=checkbox][name=isAllOnMap]").on("change", function(e) {
                        e.preventDefault();

                        var checkboxes = $(this).closest("ul").find("input[type=checkbox][name=isGroupOnMap]");
                        var isAllOnMap = $(this);

                        $.each(checkboxes, function(i, one) {

                            if ($(isAllOnMap).is(":checked")) {
                                if (!$(one).is(":checked")) {
                                    $(one).prop("checked", true);
                                    $(one).trigger("change");
                                }
                            } else {
                                if ($(one).is(":checked")) {
                                    $(one).prop("checked", false);
                                    $(one).trigger("change");
                                }
                            }


                        });



                    });


                    $(document).find("input[type=checkbox][name=isGroupOnMap]").on("change", function(e) {
                        e.preventDefault();

                        var checkboxes = $(this).closest("li").find("input[type=checkbox][name=isOnMap]");
                        var isGroupOnMap = $(this);

                        $.each(checkboxes, function(i, one) {

                            if ($(isGroupOnMap).is(":checked")) {
                                if (!$(one).is(":checked")) {
                                    $(one).prop("checked", true);
                                    $(one).trigger("change");
                                }
                            } else {
                                if ($(one).is(":checked")) {
                                    $(one).prop("checked", false);
                                    $(one).trigger("change");
                                }
                            }


                        });



                    });

                    $('[data-toggle="tooltip"]').tooltip({
                        html: true
                    });

                    $(`${window.elementID}_searchWarehouse`).on("keyup", function(e) {

                        var searchValue = this.value;

                        var warehouseList = $(document).find("ul.p-0 li.list-group-item");

                        var regex = `([\\w áéíóöőúüő-]*)(${searchValue})`;

                        $.each(warehouseList, function(i, one) {
                            //város szerint
                            var city = $(one).find("input[name=codename]").val();

                            var reg = new RegExp(regex, 'gi');

                            if (reg.test(city)) {
                                $(one).removeClass("d-none");
                            } else {
                                $(one).addClass("d-none");
                                $(one).find("input[name=isGroupOnMap]").val(0);
                                $(one).find("input[name=isGroupOnMap]").prop("checked", false);
                                $(one).find("input[name=isGroupOnMap]").trigger("change");
                            }
                        });


                    });

                    $(`${window.elementID}_searchCompany`).on("change", function(e) {

                        var searchValue = this.value;

                        var warehouseList = $(document).find("ul.p-0 li.list-group-item");



                        $.each(warehouseList, function(i, one) {
                            //város szerint
                            var companyName = $(one).find("input[name=companyName]").val();


                            if (searchValue == '' || companyName == searchValue) {
                                $(one).removeClass("d-none");
                            } else {
                                $(one).addClass("d-none");
                                $(one).find("input[name=isGroupOnMap]").val(0);
                                $(one).find("input[name=isGroupOnMap]").prop("checked", false);
                                $(one).find("input[name=isGroupOnMap]").trigger("change");
                            }
                        });


                    });




                });
            </script>


        </div>


    <?php


        $this->content = ob_get_contents();
        ob_end_clean();
    }

    private function RenderWarehouseItem($warehouse)
    {
        $dao = Application::GetInstance()->GetSql();

        $sqlLastConnection = "SELECT createDate AS lastConnectionDateTime FROM :company.ct_telemetry 
WHERE warehouseId = ?
AND type = 'disconnect'
ORDER BY createDate DESC
LIMIT 1";

        //#--Előző hónap forgalma
        $sqlPrevMonth = "SELECT COUNT(id) AS quantity FROM :company.ct_telemetry 
WHERE warehouseId = ?
AND type = 'sales'
AND YEAR(piLogCreateDate) = YEAR(CURRENT_TIMESTAMP)
AND MONTH(piLogCreateDate) = MONTH(CURRENT_TIMESTAMP)-1;";

        //#--Elmúlt 7 nap forgalma
        $sqlPrevSevenDays = "SELECT COUNT(id) AS quantity FROM :company.ct_telemetry 
WHERE warehouseId = ?
AND type = 'sales'
AND DATE(piLogCreateDate) >= DATE(DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 7 DAY))
AND DATE(piLogCreateDate) <= DATE(CURRENT_TIMESTAMP);";

        //#--Előző nap
        $sqlPrevDay = "SELECT COUNT(id) AS quantity FROM :company.ct_telemetry 
WHERE warehouseId = ?
AND type = 'sales'
AND DATE(piLogCreateDate) >= DATE(DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY))
AND DATE(piLogCreateDate) <= DATE(DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY));";

        //#--Mai nap
        $sqlToDay = "SELECT COUNT(id) AS quantity FROM :company.ct_telemetry 
WHERE warehouseId = ?
AND type = 'sales'
AND DATE(piLogCreateDate) = DATE(CURRENT_TIMESTAMP);";



        ob_start();
    ?>
        <script>
            // $(function() {
            // setInterval(async () => {

            // }, 15000);
            // });
        </script>
        <li class="list-group-item list-group-item-action p-0" data-id="<?php echo $warehouse->id; ?>" data-room="<?php echo $warehouse->U_serialNumber; ?>">
            <div class="container-fluid p-0">
                <div class="row">

                    <div class="col-md-3">
                        <?php
                        $checkbox = new InputCheckBox();
                        $checkbox->name = "isOnMap";

                        echo $checkbox->Render();

                        ?>
                        <span>

                            <?php
                            // $link = \UI\View\Pub\Pages\Administration\Definitions\StockManagement\Warehouse\Warehouse::GenerateLink();
                            // $link->SetModel(array("id" => $warehouse->id));
                            // $link->innerHTML = $warehouse->code. " - " .$warehouse->name. ' <i class="icon-external-link"></i>';

                            // echo $link->Render();

                            echo $warehouse->code . " - " . $warehouse->name;


                            $id = new InputHidden();
                            $id->name = "id";
                            $id->SetValue($warehouse->id);
                            echo $id->Render();

                            $codename = new InputHidden();
                            $codename->name = "codename";
                            $codename->SetValue($warehouse->code . " - " . $warehouse->name);
                            echo $codename->Render();

                            $codename = new InputHidden();
                            $codename->name = "companyName";
                            $codename->SetValue($warehouse->U_companyName);
                            echo $codename->Render();

                            $inputLat = new InputHidden();
                            $inputLat->name = "latitude";
                            $inputLat->SetValue($warehouse->latitude);
                            echo $inputLat->Render();

                            $inputLong = new InputHidden();
                            $inputLong->name = "longitude";
                            $inputLong->SetValue($warehouse->longitude);
                            echo $inputLong->Render();

                            ?>
                        </span>
                    </div>
                    <?php

                    $lastConnection = $dao->GetObjects(str_replace(":company", $warehouse->company, $sqlLastConnection), array($warehouse->id))[0];

                    $prevMonth = $dao->GetObjects(str_replace(":company", $warehouse->company, $sqlPrevMonth), array($warehouse->id))[0];

                    $prevSevenDays = $dao->GetObjects(str_replace(":company", $warehouse->company, $sqlPrevSevenDays), array($warehouse->id))[0];

                    $prevDay = $dao->GetObjects(str_replace(":company", $warehouse->company, $sqlPrevDay), array($warehouse->id))[0];

                    $toDay = $dao->GetObjects(str_replace(":company", $warehouse->company, $sqlToDay), array($warehouse->id))[0];
                    ?>
                    <div class="col-md-1  d-md-block d-none ">
                        <?php
                        $ui = new \UI\Html\StaticDate();
                        $ui->format = "y.m.d H:i:s";
                        $ui->SetModel($lastConnection->lastConnectionDateTime);
                        echo $ui->Render();
                        ?>
                    </div>
                    <div class="col-md-4">

                        <div class="">
                            <div class="row">
                                <div class="col text-center text-md-right">
                                    <div class="d-md-none">
                                        Előző hó
                                    </div>
                                    <div>
                                        <span class=" badge badge-primary  badge-pill">

                                            <?php

                                            $ui = new \UI\Html\StaticNumber();
                                            $ui->precision = 0;
                                            $ui->SetModel($prevMonth->quantity);
                                            echo $ui->Render();

                                            ?>
                                        </span>
                                    </div>

                                </div>
                                <div class="col text-center text-md-right">
                                    <div class="d-md-none">
                                        Előző 7 nap
                                    </div>
                                    <div>
                                        <span class=" badge badge-primary  badge-pill">
                                            <?php
                                            $ui = new \UI\Html\StaticNumber();
                                            $ui->precision = 0;
                                            $ui->SetModel($prevSevenDays->quantity);
                                            echo $ui->Render();
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col text-center text-md-right">
                                    <div class="d-md-none">
                                        Előző nap
                                    </div>
                                    <div>
                                        <span class=" badge badge-primary  badge-pill">
                                            <?php
                                            $ui = new \UI\Html\StaticNumber();
                                            $ui->precision = 0;
                                            $ui->SetModel($prevDay->quantity);
                                            echo $ui->Render();
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col text-center text-md-right">
                                    <div class="d-md-none">
                                        Ma
                                    </div>
                                    <div>
                                        <span class=" badge badge-primary  badge-pill">
                                            <?php
                                            $ui = new \UI\Html\StaticNumber();
                                            $ui->precision = 0;
                                            $ui->SetModel($toDay->quantity);
                                            echo $ui->Render();
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>





                    <div class="col-md-4">
                        <div class="uk-flex uk-flex-middle">
                            <div class="uk-flex-1 text-center">
                                <script>
                                    $(function() {

                                        let room = <?php echo !empty($warehouse->U_serialNumber) ? '"' . $warehouse->U_serialNumber . '"' : "null"; ?> || null;
                                        socket.emit("joinRoom", {
                                            room: room,
                                            isMachine: false
                                        });

                                    });
                                </script>
                                <span class="isActive text-white" rel="tooltip" data-toggle="tooltip" data-html="true" title="Fehér: Szerver offline, Fekete: Szerver online, Zöld: Kapcsolódva, Piros: Nincs kapcsolat">
                                    <i class="fa fa-wifi" aria-hidden="true"></i>
                                </span>
                                <span class="hasErrors text-warning d-none">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </span>


                            </div>
                            <div class="uk-flex-1 text-center">

                                <div>

                                    <?php
                                    $link = new Link();
                                    // $link->href = "https://www.google.com/maps/dir/?api=1&destination=".$warehouse->latitude.",".$warehouse->longitude."&travelmode=car";
                                    $link->href = "https://www.google.com/maps/dir/";
                                    $link->SetModel(array(
                                        "api" => "1",
                                        "destination" => $warehouse->latitude . "," . $warehouse->longitude,
                                        "travelmode" => "car"
                                    ));
                                    $link->innerHTML = '<i class="fa fa-map-marker" aria-hidden="true"></i>';
                                    $link->class[] = "p-3";
                                    echo $link->Render();

                                    ?>
                                </div>
                            </div>
                            <div class="">

                                <?php

                                $link = new Link();
                                // $link->href = "https://www.google.com/maps/dir/?api=1&destination=".$warehouse->latitude.",".$warehouse->longitude."&travelmode=car";
                                $link->href = "/CityMedia/Warehouse/?id=" . $warehouse->id . "&table_schema=" . $warehouse->company;
                                $link->innerHTML = 'Karbantartás <i class="icon-external-link"></i>';
                                // $link->class[] = "p-3";
                                $link->class[] = "btn";
                                $link->class[] = "btn-primary";
                                echo $link->Render();

                                ?>
                            </div>
                            <div class="uk-flex-1 text-center">
                                <div class="d-md-none">
                                    Gépben
                                </div>
                                <div>
                                    <span class="coinCount badge badge-primary  badge-pill">?</span>
                                </div>
                            </div>
                            <div class="uk-flex-1 text-center">
                                <div class="d-md-none">
                                    Raktáron
                                </div>
                                <div>
                                    <?php
                                    $quantity = 0;
                                    $openOrderQuantity = 0;
                                    try {

                                        $itemId = \DI\Model\Entity\CityMedia\Partner\Partner_Contract_Item::Get(array("warehouseId" => $warehouse->id))->itemId;

                                        $openOrderQuantity = \Control\StockManagement\Item\Item::GetOpenPurchaseOrderQuantity($itemId);
                                        $item = ItemItem::Get(array("id" => $itemId));


                                        $quantity = Item::GetQuantity($itemId);
                                        $ui = new StaticNumber();
                                        $ui->precision = 2;
                                        $ui->SetModel($quantity);
                                    ?>
                                        <span class=" badge badge-primary  badge-pill <?php echo $quantity <= $item->minimumLevel ? "bg-danger" : ""; ?>">
                                            <?php echo $ui->Render(); ?>
                                        </span>
                                    <?php
                                    } catch (\Throwable $th) {
                                    ?><span class=" badge badge-primary  badge-pill " uk-tooltip="Nincs felvíve szerződés">?</span><?php
                                                                                                                                }
                                                                                                                                    ?>
                                </div>
                            </div>
                            <div class="uk-flex-1 text-center">
                                <div class="d-md-none">
                                    Rendelés
                                </div>
                                <div>
                                    <?php

                                    $quantity = Item::GetQuantity($itemId);
                                    $ui = new StaticNumber();
                                    $ui->precision = 2;
                                    $ui->SetModel($openOrderQuantity);
                                    ?>
                                    <span class=" badge badge-primary  badge-pill ">
                                        <?php echo $ui->Render(); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>




                </div>


            </div>

        </li>



<?php

        return ob_get_clean();
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
