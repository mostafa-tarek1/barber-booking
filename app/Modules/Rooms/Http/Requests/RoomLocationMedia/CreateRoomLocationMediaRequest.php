<?php

namespace App\Modules\Rooms\Http\Requests\RoomLocationMedia;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Base\Http\Rules\ImageBase64OrFileRule;
use App\Modules\Rooms\Application\DTOS\RoomLocationMedia\RoomLocationMediaDTO;
use App\Modules\Rooms\Domain\Enums\RoomLocationMediaFileTypeEnum;
use App\Modules\Rooms\Domain\Enums\RoomLocationMediaTypeEnum;
use Illuminate\Validation\Rule;

class CreateRoomLocationMediaRequest extends BaseRequestAbstract
{
    protected $dtoClass = RoomLocationMediaDTO::class;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $fileType = null;
        $extension = null;

        // Handle file upload
        if ($this->hasFile('file')) {
            $file = $this->file('file');
            $extension = strtolower($file->getClientOriginalExtension());
        }
        // Handle base64 file
        elseif ($this->has('file') && is_string($this->file) && strpos($this->file, 'data:') === 0) {
            $mime = explode(';', $this->file)[0];
            $extension = str_replace('data:', '', explode('/', $mime)[1] ?? '');
        }

        if ($extension) {
            $fileType = $this->getFileTypeFromExtension($extension);
            $this->merge(['file_type' => $fileType]);
        }
    }

    /**
     * Get the file type from file extension
     */
    protected function getFileTypeFromExtension(string $extension): int
    {
        return match (strtolower($extension)) {
            'jpg', 'jpeg', 'png', 'gif', 'webp' => RoomLocationMediaFileTypeEnum::IMAGE->value,
            'mp4', 'webm', 'mov', 'avi', 'mkv' => RoomLocationMediaFileTypeEnum::VIDEO->value,
            'mp3', 'wav', 'ogg' => RoomLocationMediaFileTypeEnum::AUDIO->value,
            default => RoomLocationMediaFileTypeEnum::OTHER->value,
        };
    }

    public function CustomRules(): array
    {
        return [
            'room_location_id' => 'required|exists:room_locations,id',
            'type' => ['required', Rule::enum(RoomLocationMediaTypeEnum::class)],
            'title' => 'nullable|string',
            'alt' => 'nullable|string',
            'file' => [
                'required',
            ],
            'file_type' => ['nullable', 'integer', Rule::in(array_column(RoomLocationMediaFileTypeEnum::cases(), 'value'))],
            // 'status' => 'boolean',
        ];
    }
}
