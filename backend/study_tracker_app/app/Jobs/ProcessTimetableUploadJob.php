<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\TimetableOcrService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessTimetableUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public string $filePath,
        public string $semester
    ) {}

    /**
     * Execute the job.
     */
    public function handle(TimetableOcrService $ocrService): void
    {
        try {
            $file = Storage::disk('public')->get($this->filePath);
            $tempPath = storage_path('app/temp/' . basename($this->filePath));
            file_put_contents($tempPath, $file);

            $uploadedFile = new \Illuminate\Http\UploadedFile(
                $tempPath,
                basename($this->filePath),
                mime_content_type($tempPath),
                null,
                true
            );

            $parsedClasses = $ocrService->processTimetable($uploadedFile);

            // TODO: Create timetable and classes from parsed data
            // For now, create a notification with results

            \App\Models\Notification::create([
                'user_id' => $this->user->id,
                'type' => 'system',
                'title' => 'Timetable Processed',
                'message' => 'Your timetable has been processed. Please review the results.',
                'action_url' => '/timetable',
                'metadata' => ['parsed_classes' => $parsedClasses],
            ]);

            unlink($tempPath);
        } catch (\Exception $e) {
            Log::error("Failed to process timetable upload for user {$this->user->id}: " . $e->getMessage());
            throw $e;
        }
    }
}

