<?php
namespace App\Services;

use function foo\func;
use Illuminate\Support\Facades\Log;

/**
 * User: liguodong
 * Date: 2018/12/27
 * Time: 15:07 PM
 */
class CommonService
{
	/**
	 * 处理分页相关参数
	 * @param $request
	 * @return array
	 */
	public function doPaginator($request)
	{
		$paginator = [];
		if (intval($request->get('per_page')) > 0) {
			$paginator['per_page'] = intval($request->get('per_page'));
		}
		if (intval($request->get('page')) > 0) {
			$paginator['page'] = intval($request->get('page'));
		}
		if (!empty($request->get('order'))) {
			$paginator['order'] = trim($request->get('order'));
		}
		if (!empty($request->get('sort'))) {
			$paginator['sort'] = trim($request->get('sort'));
		}
		return $paginator;
	}

	/**
	 * 基础操作处理
	 * @param $params
	 * @param string $operating
	 * @return array
	 */
	public function basicDeal($params, $operating = 'where') {
		$where = [];
		foreach ($params as $field => $value) {
			switch ($operating) {
				case 'where':
					if (!empty($value) || intval($value) >= 0) {
						$where[$field] = $value;
					}
					break;
				case 'in':
					if (!empty($value) || intval($value) >= 0) {
						$where[$field] = explode(",", $value);
					}
					break;
				case 'like':
					if (!empty($value) || intval($value) >= 0) {
						$condition = str_replace('_', '\_', $value);
						$where[$field] = '%' . $condition . '%';
					}
					break;
				case 'between':
					break;
				case 'or':
					if (!empty($value) || intval($value) >= 0) {
						$where[$field] = $value;
					}
					break;
				default:
					break;
			}
		}
		return $where;
	}

	/**
	 * 关联表基础操作处理
	 * @param $params
	 * @param string $operating
	 * @return array
	 */
	public function basicRefDeal($params, $operating = 'where') {
		$where = [];
		foreach ($params as $field => $value) {
			switch ($operating) {
				case 'where':
					if (!empty($value) || intval($value) >= 0) {
						$where[$field] = $value;
					}
					break;
				case 'in':
					if (!empty($value)) {
						$where[$field] = explode(",", $value);
					}
					break;
				case 'like':
					if (!empty($value)) {
						$condition = str_replace('_', '\_', $value);
						$where[$field] = '%' . $condition . '%';
					}
					break;
				case 'between':
					break;
				case 'or':
					if (!empty($value) || intval($value) >= 0) {
						$where[$field] = $value;
					}
					break;
				default:
					break;
			}
		}
		return $where;
	}

	/**
	 * 基础查询之精确查询
	 * @param $params
	 * @return array
	 */
	public function basicWhere($params, $with = false)
	{
		if ($with) {
			$where = $this->basicRefDeal($params, 'where');
		} else {
			$where = $this->basicDeal($params, 'where');
		}
		return $where;
	}

	/**
	 * 基础查询之in查询
	 * @param $params
	 * @return array
	 */
	public function basicWhereIn($params, $with = false)
	{
		if ($with) {
			$whereIn = $this->basicRefDeal($params, 'in');
		} else {
			$whereIn = $this->basicDeal($params, 'in');
		}
		return $whereIn;
	}

	/**
	 * 基础查询之模糊查询
	 * @param $params
	 * @return array
	 */
	public function basicWhereLike($params, $with = false)
	{
		if ($with) {
			$whereLike = $this->basicRefDeal($params, 'like');
		} else {
			$whereLike = $this->basicDeal($params, 'like');
		}
		return $whereLike;
	}

