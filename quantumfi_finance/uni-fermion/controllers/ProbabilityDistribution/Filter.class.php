<?php
/**
 * @author Michele Andreoli <michi.andreoli[at]gmail.com>
 * @name Filter.class.php
 * @version 0.2 updated 06-07-2010
 * @license http://opensource.org/licenses/gpl-license-php GNU Public License
 * @package Noise
 */

class Filter {
	/**
	 * Low-pass filter
	 * @param array<double> $func
	 * @param int $val cut value
	 * @return array<double> return the filtered signal
	 */
	public function filterLP($func, $val) {
		for ($i = 0; $i < count($func); $i++) {
			if ($i > $val && $i < (count($func) - $val))
				$func[$i] = 0;
		}
		
		return $func;
	}
	
	/**
	 * High-pass filter
	 * @param array<double> $func
	 * @param int $val cut value
	 * @return array<double> return the filtered signal
	 */
	public function filterHP($func, $val) {
		for ($i = 0; $i < count($func); $i++) {
			if ($i <= $val || $i >= (count($func) - $val))
				$func[$i] = 0;
		}
		
		return $func;
	}
}

?>