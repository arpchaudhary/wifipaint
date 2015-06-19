<?php

require_once('./helpers.php');
init();
//Elegant solution using the reflectionclass 
//Creates a basic enum type implementation
// abstract class Enum
// {
//     final public function __construct($value)
//     {
//         $c = new ReflectionClass($this);
//         if(!in_array($value, $c->getConstants())) {
//             throw IllegalArgumentException();
//         }
//         $this->value = $value;
//     }

//     final public function __toString()
//     {
//         return $this->value;
//     }
// }

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


	public function __construct($start_point, $end_point){
		$this->data_map = array();
		$this->start_point = (isset($start_point) && ($start_point instanceof Point)) ? $start_point : new Point();
		$this->end_point = (isset($end_point) && ($end_point instanceof Point)) ? $end_point : new Point();
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
			$this->data_map[(string)$point] = $character;
		}
	}

	public function setWord($word, $start_point, $direction){
		$len_counter = 0;
		foreach(str_split($word) as $character){
			$curr_point = $this->incrOnDirection($start_point, $direction, $len_counter++);
			$this->setData($curr_point, $character);
			
		}
		log_debug("Set word : $word at point " . (string)$start_point . " in direction " . $direction);
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

	private function getDirection($start_point, $end_point){
		//let's use some maths here
		if($start_point->x == $end_point->y && $start_point->y == $end_point->y)
			return Direction::NONE;

		$delta_x = $end_point->x - $start_point->x; 
		$delta_y = $end_point->y - $end_point->y;

		$angle = (int)(atan2($delta_y, $delta_x) * 180 / M_PI);
		if($angle == 0){
			return Direction::RIGHT;
		}else if ($angle == 45){
			return Direction::TOP_RIGHT;
		}else if($angle == 90){
			return Direction::TOP;
		}else if($angle == 135){
			return Direction::TOP_LEFT;
		}else if($angle == 180){
			return Direction::RIGHT;
		}else if($angle == -135){
			return Direction::BOTTOM_LEFT;
		}else if($angle == -90){
			return Direction::BOTTOM;
		}else if($angle == -45){
			return Direction::BOTTOM_RIGHT;
		}else{
			return Direction::NONE;
		}



		// if($start_point->x < $end_point->x && $start_point->y == $end_point->y)
		// 	return Direction::LEFT;

		// if($start_point->x > $end_point->x && $start_point->y == $end_point->y)
		// 	return Direction::RIGHT;

		// if($start_point->x == $end_point->x && $start_point->y < $end_point->y)
		// 	return Direction::TOP;

		// if($start_point->x == $end_point->x && $start_point->y > $end_point->y)
		// 	return Direction::BOTTOM;

		// if($start_point->x < $end_point->x && $start_point->y < $end_point->y)
		// 	return Direction::BOTTOM_RIGHT;

		// if($start_point->x < $end_point->x && $start_point->y > $end_point->y)
		// 	return Direction::TOP_RIGHT;

		// if($start_point->x > $end_point->x && $start_point->y < $end_point->y)
		// 	return Direction::BOTTOM_LEFT;

		// if($start_point->x > $end_point->x && $start_point->y > $end_point->y)
		// 	return Direction::TOP_LEFT;
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

	public function getStartPointForLength($length){

	}

	private function incrOnDirection($point, $direction, $len = 1){
		$new_point = new Point($point->x, $point->y);
		switch($direction){
			case Direction::LEFT:
			$new_point->x-=$len;
			break;

			case Direction::RIGHT:
			$new_point->x+=$len;
			break;

			case Direction::TOP:
			$new_point->y+=$len;
			break;
			case Direction::BOTTOM:
			$new_point->y-=$len;
			break;

			case Direction::TOP_RIGHT:
			$new_point->x+=$len;
			$new_point->y+=$len;
			break;

			case Direction::TOP_LEFT:
			$new_point->x-=$len;
			$new_point->y+=$len;
			break;

			case Direction::BOTTOM_RIGHT:
			$new_point->x+=$len;
			$new_point->y-=$len;
			break;

			case Direction::BOTTOM_LEFT:
			$new_point->x-=$len;
			$new_point->y-=$len;
			break;


		}

		if($this->is_valid_grid_point($new_point)){
			return $new_point;
		}
		return NULL;

	}

	public function check($word, $direction, $word_start_point){
		//log_debug("Inside check function for point : " . (string)$word_start_point) . PHP_EOL;
		$word_len = strlen($word);
		$word_end_point = $this->incrOnDirection($word_start_point, $direction, $word_len);
		
		//If the word is valid to fit inside
		if($word_end_point!=NULL){

			$counter = 0;
			foreach(str_split($word) as $character){
				$char_point = $this->incrOnDirection($word_start_point, $direction);
				$char_check_point = $this->getData($char_point);
				//log_debug(($char_check_point == NULL ? "Char check point is null" : "Char check point is $char_check_point"));
				if($char_check_point!= NULL && $char_check_point != $character){
					//log_debug("Check [empty point] : " . (!$this->isEmptyPoint($char_point) ? "True" : "False"));
					//log_debug("check [getdata] : " . ($this->getData($char_point) != $character ? "True" : "False"));
					return False;
				}
					
			}

		}else{
			log_debug("Check failed for length of word");
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

$direction_list = [TOP_LEFT, TOP, TOP_RIGHT, RIGHT, BOTTOM_RIGHT, BOTTOM, BOTTOM_LEFT, LEFT];


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

	public function runGridEngine(){
		//this is a proxy call for getRandomData
		if($this->validateWords()) {
			global $direction_list;
			
			//backtrack map
			$bk_map = array();
			
			$proc_words = array();
			$word_counter = 0;
			$total_words = count($this->word_list);
			
			//This flag can also be used for time control
			$run_flag = True;
			while($run_flag){
				echo "Inside run loop".PHP_EOL;
				//Get the word at this counter
				$word = $this->word_list[$word_counter];
				$empty_points = $this->grid->getEmptyPoints();
				
				$placement_counter = 0;
				foreach($empty_points as $epoint){
					foreach ($direction_list as $dir) {

						if($this->grid->check($word, $dir, $epoint)){
							//$this->grid->setWord($word, $epoint, $dir);
							$grid_ele = $this->grid;
							$bk_obj = array("grid" => $grid_ele->setWord($word, $epoint, $dir));
							$bk_map[$word][$placement_counter++] = $bk_obj;
						}else{
							log_debug("Check was invalid for word : $word, direction : $dir, point : " . (string)$epoint);
						}
					}
				}

				if(isset($bk_map[$word])){
					log_debug("backtrack map was set for word : $word" );
					$word_counter++;
					$top_ele = array_shift($bk_map[$word]);
					$this->grid = $top_ele["grid"];
					print_r($this->grid->getGrid());
					
				}

				if($word_counter == $total_words)
					print_r($this->grid->getGrid());

				$run_flag = False;
			}
			// $this->setStaticData();
			// foreach($this->word_list as $word){
			// 	$start_point = (new Point()) -> random
			// }
		}else{
			$this->error = "Word length exceeds grid size";
		}

		// foreach($this->word_list as $word){
		// 	$randomPoint = (new Point());
		// }
		//This will fill the raw grid.
		
		// foreach($word_list as $word){

		// 	$randomPoint = (new Point()) -> randomInit($this->grid->getStartPoint()->x,$this->grid->getStartPoint()->y,
		// 												$this->grid->getEndPoint()->x, $this->grid->getEndPoint()->y);
		// }

	}

	public function getProcessedGrid(){
		$this->runGridEngine();
		$result = array();
		$result["len"] = $this->length;
		if(empty($this->error))
			$result["grid"] = $this->grid->getGrid();
		else
			$result["error"] = $this->error;
		$result["word_list"] = $this->word_list;

		return "";
		//return json_encode($result, JSON_PRETTY_PRINT);
	}

	public function getRawGrid(){
		return (string)$this->grid;
	}

}

//To test the output
//we are giving the word list right here
//We can also put it in redis in different sets 
//so that php does not have to take care of reading in general



$input_len = 8;
$input_words = array("arpit", "ABHAY", "VARUN", "ANKIT", "HIMANSHU", "URMILESH", "AMAN", "MONK");

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
$game_grid->getProcessedGrid();

?>