	/**
	 * 基础查询之区间查询
	 * @param $params
	 * @param $can
	 * @return array
	 */
	public function basicWhereBetween($params, $can = [], $with = false)
	{
		$whereBetween = [];
		if ($with) {
			foreach ($params as $key => $value) {
				$fieldWith = $fieldWithStart = $fieldWithEnd = '';
				if (starts_with($key, 'with-')) {//关联表查询
					$fieldWith = str_after($key, 'with-');
					$fieldWith = explode('-', $fieldWith);
					if (ends_with($fieldWith[1], '_start')) {
						$fieldWithStart = str_before($fieldWith[1], '_start');
					}
					if (ends_with($fieldWith[1], '_end')) {
						$fieldWithEnd = str_before($fieldWith[1], '_end');
					}
				}
				if (!empty($fieldWith)) {
					foreach ($can as $table => $item) {
						if ($fieldWith[0] == $table) {
							foreach ($item as $col) {
								if ($fieldWithStart == $col || $fieldWithEnd == $col) {
									$field      = $col;
									$fieldStart = 'with-'.$table.'-'.$field.'_start';
									$fieldEnd   = 'with-'.$table.'-'.$field.'_end';
									if ((isset($params[$fieldStart]) && !empty($params[$fieldStart])) && (!isset($params[$fieldEnd]) || empty($params[$fieldEnd]))) {//起始点
										$whereBetween[$table][$field] = ['start' => $params[$fieldStart], 'end' => ''];
									} else if ((isset($params[$fieldEnd]) && !empty($params[$fieldEnd])) && (!isset($params[$fieldStart]) || empty($params[$fieldStart]))) {//截止点
										$whereBetween[$table][$field] = ['start' => '', 'end' => $params[$fieldEnd]];
									} else if (isset($params[$fieldStart]) && !empty($params[$fieldStart]) && isset($params[$fieldEnd]) && !empty($params[$fieldEnd])) {//起始点+截止点
										$whereBetween[$table][$field] = ['start' => $params[$fieldStart], 'end' => $params[$fieldEnd]];
									}
								}
							}
						}
					}
				}
			}
		} else {
			foreach ($can as $field) {
				$fieldStart = $field.'_start';
				$fieldEnd   = $field.'_end';
				if ((isset($params[$fieldStart]) && !empty($params[$fieldStart])) && (!isset($params[$fieldEnd]) || empty($params[$fieldEnd]))) {//起始点
					$whereBetween[$field] = ['start' => $params[$fieldStart], 'end' => ''];
				} else if ((isset($params[$fieldEnd]) && !empty($params[$fieldEnd])) && (!isset($params[$fieldStart]) || empty($params[$fieldStart]))) {//截止点
					$whereBetween[$field] = ['start' => '', 'end' => $params[$fieldEnd]];
				} else if (isset($params[$fieldStart]) && !empty($params[$fieldStart]) && isset($params[$fieldEnd]) && !empty($params[$fieldEnd])) {//起始点+截止点
					$whereBetween[$field] = ['start' => $params[$fieldStart], 'end' => $params[$fieldEnd]];
				}
			}
		}
		return $whereBetween;
	}

	/**
	 * 基础查询之精确查询
	 * @param $params
	 * @return array
	 */
	public function basicOrWhere($params, $with = false)
	{
		if ($with) {
			$where = $this->basicRefDeal($params, 'or');
		} else {
			$where = $this->basicDeal($params, 'or');
		}
		return $where;
	}

