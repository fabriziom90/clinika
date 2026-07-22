<?php

namespace App\Services;

use InvalidArgumentException;

class EmployeeCodeGeneratorService
{
    protected array $prefixes = [
        \App\Models\Doctor::class => 'DOC',
        \App\Models\Nurse::class => 'NUR',
        \App\Models\Secretary::class => 'SEC',
    ];

    public function generate(string $modelClass): string
    {

        if (! isset($this->prefixes[$modelClass])) {
            throw new InvalidArgumentException("Nessun prefisso configurato per {$modelClass}");
        }

        $prefix = $this->prefixes[$modelClass];

        $last = $modelClass::query()
            ->where('employee_code', 'like', "{$prefix}%")
            ->orderByDesc('employee_code')
            ->first();

        $lastNumber = 0;

        if ($last) {
            $lastNumber = (int) substr($last->employee_code, strlen($prefix));
        }

        return sprintf('%s%06d', $prefix, $lastNumber + 1);
    }
}
