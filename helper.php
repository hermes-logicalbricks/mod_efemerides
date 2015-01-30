<?php
/**
 * Helper class for Efemerides module
 *
 * @package    Efemerides
 * @subpackage Modules
 * @link http://revolucionemosoaxaca.org
 * @license        GNU/GPL, see LICENSE.php
 * mod_efemerides is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

class modEfemeridesHelper
{
  public function __construct($params){
    $this->random_result = $params->get('use_random');
    $this->count = $params->get('count');
    $this->date_range = $params->get('date_range');
    $this->order_by = $params->get('order_by');
    $this->format_for_date= $params->get('format_for_date');
  }

  public function getEfemerides()
  {
    $efemeridesList = $this->getListEfemerides();
    if (!empty($efemeridesList))
    {
      $efemeridesList = $this->filterListEfemerides($efemeridesList);
      $efemeridesList = $this->putFormattedDate($efemeridesList);
    }
    return $efemeridesList;
  }

  private function deleteNullElements($array)
  {
    foreach($array as $key => $value) {
      if($value == "" || $value == " " || is_null($value)) {
        unset($array[$key]);
      }
      else
        $newarray[] = $value;
    }
    return $newarray;
  }

  private function getOrderEfemerides()
  {
    $order_by_length = strlen($this->order_by);

    for ($i = 0; $i < $order_by_length; ++$i)
    {
      switch ($this->order_by[$i])
      {
      case 'm':
        $order_array[] = 'MONTH';
        break;
      case 'd':
        $order_array[] = 'DAY';
        break;
      case 'y':
        $order_array[] = 'YEAR';
        break;
      }
    }
    return ' ORDER BY '.$order_array[0].'(historicdate), '.$order_array[1].'(historicdate), '.$order_array[2].'(historicdate)';
  }

  private function getListEfemerides()
  {
    $db =& JFactory::getDBO();
    $published = ' ';//' published=1';
    $select = 'SELECT id,historicdate as thedate, DAY(historicdate) as theday,MONTH(historicdate) as themonth, YEAR(historicdate) as theyear,title,description'.' FROM #__efemerides WHERE';
    $order = $this->getOrderEfemerides();
    $query = ''.$select.$published.$order;
    switch($this->date_range)
    {
    case 'by_day':
      $query = $select.' DAY(NOW())=DAY(historicdate) '.
        ' AND MONTH(NOW())=MONTH(historicdate) AND'.$published.$order;
      break;
    case 'by_month':
      $query = $select.' MONTH(NOW())=MONTH(historicdate) AND'.$published.$order;
      break;
    case 'by_year':
      $query = ''.$select.$published.$order;
      break;
    }
    $db->setQuery($query);
    $db->query();
    $efemerides = $db->loadObjectList();

    return $efemerides;
  }

  private function filterListEfemerides($list)
  {
    if ($this->random_result == '1') {
      shuffle($list);
    }
    $newlist = array_slice($list, 0, $this->count);
    return $newlist;
  }

  private function putFormattedDate($list)
  {
    jimport( 'joomla.utilities.date' );
    $config =& JFactory::getConfig();
    $offset = $config->get('offset');
    foreach ($list as $l)
    {
      $date = new JDate( $l->thedate, $offset );
      $l->formatteddate = $date->format($this->format_for_date);
      $newlist[] = $l;
    }
    return $newlist;
  }
}
?>
