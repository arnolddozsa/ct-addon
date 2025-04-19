<?php

namespace UI\View\Html\CityMedia;

use Control\Administration\Definitions\Localization\Translate\Translate;
use Control\Application;
use UI\Html\InputCheckBox;
use UI\Html\InputHidden;
use UI\Html\Link;

/**
 * City-Media Térkép nézet osztály.
 */
class Map implements \UI\View\IView
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
        $sqlLastConnection = "SELECT createDate AS lastConnectionDateTime FROM $centerDb.ct_telemetry 
WHERE warehouseId = ?
AND type = 'disconnect'
ORDER BY createDate DESC
LIMIT 1";

        //#--Előző hónap forgalma
        $sqlPrevMonth = "SELECT COUNT(id) AS quantity FROM $centerDb.ct_telemetry 
WHERE warehouseId = ?
AND type = 'sales'
AND YEAR(piLogCreateDate) = YEAR(CURRENT_TIMESTAMP)
AND MONTH(piLogCreateDate) = MONTH(CURRENT_TIMESTAMP)-1;";

        //#--Elmúlt 7 nap forgalma
        $sqlPrevSevenDays = "SELECT COUNT(id) AS quantity FROM $centerDb.ct_telemetry 
WHERE warehouseId = ?
AND type = 'sales'
AND DATE(piLogCreateDate) >= DATE(DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 7 DAY))
AND DATE(piLogCreateDate) <= DATE(CURRENT_TIMESTAMP);";

        //#--Előző nap
        $sqlPrevDay = "SELECT COUNT(id) AS quantity FROM $centerDb.ct_telemetry 
WHERE warehouseId = ?
AND type = 'sales'
AND DATE(piLogCreateDate) >= DATE(DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY))
AND DATE(piLogCreateDate) <= DATE(DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY));";

        //#--Mai nap
        $sqlToDay = "SELECT COUNT(id) AS quantity FROM $centerDb.ct_telemetry 
