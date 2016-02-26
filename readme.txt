###############################################################################

icecaststatus.class.php

###############################################################################

AXELS ICECAST STATUS PARSER
V1.0

http://www.axel-hahn.de/php_contentcache.php
License: GNU/GPL v3

2013-11-14  1.0  first time in the wild.
--------------------------------------------------------------------------------


###############################################################################

--- typical usage:

    $o = new IcecastStatus("http://[your-icecast-server]/status.xsl");  
      
    var_dump($o->getMounts());  // lists all mountpoints
      
    $a = $o->getData();         // show all details of all mountpoints
    $a = $o->getData("[Name]"); // show all details of a given mountpoint
	

see interactive demo:
http://www.axel-hahn.de/srfradio/

###############################################################################
