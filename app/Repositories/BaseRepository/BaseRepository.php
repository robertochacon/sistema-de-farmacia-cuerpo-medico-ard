<?php

namespace App\Repositories\BaseRepository;

use Illuminate\Database\Eloquent\Model;

class BaseRepository
{
    private $model;
    private $relations;

    public function __construct(Model $model, array $relations = [])
    {
        $this->model = $model;
        $this->relations = $relations;
    }

    public function all()
    {
        return $this->model->with($this->relations)->get();
    }

    public function paginate(int $perPage = 15)
    {
        return $this->model->with($this->relations)->paginate($perPage);
    }

    public function get(int $id)
    {
        return $this->model->with($this->relations)->find($id);
    }

    public function filter(array $filters)
    {
        $query = $this->model->newQuery();

        foreach ($filters as $key => $value) {
            $query->where($key, $value);
        }

        return $query->with($this->relations)->get();
    }

    public function save(Model $model)
    {
        return $model->save();
    }

    public function update(array $data, int $id)
    {
        $model = $this->model->find($id);

        if (!$model) {
            return false;
        }

        $model->fill($data);
        $model->save();

        return $model;
    }

    public function delete(Model $model)
    {
        return $model->delete();
    }
}
