<?php

namespace App\Modules\Rooms\Http\Resources\RoomLocationMedia;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Rooms\Domain\Enums\RoomLocationMediaTypeEnum;
use App\Modules\Rooms\Domain\Enums\RoomLocationMediaFileTypeEnum;
use App\Modules\Rooms\Http\Resources\Api\RoomLocation\RoomLocationResource;

class RoomLocationMediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->when($this->type, [
                'key' => $this->type?->value ?? '',
                'label' => $this->type?->name ?? '',
            ]),
            'file' => $this->file_link,
            'alt' => $this->alt,
            'file_type' => [
                'key' => $this->file_type?->value ?? $this->determineFileType($this->file_link),
                'label' => $this->file_type?->label() ?? $this->getFileTypeLabel($this->file_link),
            ],
            'room_location' => $this->roomLocation ? [
                'id' => $this->roomLocation->id,
                'titles' => [
                    'ar' => $this->roomLocation->translations
                        ->where('locale', 'ar')
                        ->first()
                        ->title ?? '',
                    'en' => $this->roomLocation->translations
                        ->where('locale', 'en')
                        ->first()
                        ->title ?? '',
                ],
            ] : null,
            // 'status' => (bool) $this->status,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Determine file type from file extension
     */
    protected function determineFileType(?string $filePath): string
    {
        if (empty($filePath)) {
            return 'unknown';
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg', 'png', 'gif', 'webp' => 'image',
            'mp4', 'webm', 'mov', 'avi', 'mkv' => 'video',
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx' => 'document',
            'mp3', 'wav', 'ogg' => 'audio',
            default => 'other',
        };
    }

    /**
     * Get human-readable file type label
     */
    protected function getFileTypeLabel(?string $filePath): string
    {
        $type = $this->determineFileType($filePath);

        return match ($type) {
            'image' => 'صورة',
            'video' => 'فيديو',
            'document' => 'ملف',
            'audio' => 'صوت',
            default => 'ملف آخر',
        };
    }
}
