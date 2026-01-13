<?php

namespace App\Http\Controllers;

use App\Http\Requests\Performance\CreateResultRequest;
use App\Http\Requests\Performance\UpdateResultRequest;
use App\Http\Traits\ApiResponse;
use App\Models\AcademicResult;
use App\Models\Course;
use App\Services\GpaCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Str;

class PerformanceController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected GpaCalculationService $gpaService
    ) {}

    /**
     * Get all academic results.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AcademicResult::where('user_id', $request->user()->id)
            ->with('course');

        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->has('semester')) {
            $query->where('semester', $request->semester);
        }

        if ($request->has('from_date')) {
            $query->where('date', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('date', '<=', $request->to_date);
        }

        if ($request->has('assessment_type')) {
            $query->where('assessment_type', $request->assessment_type);
        }

        $results = $query->paginate($request->input('per_page', 10));

        return $this->successResponse([
            'results' => $results->items(),
            'pagination' => [
                'total' => $results->total(),
                'per_page' => $results->perPage(),
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
            ],
        ]);
    }

    /**
     * Add new academic result.
     */
    public function store(CreateResultRequest $request): JsonResponse
    {
        $result = AcademicResult::create([
            'user_id' => $request->user()->id,
            ...$request->validated(),
        ]);

        $result->load('course');

        return $this->successResponse(['result' => $result], 'Result added successfully', 201);
    }

    /**
     * Bulk upload results (CSV).
     */
    public function bulkUpload(Request $request): JsonResponse
    {
        Log::info('Bulk Upload Request:', [
            'has_file' => $request->hasFile('file'),
            'all_files' => $request->allFiles(),
            'content_type' => $request->header('Content-Type'),
        ]);

        if (!$request->hasFile('file')) {
            Log::error('No file present');
            return $this->errorResponse('No file uploaded', [], 422);
        }

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        // Safely get MIME type - handle case where fileinfo extension is not available
        $mimeType = null;
        try {
            $mimeType = $file->getMimeType();
        } catch (\Exception $e) {
            // If fileinfo extension is not available, we'll rely on file extension validation
            Log::warning('MIME type detection failed (fileinfo extension may be missing): ' . $e->getMessage());
        }

        Log::info('File Details:', [
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $mimeType,
            'extension' => $extension,
            'size' => $file->getSize(),
            'path' => $file->getPathname(),
            'is_valid' => $file->isValid(),
        ]);

        if (!in_array($extension, ['csv', 'txt', 'pdf'])) {
             return $this->errorResponse(
                 "Invalid file type. Only CSV, TXT, and PDF files are allowed. You uploaded a {$extension} file.",
                 ['uploaded_extension' => $extension, 'allowed_extensions' => ['csv', 'txt', 'pdf']],
                 422
             );
        }

        try {
            DB::beginTransaction();

            $handle = null;
            $isPdf = ($extension === 'pdf');

            if ($isPdf) {
                // Parse PDF and extract text
                try {
                    $parser = new Parser();
                    $pdf = $parser->parseFile($file->getPathname());
                    $text = $pdf->getText();
                } catch (\Exception $e) {
                    Log::error('PDF parsing error: ' . $e->getMessage());
                    throw new \Exception('Failed to parse PDF file: ' . $e->getMessage());
                }
                
                if (empty(trim($text))) {
                    throw new \Exception('PDF appears to be empty or contains no extractable text. Please ensure the PDF contains readable text.');
                }
                
                // Log extracted text for debugging (first 1000 chars)
                Log::info('PDF extracted text preview:', [
                    'preview' => substr($text, 0, 1000),
                    'total_length' => strlen($text)
                ]);
                
                // Convert PDF text to CSV-like format
                // Try to detect table structure in PDF
                $lines = explode("\n", $text);
                $csvContent = [];
                $potentialTableRows = [];
                
                // First pass: collect all potential table rows
                foreach ($lines as $lineNum => $line) {
                    $originalLine = $line;
                    $line = trim($line);
                    
                    if (empty($line)) continue;
                    
                    // Skip lines that look like headers/footers (all caps, dates, page numbers)
                    if (preg_match('/^(PAGE|DATE|TOTAL|GRAND TOTAL|\d{1,2}\/\d{1,2}\/\d{2,4})/i', $line)) {
                        continue;
                    }
                    
                    // Skip semester/year headers (e.g., "Year 1: Semester 1", "Year 2: Semester 2")
                    if (preg_match('/^Year\s+\d+:\s*Semester\s+\d+/i', $line)) {
                        continue;
                    }
                    
                    // Skip summary rows (e.g., "CWA: 67.00 Total Credits: 21")
                    if (preg_match('/CWA:\s*\d+\.?\d*\s*Total\s*Credits:/i', $line) ||
                        preg_match('/^CWA:/i', $line) ||
                        preg_match('/Total\s*Credits:\s*\d+/i', $line)) {
                        continue;
                    }
                    
                    // Skip rows that start with formatting characters (e.g., "l X c c c Code...")
                    if (preg_match('/^[l1]\s+[Xx]\s+[cC]\s+[cC]\s+[cC]/', $line)) {
                        continue;
                    }
                    
                    // Skip header rows that contain column names but no data
                    // Check for patterns like "Code Course Title Credits Mark Grade" or similar
                    $headerKeywords = ['code', 'course', 'title', 'credits', 'mark', 'grade', 'score'];
                    $headerKeywordCount = 0;
                    foreach ($headerKeywords as $keyword) {
                        if (preg_match('/\b' . preg_quote($keyword, '/') . '\b/i', $line)) {
                            $headerKeywordCount++;
                        }
                    }
                    // If line contains 3+ header keywords and no numeric data (likely a header row)
                    if ($headerKeywordCount >= 3 && !preg_match('/\b\d{2,}\b/', $line)) {
                        continue;
                    }
                    
                    // Skip very short lines (likely not table data)
                    if (strlen($line) < 5) {
                        continue;
                    }
                    
                    // Check if line looks like table data
                    $isTableRow = false;
                    $processedLine = null;
                    $words = [];

                    // Method 1: Line contains commas
                    if (strpos($line, ',') !== false) {
                        $processedLine = preg_replace('/\s*,\s*/', ',', $line);
                        $isTableRow = true;
                    }
                    // Method 2: Line has tabs
                    elseif (strpos($line, "\t") !== false) {
                        $processedLine = str_replace("\t", ',', $line);
                        $isTableRow = true;
                    }
                    // Method 3: Line has multiple spaces (2 or more consecutive spaces)
                    elseif (preg_match('/\s{2,}/', $line)) {
                        // Replace multiple spaces with single comma, but preserve single spaces within fields
                        $processedLine = preg_replace('/\s{2,}/', ',', $line);
                        $isTableRow = true;
                    }
                    // Method 4: Line contains numeric patterns that suggest table data
                    // Look for patterns like: text followed by numbers, or numbers separated by spaces
                    elseif (preg_match('/\b\d+\.?\d*\s+\d+\.?\d*\b/', $line) || 
                            preg_match('/[A-Z]{2,}\d{3,4}/', $line) || // Course codes like CS101, MATH201
                            preg_match('/\b\d+\/\d+\b/', $line)) { // Fractions like 85/100
                        // Try to split on single spaces if we detect numeric patterns
                        // But be more careful - only if line has at least 3 "words"
                        $words = preg_split('/\s+/', $line);
                        if (count($words) >= 3) {
                            $processedLine = implode(',', $words);
                            $isTableRow = true;
                        }
                    }

                // For PDFs, always split into words for special format detection
                if ($isPdf && empty($words)) {
                    $words = preg_split('/\s+/', $line);
                }

                // Special handling for the specific PDF format seen in logs:
                // Pattern: "CS101 Intro to Programming 3 72 B"
                // Code (1 word) + Name (multiple words) + Credits (1 num) + Score (1 num) + Grade (1 char/word)
                if ($isPdf && count($words) >= 5) {
                    $lastIndex = count($words) - 1;
                    $grade = $words[$lastIndex];
                    $score = $words[$lastIndex - 1];
                    $credits = $words[$lastIndex - 2];

                    // Check if last parts match expected pattern (Grade, Score:Number, Credits:Number)
                    if (is_numeric($score) && is_numeric($credits)) {
                        $code = $words[0];
                        $name = implode(' ', array_slice($words, 1, $lastIndex - 3)); // Extract property

                        // Construct a CSV line with explicit comma separators
                        $processedLine = "{$code},{$name},{$credits},{$score},{$grade}";
                        $isTableRow = true;
                    }
                }

                if ($isTableRow && $processedLine) {
// ...
                        $potentialTableRows[] = [
                            'line_num' => $lineNum + 1,
                            'original' => $originalLine,
                            'processed' => $processedLine,
                            'field_count' => count(explode(',', $processedLine))
                        ];
                    }
                }
                
                // Second pass: Filter and validate potential table rows
                // Group by field count to find the most common structure
                $fieldCounts = [];
                foreach ($potentialTableRows as $row) {
                    $count = $row['field_count'];
                    $fieldCounts[$count] = ($fieldCounts[$count] ?? 0) + 1;
                }
                
                // Find the most common field count (likely the table structure)
                $mostCommonFieldCount = null;
                $maxCount = 0;
                foreach ($fieldCounts as $count => $frequency) {
                    if ($frequency > $maxCount && $count >= 3) { // At least 3 fields
                        $maxCount = $frequency;
                        $mostCommonFieldCount = $count;
                    }
                }
                
                // If we found a common structure, filter rows to match it (with some tolerance)
                if ($mostCommonFieldCount !== null) {
                    $tolerance = 1; // Allow ±1 field difference
                    foreach ($potentialTableRows as $row) {
                        if (abs($row['field_count'] - $mostCommonFieldCount) <= $tolerance) {
                            $csvContent[] = $row['processed'];
                        }
                    }
                } else {
                    // No clear structure, accept all potential rows
                    foreach ($potentialTableRows as $row) {
                        $csvContent[] = $row['processed'];
                    }
                }
                
                // Log what we extracted
                Log::info('PDF table extraction results:', [
                    'total_lines_processed' => count($lines),
                    'potential_rows_found' => count($potentialTableRows),
                    'rows_accepted' => count($csvContent),
                    'field_count_distribution' => $fieldCounts,
                    'most_common_field_count' => $mostCommonFieldCount,
                    'sample_rows' => array_slice($csvContent, 0, 5)
                ]);
                
                if (empty($csvContent)) {
                    // Provide more helpful error message with extracted text sample
                    $sampleText = implode("\n", array_slice($lines, 0, 20));
                    throw new \Exception(
                        'Could not extract table data from PDF. ' .
                        'The PDF may not contain a recognizable table structure, or the table format is not supported. ' .
                        'Please ensure the PDF contains a table with course results in a readable format ' .
                        '(comma-separated, tab-separated, or space-separated columns). ' .
                        'Extracted text sample: ' . substr($sampleText, 0, 500)
                    );
                }
                
                // Create a temporary CSV string
                $csvString = implode("\n", $csvContent);
                Log::info('Generated CSV content length: ' . strlen($csvString));
                $handle = fopen('php://temp', 'r+');
                fwrite($handle, $csvString);
                rewind($handle);
            } else {
                $handle = fopen($file->getPathname(), 'r');
            }
            
            if ($handle === false) {
                throw new \Exception('Unable to open file');
            }

            // Detect CSV delimiter by reading first few lines
            $delimiter = ',';
            $sample = '';
            $position = ftell($handle);
            for ($i = 0; $i < 3; $i++) {
                $line = fgets($handle);
                if ($line !== false) {
                    $sample .= $line;
                }
            }
            rewind($handle);
            
            // Count occurrences of common delimiters
            $delimiters = [',', ';', "\t", '|'];
            $delimiterCounts = [];
            foreach ($delimiters as $delim) {
                $delimiterCounts[$delim] = substr_count($sample, $delim);
            }
            
            // Use the delimiter with the most occurrences (but at least 2 to be valid)
            $maxCount = max($delimiterCounts);
            if ($maxCount >= 2) {
                $delimiter = array_search($maxCount, $delimiterCounts);
            }
            
            Log::info('Detected CSV delimiter:', ['delimiter' => $delimiter === "\t" ? 'TAB' : $delimiter, 'counts' => $delimiterCounts]);

            // Read first row (potential header)
            $firstRow = fgetcsv($handle, 0, $delimiter);
            if (!$firstRow) {
                throw new \Exception('Empty file or invalid format');
            }

            // Store current position to rewind if needed
            $headerPosition = ftell($handle);
            
            // Try to determine if first row is header or data
            $isHeaderRow = false;
            $headerMap = [];
            
            // Check if first row looks like headers (contains text keywords, not all numeric)
            $textKeywords = ['course', 'subject', 'code', 'score', 'marks', 'grade', 'assessment', 'type', 'name', 'semester', 'term', 'date'];
            $hasTextKeywords = false;
            $allNumeric = true;
            
            foreach ($firstRow as $col) {
                $colLower = strtolower(trim($col));
                foreach ($textKeywords as $keyword) {
                    if (strpos($colLower, $keyword) !== false) {
                        $hasTextKeywords = true;
                        break 2;
                    }
                }
                // Check if column is numeric (might be data)
                if (!empty($col) && !is_numeric(trim($col))) {
                    $allNumeric = false;
                }
            }
            
            $isHeaderRow = $hasTextKeywords || (!$allNumeric && count(array_filter($firstRow, function($v) { return !empty(trim($v)); })) >= 3);
            
            if ($isHeaderRow) {
                $header = $firstRow;
                Log::info('Detected header row:', ['headers' => $header]);
            } else {
                // First row is data, try to infer headers from position
                // Rewind and treat first row as data
                rewind($handle);
                $header = null;
                Log::info('First row appears to be data, attempting to infer column positions');
            }

            // Normalize headers to snake_case and map to expected fields
            if ($header) {
                foreach ($header as $index => $col) {
                    $col = trim($col);
                    if (empty($col)) continue;
                    
                    $cleanCol = strtolower(str_replace([' ', '-', '_', '.', '(', ')', ':', '/'], '', $col));
                    
                    // Course name variations (check for "Course Title" or similar patterns first)
                    if (preg_match('/coursetitle|course.*title|title/i', $col) && 
                        !preg_match('/code|id|number/i', $col)) {
                        if (!isset($headerMap['course_name'])) {
                            $headerMap['course_name'] = $index;
                        }
                    }
                    // Course name variations (most flexible - but not code)
                    elseif (preg_match('/course|subject|module|unit/i', $col) && 
                        !preg_match('/code|id|number|title/i', $col)) {
                        if (!isset($headerMap['course_name'])) {
                            $headerMap['course_name'] = $index;
                        }
                    }
                    // Course code variations
                    elseif (preg_match('/coursecode|course.*code|^code$|course.*id|subject.*code/i', $col)) {
                        $headerMap['course_code'] = $index;
                    }
                    // Score variations
                    elseif (preg_match('/score|marks?|obtained|points?|grade.*score/i', $col) && 
                            !preg_match('/max|total|out.*of|maximum/i', $col)) {
                        if (!isset($headerMap['score'])) {
                            $headerMap['score'] = $index;
                        }
                    }
                    // Max score variations
                    elseif (preg_match('/max.*score|maximum|total|out.*of|full.*marks?|max.*marks?/i', $col)) {
                        $headerMap['max_score'] = $index;
                    }
                    // Assessment type variations
                    elseif (preg_match('/assessment.*type|type|exam.*type|evaluation.*type/i', $col)) {
                        $headerMap['assessment_type'] = $index;
                    }
                    // Assessment name variations
                    elseif (preg_match('/assessment.*name|name|title|exam.*name|test.*name/i', $col)) {
                        $headerMap['assessment_name'] = $index;
                    }
                    // Weight variations
                    elseif (preg_match('/weight|percentage|contribution|worth/i', $col)) {
                        $headerMap['weight'] = $index;
                    }
                    // Semester variations
                    elseif (preg_match('/semester|term|session|period/i', $col)) {
                        $headerMap['semester'] = $index;
                    }
                    // Date variations
                    elseif (preg_match('/date|completed.*at|taken.*on|submitted/i', $col)) {
                        $headerMap['date'] = $index;
                    }
                    // Notes variations
                    elseif (preg_match('/notes?|comments?|description|remarks?/i', $col)) {
                        $headerMap['notes'] = $index;
                    }
                }
            }
            
            // If headers weren't found or first row was data, try to infer from data patterns
            // Note: assessment_type is optional, will default to 'assignment' if not found
            if ((!isset($headerMap['course_name']) && !isset($headerMap['course_code'])) ||
                !isset($headerMap['score']) ||
                !isset($headerMap['max_score'])) {
                
                Log::info('Header mapping incomplete, attempting to infer from data patterns', [
                    'found_mappings' => $headerMap,
                    'first_row' => $firstRow
                ]);
                
                // Rewind to start and read a few rows to analyze
                rewind($handle);
                $sampleRows = [];
                for ($i = 0; $i < 5; $i++) {
                    $row = fgetcsv($handle, 0, $delimiter);
                    if ($row && !empty(array_filter($row))) {
                        $sampleRows[] = $row;
                    }
                }
                
                if (count($sampleRows) > 0) {
                    $columnCount = count($sampleRows[0]);
                    
                    // First pass: Look for "85/100" pattern (score/max_score in one column)
                    for ($colIndex = 0; $colIndex < $columnCount; $colIndex++) {
                        $sampleValue = trim($sampleRows[0][$colIndex] ?? '');
                        if (preg_match('/^(\d+\.?\d*)\s*\/\s*(\d+\.?\d*)$/', $sampleValue, $matches)) {
                            // Found score/max_score pattern
                            if (!isset($headerMap['score'])) {
                                $headerMap['score'] = $colIndex;
                                Log::info("Inferred score at column $colIndex (from pattern like '85/100')");
                            }
                            if (!isset($headerMap['max_score'])) {
                                $headerMap['max_score'] = $colIndex; // Same column, will split later
                                Log::info("Inferred max_score at column $colIndex (from pattern like '85/100')");
                            }
                        }
                    }
                    
                    // Second pass: Analyze each column position for other patterns
                    for ($colIndex = 0; $colIndex < $columnCount; $colIndex++) {
                        $columnValues = array_filter(array_column($sampleRows, $colIndex));
                        if (empty($columnValues)) continue;
                        
                        $sampleValue = trim($columnValues[0] ?? '');
                        $isNumeric = is_numeric($sampleValue);
                        $isCourseCode = preg_match('/^[A-Z]{2,}\d{3,4}$/i', $sampleValue);
                        $isCourseName = !$isNumeric && !$isCourseCode && strlen($sampleValue) > 3 && 
                                        preg_match('/[A-Za-z]{3,}/', $sampleValue);
                        $hasSlash = strpos($sampleValue, '/') !== false && 
                                   !isset($headerMap['score']); // Already handled above
                        
                        // Infer course code (alphanumeric codes like CS101, MATH201)
                        if (!isset($headerMap['course_code']) && $isCourseCode) {
                            $headerMap['course_code'] = $colIndex;
                            Log::info("Inferred course_code at column $colIndex");
                        }
                        // Infer course name (text that's not a code and not numeric)
                        elseif (!isset($headerMap['course_name']) && $isCourseName && 
                                !isset($headerMap['course_code'])) {
                            $headerMap['course_name'] = $colIndex;
                            Log::info("Inferred course_name at column $colIndex");
                        }
                        // Infer score (numeric values, but not if already found or if it's max_score)
                        elseif (!isset($headerMap['score']) && $isNumeric) {
                            // Check if next column is also numeric (might be max_score)
                            $nextIsNumeric = false;
                            if ($colIndex + 1 < $columnCount) {
                                $nextValue = trim($sampleRows[0][$colIndex + 1] ?? '');
                                $nextIsNumeric = is_numeric($nextValue);
                            }
                            
                            // If next column is also numeric, this is likely score and next is max_score
                            if ($nextIsNumeric && !isset($headerMap['max_score'])) {
                                $headerMap['score'] = $colIndex;
                                $headerMap['max_score'] = $colIndex + 1;
                                Log::info("Inferred score at column $colIndex and max_score at column " . ($colIndex + 1));
                            } elseif (!$nextIsNumeric) {
                                // Only this column is numeric, might be score or max_score
                                // Prefer score if not set
                                if (!isset($headerMap['score'])) {
                                    $headerMap['score'] = $colIndex;
                                    Log::info("Inferred score at column $colIndex");
                                } elseif (!isset($headerMap['max_score'])) {
                                    $headerMap['max_score'] = $colIndex;
                                    Log::info("Inferred max_score at column $colIndex");
                                }
                            }
                        }
                        // Infer assessment_type (look for common assessment type keywords)
                        if (!isset($headerMap['assessment_type'])) {
                            $lowerValue = strtolower($sampleValue);
                            $assessmentKeywords = [
                                'quiz', 'midterm', 'mid-term', 'mid term', 'final', 'assignment', 
                                'project', 'exam', 'test', 'lab', 'laboratory', 'homework', 'hw',
                                'assignment', 'essay', 'presentation', 'report'
                            ];
                            foreach ($assessmentKeywords as $keyword) {
                                if (strpos($lowerValue, $keyword) !== false) {
                                    $headerMap['assessment_type'] = $colIndex;
                                    Log::info("Inferred assessment_type at column $colIndex (matched keyword: $keyword)");
                                    break;
                                }
                            }
                        }
                    }
                }
                
                // Rewind again to start processing
                rewind($handle);
                // Skip header row if we had one
                if ($isHeaderRow) {
                    fgetcsv($handle, 0, $delimiter);
                }
            } else {
                // Headers were found, rewind to after header
                if ($isHeaderRow) {
                    // Already read header, position is correct
                } else {
                    rewind($handle);
                }
            }

            // Log final header mapping
            Log::info('Final header mapping:', [
                'header_map' => $headerMap,
                'detected_headers' => $header ?? 'none (inferred)'
            ]);

            // Check required mappings (assessment_type and max_score are optional)
            if ((!isset($headerMap['course_name']) && !isset($headerMap['course_code'])) ||
                !isset($headerMap['score'])) {
                
                $missing = [];
                if (!isset($headerMap['course_name']) && !isset($headerMap['course_code'])) {
                    $missing[] = 'course_name/code';
                }
                if (!isset($headerMap['score'])) $missing[] = 'score';
                
                // Note: assessment_type and max_score are optional
                if (!isset($headerMap['assessment_type'])) {
                    Log::info('assessment_type column not found, will default to "assignment" for all rows');
                }
                if (!isset($headerMap['max_score'])) {
                    Log::info('max_score column not found, will default to 100 for all rows');
                }
                
                // Log detailed information for debugging
                Log::error('Missing required columns in bulk upload', [
                    'missing' => $missing,
                    'detected_columns' => $header ?? [],
                    'header_mapping' => $headerMap,
                    'file_extension' => $extension,
                    'first_row_sample' => $firstRow ?? null
                ]);
                
                return $this->errorResponse(
                    'Missing required columns: ' . implode(', ', $missing) . '. ' .
                    'Found columns: ' . ($header ? implode(', ', $header) : 'none detected') . '. ' .
                    'Please ensure your file has columns for course name/code and score. ' .
                    'Note: max_score is optional and will default to 100 if not provided. ' .
                    'Note: assessment_type is optional and will default to "assignment" if not provided.',
                    [
                        'missing_columns' => $missing,
                        'detected_columns' => $header ?? [],
                        'header_mapping' => $headerMap,
                        'file_type' => $extension
                    ],
                    422
                );
            }
            
            // Log if optional fields are missing (will use defaults)
            if (!isset($headerMap['assessment_type'])) {
                Log::info('assessment_type column not found in CSV, will default to "assignment" for all imported rows');
            }
            if (!isset($headerMap['max_score'])) {
                Log::info('max_score column not found in CSV, will default to 100 for all imported rows');
            }

            $userId = $request->user()->id;
            $rowNumber = 1; // Start from 1 (header is 0, but logical row 1)
            $importedCount = 0;
            $errors = [];

            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $rowNumber++;

                // Skip empty rows
                if (empty(array_filter($row))) continue;

                $rowData = [];
                // Extract data using map
                foreach ($headerMap as $key => $index) {
                    $rowData[$key] = isset($row[$index]) ? trim($row[$index]) : null;
                }
                
                // Handle case where course name might be split across multiple columns
                // This happens when PDF parsing splits "Intro to Programming" into separate columns
                $startIndex = null;
                $endIndex = null;
                
                // Determine where to start looking for course name
                if (isset($headerMap['course_code'])) {
                    $startIndex = $headerMap['course_code'] + 1;
                } elseif (isset($headerMap['course_name'])) {
                    $startIndex = $headerMap['course_name'];
                }
                
                // If we have a course_code but course_name is missing or very short, try to combine
                if (!empty($rowData['course_code']) && (empty($rowData['course_name']) || strlen($rowData['course_name']) < 5)) {
                    $combinedName = [];
                    
                    // Look for consecutive text columns after the course code
                    for ($i = $startIndex ?? 0; $i < count($row); $i++) {
                        // Skip if this column is already mapped to something important
                        $isMapped = false;
                        foreach ($headerMap as $mappedKey => $mappedIndex) {
                            if ($mappedIndex == $i && in_array($mappedKey, ['score', 'max_score', 'assessment_type', 'assessment_name', 'weight', 'semester', 'date', 'credits'])) {
                                $isMapped = true;
                                break;
                            }
                        }
                        if ($isMapped) break;
                        
                        $cellValue = trim($row[$i] ?? '');
                        // If it's numeric or empty, stop combining
                        if (empty($cellValue) || is_numeric($cellValue)) {
                            break;
                        }
                        // If it's a word (likely part of course name), add it
                        if (strlen($cellValue) > 0 && strlen($cellValue) < 30 && preg_match('/^[A-Za-z\s&]+$/', $cellValue)) {
                            $combinedName[] = $cellValue;
                        } else {
                            break;
                        }
                    }
                    
                    if (!empty($combinedName)) {
                        $rowData['course_name'] = implode(' ', $combinedName);
                        Log::info("Combined course name from multiple columns: {$rowData['course_name']}");
                    }
                }
                // If course_name is set but might be incomplete (only first word), try to extend it
                elseif (!empty($rowData['course_name']) && isset($headerMap['course_name']) && strlen($rowData['course_name']) < 15) {
                    $courseNameIndex = $headerMap['course_name'];
                    $combinedName = [$rowData['course_name']];
                    
                    // Look for additional text columns after the mapped course_name
                    for ($i = $courseNameIndex + 1; $i < count($row); $i++) {
                        // Skip if this column is already mapped to something important
                        $isMapped = false;
                        foreach ($headerMap as $mappedKey => $mappedIndex) {
                            if ($mappedIndex == $i && in_array($mappedKey, ['score', 'max_score', 'assessment_type', 'assessment_name', 'weight', 'semester', 'date', 'credits', 'course_code'])) {
                                $isMapped = true;
                                break;
                            }
                        }
                        if ($isMapped) break;
                        
                        $cellValue = trim($row[$i] ?? '');
                        // If it's numeric or empty, stop combining
                        if (empty($cellValue) || is_numeric($cellValue)) {
                            break;
                        }
                        // If it's a word (likely part of course name), add it
                        if (strlen($cellValue) > 0 && strlen($cellValue) < 30 && preg_match('/^[A-Za-z\s&]+$/', $cellValue)) {
                            $combinedName[] = $cellValue;
                        } else {
                            break;
                        }
                    }
                    
                    if (count($combinedName) > 1) {
                        $rowData['course_name'] = implode(' ', $combinedName);
                        Log::info("Extended course name from multiple columns: {$rowData['course_name']}");
                    }
                }

                // Handle case where score and max_score are in the same column (e.g., "85/100")
                if (isset($headerMap['score']) && isset($headerMap['max_score']) && 
                    $headerMap['score'] === $headerMap['max_score'] && 
                    !empty($rowData['score'])) {
                    $scoreValue = $rowData['score'];
                    if (preg_match('/^(\d+\.?\d*)\s*\/\s*(\d+\.?\d*)$/', $scoreValue, $matches)) {
                        $rowData['score'] = $matches[1];
                        $rowData['max_score'] = $matches[2];
                        Log::info("Split score/max_score from pattern: $scoreValue -> score={$rowData['score']}, max_score={$rowData['max_score']}");
                    }
                }

                // 1. Find Course
                $courseQuery = Course::where('user_id', $userId);
                if (!empty($rowData['course_code'])) {
                    $courseQuery->where('code', $rowData['course_code']);
                } elseif (!empty($rowData['course_name'])) {
                    $courseQuery->where('name', $rowData['course_name']);
                }

                $course = $courseQuery->first();
                if (!$course) {
                    $courseName = $rowData['course_name'] ?? 'Imported Course';
                    $courseCode = $rowData['course_code'] ?? null;

                    // Auto-create missing course
                    if ($courseCode && $courseName !== 'Imported Course') {
                        // We have both
                        $courseNameForCreation = $courseName;
                        $courseCodeForCreation = $courseCode;
                    } elseif ($courseCode) {
                        // Only code
                        $courseCodeForCreation = $courseCode;
                        $courseNameForCreation = "Course $courseCode";
                    } elseif ($courseName !== 'Imported Course') {
                        // Only name, generate code
                        $courseNameForCreation = $courseName;
                        // Generate code from initials (e.g. "Intro to Prog" -> "ITP100")
                        $initials = preg_replace('/[^A-Z]/', '', ucwords($courseName));
                        $courseCodeForCreation = (strlen($initials) > 0 ? $initials : 'CRS') . rand(100, 999);
                    } else {
                        $errors[] = "Row {$rowNumber}: Cannot create course - missing name and code";
                        continue;
                    }

                    Log::info("Auto-creating course: $courseNameForCreation ($courseCodeForCreation)");

                    $course = Course::create([
                        'user_id' => $userId,
                        'name' => $courseNameForCreation,
                        'code' => $courseCodeForCreation,
                        'credits' => isset($rowData['credits']) && is_numeric($rowData['credits']) ? $rowData['credits'] : 3,
                        'color' => '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT), // Random color
                        'semester' => $rowData['semester'] ?? 'Unknown',
                        'is_active' => true
                    ]);
                }

                // 2. Validate Row Data
                // Check if score is empty or missing first (but allow 0 as valid score)
                if (!isset($rowData['score']) || (is_string($rowData['score']) && trim($rowData['score']) === '') || $rowData['score'] === null) {
                    $errors[] = "Row {$rowNumber}: Score is required and cannot be empty";
                    continue;
                }
                
                // Convert score and max_score to numeric if they're strings
                if (isset($rowData['score']) && !is_numeric($rowData['score'])) {
                    $rowData['score'] = filter_var($rowData['score'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                }
                if (isset($rowData['max_score']) && !is_numeric($rowData['max_score'])) {
                    $rowData['max_score'] = filter_var($rowData['max_score'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                }
                
                // Default max_score to 100 if not provided
                if (empty($rowData['max_score']) || !is_numeric($rowData['max_score'])) {
                    $rowData['max_score'] = 100;
                    Log::info("Row {$rowNumber}: max_score not found or invalid, defaulting to 100");
                }
                
                if (!is_numeric($rowData['score'])) {
                    $errors[] = "Row {$rowNumber}: Score must be numeric (found: score='{$rowData['score']}')";
                    continue;
                }

                // Default values
                $semester = !empty($rowData['semester']) ? $rowData['semester'] : 'Unknown';
                $date = !empty($rowData['date']) ? date('Y-m-d', strtotime($rowData['date'])) : now()->format('Y-m-d');
                
                // Validate assessment_type
                $assessmentType = $rowData['assessment_type'] ?? 'assignment';
                $validAssessmentTypes = ['quiz', 'midterm', 'final', 'assignment', 'project', 'exam', 'test', 'lab'];
                if (!in_array(strtolower($assessmentType), $validAssessmentTypes)) {
                    // Try to normalize common variations
                    $normalized = strtolower($assessmentType);
                    if (in_array($normalized, ['homework', 'hw'])) {
                        $assessmentType = 'assignment';
                    } elseif (in_array($normalized, ['mid', 'mid-term', 'mid term'])) {
                        $assessmentType = 'midterm';
                    } else {
                        // Default to assignment if not recognized
                        $assessmentType = 'assignment';
                        Log::warning("Unknown assessment_type '{$rowData['assessment_type']}' in row {$rowNumber}, defaulting to 'assignment'");
                    }
                }

                // Create Result
                AcademicResult::create([
                    'user_id' => $userId,
                    'course_id' => $course->id,
                    'assessment_type' => $assessmentType,
                    'assessment_name' => $rowData['assessment_name'] ?? 'Bulk Upload Import',
                    'score' => floatval($rowData['score']),
                    'max_score' => floatval($rowData['max_score']),
                    'percentage' => (floatval($rowData['score']) / floatval($rowData['max_score'])) * 100,
                    'weight' => !empty($rowData['weight']) ? floatval($rowData['weight']) : 0,
                    'semester' => $semester,
                    'date' => $date,
                    'notes' => $rowData['notes'] ?? 'Imported via bulk upload'
                ]);

                $importedCount++;
            }

            if ($handle) {
                fclose($handle);
            }

            if (count($errors) > 0) {
                DB::rollBack(); // "All or Nothing" policy
                Log::error('Bulk upload validation errors', [
                    'error_count' => count($errors),
                    'errors' => $errors,
                    'imported_count' => $importedCount
                ]);
                return $this->errorResponse(
                    'Bulk upload failed due to validation errors', 
                    [
                        'row_errors' => $errors,
                        'total_errors' => count($errors),
                        'successful_imports' => $importedCount
                    ], 
                    422
                );
            }

            DB::commit();

            return $this->successResponse([
                'total_uploaded' => $importedCount,
                'successful' => $importedCount,
                'failed' => 0,
                'errors' => []
            ], "Successfully imported {$importedCount} results", 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk upload exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            // Return more detailed error information
            $errorData = [
                'exception_message' => $e->getMessage(),
                'exception_type' => get_class($e)
            ];
            
            return $this->errorResponse(
                'Bulk upload failed: ' . $e->getMessage(), 
                $errorData, 
                422
            );
        }
    }

    /**
     * Bulk upload PDF files for academic results.
     */
    public function bulkUploadPdfs(Request $request): JsonResponse
    {
        // Log request details for debugging
        Log::info('Bulk PDF Upload Request:', [
            'has_pdf_files' => $request->hasFile('pdf_files'),
            'all_files' => array_keys($request->allFiles()),
            'all_input' => array_keys($request->all()),
            'content_type' => $request->header('Content-Type'),
            'pdf_files_count' => $request->hasFile('pdf_files') ? count($request->file('pdf_files')) : 0,
        ]);

        // Get files - Laravel handles pdf_files[] array notation automatically
        $files = $request->file('pdf_files');
        
        // Handle case where files might be sent without array notation or as single file
        if (!$files) {
            // Try alternative: check if files are sent as pdf_files[0], pdf_files[1], etc.
            $allFiles = $request->allFiles();
            $files = [];
            foreach ($allFiles as $key => $file) {
                if (strpos($key, 'pdf_files') !== false) {
                    if (is_array($file)) {
                        $files = array_merge($files, $file);
                    } else {
                        $files[] = $file;
                    }
                }
            }
        }

        // If still no files, return error
        if (empty($files)) {
            Log::error('No PDF files found in request', [
                'all_files' => $request->allFiles(),
                'all_input_keys' => array_keys($request->all()),
            ]);
            return $this->errorResponse(
                'No PDF files uploaded. Please select at least one PDF file.',
                ['received_files' => array_keys($request->allFiles())],
                422
            );
        }

        // Convert to array if single file
        if (!is_array($files)) {
            $files = [$files];
        }

        // Validate course_id and semester if provided
        $validator = Validator::make($request->only(['course_id', 'semester']), [
            'course_id' => 'nullable|uuid|exists:courses,id',
            'semester' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            Log::error('PDF upload validation failed', [
                'errors' => $validator->errors()->toArray(),
            ]);
            return $this->errorResponse(
                'Validation failed',
                ['errors' => $validator->errors()->toArray()],
                422
            );
        }

        // Manual validation for files
        $fileErrors = [];
        foreach ($files as $index => $file) {
            if (!$file || !$file->isValid()) {
                $fileErrors[] = "File " . ($index + 1) . ": Invalid or missing file";
                continue;
            }

            // Check file size (5MB = 5120 KB)
            if ($file->getSize() > 5 * 1024 * 1024) {
                $fileErrors[] = "File " . ($index + 1) . " ({$file->getClientOriginalName()}): File size exceeds 5MB limit";
                continue;
            }

            $extension = strtolower($file->getClientOriginalExtension());
            
            // Check file extension
            if ($extension !== 'pdf') {
                $fileErrors[] = "File " . ($index + 1) . " ({$file->getClientOriginalName()}): Must be a PDF file (found extension: {$extension})";
                continue;
            }

            // Try to validate MIME type if fileinfo extension is available
            try {
                $mimeType = $file->getMimeType();
                if ($mimeType && $mimeType !== 'application/pdf') {
                    $fileErrors[] = "File " . ($index + 1) . " ({$file->getClientOriginalName()}): Invalid MIME type '{$mimeType}'";
                }
            } catch (\Exception $e) {
                // fileinfo extension not available - rely on extension validation only
                Log::info('MIME type validation skipped (fileinfo extension may be missing)', [
                    'file' => $file->getClientOriginalName(),
                    'extension' => $extension
                ]);
            }
        }

        if (!empty($fileErrors)) {
            Log::error('PDF file validation failed', [
                'errors' => $fileErrors,
            ]);
            return $this->errorResponse(
                'File validation failed',
                ['errors' => $fileErrors],
                422
            );
        }

        $userId = $request->user()->id;
        $uploadedFiles = [];
        $errors = [];

        try {
            DB::beginTransaction();

            // Files are already retrieved above
            $courseId = $request->input('course_id');
            $semester = $request->input('semester', 'Unknown');

            // If course_id is not provided, get the first course for the user
            if (!$courseId) {
                $course = Course::where('user_id', $userId)->first();
                if (!$course) {
                    DB::rollBack();
                    return $this->errorResponse(
                        'No course found. Please create a course first or provide a course_id.',
                        [],
                        422
                    );
                }
                $courseId = $course->id;
            }

            foreach ($files as $index => $file) {
                try {
                    // Validate file
                    if (!$file->isValid()) {
                        $errors[] = "File " . ($index + 1) . " ({$file->getClientOriginalName()}): Invalid file";
                        continue;
                    }

                    // Store file in storage/app/public/academic_results
                    $filePath = $file->store('academic_results', 'public');

                    // Create database entry for each PDF
                    // Note: This creates a minimal entry with the file_path
                    // The user can later extract data from the PDF or manually fill in details
                    $result = AcademicResult::create([
                        'user_id' => $userId,
                        'course_id' => $courseId,
                        'assessment_type' => 'assignment', // Default, can be updated later
                        'assessment_name' => $file->getClientOriginalName(),
                        'score' => 0, // Default, to be filled later
                        'max_score' => 100, // Default
                        'percentage' => 0, // Default
                        'semester' => $semester,
                        'date' => now()->format('Y-m-d'),
                        'notes' => 'Uploaded via bulk PDF upload',
                        'file_path' => $filePath,
                    ]);

                    $uploadedFiles[] = [
                        'id' => $result->id,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $filePath,
                        'size' => $file->getSize(),
                    ];

                    Log::info("PDF uploaded successfully", [
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $filePath,
                        'result_id' => $result->id
                    ]);
                } catch (\Exception $e) {
                    $errors[] = "File " . ($index + 1) . " ({$file->getClientOriginalName()}): " . $e->getMessage();
                    Log::error("Failed to upload PDF file", [
                        'file_name' => $file->getClientOriginalName(),
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            if (count($errors) > 0 && count($uploadedFiles) === 0) {
                // All files failed
                DB::rollBack();
                return $this->errorResponse(
                    'All PDF uploads failed',
                    ['errors' => $errors],
                    422
                );
            }

            DB::commit();

            $message = count($uploadedFiles) . ' PDF file(s) uploaded successfully';
            if (count($errors) > 0) {
                $message .= ', ' . count($errors) . ' file(s) failed';
            }

            return $this->successResponse([
                'uploaded' => $uploadedFiles,
                'errors' => $errors,
                'total_uploaded' => count($uploadedFiles),
                'total_failed' => count($errors),
            ], $message, 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk PDF upload exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return $this->errorResponse(
                'Bulk PDF upload failed: ' . $e->getMessage(),
                ['exception_message' => $e->getMessage()],
                422
            );
        }
    }

    /**
     * Get single result.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $result = AcademicResult::where('user_id', $request->user()->id)
            ->with('course')
            ->findOrFail($id);

        return $this->successResponse(['result' => $result]);
    }

    /**
     * Update result.
     */
    public function update(UpdateResultRequest $request, string $id): JsonResponse
    {
        $result = AcademicResult::where('user_id', $request->user()->id)->findOrFail($id);
        $result->update($request->validated());
        $result->load('course');

        return $this->successResponse(['result' => $result], 'Result updated successfully');
    }

    /**
     * Delete result.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $result = AcademicResult::where('user_id', $request->user()->id)->findOrFail($id);
        $result->delete();

        return $this->successResponse(null, 'Result deleted successfully');
    }

    /**
     * Get GPA trend over time.
     */
    public function gpaTrend(Request $request): JsonResponse
    {
        $period = $request->input('period', 'all');
        $trend = $this->gpaService->getGpaTrend($request->user(), $period);

        return $this->successResponse($trend);
    }

    /**
     * Get subject-wise performance.
     */
    public function subjects(Request $request): JsonResponse
    {
        $semester = $request->input('semester');
        $subjects = $this->gpaService->getSubjectPerformance($request->user(), $semester);

        return $this->successResponse(['subjects' => $subjects]);
    }

    /**
     * Bulk store academic results from PDF files.
     * Extracts data from PDFs using text parsing and RegEx.
     */
    public function bulkStore(Request $request): JsonResponse
    {
        Log::info('Bulk Store PDF Request:', [
            'has_pdf_files' => $request->hasFile('pdf_files'),
            'all_files' => array_keys($request->allFiles()),
            'content_type' => $request->header('Content-Type'),
        ]);

        // Validate that pdf_files is an array of PDFs
        $validator = Validator::make($request->all(), [
            'pdf_files' => 'required|array|min:1',
            'pdf_files.*' => 'required|file|mimes:pdf|max:5120', // 5MB max per file
        ]);

        if ($validator->fails()) {
            Log::error('PDF files validation failed', [
                'errors' => $validator->errors()->toArray(),
            ]);
            return $this->errorResponse(
                'Validation failed',
                ['errors' => $validator->errors()->toArray()],
                422
            );
        }

        $files = $request->file('pdf_files');
        
        // Convert to array if single file
        if (!is_array($files)) {
            $files = [$files];
        }

        $userId = $request->user()->id;
        $processedCount = 0;
        $errors = [];
        $results = [];

        try {
            DB::beginTransaction();

            foreach ($files as $index => $file) {
                try {
                    // Validate file
                    if (!$file->isValid()) {
                        $errors[] = "File " . ($index + 1) . " ({$file->getClientOriginalName()}): Invalid file";
                        continue;
                    }

                    // Store file
                    $filePath = $file->store('academic_results', 'public');

                    // Extract text from PDF
                    $parser = new Parser();
                    $pdf = $parser->parseFile($file->getPathname());
                    $text = $pdf->getText();

                    if (empty(trim($text))) {
                        $errors[] = "File " . ($index + 1) . " ({$file->getClientOriginalName()}): PDF appears to be empty or contains no extractable text";
                        continue;
                    }

                    Log::info('PDF extracted text preview:', [
                        'file' => $file->getClientOriginalName(),
                        'preview' => substr($text, 0, 500),
                        'total_length' => strlen($text)
                    ]);

                    // Extract semester using RegEx
                    $semester = $this->extractSemester($text);
                    
                    // Extract GPA values using RegEx
                    $gpaValues = $this->extractGpaValues($text);
                    
                    // Extract course results using RegEx
                    $courseResults = $this->extractCourseResults($text);

                    Log::info('Extracted data from PDF:', [
                        'file' => $file->getClientOriginalName(),
                        'semester' => $semester,
                        'gpa_values' => $gpaValues,
                        'course_results_count' => count($courseResults)
                    ]);

                    // Process each course result
                    foreach ($courseResults as $courseData) {
                        // Find or create course
                        $course = $this->findOrCreateCourse($userId, $courseData);

                        // Create academic result
                        $result = AcademicResult::create([
                            'user_id' => $userId,
                            'course_id' => $course->id,
                            'assessment_type' => $courseData['assessment_type'] ?? 'assignment',
                            'assessment_name' => $courseData['assessment_name'] ?? $file->getClientOriginalName(),
                            'score' => $courseData['score'] ?? 0,
                            'max_score' => $courseData['max_score'] ?? 100,
                            'percentage' => $courseData['percentage'] ?? 0,
                            'grade' => $courseData['grade'] ?? null,
                            'weight' => $courseData['weight'] ?? null,
                            'semester' => $semester ?? 'Unknown',
                            'date' => $courseData['date'] ?? now()->format('Y-m-d'),
                            'notes' => 'Extracted from PDF: ' . $file->getClientOriginalName(),
                            'file_path' => $filePath,
                        ]);

                        $results[] = $result;
                        $processedCount++;
                    }

                    // If no course results were extracted, create a placeholder entry
                    if (empty($courseResults)) {
                        $course = Course::where('user_id', $userId)->first();
                        if (!$course) {
                            $course = Course::create([
                                'user_id' => $userId,
                                'name' => 'Imported Course',
                                'code' => 'IMP' . rand(100, 999),
                                'credits' => 3,
                                'color' => '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT),
                                'semester' => $semester ?? 'Unknown',
                                'is_active' => true
                            ]);
                        }

                        $result = AcademicResult::create([
                            'user_id' => $userId,
                            'course_id' => $course->id,
                            'assessment_type' => 'assignment',
                            'assessment_name' => $file->getClientOriginalName(),
                            'score' => 0,
                            'max_score' => 100,
                            'percentage' => 0,
                            'semester' => $semester ?? 'Unknown',
                            'date' => now()->format('Y-m-d'),
                            'notes' => 'Uploaded via bulk PDF upload - data extraction pending',
                            'file_path' => $filePath,
                        ]);

                        $results[] = $result;
                        $processedCount++;
                    }

                } catch (\Exception $e) {
                    $errors[] = "File " . ($index + 1) . " ({$file->getClientOriginalName()}): " . $e->getMessage();
                    Log::error("Failed to process PDF file", [
                        'file_name' => $file->getClientOriginalName(),
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            if (count($errors) > 0 && $processedCount === 0) {
                // All files failed
                DB::rollBack();
                return $this->errorResponse(
                    'All PDF processing failed',
                    ['errors' => $errors],
                    422
                );
            }

            DB::commit();

            $message = "Successfully processed {$processedCount} result(s) from " . count($files) . " PDF file(s)";
            if (count($errors) > 0) {
                $message .= ', ' . count($errors) . ' error(s) occurred';
            }

            return $this->successResponse([
                'processed' => $processedCount,
                'errors' => $errors,
                'total_files' => count($files),
                'results' => array_map(function($r) {
                    return [
                        'id' => $r->id,
                        'course_id' => $r->course_id,
                        'assessment_name' => $r->assessment_name,
                        'score' => $r->score,
                        'max_score' => $r->max_score,
                        'percentage' => $r->percentage,
                        'semester' => $r->semester,
                    ];
                }, $results)
            ], $message, 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk store PDF exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return $this->errorResponse(
                'Bulk PDF processing failed: ' . $e->getMessage(),
                ['exception_message' => $e->getMessage()],
                422
            );
        }
    }

    /**
     * Extract semester from PDF text using RegEx.
     */
    private function extractSemester(string $text): ?string
    {
        // Common semester patterns
        $patterns = [
            // Fall 2024, Spring 2024, etc.
            '/(Fall|Spring|Summer|Winter)\s+(\d{4})/i',
            // Semester 1 2024, Semester 2 2024
            '/Semester\s+(\d+)\s+(\d{4})/i',
            // 2024 Fall, 2024 Spring
            '/(\d{4})\s+(Fall|Spring|Summer|Winter)/i',
            // Fall/2024, Spring/2024
            '/(Fall|Spring|Summer|Winter)\/?(\d{4})/i',
            // S1 2024, S2 2024
            '/S(\d+)\s+(\d{4})/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                if (count($matches) >= 3) {
                    // Format: "Fall 2024" or "2024 Fall"
                    if (preg_match('/^\d{4}$/', $matches[1])) {
                        return $matches[2] . ' ' . ucfirst(strtolower($matches[1]));
                    } else {
                        return ucfirst(strtolower($matches[1])) . ' ' . $matches[2];
                    }
                } elseif (count($matches) >= 2) {
                    // Semester number format
                    if (preg_match('/Semester\s+(\d+)/i', $matches[0], $semMatch)) {
                        return 'Semester ' . $semMatch[1] . ' ' . ($matches[2] ?? date('Y'));
                    }
                }
            }
        }

        return null;
    }

    /**
     * Extract GPA values from PDF text using RegEx.
     */
    private function extractGpaValues(string $text): array
    {
        $gpaValues = [];

        // Patterns for GPA: "GPA: 3.5", "GPA 3.5", "Grade Point Average: 3.5", etc.
        $patterns = [
            '/GPA[:\s]+(\d+\.?\d*)/i',
            '/Grade\s+Point\s+Average[:\s]+(\d+\.?\d*)/i',
            '/CGPA[:\s]+(\d+\.?\d*)/i',
            '/Cumulative\s+GPA[:\s]+(\d+\.?\d*)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $text, $matches)) {
                foreach ($matches[1] as $gpa) {
                    $gpaFloat = floatval($gpa);
                    if ($gpaFloat >= 0 && $gpaFloat <= 4.0) {
                        $gpaValues[] = $gpaFloat;
                    }
                }
            }
        }

        return array_unique($gpaValues);
    }

    /**
     * Extract course results from PDF text using RegEx.
     */
    private function extractCourseResults(string $text): array
    {
        $results = [];
        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Pattern 1: Course code, name, credits, score, grade
            // Example: "CS101 Intro to Programming 3 72 B"
            if (preg_match('/^([A-Z]{2,}\d{3,4})\s+(.+?)\s+(\d+)\s+(\d+\.?\d*)\s+([A-F][+-]?)$/i', $line, $matches)) {
                $results[] = [
                    'course_code' => $matches[1],
                    'course_name' => trim($matches[2]),
                    'credits' => intval($matches[3]),
                    'score' => floatval($matches[4]),
                    'max_score' => 100,
                    'percentage' => floatval($matches[4]),
                    'grade' => strtoupper($matches[5]),
                    'assessment_type' => 'final',
                ];
                continue;
            }

            // Pattern 2: Course code, score/max_score, grade
            // Example: "CS101 85/100 A"
            if (preg_match('/^([A-Z]{2,}\d{3,4})\s+(\d+\.?\d*)\/(\d+\.?\d*)\s+([A-F][+-]?)$/i', $line, $matches)) {
                $score = floatval($matches[2]);
                $maxScore = floatval($matches[3]);
                $results[] = [
                    'course_code' => $matches[1],
                    'score' => $score,
                    'max_score' => $maxScore,
                    'percentage' => ($score / $maxScore) * 100,
                    'grade' => strtoupper($matches[4]),
                    'assessment_type' => 'final',
                ];
                continue;
            }

            // Pattern 3: Course name, score, grade
            // Example: "Mathematics 85 A"
            if (preg_match('/^([A-Z][A-Za-z\s&]+?)\s+(\d+\.?\d*)\s+([A-F][+-]?)$/i', $line, $matches)) {
                $results[] = [
                    'course_name' => trim($matches[1]),
                    'score' => floatval($matches[2]),
                    'max_score' => 100,
                    'percentage' => floatval($matches[2]),
                    'grade' => strtoupper($matches[3]),
                    'assessment_type' => 'final',
                ];
                continue;
            }

            // Pattern 4: Score percentage
            // Example: "CS101 85% A"
            if (preg_match('/^([A-Z]{2,}\d{3,4})\s+(\d+\.?\d*)%\s+([A-F][+-]?)$/i', $line, $matches)) {
                $percentage = floatval($matches[2]);
                $results[] = [
                    'course_code' => $matches[1],
                    'score' => $percentage,
                    'max_score' => 100,
                    'percentage' => $percentage,
                    'grade' => strtoupper($matches[3]),
                    'assessment_type' => 'final',
                ];
                continue;
            }
        }

        return $results;
    }

    /**
     * Find or create a course for the user.
     */
    private function findOrCreateCourse(string $userId, array $courseData): Course
    {
        $courseQuery = Course::where('user_id', $userId);

        if (!empty($courseData['course_code'])) {
            $courseQuery->where('code', $courseData['course_code']);
        } elseif (!empty($courseData['course_name'])) {
            $courseQuery->where('name', $courseData['course_name']);
        }

        $course = $courseQuery->first();

        if (!$course) {
            $courseName = $courseData['course_name'] ?? 'Imported Course';
            $courseCode = $courseData['course_code'] ?? null;

            if (!$courseCode && $courseName !== 'Imported Course') {
                // Generate code from initials
                $initials = preg_replace('/[^A-Z]/', '', ucwords($courseName));
                $courseCode = (strlen($initials) > 0 ? $initials : 'CRS') . rand(100, 999);
            } elseif (!$courseCode) {
                $courseCode = 'IMP' . rand(100, 999);
            }

            $course = Course::create([
                'user_id' => $userId,
                'name' => $courseName,
                'code' => $courseCode,
                'credits' => $courseData['credits'] ?? 3,
                'color' => '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT),
                'semester' => 'Unknown',
                'is_active' => true
            ]);
        }

        return $course;
    }
}

