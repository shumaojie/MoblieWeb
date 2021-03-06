
<?php

/** @file
 * This file is part of Google Chart PHP library.
 *
 * Copyright (c) 2010 Rémi Lanvin <remi@cloudconnected.fr>
 *
 * Licensed under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE file.
 */

include_once dirname(__FILE__).'/../GoogleChartMarker.php';

/**
 * A Range marker.
 *
 * This class implement Range Markers feature (@c chm).
 *
 * @par Example
 *
 *
 * @see GoogleChartMarker
 * @see http://code.google.com/apis/chart/docs/chart_params.html#gcharts_range_markers
 */
class GoogleChartRangeMarker extends GoogleChartMarker
{
	/**
	 * Compute the parameter value.
	 *
	 * @note For internal use only.
	 * @param $index (int) index of the data serie.
	 * @return string
	 */
	public function compute($index)
	{
		
	}
}
