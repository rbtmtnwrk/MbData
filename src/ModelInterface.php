<?php
namespace MbData;

interface ModelInterface
{
    public function relationLoaded($relation);

    public function getRelations();
}

/* End of file */
