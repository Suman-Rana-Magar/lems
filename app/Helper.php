<?php

namespace App;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as LengthAwarePaginate;

trait Helper
{
    public function pagination(LengthAwarePaginator $paginated)
    {
        return [
            'total' => $paginated->total(),
            'last_page' => $paginated->lastPage(),
            'per_page' => (int)$paginated->perPage(),
            'current_page' => $paginated->currentPage(),
            'path' => \request()->path(),
        ];
    }

    public function paginateRequest($request, $class, $resource = null, $select = null, $relation = null)
    {
        $per_page = count($class::all());
        $current_page = config('custom.current_page');
        if (isset($request['current_page'])) {
            $per_page = isset($request['per_page']) ? $request['per_page'] : 20;
            if (isset($request['current_page'])) $current_page = $request['current_page'];
        }
        if ($select) {
            if ($relation) $chhetras = $class::with($relation)->select($select)->paginate($per_page, '*', null, $current_page);
            else $chhetras = $class::select($select)->paginate($per_page, '*', null, $current_page);
        } else {
            if ($relation) $chhetras = $class::with($relation)->paginate($per_page, '*', null, $current_page);
            else $chhetras = $class::paginate($per_page, '*', null, $current_page);
        }
        $response = $this->pagination($chhetras);
        $response['data'] = isset($resource) ? $resource::collection($chhetras) : $chhetras;
        return $response;
    }

    public function arrayPaginateRequest($request, $data)
    {
        if (empty($data)) $data = [null];
        $per_page = count($data);
        $current_page = config('custom.current_page');
        if (isset($request['current_page'])) {
            $per_page = isset($request['per_page']) ? $request['per_page'] : 20;
            if (isset($request['current_page'])) $current_page = $request['current_page'];
        }
        $chhetras = new LengthAwarePaginate(collect($data)->forPage($current_page, $per_page), count($data), $per_page, $current_page);
        $response = $this->pagination($chhetras);
        $response['data'] = is_null($chhetras->values()->last()) ? [] : $chhetras->values();
        return $response;
    }
}
