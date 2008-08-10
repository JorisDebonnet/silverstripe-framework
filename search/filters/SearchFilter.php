<?php
/**
 * @package sapphire
 * @subpackage search
 */

/**
 * @todo documentation
 *
 * @package sapphire
 * @subpackage search
 */
abstract class SearchFilter extends Object {
	
	protected $model;
	protected $name;
	protected $value;
	protected $relation;
	
	function __construct($name, $value = false) {
		$this->addRelation($name);
		$this->value = $value;
	}
	
	/**
	 * Called by constructor to convert a string pathname into
	 * a well defined relationship sequence.
	 *
	 * @param unknown_type $name
	 */
	protected function addRelation($name) {
		if (strstr($name, '.')) {
			$parts = explode('.', $name);
			$this->name = array_pop($parts);
			$this->relation = $parts;
		} else {
			$this->name = $name;
		}
	}
	
	/**
	 * Set the root model class to be selected by this
	 * search query.
	 *
	 * @param string $className
	 */	
	public function setModel($className) {
		$this->model = $className;
	}
	
	/**
	 * Set the current value to be filtered on.
	 *
	 * @param string $value
	 */
	public function setValue($value) {
		$this->value = $value;
	}
	
	/**
	 * Accessor for the current value to be filtered on.
	 *
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}
	
	/**
	 * The original name of the field.
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Normalizes the field name to table mapping.
	 * 
	 * @return string
	 */
	protected function getDbName() {
		// SRM: This code finds the table where the field named $this->name lives
		// Todo: move to somewhere more appropriate, such as DataMapper, the magical class-to-be?
		$candidateClass = $this->model;
		while($candidateClass != 'DataObject') {
			if(singleton($candidateClass)->fieldExists($this->name)) break;
			$candidateClass = get_parent_class($candidateClass);
		}
		if($candidateClass == 'DataObject') user_error("Couldn't find field $this->name in any of $this->model's tables.", E_USER_ERROR);
		
		return $candidateClass . "." . $this->name;
	}
	
	/**
	 * Traverse the relationship fields, and add the table
	 * mappings to the query object state.
	 * 
	 * @todo try to make this implicitly triggered so it doesn't have to be manually called in child filters
	 * @param SQLQuery $query
	 * @return SQLQuery
	 */
	protected function applyRelation($query) {
		if (is_array($this->relation)) {
			$model = singleton($this->model);
			foreach($this->relation as $rel) {
				if ($component = $model->has_one($rel)) {	
					$foreignKey = $model->getReverseAssociation($component);
					$query->leftJoin($component, "`$component`.`ID` = `{$this->model}`.`{$foreignKey}ID`");
					$this->model = $component;
				} elseif ($component = $model->has_many($rel)) {
					$ancestry = $model->getClassAncestry();
					$model = singleton($component);
					$foreignKey = $model->getReverseAssociation($ancestry[0]);
					$foreignKey = ($foreignKey) ? $foreignKey : $ancestry[0];
					$query->leftJoin($component, "`$component`.`{$foreignKey}ID` = `{$this->model}`.`ID`");
					$this->model = $component;
				} elseif ($component = $model->many_many($rel)) {
					list($parentClass, $componentClass, $parentField, $componentField, $relationTable) = $component;
					$parentBaseClass = ClassInfo::baseDataClass($parentClass);
					$componentBaseClass = ClassInfo::baseDataClass($componentClass);
					$query->innerJoin($relationTable, "`$relationTable`.`$parentField` = `$parentBaseClass`.`ID`");
					$query->leftJoin($componentClass, "`$relationTable`.`$componentField` = `$componentClass`.`ID`");
					$this->model = $componentClass;
				}
			}
		}
		return $query;
	}
	
	/**
	 * Apply filter criteria to a SQL query.
	 *
	 * @param SQLQuery $query
	 * @return SQLQuery
	 */
	abstract public function apply(SQLQuery $query);
	
}
?>