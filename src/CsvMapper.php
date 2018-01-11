<?php

namespace MbData;

abstract class CsvMapper implements CsvMapperInterface
{
    protected $header;
    protected $columnProperties;
    protected $indexed;

    public function addColumnProperty($column, $label, $keywords, $csvColumn = '', $index =  null)
    {
        $this->columnProperties[$column] = (object) compact('column', 'label', 'keywords', 'csvColumn', 'index');

        return $this;
    }

    public function getColumnProperties()
    {
        return $this->columnProperties;
    }

    public function setColumnProperties($properties)
    {
        $this->columnProperties = $properties;

        return $this;
    }

    public function __construct()
    {
        //
    }

    public function map($header)
    {
        $this->header = $header;

        $this->parseColumns($header);

        return $this->columnProperties;
    }

    private function parseColumns($columns)
    {
        foreach ($columns as $i => $name) {
            foreach ($this->columnProperties as $key => $property) {
                $search = explode(' ', $name);
                $found  = false;

                foreach ($search as $word) {
                    foreach ($property->keywords as $keyword) {
                        preg_match("/{$keyword}/i", $word) && $found = true;
                    }
                }

                $found && $this->columnProperties[$key]->csvColumn = $name;

                if ($found) {
                    $this->columnProperties[$key]->csvColumn = $name;
                    $this->columnProperties[$key]->index     = $i;
                }
            }
        }
    }

    public function extract($row)
    {
        $data = [];

        foreach ($row as $index => $value) {
            $property = $this->getByIndex($index);

            $property && $data[$property->column] = $value;
        }

        return $data;
    }

    public function getByIndex($index)
    {
        if (! $this->indexed) {
            $this->indexed = [];

            foreach ($this->columnProperties as $property) {
                $property->index !== null && $this->indexed[$property->index] = $property;
            }
        }

        return array_key_exists($index, $this->indexed) ? $this->indexed[$index] : null;
    }
}
