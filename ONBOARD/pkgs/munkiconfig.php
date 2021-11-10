<?php

include_once "/etc/makemunki/readconfig.php";

$gconf = new ReadConfig('/etc/makemunki/config');
set_include_path(get_include_path() . ':'.$gconf->main->codehome);
include_once "mtm.php";
include_once "munkipackage.php";

$mtm = new MTM;

if(!isset($_GET['ident'])) {
    $mtm->send_404_page("No computer ident sent");
    exit(0);
}


$ident =  trim(base64_decode($_GET['ident']));
//$ident = "0123456789AB=C";

if($ident === "") {
    $mtm->send_404_page("No computer ident sent");
    exit(0);
}

try {
    $clientpkg = $mtm->generate_cert($ident);
}
catch(exception $e) {
    file_put_contents("/var/storage/phpsessions/makepkgerror",$e->getMessage());
    $mtm->send_404_page($e->getMessage());
    exit(0);
}

if(is_array($clientpkg) && array_key_exists('error',$clientpkg)) {
    print "Got an error\n";
    $mtm->send_404_page($clientpkg['error']);
    exit(0);
}

$mp = new MunkiPackage;
$package = $mp->gen_package($clientpkg->ID);

if($package === "") {
    $mtm->send_404_page('Error creating custom package');
    exit(0);
}

header('Content-Type: Application/octet-stream');
header('Content-Description: File Transfer');
header('Content-Disposition: attachment; filename="munkiconfig.pkg"');
header('Cache-Control: must-revalidate');

echo $package;

exit(0);
