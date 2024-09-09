<?php

namespace App\Support\Presenters;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Container\BindingResolutionException;

class CollectionPresenter implements Presenter
{
    /**
     * @param $data
     * @throws BindingResolutionException
     * @return array
     */
    public function present($data): array
    {
        return $this->presentCollection($data, $data->groupBy, $data->keyBy);
    }

    /**
     * Вывести представление обычной коллекции.
     *
     * @param Collection $collection
     * @param $groupBy
     * @throws BindingResolutionException
     * @return array
     */
    private function presentCollection(Collection $collection, $groupBy = null, $keyBy = null): array
    {
        $itemPresenter = null;

        if (method_exists($collection, 'getItemPresenter')) {
            $itemPresenter = $collection->getItemPresenter();
            if (is_string($itemPresenter)) {
                $itemPresenter = app()->make($itemPresenter);
            }
        }

        $collection = $collection->map(fn ($data) => $this->presentBy($data, $itemPresenter));

        if ($groupBy) {
            $collection = $collection->filter(fn ($item) => !empty($item[$groupBy]))->groupBy($groupBy);
        }

        if ($keyBy) {
            $collection = $collection->keyBy($keyBy);
        }

        return $collection->toArray();
    }

    /**
     * Вернуть представление объекта используя переданный презентер.
     * @param $data
     * @param Presenter|null $presenter
     * @throws BindingResolutionException
     * @return mixed
     */
    private function presentBy($data, ?Presenter $presenter): mixed
    {
        if (!is_null($presenter)) {
            return $presenter->present($data);
        }

        if ($data instanceof Arrayable) {
            return $data->toArray();
        }

        return $data;
    }
}
