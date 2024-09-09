<?php

namespace App\Support\Presenters;

/**
 * Trait PresentableModelCollectionTrait.
 * @package App\Support\Presenters
 */
trait PresentableModelCollectionTrait
{
    /**
     * Create a new Presentable Collection instance.
     *
     * @param array $models
     * @return PresentableCollection
     */
    public function newCollection(array $models = []): PresentableCollection
    {
        return new PresentableCollection($models);
    }
}
