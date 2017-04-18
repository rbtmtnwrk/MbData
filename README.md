# MbData

MbData is a library that contains classes for data storage and transformation. The repository class is an Eloquent repository.

- [Repositories](#repositories)
    - [Repository Transformers](#repository-transformers)
    - [then Methods](#then-methods)
    - [then and Relations](#then-and-relations)
    - [Available Methods](#available-methods)
- [Transformers](#transformers)
    - [Named and Runtime Transformations](#named-and-runtime-transformations)
    - [Transformer Relations](#transformer-relations)

## Repositories

To create an Eloquent repository extend <code>AbstractEloquentRepository</code> and inject the model.

```
class EloquentFooRepository extends AbstractEloquentRepository
{
    public function __construct(\Foo $model)
    {
        $this->model = $model;
    }
}
```

### Repository Transformers

To enable transformations, inject a [Transformer](#transformers).

```
class EloquentFooRepository extends AbstractEloquentRepository
{
    public function __construct(\Foo $model, FooTransformer $transformer)
    {
        $this->model       = $model;
        $this->transformer = $transformer;
    }
}
```

 Or use a closure that takes the model as a parameter:

```
 class EloquentFooRepository extends AbstractEloquentRepository
{
    public function __construct(\Foo $model)
    {
        $this->model = $model;

        $this->transformer = function ($model) {
            return [
                'id'  => $model->foo_id,
                'bar' => $model->foo_bar,
            ];
        };
    }
}
```

To return transformed data, use the <code>transform</code> method. Not using the transform method returns the eloquent collection or model:

```
$fooRepository = \App::make('FooRepository');

$transformedFoos = $fooRepository->transform()->all();

$fooEloquentCollection = $fooRepository->all();
```

### then Methods

To return model or transformed data from the save and create methods the the <code>then</code> methods

**Model data**

```
// Save
$fooModel = $FooRepository->save($data)->thenGet();

// Create
$fooModel = $FooRepository->create($data)->thenGet();

```

**Transformed data**

```
// Save
$transformedFoo = $FooRepository->save($data)->thenTransform();

// Create
$transformedFoo = $FooRepository->create($data)->thenTransform();

```
### then and Relations

In cases when you save or create and entity that needs some relational data loaded after the operation, use thenGetWith | thenTransformWith:

```
/**
 * Saves Foo, loads Bar relation with Bar's Baz relation,
 * and Fiz relation, and then returns Foo transformed.
 */

$foo = $FooRepository->newQuery()
    ->where('id', 1)
    ->save('name' => 'Foo Bazzle')
    ->thenTransformWith('bar.baz', 'fiz');

/**
 * Creates new Foo, loads it's Group relation,
 * and then returns Foo transformed.
 */

$newFoo = $FooRepository->newQuery()
    ->create(['name' => 'Foo Dizzle', 'group_id' => 1])
    ->thenTransformWith('group');
```


## Available Methods

```
setColumns($columns)

getColumns()

getTransformer()

setTransformer($transformer)

callTransformerMethod($method, $params = [])

index()

transform()

newQuery()

find($id, $columns = null)

first($columns = null)

get($columns = null)

all($columns = null)

where($column, $operator = null, $value = null)

whereIn($column, $values, $boolean = 'and', $not = false)

whereNotIn($column, $values, $boolean = 'and')

orWhere($column, $operator = null, $value = null)

whereHas($relation, \Closure $callback)

whereDoesntHave($relation, \Closure $callback = null)

orWhereHas($relation, \Closure $callback)

orderBy($column, $direction = 'asc')

join($table, $one, $operator = null, $two = null, $type = 'inner', $where = false)

joinWhere($table, $one, $operator, $two, $type = 'inner')

leftJoin($table, $first, $operator = null, $second = null)

leftJoinWhere($table, $one, $operator, $two)

rightJoin($table, $first, $operator = null, $second = null)

rightJoinWhere($table, $one, $operator, $two)

skip($value)

take($value)

with($with)

load($relations)

create($data)

save($data)

thenTransform($with = null)

thenTransformWith($with)

thenGet($with = null)

thenGetWith($with)

update($data)

delete()
```

## Transformers

To create a transformer extend the <code>AbstractTransformer</code> class:

```
class FooTransformer extends AbstractTransformer
{

}
```

To specify properties to be included in the transformation, ie <code>"property" => $model->property</code>, use the <code>setProperties</code> method:

```
class FooTransformer extends AbstractTransformer
{
    public function __construct()
    {
        $this->setProperties(['id', 'foo']);
    }
}

. . .

// Which creates these properties in the transformed array:

$model = (object) [
    'id' => 1,
    'foo' => 'Foo',
    'bar' => 'Bar',
];

$transformed = $fooTransformer->transform($model);

$transformed == [
    'id'  => 1,
    'foo' => 'Foo',
];
```

To cast properties to a specific type, set properties with a keyed array <code>property => type</code>:

Types currently supported are:

- int
- float
- bool
- string

```
class FooTransformer extends AbstractTransformer
{
    public function __construct()
    {
        $this->setProperties([
            'id' => 'int',
            'foo', // Skips type transformation
         ]);
    }
}

. . .

$model = (object) [
    'id' => '1',
    'foo' => 'Foo',
];

$transformed = $fooTransformer->transform($model);

// gettype($fooTransformed['id'])  == 'integer';
// gettype($fooTransformed['foo']) == 'string';

```


To create specific transforms, override the <code>build</code> method with the <code>$model</code> as the parameter:

```
class FooTransformer extends AbstractTransformer
{
    public function __construct()
    {
        $this->setProperties(['id' => 'int', 'foo']);
    }

    public function build($model)
    {
        $array = parent::build($model);
        $array['foo_special'] = doSomethingSpecial($model->foo);

        return $array;
    }
}
```

### Named and Runtime Transformations

To add additional transformations during runtime, use the <code>addTransforms(\Closure transform)</code> method.

Your closure must accept the <code>model</code> and the transformed <code>array</code>:

```
$foo = new Foo;

$transformer = new FooTransformer;

. . .

// You can pass the array by reference
$transformer->addTransform(function($model, &$array) {
    $array['foo_tastic'] = $model->foo . '-tastic!';
});

// Or return it
$transformer->addTransform(function($model, $array) {
    $array['foo_tastic'] = $model->foo . '-tastic!';

    return $array
});

```

#### Named Transforms

Add a named transform. Useful when you need to check if a transform exists:

```
$foo = new Foo;
$transformer = new FooTransformer;

$closure = function($model, &$array) {
    $array['foo_tastic'] = $model->foo . '-tastic!';
};

// Add a name to the transform
$transformer->addTransform($closure, 'footastic');

. . .

// Then you can check for existence
if (! $transformer->hasTransform('footastic')) {
    throw new Exception('Footastic transform does not exist');
}

```

### Transformer Relations

Create a relation using the setRelation method, specifying the relation name that is in the model, and the transformer to be used. In this example we are injecting the related transformer in the constructor:

```
class FooTransformer extends AbstractTransformer
{
    public function __construct(\BarTransformer $barTransformer)
    {
        $this->setProperties(['id' => 'int', 'foo']);
        $this->setRelation('bar', $barTransformer);
    }

    . . .
}
```

Or you can use a closure that accepts the model as a parameter:

```
class FooTransformer extends AbstractTransformer
{
    public function __construct()
    {
        $this->setProperties(['id' => 'int', 'foo']);

        $this->setRelation('bar', function($model) {
            return [
                'id'   => $model->bar_id,
                'name' => $model->bar_name,
            ];
        });
    }

    . . .
}
```
