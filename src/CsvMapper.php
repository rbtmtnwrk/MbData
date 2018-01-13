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

    /**
     * Maps header row to column properties.
     * @param  array $header
     * @return array
     */
    public function map($header)
    {
        foreach ($header as $i => $name) {
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

        return $this->columnProperties;
    }

    /**
     * Extracts data from row into properties format.
     * @param  array $row
     * @return array
     */
    public function extract($row)
    {
        $data = [];

        foreach ($row as $index => $value) {
            $property = $this->getByIndex($index);

            $property && $data[$property->column] = $value;
        }

        return $data;
    }

    /**
     * Gets propery by column index.
     * @param  int $index
     * @return Object
     */
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
