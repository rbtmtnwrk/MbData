<?php
namespace MbData;

interface ModelInterface
{
    public function relationLoaded($relation);

    /**
     * Used in AbstractEloquentRepository::secureModel
     */
    public function getRelations();
}

/* End of file */
