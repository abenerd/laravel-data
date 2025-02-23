<?php

namespace Spatie\LaravelData\Tests\Support\TypeScriptTransformer;

use ReflectionClass;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Support\TypeScriptTransformer\DataTypeScriptTransformer;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\TestCase;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class DataTypeScriptTransformerTest extends TestCase
{
    /** @test */
    public function it_can_covert_a_data_object_to_typescript()
    {
        $config = TypeScriptTransformerConfig::create();

        $data = new class(null, 42, true, 'Hello world', 3.14, ['the', 'meaning', 'of', 'life'], Lazy::create(fn () => 'Lazy'), SimpleData::create('Simple data'), SimpleData::collection([]),) extends Data {
            public function __construct(
                public null | int $nullable,
                public int $int,
                public bool $bool,
                public string $string,
                public float $float,
                /** @var string[] */
                public array $array,
                public Lazy | string $lazy,
                public SimpleData $simpleData,
                /** @var \Spatie\LaravelData\Tests\Fakes\SimpleData[] */
                public DataCollection $dataCollection,
            ) {
            }
        };

        $transformer = new DataTypeScriptTransformer($config);

        $reflection = new ReflectionClass($data);

        $this->assertTrue($transformer->canTransform($reflection));
        $this->assertMatchesSnapshot($transformer->transform($reflection, 'DataObject')->transformed);
    }
}
