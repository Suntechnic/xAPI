<?

namespace X;

class Result extends \Bitrix\Main\Result
{
	/** @var array */
	protected $id = false; // полседний id
	protected $ide = []; // Именованные id
	protected $ids = []; // стэк id
	protected $bx_results = []; // стэк результатов

	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Возвращает id или именованный, связанный с сущностью id
	 *
	 * @param mixed $entity
	 * @return mixed
	 */
	public function getId ($entity=false)
	{
		if ($entity) return $this->ide[$entity];
		return $this->id;
	}
	#
	
	/**
	 * Возвращает result и смещает указатель
	 *
	 * @param mixed $entity
	 * @return mixed
	 */
	public function get ()
	{
		$r = current($this->bx_results);
		next($this->bx_results);
		return $r;
	}
	#
	
	/**
	 * Add the id to stack
	 *
	 * @param mixed $id, mixed $entity
	 * @return $this
	 */
	public function addId ($id,$entity=false)
	{
		$this->ids[] = $id;
		return $this->setId($id,$entity);
	}
	#
	
	/**
	 * Set the id out stack
	 *
	 * @param mixed $id, mixed $entity
	 * @return $this
	 */
	public function setId ($id,$entity=false)
	{
		$this->id = $id;
		if ($entity) $this->ide[$entity] = $id;
		return $this;
	}
	#
	
	/**
	 * Add the result.
	 *
	 * @param Result $result
	 * @return $this
	 */
	public function add ($result)
	{
		$this->addId($result->getId());
		if (!$result->isSuccess()) {
			$this->addErrors($result->getErrors());
		}
		$this->bx_results[] = $result;
		return $this;
	}
	#
	
	/**
	 * Adds the error.
	 *
	 * @param Error $error or string $error
	 * @return $this
	 */
	public function addError($error)
	{
		if (!is_a($error,'\Bitrix\Main\Error')) $error = new \Bitrix\Main\Error($error);
		return parent::addError($error);
	}
	#

	/**
	 * Adds array of Error objects
	 *
	 * @param Error[] $errors or string[] $errors
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
	#
}
