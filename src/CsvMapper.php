<?php

namespace MbData;

abstract class CsvMapper implements CsvMapperInterface
{
    protected $header;
    protected $columnProperties;

    public function addColumnProperty($column, $label, $keywords, $csvColumn = '')
    {
        $this->columnProperties[$column] = (object) compact('column', 'label', 'keywords', 'csvColumn');

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

        return $this;
    }

    private function parseColumns($columns)
    {
        foreach ($columns as $name) {
            foreach ($this->columnProperties as $key => $property) {
                $search = explode(' ', $name);
                $found  = false;

                foreach ($search as $word) {
                    foreach ($property->keywords as $keyword) {
                        preg_match("/{$keyword}/i", $word) && $found = true;
                    }
                }

                $found && $this->columnProperties[$key]->csvColumn = $name;
            }
        }
    }
}
