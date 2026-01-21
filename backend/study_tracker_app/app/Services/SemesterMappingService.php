<?php

namespace App\Services;

use Illuminate\Support\Str;

class SemesterMappingService
{
    /**
     * Common variations of terms mapped to standard names.
     */
    protected array $termMappings = [
        'fall' => 'Fall',
        'spring' => 'Spring',
        'summer' => 'Summer',
        'winter' => 'Winter',
        'sem1' => 'Semester 1',
        'sem2' => 'Semester 2',
        'term1' => 'Semester 1',
        'term2' => 'Semester 2',
        'term3' => 'Semester 3',
        'first semester' => 'Semester 1',
        'second semester' => 'Semester 2',
        'q1' => 'Quarter 1',
        'q2' => 'Quarter 2',
        'q3' => 'Quarter 3',
        'q4' => 'Quarter 4',
    ];

    /**
     * Standardize a semester string.
     * Example: "term 1 2024" -> "Semester 1 2024"
     */
    public function standardize(string $input): string
    {
        $input = strtolower(trim($input));

        // Extract year if present
        $year = '';
        if (preg_match('/\b(20\d{2})\b/', $input, $matches)) {
            $year = $matches[1];
            $input = str_replace($year, '', $input);
            $input = trim($input);
        }

        // Try to find a mapping
        foreach ($this->termMappings as $variation => $standard) {
            if (str_contains($input, $variation)) {
                return $standard . ($year ? " $year" : "");
            }
        }

        // If no mapping found, just capitalize
        return Str::headline($input) . ($year ? " $year" : "");
    }
}
