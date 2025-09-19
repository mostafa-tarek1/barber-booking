<?php

use App\Modules\Rooms\Http\Controllers\Api\RoomAdditional\RoomAdditionalController;
use App\Modules\Rooms\Http\Controllers\Api\RoomController;
use App\Modules\Rooms\Http\Controllers\Api\RoomLocation\RoomLocationController;
use App\Modules\Rooms\Http\Controllers\Api\RoomLocationMedia\RoomLocationMediaController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    // Room endpoints
    Route::post('fetch_rooms', [RoomController::class, 'fetchRooms']);
    Route::post('fetch_room_details', [RoomController::class, 'fetchRoomDetails']);

    // Room Additional endpoints
    Route::post('fetch_room_additionals', [RoomAdditionalController::class, 'fetchRoomAdditionals']);
    Route::post('fetch_room_additional_details', [RoomAdditionalController::class, 'fetchRoomAdditionalDetails']);
    // Room Location endpoints
    Route::post('fetch_room_locations', [RoomLocationController::class, 'fetchRoomLocations']);
    Route::post('fetch_room_location_details', [RoomLocationController::class, 'fetchRoomLocationDetails']);
    // Room LocationMedia endpoints
    Route::post('fetch_room_location_media', [RoomLocationMediaController::class, 'fetchRoomLocationMedias']);
    Route::post('fetch_room_location_media_details', [RoomLocationMediaController::class, 'fetchRoomLocationMediaDetails']);
});
