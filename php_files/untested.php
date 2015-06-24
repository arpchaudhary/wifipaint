<?php

require_once('./helpers.php');
init();


class Point{
	public $x;
	public $y;

	//Default values for simpler implementation
	public function __construct($x = 0, $y = 0){
		$this->x = is_numeric($x) ? $x : 0;
		$this->y = is_numeric($y) ? $y : 0;
	}

	public function __toString(){
		return "(" . $this->x . "," . $this->y . ")";
	}

	//convenience wrapper to decode from string
	//On error throws IllegalArgumentException instead of failing silently
	public function fromString($point_str){
		if(is_string($point_str) && preg_match("/\([0-9]+,[0-9]+\)/", $point_str)){
			$proc_str = substr($point_str, 1, -1); //This should remove the brackets
			$values = explode(',', $proc_str); //Explode into two parts based no comma
			$this->x = $values[0];
			$this->y = $values[1];
		}else
			throw IllegalArgumentException();

		//To allow chaining of calls
		return $this;
	}

	public function randomInit($min_x = 0, $min_y = 0, $max_x = 100, $max_y = 100){
		$this->x = rand($min_x, $max_x);
		$this->y = rand($min_y, $max_y);
		return $this;
	}
}

//A grid is nothing but a start point and end point and data storage
//This allows us to make a rectangular grid at any point in time
class Grid{
	private $data_map;
	private $start_point;
	private $end_point;
	private $mark_map;
	private $random_list;


	public function __construct($start_point, $end_point){
		$this->data_map = array();
		$this->start_point = (isset($start_point) && ($start_point instanceof Point)) ? $start_point : new Point();
		$this->end_point = (isset($end_point) && ($end_point instanceof Point)) ? $end_point : new Point();
		$this->random_list = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	}

	private function is_valid_grid_point($point) {
		return 	($point instanceof Point) &&
				($point->x >= $this->start_point->x && $point->y >= $this->start_point->y) && 
				($point->x <= $this->end_point->x && $point->y <= $this->end_point->y);
	}

	private function is_valid_data($character) {
		return (is_string($character) && strlen($character) == 1);
	}

	public function getGrid(){
		return $this->data_map;
	}

	// public function getRandomDataGrid(){
	// 	$this->fillRandomData();
	// 	return $this->getGrid();
	// }

	public function getRandomDataGrid(){
		
		$random_data_map = array();

		for($i = $this->start_point->y ; $i <= $this->end_point->y; $i++){
			
			for($j = $this->start_point->x ; $j <= $this->end_point->x; $j++){
				$p = new Point($i,$j);

				$data = $this->getData($p);
				if($data == NULL)
					$data =  $this->random_list{rand(0, strlen($this->random_list) - 1)};
				$random_data_map[(string)$p] = $data;
			}
			
		}
		return $random_data_map;
		
	}

	public function setGrid($grid){
		$this->data_map = $grid;
	}

	public function getStartPoint(){
		return $this->start_point;
	}

	public function getEndPoint(){
		return $this->end_point;
	}

	public function setData($point, $character){
		if($this->is_valid_grid_point($point) && $this->is_valid_data($character)){
			//This basically converts the co-ordinates to a string that PHP arrays can understand.
			//Shows an interesting usage
			if($this->isEmptyPoint($point))
				$this->mark_map[(string)$point] = 0;

			$this->mark_map[(string)$point] += 1;
			$this->data_map[(string)$point] = $character;
		}
	}

	public function removeData($point){
		if($this->is_valid_grid_point($point) && !$this->isEmptyPoint($point)){
			$this->mark_map[(string)$point] -= 1;
			if($this->mark_map[(string)$point] == 0){
				unset($this->mark_map[(string)$point]);
				unset($this->data_map[(string)$point]);
			}
		}
	}



	public function setWord($word, $start_point, $direction){
		$len_counter = 0;
		foreach(str_split($word) as $character){
			$curr_point = $this->incrOnDirection($start_point, $direction, $len_counter++);
			$this->setData($curr_point, $character);
		}
		//log_debug("Set word : $word at point " . (string)$start_point . " in direction " . $direction);
	}

	public function removeWord($word, $start_point, $direction){
		$len_counter = 0;
		foreach(str_split($word) as $character){
			$curr_point = $this->incrOnDirection($start_point, $direction, $len_counter++);
			$this->removeData($curr_point, $character);
		}
	}
	
	public function isEmptyPoint($point){
		//log_debug((string)$point . " was found : " . ($this->is_valid_grid_point($point) && !array_key_exists((string)$point, $this->data_map) ? "empty" : "non-empty"));
		return ($this->is_valid_grid_point($point) && !array_key_exists((string)$point, $this->data_map));
	}
	
	public function getData($point){
		if(!$this->isEmptyPoint($point))
			return $this->data_map[(string)$point];
		return NULL;
	}

	public function __toString(){
		$result = "";
		foreach ($this->data_map as $point_str => $character) {
			$result .= $point_str . ' : ' . $character . PHP_EOL;
		}
		return $result;
	}


