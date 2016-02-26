<?php

/**
 * AXELS ICECAST STREAMSTATUS CLASS<br>
 * <br>
 * THERE IS NO WARRANTY FOR THE PROGRAM, TO THE EXTENT PERMITTED BY APPLICABLE <br>
 * LAW. EXCEPT WHEN OTHERWISE STATED IN WRITING THE COPYRIGHT HOLDERS AND/OR <br>
 * OTHER PARTIES PROVIDE THE PROGRAM ?AS IS? WITHOUT WARRANTY OF ANY KIND, <br>
 * EITHER EXPRESSED OR IMPLIED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED <br>
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE. THE <br>
 * ENTIRE RISK AS TO THE QUALITY AND PERFORMANCE OF THE PROGRAM IS WITH YOU. <br>
 * SHOULD THE PROGRAM PROVE DEFECTIVE, YOU ASSUME THE COST OF ALL NECESSARY <br>
 * SERVICING, REPAIR OR CORRECTION.<br>
 * <br>
 * --------------------------------------------------------------------------------<br>
 * <br>
 * --- HISTORY:<br>
 * 2013-11-14  1.0  first time in the wild.<br>
 * --------------------------------------------------------------------------------<br>
 * @version 1.0
 * @author Axel Hahn
 * @link http://www.axel-hahn.de/
 * @license GPL
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL 3.0
 * @package IcecastStatus
 */
class IcecastStatus {

    /**
     * url to the sttaus.xsl of an icecast server
     * @var string
     */
    private $_sStatusUrl = false;

    /**
     * array with all parsed data of all streams
     * @var array
     */
    private $_aStatus = array();

    /**
     * 
     * @param string $sUrl  urls to the status.xsl
     * @return boolean
     */
    public function __construct($sUrl) {
        $this->_sStatusUrl = $sUrl;
        $this->_getIcecastStatus();
        return true;
    }

    /**
     * read icecast status.xsl and create an array with all status infos.
     * @return boolean
     */
    private function _getIcecastStatus() {
        $this->_aStatus = array();
        $sStatusHtml=@file_get_contents($this->_sStatusUrl);

        if (!$sStatusHtml){
            return false;
        }

        // Loop ueber alle Mountpoints
        $streamdivs = explode('<div class="newscontent">', $sStatusHtml);
        if (!count($streamdivs)){
            return false;
        }
        foreach ($streamdivs AS $streamdiv) {
            @preg_match("/<h3>(.*)<\/h3>/", $streamdiv, $aMatches);
            if ($aMatches && $aMatches[1]) {
                $aTmp = explode(" ", $aMatches[1]);
                $sStation = $aTmp[2];

                $tables = explode("<table", $streamdiv);
                foreach ($tables AS $table) {
                    $sSearch = preg_replace("/\W/", ".*", "Stream Title:");
                    if (preg_match("/(<td(.*)>" . $sSearch . "<\/td>)/", $table)) {
                        $rows = explode("<tr>", $table);
                        foreach ($rows AS $row) {
                            if (preg_match_all("/<td.*>(.*)<\/td>/siU", $row, $matches)) {
                                $type = trim(str_replace(":", "", $matches[1][0]));
                                $value = $matches[1][1];
                                $this->_aStatus[$sStation][$type] = $value;
                            }
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * get a flat array with all mount points
     * @return array
     */
    public function getMounts() {
        return array_keys($this->_aStatus);
    }

    /**
     * get all icecast data as an array.<br>
     * With a a given mount you get data of all mounts; with an existing
     * mount you get the data of this mount only.<br>
     * <br>
     * <code>
     * require_once("icecaststatus.class.php");<br>
     * $o = new IcecastStatus($sStatusUrl);<br>
     * var_dump($o->getData("/m/drsvirus/aacp_32"));<br>
     * </code>
     * returns<br>
     * array (size=10)<br>
     *   'Stream Title' => string 'SRF Virus' (length=9)<br>
     *   'Stream Description' => string 'Radio SRF Virus - Radio, aber anders - srfvirus.ch...' (length=111)<br>
     *   'Content Type' => string 'audio/aacp' (length=10)<br>
     *   'Mount Start' => string '08/Oct/2013:15:16:39 +0200' (length=26)<br>
     *   'Bitrate' => string '32 ' (length=3)<br>
     *   'Current Listeners' => string '0' (length=1)<br>
     *   'Peak Listeners' => string '1' (length=1)<br>
     *   'Stream Genre' => string 'Pop Music' (length=9)<br>
     *   'Stream URL' => string '<a target="_blank" href="http://srfvirus.ch ">http://srfvirus.ch </a>' ... (length=69)<br>
     *   'Current Song' => string 'Radio SRF Virus - Radio, aber anders - srfvirus.ch ...' (length=115)
     * 
     * @param string $sMount name of an existing mount (use getMounts() method before); optional
     * @return array
     */
    public function getData($sMount = false) {
        if (!$sMount) {
            return $this->_aStatus;
        }
        if (!array_key_exists($sMount, $this->_aStatus)) {
            return false;
        }
        return $this->_aStatus[$sMount];
    }

}
