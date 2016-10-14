<?php
namespace Frozennode\Administrator\Fields;

use Illuminate\Database\Query\Builder as QueryBuilder;

class Enum extends Field {

	/**
	 * The options used for the enum field
	 *
	 * @var array
	 */
	protected $rules = array(
		'options' => 'required|array|not_empty',
	);

	/**
	 * Builds a few basic options
	 */
	public function build()
	{
		parent::build();

		$options = $this->suppliedOptions;

		$dataOptions = $options['options'];
		$options['options'] = array();

		if (!is_array($dataOptions) && $dataOptions) {
			$model = $this->config->getDataModel();

			$dataOptions = $model->{$dataOptions}();

			if ( !$dataOptions ) {
				$dataOptions = [
					trans('Not found any values'),
				];
			}
		}

		$isAssocArr = !array_key_exists(0, $dataOptions);

		//iterate over the options to create the options assoc array
		foreach ($dataOptions as $val => $text)
		{
			$options['options'][] = array(
				'id' => $isAssocArr ? $val : $text,
				'text' => '' . $text,
			);
		}

		$this->suppliedOptions = $options;
	}

	/**
	 * Fill a model with input data
	 *
	 * @param \Illuminate\Database\Eloquent\model	$model
	 * @param mixed									$input
	 */
	public function fillModel(&$model, $input)
	{
		$model->{$this->getOption('field_name')} = $input;
	}

	/**
	 * Sets the filter options for this item
	 *
	 * @param array		$filter
	 *
	 * @return void
	 */
	public function setFilter($filter)
	{
		parent::setFilter($filter);

		$this->userOptions['value'] = $this->getOption('value') === '' ? null : $this->getOption('value');
	}

	/**
	 * Filters a query object
	 *
	 * @param \Illuminate\Database\Eloquent\Builder	$query
	 * @param array									$selects
	 *
	 * @return void
	 */
	public function filterQuery( &$query, &$selects = null)
	{
		//run the parent method
		parent::filterQuery($query, $selects);

		//if there is no value, return
		if ($this->getFilterValue($this->getOption('value'))===false)
		{
			return;
		}

		if (@$this->getOption('scope')) {
			$fieldName = $this->getOption('field_name');
			$fieldName = camel_case($fieldName);
			$query->{$fieldName}($this->getOption('value'));
		} else {
			$query->where( $this->config->getDataModel()->getTable() . '.' . $this->getOption( 'field_name' ), '=', $this->getOption( 'value' ) );
		}
	}
}