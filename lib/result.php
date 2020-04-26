<?

namespace X;

class Result extends \Bitrix\Main\Result
{
	/** @var array */
	protected $id = false;
	protected $ids = [];

	public function __construct()
	{
		parent::__construct();
	}

	public function setId($id,$entity=false)
	{
		$this->id = $id;
		if ($entity) $this->ids[$entity] = $id;
	}
	
	public function getId($entity=false)
	{
		if ($entity) $this->ids[$entity] = $id;
		
		return $this->id;
	}
	
	
	/**
	 * Adds the error.
	 *
	 * @param Error $error
	 * @return $this
	 */
	public function addError($error)
	{
		if (!is_a($error,'\Bitrix\Main\Error')) $error = new \Bitrix\Main\Error($error);
		return parent::addError($error);
	}

	/**
	 * Adds array of Error objects
	 *
	 * @param Error[] $errors
	 * @return $this
	 */
	public function addErrors(array $errors)
	{
		
		$errors = array_map(function ($e) {
			if (!is_a($e,'\Bitrix\Main\Error')) $e = new \Bitrix\Main\Error($e);
			return $e;
		}, $errors);
		
		return parent::addErrors($errors);
	}
}
