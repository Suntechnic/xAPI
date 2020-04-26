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
}
