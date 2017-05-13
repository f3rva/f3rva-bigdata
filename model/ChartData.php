<?php
namespace F3\Model;

class ChartData {
	private $xLabels;
	private $series;
	
	public function getXLabels() {
		return $this->xLabels;
	}
	
	public function getXLabelsKeys() {
		return array_keys($this->xLabels);
	}
	
	public function setXLabels($xLabels) {
		$this->xLabels= $xLabels;
	}
	
	public function getSeries() {
		return $this->series;
	}
	
	public function getSeriesImploded() {
		$allSeries = array();

		foreach ($this->getSeries() as $ser) {
			array_push($allSeries, '[' . implode(",", $ser) . ']');
		}
		
		return implode(",", $allSeries);
	}
	
	public function getSeriesKeysImploded() {
		return "'" . implode("','", array_keys($this->getSeries())) . "'";
	}
	
	public function setSeries($series) {
		$this->series = $series;
	}
}

?>