	public function getEmptyPoints(){
		$empty_points = array();
		for($i = $this->start_point->x; $i <= $this->end_point->x; $i++) {
			for($j = $this->start_point->y; $j <= $this->end_point->y; $j++){
				$check_point = new Point($i, $j);
				if ($this->isEmptyPoint($check_point)){
				  	array_push($empty_points, $check_point);
				}
			}
		}
		return $empty_points;
	}

	public function getConsoleGrid(){
		$output = "";
		for($i = $this->start_point->y ; $i <= $this->end_point->y; $i++){
			
			for($j = $this->start_point->x ; $j <= $this->end_point->x; $j++){
				$p = new Point($i,$j);

				$data = $this->getData($p);
				if($data == NULL)
					$data = $this->random_list{rand(0, strlen($this->random_list) - 1)};
				$output .= $data .' ';
			}
			$output .= PHP_EOL;
		}
		return $output;
	}

	public function getStartPointForLength($length){

	}

	private function incrOnDirection($point, $direction, $len = 1){

		// echo "INCR point : " . (string)$point . " direction : " . (string)$direction . " len : " . (string)$len . PHP_EOL;
		
		$new_point = new Point($point->x, $point->y);
		switch($direction){
			case Direction::LEFT:
			$new_point->y -= $len;
			break;

			case Direction::RIGHT:
			$new_point->y += $len;
			break;

			case Direction::TOP:
			$new_point->x -= $len;
			break;

			case Direction::BOTTOM:
			$new_point->x += $len;
			break;

			case Direction::TOP_RIGHT:
			$new_point->x -= $len;
			$new_point->y += $len;
			break;

			case Direction::TOP_LEFT:
			$new_point->x -= $len;
			$new_point->y -= $len;
			break;

			case Direction::BOTTOM_RIGHT:
			$new_point->x += $len;
			$new_point->y += $len;
			break;

			case Direction::BOTTOM_LEFT:
			$new_point->x += $len;
			$new_point->y -= $len;
			break;

			case Direction::NONE:
			return NULL;

		}

		if($this->is_valid_grid_point($new_point)){
			return $new_point;
		}
		return NULL;

	}

	public function check($word, $word_start_point, $direction){
		//log_debug("Inside check function for point : " . (string)$word_start_point) . PHP_EOL;
		//echo "Checking \"$word\" at point $point in direction : $direction". PHP_EOL ;
		$word_len = strlen($word);
		$word_end_point = $this->incrOnDirection($word_start_point, $direction, $word_len - 1);
		
		//If the word is valid to fit inside
		if($word_end_point!=NULL){

			$counter = 0;
			foreach(str_split($word) as $character){
				$char_point = $this->incrOnDirection($word_start_point, $direction, $counter++);
				// echo "char point : $char_point".PHP_EOL;
				if($char_point == NULL){
					return False;
				}

				$char_check_point = $this->getData($char_point);

				//echo "char check point : $char_check_point".PHP_EOL;
				//log_debug(($char_check_point == NULL ? "Char check point is null" : "Char check point is $char_check_point"));
				if($char_check_point!= NULL && $char_check_point != $character){
					//log_debug("Check [empty point] : " . (!$this->isEmptyPoint($char_point) ? "True" : "False"));
					//log_debug("check [getdata] : " . ($this->getData($char_point) != $character ? "True" : "False"));
					// echo "Should return false from here" . PHP_EOL;
					return False;
				}
					
			}

		}else{
			//log_debug("Check failed for length of word");
			return False;
		}

		return True;
	}

}


//Enum is a user defined class and not a 
class Layout extends Enum{
	const HORIZONTAL = "HORIZONTAL";
	const VERTICAL = "VERTICAL";
	const DIAGONAL = "DIAGONAL";
}

class Direction extends Enum{
	const NONE = 0;
	const TOP_LEFT = 1;
	const TOP = 2;
	const TOP_RIGHT = 3;
	const LEFT = 4;
	const RIGHT = 5;
	const BOTTOM_LEFT = 6;
	const BOTTOM = 7;
	const BOTTOM_RIGHT = 8;
}

//$direction_list = [TOP_LEFT, TOP, TOP_RIGHT, RIGHT, BOTTOM_RIGHT, BOTTOM, BOTTOM_LEFT, LEFT];


//The Grid maker generates only square grids as of now
//This functionality can be easily extended
class GridMaker{
	
	private $grid;
	private $length;
	private $alpha_list;
	private $word_list;
	private $error;
	private $marked_grid;

	public function __construct($length, $word_list){
		
		$start_point = new Point();
		$end_point = new Point($length - 1, $length -1);
		$this->length = $length;
		$this->word_list = $word_list;
		$this->alpha_list = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$this->grid = new Grid($start_point, $end_point);
	}

	private function setStaticData(){
		$random_str = "HZABHAYUYIXEIORATKMHNMURIDGAIPKPKBMLNMQINAEOOSRTASCMNTHMHNURAVSU";
		$i = $j =  0;
		$len = 8;
		foreach(str_split($random_str) as $character){
			$this->grid->setData(new Point($i,$j), $character);	
			$j++;
			if($j != 0 && $j % $len == 0){
				$i++;
				$j = 0;
			}
			if($i != 0 && $i % $len == 0){
				break;
			}
		}
	}

