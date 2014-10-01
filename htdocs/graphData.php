<?php
//start the json data in the format Google Chart js/API expects to receieve it
$data = array('cols' => array(array('label' => 'Friend', 'type' => 'string'),
							  array('label' => 'Steps x 100', 'type' => 'number'),
                              array('label' => 'Active Minutes', 'type' => 'number')),
              'rows' => array());
              
//grab the file with friend/steps pairs to avoid going to db           
$bcArray = json_decode(file_get_contents('php/barchart.json'), true);

//Add each friend/step pair as row data in chart data format
foreach($bcArray as $k => $v) {
	$smallSteps = $v['steps']/100;
	$data['rows'][] = array('c' => array(array('v' => $k), array('v' => $smallSteps), array('v' => $v['active_minutes'])));
	} 
 
// encode array in json form google chart expects
$enData = json_encode($data); 

// store and return json file to main page ajax request, file write for debug 
file_put_contents('data2.json', $enData);
echo $enData;
?>