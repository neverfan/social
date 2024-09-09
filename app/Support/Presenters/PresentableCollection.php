<?php

namespace App\Support\Presenters;

use Illuminate\Database\Eloquent\Collection;

/**
 * Class PresentableCollection.
 *
 * @package App\Support\Presenters
 */
class PresentableCollection extends Collection implements Presentable
{
    use PresentableTrait;

    public ?string $groupBy = null;

    public ?string $keyBy = null;

    protected ?string $itemPresenter = null;

    /**
     * Презентер для коллекции.
     *
     * @return string
     */
    protected $presenter = CollectionPresenter::class;

    public function setItemPresenter(string $presenter): self
    {
        $this->itemPresenter = $presenter;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getItemPresenter(): ?string
    {
        return $this->itemPresenter;
    }

    public function setItems($items = []): void
    {
        $this->items = $this->getArrayableItems($items);
    }

    public function setKeyBy(string $keyBy): self
    {
        $this->keyBy = $keyBy;

        return $this;
    }
}