	private function validateWords(){
		foreach($this->word_list as $word) {
			if(strlen($word) > $this->length)
				return False;
		}
		return True;
	}


	// public function runGridEngine($data_grid, $empty_points, $word_list){
	// 	if(count($word_list) == 0)
	// 		return True;

	// 	$word = array_shift($word_list);

	// 	$empty_points = $this->
	// 	for($i = 1; $i <= 8; $i++)
	// 	//this is a proxy call for getRandomData
	// 	//if($this->validateWords()) {
	// 		// global $direction_list;
	// 		// log_debug("Words validated");

	// 		// $empty_points = $this->grid->getEmptyPoints();

	// 		// foreach($this->word_list as $word){
	// 		// 	$this->grid->setWord();
	// 		// }
	// 		// //Write engine here


			
			
			
	// 	// }else{
	// 	// 	$this->error = "Word length exceeds grid size";
	// 	// }

	// }

	// public function runGridEngine(){
	// 	$this->grid->setWord("arpit", new Point(1,1), Direction::RIGHT);
	// 	$this->grid->setWord("varun", new Point(0,1), Direction::BOTTOM);
	// 	$this->grid->removeWord("arpit", new Point(1,1), Direction::RIGHT);

	// 	print_r($this->grid->mark_map);
	// }

	public function getProcessedGrid(){
		global $global_counter;
		global $global_time;
		global $global_store;

		$result = array();
		if($this->validateWords()){
			$global_time = microtime(true);
			runGridEngine($this->grid, $this->word_list);


			if(count($global_store) > 0){
				
				
				$result["len"] = $this->length;
				if(empty($this->error))
					$result["grid"] = $global_store[rand(0,count($global_store) - 1)];
				else
					$result["error"] = $this->error;
				$result["word_list"] = $this->word_list;

				//return $this->grid->getConsoleGrid();
			}else {
				$result["error"] = "No grid found";
				//echo "Total possiblities : " . $global_counter . PHP_EOL;
				//return "Solution not found" . PHP_EOL;
			}
		}else{
			$result["error"] = "Word exceeds grid size";
		}

		return json_encode($result);
	}

	public function getRawGrid(){
		return (string)$this->grid;
	}

}




$global_counter  = 0;


$global_time = 0;
$global_time_threshold = 500;
$global_store =array();

function runGridEngine($data_grid_obj, $word_list){
	//echo $data_grid_obj->getConsoleGrid() . PHP_EOL . PHP_EOL;
	//global $global_counter;
	global $global_time;
	global $global_time_threshold;
	global $global_store;
	if(count($word_list) <= 0 || ((microtime(true) - $global_time) * 1000 )> $global_time_threshold ){

		return True;
	}

	$word = array_shift($word_list);
	$empty_points = $data_grid_obj->getEmptyPoints();

	foreach($empty_points as $emp_point){
		for($dir = Direction::TOP_LEFT; $dir <= Direction::BOTTOM_RIGHT; $dir++){

			if($data_grid_obj->check($word, $emp_point, $dir)){
				$data_grid_obj->setWord($word, $emp_point, $dir);
				if(runGridEngine($data_grid_obj, $word_list)){

					if(((microtime(true) - $global_time) * 1000 ) < $global_time_threshold ) {

						//echo $data_grid_obj->getConsoleGrid() . PHP_EOL;
						array_push($global_store, $data_grid_obj->getRandomDataGrid());
						$data_grid_obj->removeWord($word, $emp_point, $dir);
						//$global_counter ++;
						return;
					}
				}
				$data_grid_obj->removeWord($word, $emp_point, $dir);
				//array_push($word_list, $word);
			}
		}
	}
	return False;
}











$input_len = isset($_GET["len"]) ? (int)$_GET["len"] : 7;
//$input_words = array("arpit", "ABHAY", "VARUN", "ANKIT", "AMAN", "MONK", "HIMANSHU", "URMILESH");
$input_words = array("AMAN", "ARPIT", "VARUN", "ANKIT", "MONK","ABHAY");

if(isset($_GET["words"])){
	$input_words = explode(',', $_GET["words"]);
}


//$input_words = array("DOSA", "IDLI", "PARATHA", "LASSI", "ROTI");

//$input_words = array("DOSA", "ARPIT");

//convert all the words to upper case
foreach($input_words as $key => $word){

	if(!ctype_upper($word))
		$input_words[$key] = strtoupper($word);

	$input_words[$key] = str_replace(' ', '', $input_words[$key]);
}


//Gives first preference to length
//And then sort alphabetically
usort($input_words, function($a, $b) {
    $len_a = strlen($a);
    $len_b = strlen($b);
    if($len_a == $len_b)
        return strcmp($a, $b);
    else
	   return strlen($b) - strlen($a);
});


$game_grid = new GridMaker($input_len, $input_words);
echo $game_grid->getProcessedGrid();

?>