WHERE warehouseId = ?
AND type = 'sales'
AND DATE(piLogCreateDate) = DATE(CURRENT_TIMESTAMP);";

        ?>


        <div class="row">
            <div class="col-4 input-group">
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
        </div>

        <div class="container-fluid">
            <ul class="list-group list-group-flush sticky-top bold">

                <li class="list-group-item list-group-item-action " data-id="1">
                    <div class="container-fluid">
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
                                        <div class="col text-right">
                                            Előző hó



                                        </div>
                                        <div class="col text-right">
                                            Előző 7 nap

                                        </div>
                                        <div class="col text-right">
                                            Előző nap
                                        </div>
                                        <div class="col text-right">
                                            Mai nap
                                        </div>
                                    </div>
                                </div>
                            </div>





                            <div class="col-md-4 row">

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


                    <ul uk-accordion class="p-0">



                        <?php

                        $sql = "SELECT * FROM $centerDb.nubes_warehouse WHERE LENGTH(U_serialNumber) > 0";

                        //$warehouseList = \DI\Model\Entity\Administration\Definitions\StockManagement\Warehouse\Warehouse::GetObjectList(array("U_serialNumber|>" => 0));
                        $warehouseList = $dao->GetObjects($sql, [], "\\DI\\Model\\Entity\\Administration\\Definitions\\StockManagement\\Warehouse\\Warehouse");


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
                                $region = \DI\Model\Entity\Administration\Definitions\Localization\Country\Region::Get(array("id" => $key));
                                $title = $region->name;
                            }

                        ?>


                            <li>


                                <a class="uk-accordion-title" href="#">
                                    <?php
                                    $checkbox = new InputCheckBox();
                                    $checkbox->name = "isGroupOnMap";

                                    echo $checkbox->Render();


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
                                <div class="uk-accordion-content">
                                    <ul class="list-group list-group-flush">

                                        <?php
                                        foreach ($whCat as $warehouse) {
                                        ?>
                                            <li class="list-group-item list-group-item-action " data-id="<?php echo $warehouse->id; ?>">
                                                <div class="container-fluid">
                                                    <div class="row">

                                                        <div class="col-md-3">
                                                            <?php
                                                            $checkbox = new InputCheckBox();
                                                            $checkbox->name = "isOnMap";

                                                            echo $checkbox->Render();

                                                            ?>
                                                            <span>

                                                                <?php
                                                                $link = \UI\View\Pub\Pages\Administration\Definitions\StockManagement\Warehouse\Warehouse::GenerateLink();
                                                                $link->SetModel(array("id" => $warehouse->id));
                                                                $link->innerHTML = $warehouse->code . " - " . $warehouse->name . ' <i class="icon-external-link"></i>';

                                                                echo $link->Render();


                                                                $id = new InputHidden();
                                                                $id->name = "id";
                                                                $id->SetValue($warehouse->id);
                                                                echo $id->Render();

                                                                $codename = new InputHidden();
                                                                $codename->name = "codename";
                                                                $codename->SetValue($warehouse->code . " - " . $warehouse->name);
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

                                                        $lastConnection = $dao->GetObjects($sqlLastConnection, array($warehouse->id))[0];

                                                        $prevMonth = $dao->GetObjects($sqlPrevMonth, array($warehouse->id))[0];

                                                        $prevSevenDays = $dao->GetObjects($sqlPrevSevenDays, array($warehouse->id))[0];

                                                        $prevDay = $dao->GetObjects($sqlPrevDay, array($warehouse->id))[0];

                                                        $toDay = $dao->GetObjects($sqlToDay, array($warehouse->id))[0];
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
                                                            <div class="d-md-none">
                                                                <div class="row">
                                                                    <div class="col text-right">
                                                                        Előző hó



                                                                    </div>
                                                                    <div class="col text-right">
                                                                        Előző 7 nap

                                                                    </div>
                                                                    <div class="col text-right">
                                                                        Előző nap
                                                                    </div>
                                                                    <div class="col text-right">
                                                                        Mai nap
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="">
                                                                <div class="row">
                                                                    <div class="col text-right">
                                                                        <?php

                                                                        $ui = new \UI\Html\StaticNumber();
                                                                        $ui->precision = 0;
                                                                        $ui->SetModel($prevMonth->quantity);
                                                                        echo $ui->Render();

                                                                        ?>
                                                                    </div>
                                                                    <div class="col text-right">
                                                                        <?php
                                                                        $ui = new \UI\Html\StaticNumber();
                                                                        $ui->precision = 0;
                                                                        $ui->SetModel($prevSevenDays->quantity);
                                                                        echo $ui->Render();
                                                                        ?>
                                                                    </div>
                                                                    <div class="col text-right">
                                                                        <?php
                                                                        $ui = new \UI\Html\StaticNumber();
                                                                        $ui->precision = 0;
                                                                        $ui->SetModel($prevDay->quantity);
                                                                        echo $ui->Render();
                                                                        ?>
                                                                    </div>
                                                                    <div class="col text-right">
                                                                        <?php
                                                                        $ui = new \UI\Html\StaticNumber();
                                                                        $ui->precision = 0;
                                                                        $ui->SetModel($toDay->quantity);
                                                                        echo $ui->Render();
                                                                        ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>





                                                        <div class="col-md-4 row">

                                                            <div class="col">
                                                                <script>
                                                                    $(function() {
                                                                        var piItem = $("[data-id=<?php echo $warehouse->id; ?>]");
                                                                        <?php
                                                                        $root_web = \Control\Application::GetInstance()->config->root_web;
                                                                        $root_web = rtrim($root_web, "/");
                                                                        $root_web = \explode(":", $root_web);
                                                                        $root_web = $root_web[0] . ":" . $root_web[1];
                                                                        ?>
                                                                        var url = new URL(window.location.href);
                                                                        url.port = (url.protocol == "https:" ? 40006 : 40005);
                                                                        url.pathname = "";

                                                                        var socket = io(`${url.toString()}`, {
                                                                            auth: {
                                                                                token: "abc"
                                                                            },
                                                                            query: {
                                                                                room: <?php echo !empty($warehouse->U_serialNumber) ?  '"' . $warehouse->U_serialNumber . '"' : "null"; ?> ||
                                                                                    null
                                                                            }
                                                                        });


                                                                        socket.on("connect", () => {
                                                                            piItem.find(".isActive").removeClass(
                                                                                    "text-white text-success text-danger")
                                                                                .addClass("text-black-50");
                                                                        });

                                                                        socket.on("disconnect", () => {
                                                                            piItem.find(".isActive").removeClass(
                                                                                    "text-black-50 text-success text-danger")
                                                                                .addClass("text-white");
                                                                        });

                                                                        socket.on("piconnected", (data) => {
                                                                            if (data.connected) {
                                                                                piItem.find(".isActive").removeClass(
                                                                                        "text-black-50 text-white text-danger")
                                                                                    .addClass("text-success");

                                                                                socket.emit("getInfo");

                                                                            } else {
                                                                                piItem.find(".isActive").removeClass(
                                                                                        "text-black-50 text-success text-white")
                                                                                    .addClass("text-danger");
                                                                            }
                                                                        });

                                                                        socket.on("infoChange", (data) => {

                                                                            piItem.find(".coinCount").text(data.coinCount);
                                                                        });

                                                                    });
                                                                </script>
                                                                <span class="p-3 isActive text-white" rel="tooltip" data-toggle="tooltip" data-html="true" title="Fehér: Szerver offline, Fekete: Szerver online, Zöld: Kapcsolódva, Piros: Nincs kapcsolat">
                                                                    <i class="fa fa-wifi" aria-hidden="true"></i>
                                                                </span>
                                                            </div>
                                                            <div class="col">
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
                                                            <div class="col">
                                                                <?php

                                                                $link = new Link();
                                                                // $link->href = "https://www.google.com/maps/dir/?api=1&destination=".$warehouse->latitude.",".$warehouse->longitude."&travelmode=car";
                                                                $link->href = "/CityMedia/Warehouse/?id=" . $warehouse->id;
                                                                $link->innerHTML = 'Karbantartás <i class="icon-external-link"></i>';
                                                                $link->class[] = "p-3";
                                                                echo $link->Render();

                                                                ?>
                                                            </div>
                                                            <div class="col">
                                                                <span class="coinCount badge badge-primary  badge-pill">?</span>
                                                            </div>
                                                        </div>




                                                    </div>


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
        <div class="container-fluid">
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
                        console.log(regex);



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




                });
            </script>


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
