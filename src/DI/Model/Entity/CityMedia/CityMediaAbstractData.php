<?php
namespace DI\Model\Entity\CityMedia;

/**
 * City-Media addonhoz egy abstractdata ami prefixe ct_
 */
class CityMediaAbstractData extends \DI\Model\Entity\AbstractData{

	/**
	 * Visszatér a dbprefixszel
	 * Felülírható így más namespace-ekben is kezelni tud táblákat
	 * @return string
	 */
	public static function GetDbPrefix(): string{
		return "ct_";
	}

    /**
     *
     * Előkészíti a bizonylat univerzális konveriót.
     *
     * @param string $destObjectNS Cél bizonylat névtér.
     * @return object
     * @throws \Exception
     */
    public function PrepareObject(string $destObjectNS){
        $destObj = $this::CloneDocument($destObjectNS, $this);

        return $destObj;
    }

}