	/**
	 * 基础查询
	 * @param $query
	 * @param array $with
	 * @param bool $count
	 * @param array $paginator
	 * @param array $whereLike
	 * @param array $whereBetween
	 * @param array $orWhere
	 * @return mixed
	 */
	public function basicQuery($query, $with = [],  $count = false, $paginator = [], $whereIn = [], $whereLike = [], $whereBetween = [], $orWhere = [])
	{
		try {
			$query = $query->where(function ($query) use ($whereIn, $whereLike, $whereBetween, $orWhere) {
				if (!empty($whereLike)) {
					foreach ($whereLike as $key => $like) {
						$query = $query->where($key, 'like', $like);
					}
				}
				if (!empty($whereBetween)) {
					$query = $query->where(function ($query) use ($whereBetween) {
						foreach ($whereBetween as $key => $value) {
							if (!empty($value['start']) && !empty($value['end'])) {//起始+截止时间
								$query->where($key, '>=', $value['start'])->where($key, '<=', $value['end']);
							} else if (!empty($value['start']) && empty($value['end'])) {//起始时间
								$query->where($key, '>=', $value['start']);
							} else if (empty($value['start']) && !empty($value['end'])) {//截止时间
								$query->where($key, '<=', $value['end']);
							}
						}
					});
				}
				if (!empty($whereIn)) {
					foreach ($whereIn as $key => $value) {
						$query = $query->whereIn($key, $value);
					}
				}
			});

			if (!empty($orWhere)) {
				$query = $query->orWhere(function ($query) use ($orWhere) {
					foreach ($orWhere as $key => $value) {
						//$query = $query->orWhere([$key => $value]);
						$query = $query->where([$key => $value]);
					}
				});
			}

			if ($count) {//统计总数
				$result = $query->count();
			} else {//列表
				$perPage = (isset($paginator['per_page']) && !empty($paginator['per_page'])) ? $paginator['per_page'] : config('app.default_per_page');
				$order   = (isset($paginator['order'])    && !empty($paginator['order'])) ? $paginator['order'] : 'updated_at';
				$sort    = (isset($paginator['sort'])     && !empty($paginator['sort'])) ? $paginator['sort'] : 'DESC';
				if (!empty($with)) {//关系表查询
					if (!empty($with['with'])) {//有查询条件
						foreach ($with['with'] as $key_where => $value_where) {
							$query = $this->withQuery($query, $value_where, $key_where);
						}
					} else {//无查询条件
						$query = $query->with($with['table']);
					}
				} else {//无关系表查询
					//$query = $query->with('groups');
				}

				$result = $query->paginate($perPage);
			}
			return $result;
		} catch (\Exception $e) {
			Log::info('Basic query error: '.$e->getMessage().'\n');
			throw new \Exception('标签查询有误'.$e->getMessage());
		}
	}

	/**
	 * 基础查询条件处理
	 * @param $params
	 * @param $object
	 * @return array
	 */
	public function sqlPrepare($params, $object)
	{
		$where = $whereIn = $whereLike = $whereBetween = $orWhere = array();
		$whereRef = $whereInRef = $whereLikeRef = $whereBetweenRef = $orWhereRef = array();
		$params_where = $params_where_in = $params_where_like = $params_where_between = $params_or_where = [];
		$params_where_ref = $params_where_in_ref = $params_where_like_ref = $params_where_between_ref = $params_or_where_ref = [];
		foreach ($params as $field => $value) {
			$fieldWith = '';
			if (starts_with($field, 'with-')) {//关联表查询
				$fieldWith = str_after($field, 'with-');
				$fieldWith = explode('-', $fieldWith);
			}

			if (empty($fieldWith)) {
				/*基础表查询处理*/
				//where精确查询条件
				if (isset($object->fields_where) && in_array($field, $object->fields_where)) {
					$params_where[$field] = $value;
				}
				//in查询条件
				if (isset($object->fields_where_in) && array_key_exists($field, $object->fields_where_in)) {
					$params_where_in[$object->fields_where_in[$field]] = $value;
				}
				//模糊查询条件
				if (isset($object->fields_where_like) && in_array($field, $object->fields_where_like)) {
					$params_where_like[$field] = $value;
				}
				//between区间查询条件
				//或条件查询
				if (isset($object->fields_or_where) && array_key_exists($field, $object->fields_or_where)) {
					$params_or_where[$object->fields_or_where[$field]] = $value;
				}
				//where精确查询条件
				$where = $this->basicWhere($params_where);
				//in查询条件
				$whereIn = $this->basicWhereIn($params_where_in);
				//like模糊查询条件
				$whereLike = $this->basicWhereLike($params_where_like);
				//between区间查询条件
				$whereBetween = $this->basicWhereBetween($params, $object->fields_where_between);
				//or或条件查询
				$orWhere = $this->basicOrWhere($params_or_where);
			} else {
				/*关联表条件查询处理*/
				//where精确查询条件
				if (isset($object->fields_where_ref) && array_key_exists($fieldWith[0], $object->fields_where_ref) && !isset($fieldWith[2])) {
					$params_where_ref[$fieldWith[0].'.'.$fieldWith[1]] = $value;
				}
				//in查询条件
				if (isset($object->fields_where_in_ref) && array_key_exists($fieldWith[0], $object->fields_where_in_ref) && isset($fieldWith[2]) && $fieldWith[2] == 'in') {
					$params_where_in_ref[$fieldWith[0].'.'.$fieldWith[1]] = $value;
				}
				//模糊查询条件
				if (isset($object->fields_where_like_ref) && array_key_exists($fieldWith[0], $object->fields_where_like_ref) && isset($fieldWith[2]) && $fieldWith[2] == 'like') {
					$params_where_like_ref[$fieldWith[0].'.'.$fieldWith[1]] = $value;
				}
				//between区间查询条件
				//或条件查询
				if (isset($object->fields_or_where_ref) && array_key_exists($fieldWith[0], $object->fields_or_where_ref) && isset($fieldWith[2]) && $fieldWith[2] == 'or') {
					$params_or_where_ref[$fieldWith[0].'.'.$fieldWith[1]] = $value;
				}

				/*关联表相关查询*/
				//where精确查询条件
				$whereRef = $this->basicWhere($params_where_ref, true);
				//in查询条件
				$whereInRef = $this->basicWhereIn($params_where_in_ref, true);
				//like模糊查询条件
				$whereLikeRef = $this->basicWhereLike($params_where_like_ref, true);
				//between区间查询条件
				$whereBetweenRef = $this->basicWhereBetween($params, $object->fields_where_between_ref, true);
				//or或条件查询
				$orWhereRef = $this->basicOrWhere($params_or_where_ref, true);
			}
		}

		if (!empty($whereRef)) {
			$whereRef = $this->conversionFormat($whereRef);
		}
		if (!empty($whereInRef)) {
			$whereInRef = $this->conversionFormat($whereInRef);
		}
		if (!empty($whereLikeRef)) {
			$whereLikeRef = $this->conversionFormat($whereLikeRef);
		}
		if (!empty($orWhereRef)) {
			$orWhereRef = $this->conversionFormat($orWhereRef);
		}

		return [
			'where'        => $where,
			'whereIn'      => $whereIn,
			'whereLike'    => $whereLike,
			'whereBetween' => $whereBetween,
			'orWhere'      => $orWhere,
			'whereRef'     => $whereRef,
			'whereInRef'   => $whereInRef,
			'whereLikeRef' => $whereLikeRef,
			'whereBetweenRef' => $whereBetweenRef,
			'orWhereRef'   => $orWhereRef,
		];
	}

