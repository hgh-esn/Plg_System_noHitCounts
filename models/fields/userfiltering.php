defined('JPATH_BASE') or die();

class JFormFieldUserfiltering extends JFormFieldUser 
{
	public $type = 'userfiltering';
	protected function getGroups()
	{
		$groups = array();
		$groups[] = 2; // put here the list of the groups you want to filter
		return $groups;
	}
} 