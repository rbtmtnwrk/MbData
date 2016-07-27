# MbData

MbData is a library that contains classes for data storage and transformation. The repository class is an Eloquent repository.

- [Repositories](#repositories)
    - [Repository Transformers](#repository-transformers)
    - [then Methods](#then-methods)
    - [Available Methods](#available-methods)
- [Transformers](#transformers)
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

## Available Methods

The goal is to implement all or most of the methods available in the Eloquent builder, but for now these are currently available:

```
getColumns($columns = null)

setColumns($columns)

getModel()

setModel($model)

getTransformer()

setTransformer($transformer)

transform()

find($id, $columns = null)

all($columns = null)

first($columns = null)

where($column, $operator = null, $value = null)

whereIn($column, $values)

orWhere($column, $operator = null, $value = null)

whereHas($relation, \Closure $callback)

whereDoesntHave($relation, \Closure $callback = null)

orWhereHas($relation, \Closure $callback)

orderBy($column, $direction = 'asc')

with($with)

getBuilder()

create($data)

save($data, $entity = null)

thenTransform()

thenGet()

update($data)

delete()

reset() // Via the \Mb\ResetableTrait

```

## Transformers

To create a transformer extend the <code>AbstractTransformer</code> class:

```
class FooTransformer extends AbstractTransformer
{

}
```

To specify properties that are direct transformations, ie <code>"property" => $model->property</code>, use the <code>setProperties</code> method:

```
class FooTransformer extends AbstractTransformer
{
    public function __construct()
    {
        $this->setProperties(['id', 'foo']);
    }
}

// Which creates these properties in the transformed array:

$fooTransformed = [
    'id'  => [foo "id" value],
    'foo' => [foo "foo" value],
]
```

To create specific transforms, override the <code>build</code> method with the <code>$model</code> as the parameter:

```
class FooTransformer extends AbstractTransformer
{
    public function __construct()
    {
        $this->setProperties(['id', 'foo']);
    }

    public function build($model)
    {
        $array = parent::build($model);
        $array['foo_special'] = doSomethingSpecial(model->$foo);

        return $array;
    }
}
```

### Transformer Relations

Create a relation using the setRelation method, specifying the relation name that is in the model, and the transformer to be used. In this example we are injecting the related transformer in the constructor:

```
class FooTransformer extends AbstractTransformer
{
    public function __construct(\BarTransformer $barTransformer)
    {
        $this->setProperties(['id', 'foo']);
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
        $this->setProperties(['id', 'foo']);

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