	/**
	 * 转换数据格式
	 * @param $params
	 * @return array
	 */
	public function conversionFormat($params) {
		$result = [];
		if (!empty($params)) {
			foreach ($params as $key => $value) {
				$tableArr = explode('.', $key);
				$table = $tableArr[0];
				$column = $tableArr[1];
				$result[$table][$column] = $value;
			}
		}
		return $result;
	}

	/**
	 * 关联查询条件处理
	 * @param $query
	 * @param $params
	 * @param string $op_type
	 * @return mixed
	 */
	public function withQuery($query, $params, $op_type = 'where')
	{
		foreach ($params as $table => $column) {
			foreach ($column as $col => $val) {
				switch ($op_type) {
					case 'where':
						$query = $query->with($table)->whereHas($table, function ($query2) use ($col, $val) {
							$query2->where($col, $val);
						});
						break;
					case 'whereIn':
						$query = $query->with($table)->whereHas($table, function ($query2) use ($col, $val) {
							$query2->whereIn($col, $val);
						});
						break;
					case 'like':
						$query = $query->with($table)->whereHas($table, function ($query2) use ($col, $val) {
							$query2->where($col, 'like', $val);
						});
						break;
					case 'between':
						$query = $query->with($table)->whereHas($table, function ($query2) use ($table, $col, $val) {
							$doTable =$query2->getModel()->getTable();
							$valStart = $val['start'];
							$valEnd   = $val['end'];
							if (!empty($valStart) && !empty($valEnd)) {
								$query2->whereBetween($doTable.'.'.$col, [$valStart, $valEnd]);
							} else if (!empty($valStart) && empty($valEnd)) {
								$query2->where($doTable.'.'.$col, '>=', $valStart);
							} else if (empty($valStart) && !empty($valEnd)) {
								$query2->where($doTable.'.'.$col, '<=', $valEnd);
							}
						});
						break;
					case 'or':
						$query = $query->with($table)->orWhereHas($table, function ($query2) use ($col, $val) {
							$query2->where($col, $val);
						});
						break;
					default:
						break;
				}
			}
		}
		return $query;
	}
}