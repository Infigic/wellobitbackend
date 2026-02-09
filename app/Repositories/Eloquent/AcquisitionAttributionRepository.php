<?php

namespace App\Repositories\Eloquent;

use App\Models\AcquisitionAttribution;
use App\Repositories\Contracts\AcquisitionAttributionInterface;

class AcquisitionAttributionRepository implements AcquisitionAttributionInterface
{
    public function create(array $data)
    {
        return AcquisitionAttribution::create($data);
    }

}
