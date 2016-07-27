<?php

namespace MbData;

interface EloquentRepositoryInterface
{
    public function getModel();

    public function setModel($model);

    public function getBuilder();
}

/* End of file */
