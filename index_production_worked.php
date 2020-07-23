<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("true_read");
?>

<main class="catalog-page category-catalog-page" id="start">

<?
function xml2assoc(&$xml){
    $assoc = array();
    $n = 0;
    while($xml->read()){
        if($xml->nodeType == XMLReader::END_ELEMENT) break;
        if($xml->nodeType == XMLReader::ELEMENT and !$xml->isEmptyElement){
            $assoc[$n]['name'] = $xml->name;
            if($xml->hasAttributes) while($xml->moveToNextAttribute()) $assoc[$n]['atr'][$xml->name] = $xml->value;
            $assoc[$n]['val'] = xml2assoc($xml);
            $n++;
        }
        else if($xml->isEmptyElement){
            $assoc[$n]['name'] = $xml->name;
            if($xml->hasAttributes) while($xml->moveToNextAttribute()) $assoc[$n]['atr'][$xml->name] = $xml->value;
            $assoc[$n]['val'] = "";
            $n++;               
        }
        else if($xml->nodeType == XMLReader::TEXT) $assoc = $xml->value;
    }
    return $assoc;
}

$glob = glob("/var/www/sibirix2/data/www/ohotaktiv.ru/12dev/quantity/xml/*.xml");

echo "file_count: ".count($glob)."<br>";

$step_time = $_GET['step_time'];

$all_time = $_GET['all_time'];

$file_count = $_GET['file_c'];

$h_counter = $_GET['h_counter']; // start h_counter = 0

$xml = new XMLReader();

$q_file = $glob[$file_count];

$xml->open($q_file);
$assoc = xml2assoc($xml);

echo "file: ".$q_file."<br>";
/* *** */
$load_time_start = mktime();

$all_items = count($assoc[0]['val']);
$all_files = count($glob);

$end = $all_items; //$all_items;
$step = 10;

echo "all items: ".$all_items."<br>";

while ($h_counter < $end) {

	$code = $assoc[0]['val'][$h_counter]['atr']['GUID'];
	$results = $DB->Query("SELECT IBLOCK_ELEMENT_ID FROM b_iblock_element_property WHERE VALUE='$code' AND DESCRIPTION='Код'");
		while ($row = $results->Fetch())
		{
		$res = CIBlockElement::GetList(array(), array('IBLOCK_ID' => 10, 'ID' => $row['IBLOCK_ELEMENT_ID'], 'SITE_ID' => "s1"));
        $item = $res->Fetch();
			if (!empty($item['ID']) && $item['ACTIVE'] = 'Y') { 
			$el = $item['ID']; 
			}
		}

	$file_c = 0;

	while (count($assoc[0]['val'][$h_counter]['val'][0]['val']) > $file_c) {

		$sh_guid = $assoc[0]['val'][$h_counter]['val'][0]['val'][$file_c]['atr']['GUID'];
		$sh_qty = $assoc[0]['val'][$h_counter]['val'][0]['val'][$file_c]['atr']['Quantity'];
		$results_sh_id = $DB->Query("SELECT ID FROM b_catalog_store WHERE XML_ID='$sh_guid'");

			if ($row_sh_id = $results_sh_id->Fetch()) {
			$shop_id = $row_sh_id['ID'];
			}

		$results_qty = $DB->Query("SELECT * FROM b_catalog_store_product WHERE STORE_ID='$shop_id' AND PRODUCT_ID='$el'"); //$el //$code
			if ($row_qty = $results_qty->Fetch()) {
				if ($sh_qty !== $row_qty['qty']) {
				$results_upd = $DB->Query("UPDATE b_catalog_store_product SET AMOUNT = '$sh_qty' WHERE STORE_ID = '$shop_id' AND PRODUCT_ID = '$el'");
				}
			} else {
			$results_insert = $DB->Query("INSERT INTO b_catalog_store_product (PRODUCT_ID, AMOUNT, STORE_ID) VALUES ('$el', '$sh_qty', '$shop_id')");
			}
		$file_c++;
	}

	$load_time = mktime() - $load_time_start;
	$all_time = $all_time + $load_time;
	
	$z = $h_counter % $step;

	if ($z == 0) {
		$h_counter++;
		header("refresh: 2; url=/12dev/quantity/index.php?h_counter=$h_counter&file_c=$file_count&step_time=$load_time&all_time=$all_time");
		break;
	} 

	$h_counter++;
	
}

	$text = "h_counter: ".$h_counter."\nfile_c: ".$file_count;
$fp = fopen("/var/www/sibirix2/data/www/ohotaktiv.ru/12dev/quantity/log.txt", "w"); 
	fwrite($fp, $text); 
	fclose($fp);

	if ($h_counter >= $end && $file_count < $all_files) {
	$file_count++;
	header("refresh: 2; url=/12dev/quantity/index.php?h_counter=0&file_c=$file_count&step_time=$load_time&all_time=$all_time");
	}

	echo "h_counter: ".$h_counter."<br>";
	echo "step_time: ".$load_time."<br>";
	echo "all_time: ";
	echo "<br>";
	echo "<a href='/12dev/quantity/index.php?h_counter=0&file_c=0&step_time=0&all_time=0'>repeat</a><br>";

?>

</main>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
