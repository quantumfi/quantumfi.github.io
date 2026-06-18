<?php
/**
 * @author Michele Andreoli <michi.andreoli[at]gmail.com>
 * @name index.php
 * @version 0.3 updated 06-07-2010
 * @license http://opensource.org/licenses/gpl-license-php GNU Public License
 * @package Noise
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Noise</title>
    </head>
    <body>
    	<?php
    		require_once 'Noise.class.php';
    		require_once 'FFT/FFT.class.php';
    		require_once 'Filter.class.php';
    		
    		// Define a sinuosoidal function
    		$f = array();
    		$f[0] = 0;
			for ($i = 1; $i < 512; $i++) {
				$f[$i] = sin(2 * M_PI / 512 * $i);
			}
			
			// Define a exponential function
			$g = array();
			$index = 0;
			for ($i = -200.0; $i < 200.0; $i += 400.0 / 512) {
				$g[$index] = 1 + (2 * sqrt(pow(200.0, 2.0) - pow($i, 2.0)));
				$index++;
			}
    		
    		$noise = new Noise();
    		$filter = new Filter();
    		$fft = new FFT(512);
    		
    		// Add gaussian noise in the sinusoidal function
    		$f = $noise->addGaussian($f, 0, 0.1);
    		// Add poissonian noise in the exponential function
    		$g = $noise->addPoisson($g);
    		
    		// Calculate the FFT of function f with gaussian noise
    		$f = $fft->getAbsFFT($fft->fft($f));
    		// Remove the high frequency (noise)
    		$f = $filter->filterLP($f, 1);
    		// Calculate the inverse FFT of function f, and return the original signal f
    		$f = $fft->ifft($fft->doubleToComplex($f));
    		$f = $fft->complexToDouble($f);
    		    		
    		for ($i = 0; $i < count($f); $i++)
    			echo $f[$i]."<br/>";
    		
    	?>
    </body>
</html>