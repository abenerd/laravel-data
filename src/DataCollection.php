<?php

namespace Spatie\LaravelData;

use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Collection;
use IteratorAggregate;
use Spatie\LaravelData\Concerns\IncludeableData;
use Spatie\LaravelData\Concerns\ResponsableData;
use Spatie\LaravelData\Transformers\DataCollectionTransformer;

class DataCollection implements Responsable, Arrayable, Jsonable, IteratorAggregate, Countable, ArrayAccess
{
    use ResponsableData;
    use IncludeableData;

    private ?Closure $through = null;

    private ?Closure $filter = null;

    private array | AbstractPaginator | CursorPaginator $items;

    public function __construct(
        private string $dataClass,
        Collection | array | AbstractPaginator | CursorPaginator $items
    ) {
        $this->items = $items instanceof Collection ? $items->all() : $items;
    }

    public function through(Closure $through): static
    {
        $this->through = $through;

        return $this;
    }

    public function filter(Closure $filter): static
    {
        $this->filter = $filter;

        return $this;
    }

    public function items(): Collection | array | AbstractPaginator | CursorPaginator
    {
        return $this->items;
    }

    public function all(): array
    {
        return $this->getTransformer()->withoutValueTransforming()->transform();
    }

    public function toArray(): array
    {
        return $this->getTransformer()->transform();
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    public function getIterator(): ArrayIterator
    {
        $items = $this->getTransformer()->withoutValueTransforming()->transform();

        return new ArrayIterator($items);
    }

    public function count()
    {
        return count($this->items);
    }

    public function offsetExists($offset): bool
    {
        return match (true) {
            is_array($this->items) => array_key_exists($offset, $this->items),
            $this->items instanceof AbstractPaginator, $this->items instanceof AbstractCursorPaginator => array_key_exists($offset, $this->items->items())
        };
    }

    public function offsetGet($offset): Data
    {
        $item = match (true) {
            is_array($this->items) => $this->items[$offset],
            $this->items instanceof AbstractPaginator, $this->items instanceof AbstractCursorPaginator => $this->items->items()[$offset]
        };

        return $item instanceof Data
            ? $item
            : $this->dataClass::create($item);
    }

    public function offsetSet($offset, $value): void
    {
        match (true) {
            is_array($this->items) => $this->items[$offset] = $value,
            default => throw new Exception('Cannot update a value in a paginated collection')
        };
    }

    public function offsetUnset($offset): void
    {
        if (is_array($this->items)) {
            unset($this->items[$offset]);

            return;
        }

        throw new Exception('Unsetting in paginated collection is prohibited');
    }

    private function getTransformer(): DataCollectionTransformer
    {
        return new DataCollectionTransformer(
            $this->dataClass,
            $this->getInclusionTree(),
            $this->getExclusionTree(),
            $this->items,
            $this->through,
            $this->filter
        );
    }
}
