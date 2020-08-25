<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("true_read");
?>
  <style>
   .quantity_page_style { 
    font-size: 12px; 
    color: #333366; 
   }
   .file_count, .sh_guid, .sh_qty__ins, .step_time {
   	color: #f0f; 
   }
   .file_name, .id_prod, .sh_id {
   	color: #00f; 
   }
   .all_items, .sh_qty, .h_counter {
   	color: #c30; 
   }
   .repeat {
   	margin-top: 20px;
   	color: #0f0; 
   	font-weight: 600;
   	font-size: 14px; 
   	float: left;
   }
  </style>

<main class="catalog-page category-catalog-page quantity_page_style centering" id="start">

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

$glob = glob("/var/www/sibirix2/data/www/ohotaktiv.ru/obmen_files/quantity/*.xml");

echo "<p class='file_count'>file_count: ".count($glob)."</p><br>";

$step_time = $_GET['step_time'];

$all_time = $_GET['all_time'];

$file_count = $_GET['file_c'];

$h_counter = $_GET['h_counter']; // start h_counter = 0

$xml = new XMLReader();

$q_file = $glob[$file_count];

$xml->open($q_file);
$assoc = xml2assoc($xml);

echo "<p class='file_name'>file: ".$q_file."</p>";
/* *** */
$load_time_start = mktime();

$all_items = count($assoc[0]['val']);
$all_files = count($glob);

$end = $all_items; //$all_items;
$step = 10;

echo "<p class='all_items'>all items: ".$all_items."</p>";

while ($h_counter < $end) {

	$code = $assoc[0]['val'][$h_counter]['atr']['GUID'];
	$results = $DB->Query("SELECT IBLOCK_ELEMENT_ID FROM b_iblock_element_property WHERE VALUE='$code' AND DESCRIPTION='Код'");
		while ($row = $results->Fetch())
		{
		$res = CIBlockElement::GetList(array(), array('IBLOCK_ID' => 10, 'ID' => $row['IBLOCK_ELEMENT_ID'], 'SITE_ID' => "s1"));
        $item = $res->Fetch();
			if (!empty($item['ID']) && $item['ACTIVE'] = 'Y') { 
			$el = $item['ID']; 
			echo "<p class='id_prod'>ID PROD: ".$el."</p>";
			}
		}

	$file_c = 0;

	while (count($assoc[0]['val'][$h_counter]['val'][0]['val']) > $file_c) {

		$sh_guid = $assoc[0]['val'][$h_counter]['val'][0]['val'][$file_c]['atr']['GUID']; // Если гуид БД-2, то плюс в гуид БД-1

		/* Quantina Mirage Merge */
		/* Duplcate mirror store. Exchange guids */
	  if ($sh_guid == '4b3688d8-10af-11e8-80db-c86000606f92') { // Если НН Лескова БД-2
	$sh_guid = 'ee10dd60-a700-489c-ac7d-bb5504a74510'; 	 // + guid БД-1
} elseif ($sh_guid == '38cb753a-88c3-11e8-80e4-c86000606f92') { // Если НН Бекетова БД-2
	$sh_guid = '3934afd9-db2e-4e40-be95-232d6c1c8947'; 	 // + guid БД-1
} elseif ($sh_guid == '84d58d66-88c5-11e8-80e4-c86000606f92') { // Если НН Казан. шоссе БД-2
	$sh_guid = '8337fbada-4a69-48ff-a960-552966d2eba0'; 	 // + guid БД-1
} elseif ($sh_guid == 'cd840965-b4d3-11e8-80eb-c86000606f92') { // Если НН Культуры БД-2
	$sh_guid = 'e2d59019-086b-44ac-b757-c5867e708ad5'; 	 // + guid БД-1
} elseif ($sh_guid == '88abc5c0-b724-11e8-80eb-c86000606f92') { // Если НН Пр-т Ленина БД-2
	$sh_guid == '87f3655d-3ccc-45f5-a70e-57f92d29c202'; 	 // + guid БД-1
} elseif ($sh_guid == '17f7624a-09fb-11ea-8101-c86000606f92') { // Если Владивосток Кораб. наб. БД-2
	$sh_guid = '91e54f46-e4ed-4ad8-aad7-1ec6bcd2514e'; 	 // + guid БД-1
} elseif ($sh_guid == '8f85ce56-646a-11ea-8106-c86000606f92') { // Если Сургут БД-2
	$sh_guid = '58f9dadc-7afe-4640-a2dc-b7abbb7cdc07'; 	 // + guid БД-1
} else {
	$sh_guid = $assoc[0]['val'][$h_counter]['val'][0]['val'][$file_c]['atr']['GUID']; // Из файла
}
		echo "<p class='sh_guid'>sh_guid: ".$sh_guid."</p><br>";
		/* One GUID only here */
		$sh_qty = $assoc[0]['val'][$h_counter]['val'][0]['val'][$file_c]['atr']['Quantity'];
		echo "<p class='sh_qty'>sh_qty: ".$sh_qty."</p><br>";
		$results_sh_id = $DB->Query("SELECT ID FROM b_catalog_store WHERE XML_ID='$sh_guid'");

			while ($row_sh_id = $results_sh_id->Fetch()) {
			$shop_id = $row_sh_id['ID'];
			echo "<p class='sh_id'>sh_id: ".$shop_id."</p><br>";
			//print_r($row_sh_id);
			}

		$results_qty = $DB->Query("SELECT * FROM b_catalog_store_product WHERE STORE_ID='$shop_id' AND PRODUCT_ID='$el'"); //$el //$code
			if ($row_qty = $results_qty->Fetch()) {
				if ($sh_qty !== $row_qty['qty']) { // Если количества в файле и в таблице не равны
				$results_upd = $DB->Query("UPDATE b_catalog_store_product SET AMOUNT = '$sh_qty' WHERE STORE_ID = '$shop_id' AND PRODUCT_ID = '$el'");
				if ($results_upd) {
					echo "<p class='sh_qty__ins'>sh_qty_INSERT: ".$sh_qty."</p>";
				}
				}
			} else {
			$results_insert = $DB->Query("INSERT INTO b_catalog_store_product (PRODUCT_ID, AMOUNT, STORE_ID) VALUES ('$el', '$sh_qty', '$shop_id')");
			// Если нет инфы, значит нечего изменять
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

	echo "<p class='h_counter'>h_counter: ".$h_counter."</p>";
	echo "<p class='step_time'>step_time: ".$load_time."</p>";
	echo "all_time: ";
	echo "<br>";
	echo "<a class='repeat' href='/12dev/quantity/index.php?h_counter=0&file_c=0&step_time=0&all_time=0'>repeat</a><br>";

?>

</main>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
